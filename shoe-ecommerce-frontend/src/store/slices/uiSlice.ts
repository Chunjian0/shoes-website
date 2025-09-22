import { createSlice, PayloadAction } from '@reduxjs/toolkit';

// 调试日志函数
const logDebug = (message: string, data?: any) => {
  console.log(`[UISlice] ${message}`, data ? data : '');
};

// UI状态类型定义
interface UIState {
  // 全局加载状态
  isLoading: boolean;
  
  // 移动端菜单状态
  isMobileMenuOpen: boolean;
  
  // 搜索栏状态
  isSearchOpen: boolean;
  
  // 过滤器状态
  isFilterOpen: boolean;
  
  // 排序选项
  sortOption: string;
  
  // 主题模式
  darkMode: boolean;
  
  // 通知
  notifications: {
    id: string;
    type: 'info' | 'success' | 'warning' | 'error';
    message: string;
    autoClose?: boolean;
    duration?: number;
  }[];
  
  // 模态框
  modal: {
    isOpen: boolean;
    type: string | null;
    data: any;
  };
}

// 初始状态
const initialState: UIState = {
  isLoading: false,
  isMobileMenuOpen: false,
  isSearchOpen: false,
  isFilterOpen: false,
  sortOption: 'newest',
  darkMode: false,
  notifications: [],
  modal: {
    isOpen: false,
    type: null,
    data: null
  }
};

// UI切片
const uiSlice = createSlice({
  name: 'ui',
  initialState,
  reducers: {
    // 设置全局加载状态
    setLoading: (state, action: PayloadAction<boolean>) => {
      logDebug('设置加载状态', action.payload);
      state.isLoading = action.payload;
    },
    
    // 切换移动端菜单
    toggleMobileMenu: (state) => {
      logDebug('切换移动端菜单', !state.isMobileMenuOpen);
      state.isMobileMenuOpen = !state.isMobileMenuOpen;
      
      // 关闭其他打开的UI元素
      if (state.isMobileMenuOpen) {
        state.isSearchOpen = false;
        state.isFilterOpen = false;
      }
    },
    
    // 关闭移动端菜单
    closeMobileMenu: (state) => {
      logDebug('关闭移动端菜单');
      state.isMobileMenuOpen = false;
    },
    
    // 切换搜索栏
    toggleSearch: (state) => {
      logDebug('切换搜索栏', !state.isSearchOpen);
      state.isSearchOpen = !state.isSearchOpen;
      
      // 关闭其他打开的UI元素
      if (state.isSearchOpen) {
        state.isMobileMenuOpen = false;
        state.isFilterOpen = false;
      }
    },
    
    // 关闭搜索栏
    closeSearch: (state) => {
      logDebug('关闭搜索栏');
      state.isSearchOpen = false;
    },
    
    // 切换过滤器
    toggleFilter: (state) => {
      logDebug('切换过滤器', !state.isFilterOpen);
      state.isFilterOpen = !state.isFilterOpen;
      
      // 关闭其他打开的UI元素
      if (state.isFilterOpen) {
        state.isMobileMenuOpen = false;
        state.isSearchOpen = false;
      }
    },
    
    // 关闭过滤器
    closeFilter: (state) => {
      logDebug('关闭过滤器');
      state.isFilterOpen = false;
    },
    
    // 设置排序选项
    setSortOption: (state, action: PayloadAction<string>) => {
      logDebug('设置排序选项', action.payload);
      state.sortOption = action.payload;
    },
    
    // 切换主题模式
    toggleDarkMode: (state) => {
      logDebug('切换主题模式', !state.darkMode);
      state.darkMode = !state.darkMode;
      
      // 保存到本地存储
      localStorage.setItem('darkMode', state.darkMode ? 'true' : 'false');
      
      // 应用到HTML元素
      if (state.darkMode) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    },
    
    // 设置主题模式
    setDarkMode: (state, action: PayloadAction<boolean>) => {
      logDebug('设置主题模式', action.payload);
      state.darkMode = action.payload;
      
      // 保存到本地存储
      localStorage.setItem('darkMode', state.darkMode ? 'true' : 'false');
      
      // 应用到HTML元素
      if (state.darkMode) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    },
    
    // 添加通知
    addNotification: (state, action: PayloadAction<{
      type: 'info' | 'success' | 'warning' | 'error';
      message: string;
      autoClose?: boolean;
      duration?: number;
    }>) => {
      logDebug('添加通知', action.payload);
      const id = Date.now().toString();
      state.notifications.push({
        id,
        ...action.payload
      });
    },
    
    // 移除通知
    removeNotification: (state, action: PayloadAction<string>) => {
      logDebug('移除通知', action.payload);
      state.notifications = state.notifications.filter(
        notification => notification.id !== action.payload
      );
    },
    
    // 清除所有通知
    clearNotifications: (state) => {
      logDebug('清除所有通知');
      state.notifications = [];
    },
    
    // 打开模态框
    openModal: (state, action: PayloadAction<{ type: string; data?: any }>) => {
      logDebug('打开模态框', action.payload);
      state.modal = {
        isOpen: true,
        type: action.payload.type,
        data: action.payload.data || null
      };
    },
    
    // 关闭模态框
    closeModal: (state) => {
      logDebug('关闭模态框');
      state.modal = {
        isOpen: false,
        type: null,
        data: null
      };
    },
    
    // 更新模态框数据
    updateModalData: (state, action: PayloadAction<any>) => {
      logDebug('更新模态框数据', action.payload);
      if (state.modal.isOpen) {
        state.modal.data = action.payload;
      }
    }
  }
});

// 导出Action
export const {
  setLoading,
  toggleMobileMenu,
  closeMobileMenu,
  toggleSearch,
  closeSearch,
  toggleFilter,
  closeFilter,
  setSortOption,
  toggleDarkMode,
  setDarkMode,
  addNotification,
  removeNotification,
  clearNotifications,
  openModal,
  closeModal,
  updateModalData
} = uiSlice.actions;

// 导出Reducer
export default uiSlice.reducer; 