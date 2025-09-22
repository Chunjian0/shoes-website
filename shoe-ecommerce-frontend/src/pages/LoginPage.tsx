import { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { toast } from 'react-toastify';
// import { useAuth } from '../contexts/AuthContext'; // Removed
import { useAppDispatch, useAppSelector } from '../store'; // Added Redux hooks
import { login } from '../store/slices/authSlice'; // Import login thunk
import LoadingSpinner from '../components/LoadingSpinner';

const LoginPage = () => {
  const location = useLocation();
  const navigate = useNavigate();
  // const { login, loading: authLoading } = useAuth(); // Removed
  const dispatch = useAppDispatch(); // Added
  const { loading: authLoading, error: authError } = useAppSelector(state => state.auth); // Added Redux state access
  
  // 从URL参数中获取returnUrl
  const searchParams = new URLSearchParams(location.search);
  const returnUrl = searchParams.get('returnUrl') || '/';
  
  // 表单状态
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    remember: false
  });
  
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  // 处理输入变化
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
    
    // 清除对应字段的错误
    if (errors[name]) {
      setErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[name];
        return newErrors;
      });
    }
  };

  // 验证表单
  const validateForm = () => {
    const newErrors: Record<string, string> = {};
    
    if (!formData.email.trim()) {
      newErrors.email = 'Please enter your email';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Please enter a valid email address';
    }
    
    if (!formData.password) {
      newErrors.password = 'Please enter your password';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  // 在登录成功的处理函数中导航到returnUrl
  const handleLoginSuccess = () => {
    navigate(returnUrl);
  };

  // 提交登录
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrors({}); // Clear previous local errors

    if (!validateForm()) {
      return;
    }

    try {
      setLoading(true); // Keep local loading for button state
      // await login(formData.email, formData.password, formData.remember); // Removed context login
      // Dispatch Redux login thunk
      const resultAction = await dispatch(login({ email: formData.email, password: formData.password }));
      
      // Check if login was successful (thunk fulfilled)
      if (login.fulfilled.match(resultAction)) {
          handleLoginSuccess();
      } else {
          // Handle rejected login (error message is set in authSlice)
          if (resultAction.payload) {
              // Set local error for display if needed, or rely on toast from slice
              setErrors({ credentials: resultAction.payload as string });
          } else {
              setErrors({ credentials: 'An unknown login error occurred.' });
          }
      }

    } catch (error: any) {
      // This catch might not be necessary if thunk handles errors, but kept for safety
      console.error('Login submission error:', error);
      setErrors({ credentials: 'Login failed. Please try again.' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-neutral-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-primary">Welcome Back</h1>
          <p className="mt-2 text-neutral-600">Sign in to your account to continue</p>
        </div>
        
        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Global error message - Now reads from local errors state, which gets updated from authError if needed */}
          {errors.credentials && (
            <div className="bg-danger-light text-danger p-3 rounded-md text-sm">
              {errors.credentials}
            </div>
          )}
          
          {/* Email */}
          <div>
            <label htmlFor="email" className="block text-sm font-medium text-neutral-700 mb-1">
              Email
            </label>
            <input
              id="email"
              name="email"
              type="email"
              value={formData.email}
              onChange={handleChange}
              className={`input ${errors.email ? 'border-danger' : ''}`}
              placeholder="Enter your email"
            />
            {errors.email && (
              <p className="mt-1 text-sm text-danger">{errors.email}</p>
            )}
          </div>
          
          {/* Password */}
          <div>
            <div className="flex items-center justify-between mb-1">
              <label htmlFor="password" className="block text-sm font-medium text-neutral-700">
                Password
              </label>
              <Link to="/forgot-password" className="text-sm text-primary hover:text-primary-dark">
                Forgot password?
              </Link>
            </div>
            <input
              id="password"
              name="password"
              type="password"
              value={formData.password}
              onChange={handleChange}
              className={`input ${errors.password ? 'border-danger' : ''}`}
              placeholder="Enter your password"
            />
            {errors.password && (
              <p className="mt-1 text-sm text-danger">{errors.password}</p>
            )}
          </div>
          
          {/* Remember me */}
          <div className="flex items-center">
            <input
              id="remember"
              name="remember"
              type="checkbox"
              checked={formData.remember}
              onChange={handleChange}
              className="h-4 w-4 text-primary border-neutral-300 rounded focus:ring-primary"
            />
            <label htmlFor="remember" className="ml-2 block text-sm text-neutral-700">
              Remember me
            </label>
          </div>
          
          {/* Submit button - uses combined loading state */}
          <div>
            <button
              type="submit"
              disabled={loading || authLoading}
              className="w-full btn btn-primary"
            >
              {(loading || authLoading) ? <LoadingSpinner size="small" /> : 'Sign In'}
            </button>
          </div>
        </form>
        
        <div className="mt-6 text-center">
          <p className="text-sm text-neutral-600">
            Don't have an account?
            <Link to="/register" className="ml-1 font-medium text-primary hover:text-primary-dark">
              Sign up now
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
};

export default LoginPage; 