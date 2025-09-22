import React, { useEffect, useState, useCallback, useRef } from 'react';
import { toast } from 'react-toastify';
import { useNavigate } from 'react-router-dom';
import { apiService } from '../services/api';
import { useAppSelector, useAppDispatch } from '../store';
import { logout } from '../store/slices/authSlice';

// 配置选项 - 可根据需要调整
const SESSION_CONFIG = {
  // 会话超时时间 (毫秒) - 用户不活动超过此时间将被视为不活跃
  TIMEOUT: 30 * 60 * 1000, // 30分钟 (原为15分钟)
  
  // 会话ping间隔 (毫秒) - 多久向服务器发送一次活动状态
  PING_INTERVAL: 15 * 60 * 1000, // 15分钟 (原为5分钟)
  
  // 会话检查间隔 (毫秒) - 多久验证一次会话有效性
  CHECK_INTERVAL: 5 * 60 * 1000, // 5分钟 (原为1分钟)
  
  // 用户活动防抖延迟 (毫秒) - 多久之后才记录用户活动
  ACTIVITY_DEBOUNCE: 5000, // 5秒
  
  // API请求超时时间 (毫秒)
  REQUEST_TIMEOUT: 10000, // 10秒
  
  // 最大连续错误次数 - 达到此次数后暂停检查
  MAX_ERROR_COUNT: 3,
  
  // 错误后恢复时间 (毫秒) - 遇到错误后多久恢复检查
  ERROR_RECOVERY_TIME: 5 * 60 * 1000, // 5分钟
  
  // 是否在开发环境中启用会话管理
  ENABLE_IN_DEV: false,
};

// 用于防抖的工具函数
function debounce<T extends (...args: any[]) => any>(func: T, wait: number): (...args: Parameters<T>) => void {
  let timeout: ReturnType<typeof setTimeout> | null = null;
  return function(...args: Parameters<T>) {
    if (timeout) {
      clearTimeout(timeout);
    }
    timeout = setTimeout(() => {
      func(...args);
    }, wait);
  };
}

// 超时Promise工具函数
const timeoutPromise = <T,>(promise: Promise<T>, ms: number): Promise<T> => {
  return Promise.race([
    promise,
    new Promise<T>((_, reject) => 
      setTimeout(() => reject(new Error(`请求超时 (${ms}ms)`)), ms)
    )
  ]);
};

