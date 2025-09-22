import { useState, useEffect } from 'react';
import { toast } from 'react-toastify';
import { useAuth } from '../contexts/AuthContext';
import LoadingSpinner from '../components/LoadingSpinner';

const AccountPage = () => {
  const { user, updateProfile, changePassword, loading: authLoading } = useAuth();
  
  // 个人信息表单状态
  const [profileForm, setProfileForm] = useState({
    name: '',
    email: '',
    phone: '',
  });
  
  // 密码修改表单状态
  const [passwordForm, setPasswordForm] = useState({
    current_password: '',
    password: '',
    password_confirmation: '',
  });
  
  const [activeTab, setActiveTab] = useState('profile');
  const [loading, setLoading] = useState(false);
  const [profileErrors, setProfileErrors] = useState<Record<string, string>>({});
  const [passwordErrors, setPasswordErrors] = useState<Record<string, string>>({});

  // 初始化用户信息
  useEffect(() => {
    if (user) {
      setProfileForm({
        name: user.name || '',
        email: user.email || '',
        phone: user.phone || '',
      });
    }
  }, [user]);

  // 处理个人信息输入变化
  const handleProfileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setProfileForm(prev => ({ ...prev, [name]: value }));
    
    // 清除对应字段的错误
    if (profileErrors[name]) {
      setProfileErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[name];
        return newErrors;
      });
    }
  };

  // 处理密码修改输入变化
  const handlePasswordChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setPasswordForm(prev => ({ ...prev, [name]: value }));
    
    // 清除对应字段的错误
    if (passwordErrors[name]) {
      setPasswordErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[name];
        return newErrors;
      });
    }
  };

  // 验证个人信息表单
  const validateProfileForm = () => {
    const newErrors: Record<string, string> = {};
    
    if (!profileForm.name.trim()) {
      newErrors.name = 'Please enter your name';
    }
    
    if (!profileForm.email.trim()) {
      newErrors.email = 'Please enter your email';
    } else if (!/\S+@\S+\.\S+/.test(profileForm.email)) {
      newErrors.email = 'Please enter a valid email address';
    }
    
    setProfileErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  // 验证密码修改表单
  const validatePasswordForm = () => {
    const newErrors: Record<string, string> = {};
    
    if (!passwordForm.current_password) {
      newErrors.current_password = 'Please enter your current password';
    }
    
    if (!passwordForm.password) {
      newErrors.password = 'Please enter a new password';
    } else if (passwordForm.password.length < 8) {
      newErrors.password = 'Password must be at least 8 characters long';
    }
    
    if (passwordForm.password !== passwordForm.password_confirmation) {
      newErrors.password_confirmation = 'The passwords do not match';
    }
    
    setPasswordErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  // 提交个人信息更新
  const handleProfileSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateProfileForm()) {
      return;
    }
    
    try {
      setLoading(true);
      await updateProfile(profileForm);
      toast.success('Profile updated successfully');
    } catch (error: any) {
      const responseErrors = error.response?.data?.errors;
      
      if (responseErrors) {
        // 处理后端返回的验证错误
        const formattedErrors: Record<string, string> = {};
        Object.keys(responseErrors).forEach(key => {
          formattedErrors[key] = responseErrors[key][0];
        });
        setProfileErrors(formattedErrors);
      }
    } finally {
      setLoading(false);
    }
  };

  // 提交密码修改
  const handlePasswordSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validatePasswordForm()) {
      return;
    }
    
    try {
      setLoading(true);
      await changePassword(passwordForm);
      
      // 重置密码表单
      setPasswordForm({
        current_password: '',
        password: '',
        password_confirmation: '',
      });
      
      toast.success('Password changed successfully');
    } catch (error: any) {
      const responseErrors = error.response?.data?.errors;
      
      if (responseErrors) {
        // 处理后端返回的验证错误
        const formattedErrors: Record<string, string> = {};
        Object.keys(responseErrors).forEach(key => {
          formattedErrors[key] = responseErrors[key][0];
        });
        setPasswordErrors(formattedErrors);
      } else if (error.response?.status === 422) {
        setPasswordErrors({ current_password: 'The current password is incorrect' });
      }
    } finally {
      setLoading(false);
    }
  };

  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <LoadingSpinner size="large" />
      </div>
    );
  }

  return (
    <div className="container py-8">
      <h1 className="text-2xl font-bold mb-6">My Account</h1>
      
      {/* 选项卡 */}
      <div className="border-b border-neutral-200 mb-6">
        <div className="flex space-x-8">
          <button
            className={`pb-4 font-medium text-sm ${
              activeTab === 'profile'
                ? 'border-b-2 border-primary text-primary'
                : 'text-neutral-500 hover:text-neutral-700'
            }`}
            onClick={() => setActiveTab('profile')}
          >
            Profile
          </button>
          <button
            className={`pb-4 font-medium text-sm ${
              activeTab === 'password'
                ? 'border-b-2 border-primary text-primary'
                : 'text-neutral-500 hover:text-neutral-700'
            }`}
            onClick={() => setActiveTab('password')}
          >
            Change Password
          </button>
        </div>
      </div>
      
      {/* 个人信息表单 */}
      {activeTab === 'profile' && (
        <div className="bg-white rounded-lg shadow-sm p-6">
          <form onSubmit={handleProfileSubmit} className="max-w-md space-y-6">
            {/* 姓名 */}
            <div>
              <label htmlFor="name" className="block text-sm font-medium text-neutral-700 mb-1">
                Name
              </label>
              <input
                id="name"
                name="name"
                type="text"
                value={profileForm.name}
                onChange={handleProfileChange}
                className={`input ${profileErrors.name ? 'border-danger' : ''}`}
                placeholder="Please enter your name"
              />
              {profileErrors.name && (
                <p className="mt-1 text-sm text-danger">{profileErrors.name}</p>
              )}
            </div>
            
            {/* 邮箱 */}
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-neutral-700 mb-1">
                Email
              </label>
              <input
                id="email"
                name="email"
                type="email"
                value={profileForm.email}
                onChange={handleProfileChange}
                className={`input ${profileErrors.email ? 'border-danger' : ''}`}
                placeholder="Please enter your email"
                disabled
              />
              {profileErrors.email && (
                <p className="mt-1 text-sm text-danger">{profileErrors.email}</p>
              )}
              <p className="mt-1 text-xs text-neutral-500">Email address cannot be modified</p>
            </div>
            
            {/* 电话 */}
            <div>
              <label htmlFor="phone" className="block text-sm font-medium text-neutral-700 mb-1">
                Phone
              </label>
              <input
                id="phone"
                name="phone"
                type="tel"
                value={profileForm.phone}
                onChange={handleProfileChange}
                className={`input ${profileErrors.phone ? 'border-danger' : ''}`}
                placeholder="Please enter your phone number (optional)"
              />
              {profileErrors.phone && (
                <p className="mt-1 text-sm text-danger">{profileErrors.phone}</p>
              )}
            </div>
            
            {/* 提交按钮 */}
            <div>
              <button
                type="submit"
                disabled={loading || authLoading}
                className="btn btn-primary"
              >
                {(loading || authLoading) ? <LoadingSpinner size="small" /> : 'Save Changes'}
              </button>
            </div>
          </form>
        </div>
      )}
      
      {/* 密码修改表单 */}
      {activeTab === 'password' && (
        <div className="bg-white rounded-lg shadow-sm p-6">
          <form onSubmit={handlePasswordSubmit} className="max-w-md space-y-6">
            {/* 当前密码 */}
            <div>
              <label htmlFor="current_password" className="block text-sm font-medium text-neutral-700 mb-1">
                Current Password
              </label>
              <input
                id="current_password"
                name="current_password"
                type="password"
                value={passwordForm.current_password}
                onChange={handlePasswordChange}
                className={`input ${passwordErrors.current_password ? 'border-danger' : ''}`}
                placeholder="Please enter your current password"
              />
              {passwordErrors.current_password && (
                <p className="mt-1 text-sm text-danger">{passwordErrors.current_password}</p>
              )}
            </div>
            
            {/* 新密码 */}
            <div>
              <label htmlFor="password" className="block text-sm font-medium text-neutral-700 mb-1">
                New Password
              </label>
              <input
                id="password"
                name="password"
                type="password"
                value={passwordForm.password}
                onChange={handlePasswordChange}
                className={`input ${passwordErrors.password ? 'border-danger' : ''}`}
                placeholder="Please set a new password (at least 8 characters)"
              />
              {passwordErrors.password && (
                <p className="mt-1 text-sm text-danger">{passwordErrors.password}</p>
              )}
            </div>
            
            {/* 确认新密码 */}
            <div>
              <label htmlFor="password_confirmation" className="block text-sm font-medium text-neutral-700 mb-1">
                Confirm New Password
              </label>
              <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                value={passwordForm.password_confirmation}
                onChange={handlePasswordChange}
                className={`input ${passwordErrors.password_confirmation ? 'border-danger' : ''}`}
                placeholder="Please enter the new password again"
              />
              {passwordErrors.password_confirmation && (
                <p className="mt-1 text-sm text-danger">{passwordErrors.password_confirmation}</p>
              )}
            </div>
            
            {/* 提交按钮 */}
            <div>
              <button
                type="submit"
                disabled={loading || authLoading}
                className="btn btn-primary"
              >
                {(loading || authLoading) ? <LoadingSpinner size="small" /> : 'Change Password'}
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
};

export default AccountPage; 