// 会话管理器组件
const SessionManager: React.FC = () => {
  const { isAuthenticated, loading: authLoading } = useAppSelector(state => state.auth);
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const [lastActivity, setLastActivity] = useState<number>(Date.now());
  const [isActive, setIsActive] = useState<boolean>(true);
  const [errorCount, setErrorCount] = useState<number>(0);
  const [isPaused, setIsPaused] = useState<boolean>(false);
  
  // 使用refs存储计时器ID
  const pingTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const checkTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const recoveryTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  
  // 使用ref存储是否启用会话管理
  const isEnabledRef = useRef<boolean>(
    process.env.NODE_ENV !== 'development' || SESSION_CONFIG.ENABLE_IN_DEV
  );

  // 开发环境日志函数
  const logDebug = useCallback((message: string, data?: any) => {
    if (process.env.NODE_ENV === 'development') {
      console.log(`[SessionManager] ${message}`, data || '');
    }
  }, []);

  // 处理用户活动 - 使用防抖减少更新频率
  const handleUserActivity = useCallback(
    debounce(() => {
      // 只有当状态不活跃时才更新和记录
      if (!isActive) {
        logDebug('User activity resumed');
        setIsActive(true);
      }
      setLastActivity(Date.now());
    }, SESSION_CONFIG.ACTIVITY_DEBOUNCE),
    [isActive, logDebug]
  );

  // 检查用户是否超时
  const checkTimeout = useCallback(() => {
    const now = Date.now();
    const inactiveTime = now - lastActivity;
    
    if (inactiveTime > SESSION_CONFIG.TIMEOUT) {
      if (isActive) {
        logDebug('User inactive timeout');
        setIsActive(false);
        // 可以选择自动登出或显示提醒
        // logout();
      }
    }
  }, [lastActivity, isActive, logDebug]);

  // Ping会话 - 通知服务器用户活动
  const pingSession = useCallback(async () => {
    // 如果未登录、已暂停或错误过多，则跳过
    if (!isAuthenticated || isPaused || errorCount >= SESSION_CONFIG.MAX_ERROR_COUNT) {
      return;
    }
    
    try {
      logDebug('Ping session');
      // 添加超时处理
      await timeoutPromise(
        apiService.auth.ping(),
        SESSION_CONFIG.REQUEST_TIMEOUT
      );
      
      // 成功ping后重置错误计数
      if (errorCount > 0) {
        setErrorCount(0);
      }
    } catch (error) {
      logDebug('Ping session error', error);
      setErrorCount(prev => prev + 1);
      
      // 如果错误过多，暂停会话检查
      if (errorCount + 1 >= SESSION_CONFIG.MAX_ERROR_COUNT) {
        logDebug('Too many errors. Pause session check.');
        setIsPaused(true);
        
        // 设置恢复定时器
        if (recoveryTimerRef.current) {
          clearTimeout(recoveryTimerRef.current);
        }
        recoveryTimerRef.current = setTimeout(() => {
          logDebug('Resume session check.');
          setIsPaused(false);
          setErrorCount(0);
        }, SESSION_CONFIG.ERROR_RECOVERY_TIME);
      }
    }
  }, [isAuthenticated, isPaused, errorCount, logDebug]);

  // 检查会话状态
  const checkSession = useCallback(async () => {
    // 如果未登录、已暂停或错误过多，则跳过
    if (!isAuthenticated || isPaused || errorCount >= SESSION_CONFIG.MAX_ERROR_COUNT) {
      return;
    }
    
    try {
      logDebug('Check session');
      // 添加超时处理
      const response = await timeoutPromise(
        apiService.auth.checkSession(),
        SESSION_CONFIG.REQUEST_TIMEOUT
      );
      
      if (!response.authenticated) {
        logDebug('Session expired. Execute logout.');
        toast.warning('Your session has expired. Please log in again.');
        dispatch(logout());
        navigate('/login');
      } else {
        logDebug('Session is valid.');
        // 成功检查后重置错误计数
        if (errorCount > 0) {
          setErrorCount(0);
        }
      }
    } catch (error) {
      logDebug('Session check error', error);
      setErrorCount(prev => prev + 1);
      
      // 如果错误过多，暂停会话检查
      if (errorCount + 1 >= SESSION_CONFIG.MAX_ERROR_COUNT) {
        logDebug('Too many errors. Pause session check.');
        setIsPaused(true);
        
        // 设置恢复定时器
        if (recoveryTimerRef.current) {
          clearTimeout(recoveryTimerRef.current);
        }
        recoveryTimerRef.current = setTimeout(() => {
          logDebug('Resume session check.');
          setIsPaused(false);
          setErrorCount(0);
        }, SESSION_CONFIG.ERROR_RECOVERY_TIME);
      }
    }
  }, [isAuthenticated, isPaused, errorCount, logDebug, dispatch, navigate]);

  // 设置ping会话定时器
  useEffect(() => {
    // 如果未启用或未登录，则不设置定时器
    if (!isEnabledRef.current || !isAuthenticated) {
      return;
    }
    
    logDebug('Set ping timer.');
    
    // 初始ping - 延迟5秒
    const initialPingTimer = setTimeout(() => {
      pingSession();
    }, 5000);
    
    // 定期ping
    pingTimerRef.current = setInterval(() => {
      pingSession();
    }, SESSION_CONFIG.PING_INTERVAL);
    
    return () => {
      clearTimeout(initialPingTimer);
      if (pingTimerRef.current) {
        clearInterval(pingTimerRef.current);
        pingTimerRef.current = null;
      }
    };
  }, [isAuthenticated, pingSession, logDebug]);

  // 设置检查会话定时器
  useEffect(() => {
    // 如果未启用或未登录，则不设置定时器
    if (!isEnabledRef.current || !isAuthenticated) {
      return;
    }
    
    logDebug('Set check session timer.');
    
    // 初始检查 - 延迟10秒
    const initialCheckTimer = setTimeout(() => {
      checkSession();
    }, 10000);
    
    // 定期检查
    checkTimerRef.current = setInterval(() => {
      checkSession();
    }, SESSION_CONFIG.CHECK_INTERVAL);
    
    return () => {
      clearTimeout(initialCheckTimer);
      if (checkTimerRef.current) {
        clearInterval(checkTimerRef.current);
        checkTimerRef.current = null;
      }
    };
  }, [isAuthenticated, checkSession, logDebug]);

  // 监听用户活动并更新最后活动时间
  useEffect(() => {
    // 如果未启用，则不添加事件监听
    if (!isEnabledRef.current) {
      return;
    }
    
    logDebug('Add user activity listener.');
    
    // 用户活动事件列表
    const userActivityEvents = [
      'mousedown', 'mousemove', 'keydown', 
      'scroll', 'touchstart', 'click', 'focus'
    ];
    
    userActivityEvents.forEach(event => {
      window.addEventListener(event, handleUserActivity);
    });
    
    // 监听页面可见性变化
    const handleVisibilityChange = () => {
      if (!document.hidden) {
        // 页面变为可见时，更新活动时间
        handleUserActivity();
        // 立即检查会话状态
        if (isAuthenticated) {
          checkSession();
        }
      }
    };
    
    document.addEventListener('visibilitychange', handleVisibilityChange);
    
    return () => {
      userActivityEvents.forEach(event => {
        window.removeEventListener(event, handleUserActivity);
      });
      document.removeEventListener('visibilitychange', handleVisibilityChange);
    };
  }, [handleUserActivity, isAuthenticated, checkSession, logDebug]);

  // 定期检查是否超时
  useEffect(() => {
    // 如果未启用，则不设置定时器
    if (!isEnabledRef.current) {
      return;
    }
    
    const timeoutCheckInterval = setInterval(checkTimeout, 60000); // 每分钟检查一次
    
    return () => {
      clearInterval(timeoutCheckInterval);
    };
  }, [checkTimeout]);

  // 登录状态变更时的处理
  useEffect(() => {
    if (isAuthenticated) {
      // 用户登录，立即更新活动时间
      handleUserActivity();
    } else {
      // 用户登出，清除所有定时器
      if (pingTimerRef.current) {
        clearInterval(pingTimerRef.current);
        pingTimerRef.current = null;
      }
      if (checkTimerRef.current) {
        clearInterval(checkTimerRef.current);
        checkTimerRef.current = null;
      }
      if (recoveryTimerRef.current) {
        clearTimeout(recoveryTimerRef.current);
        recoveryTimerRef.current = null;
      }
    }
  }, [isAuthenticated, handleUserActivity]);

  // 组件不渲染任何内容
  return null;
};

export default SessionManager; 