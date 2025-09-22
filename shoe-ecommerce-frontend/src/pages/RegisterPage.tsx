import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { apiService } from '../services/api';
import { useAppDispatch, useAppSelector } from '../store';
import { register } from '../store/slices/authSlice';
import LoadingSpinner from '../components/LoadingSpinner';

const RegisterPage = () => {
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const { loading: authLoading, error: authError } = useAppSelector(state => state.auth);
  
  // 表单状态
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    verification_code: '',
    contact_number: ''
  });
  
  // 密码强度相关状态
  const [passwordStrength, setPasswordStrength] = useState({
    score: 0,
    color: 'bg-neutral-200',
    message: '',
  });
  const [showWeakPasswordConfirm, setShowWeakPasswordConfirm] = useState(false);
  
  // 验证码状态
  const [verificationSent, setVerificationSent] = useState(false);
  const [countdown, setCountdown] = useState(0);
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  // 处理输入变化
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
    
    // 如果是密码字段,评估密码强度
    if (name === 'password') {
      checkPasswordStrength(value);
    }
    
    // 清除对应字段的错误
    if (errors[name]) {
      setErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[name];
        return newErrors;
      });
    }
  };

  // 评估密码强度
  const checkPasswordStrength = (password: string) => {
    if (!password) {
      setPasswordStrength({
        score: 0,
        color: 'bg-neutral-200',
        message: '',
      });
      return;
    }
    
    // Basic scoring rules
    let score = 0;
    let message = '';
    let color = 'bg-neutral-200';
    
    // Length check
    if (password.length >= 8) score += 1;
    if (password.length >= 12) score += 1;
    
    // Complexity check
    if (/[A-Z]/.test(password)) score += 1;
    if (/[0-9]/.test(password)) score += 1;
    if (/[^A-Za-z0-9]/.test(password)) score += 1;
    
    // Set color and message based on score
    if (score < 3) {
      color = 'bg-danger';
      message = 'Weak';
    } else if (score < 5) {
      color = 'bg-warning';
      message = 'Medium';
    } else {
      color = 'bg-success';
      message = 'Strong';
    }
    
    setPasswordStrength({
      score,
      color,
      message,
    });
  };

  // 验证表单
  const validateForm = () => {
    const newErrors: Record<string, string> = {};
    
    if (!formData.name.trim()) {
      newErrors.name = 'Please enter your name';
    }
    
    if (!formData.email.trim()) {
      newErrors.email = 'Please enter your email';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Please enter a valid email address';
    }
    
    if (!formData.password) {
      newErrors.password = 'Please enter a password';
    } else if (formData.password.length < 8) {
      newErrors.password = 'Password must be at least 8 characters';
    }
    
    if (formData.password !== formData.password_confirmation) {
      newErrors.password_confirmation = 'Passwords do not match';
    }
    
    if (verificationSent && !formData.verification_code) {
      newErrors.verification_code = 'Please enter the verification code';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  // 发送验证码
  const sendVerificationCode = async () => {
    // Validate email
    if (!formData.email.trim()) {
      setErrors(prev => ({ ...prev, email: 'Please enter your email' }));
      return;
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      setErrors(prev => ({ ...prev, email: 'Please enter a valid email address' }));
      return;
    }
    
    try {
      setLoading(true);
      await apiService.auth.sendVerificationCode({ email: formData.email });
      setVerificationSent(true);
      
      // Start countdown
      setCountdown(60);
      const timer = setInterval(() => {
        setCountdown(prev => {
          if (prev <= 1) {
            clearInterval(timer);
            return 0;
          }
          return prev - 1;
        });
      }, 1000);
      
      toast.success('Verification code has been sent to your email');
    } catch (error: any) {
      // Handle common errors
      if (error.response?.status === 429) {
        toast.error('Too many requests. Please try again later');
      } else if (error.response?.status === 422 && error.response?.data?.message) {
        // Email already exists or other validation errors
        toast.error(error.response.data.message);
      } else {
        toast.error('Failed to send verification code. Please try again');
      }
    } finally {
      setLoading(false);
    }
  };

  // 提交注册
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }
    
    // 检查密码强度是否过低,弱密码需要确认
    if (passwordStrength.score < 3 && !showWeakPasswordConfirm) {
      setShowWeakPasswordConfirm(true);
      return;
    }
    
    try {
      setLoading(true);
      const resultAction = await dispatch(register(formData));

      if (register.fulfilled.match(resultAction)) {
          if (resultAction.payload.token && resultAction.payload.user) {
              toast.success('Registration and login successful!');
              navigate('/');
          } else {
              toast.success(resultAction.payload.message || 'Registration successful! Please login');
              navigate('/login');
          }
      } else {
          if (resultAction.payload) {
               const errorMessage = resultAction.payload as string;
               if (errorMessage.includes('email has already been taken')) {
                   setErrors({ email: errorMessage });
               } else if (errorMessage.includes('verification code')) {
                   setErrors({ verification_code: errorMessage });
               } else {
                   setErrors({ general: errorMessage });
               }
          } else {
              setErrors({ general: 'An unknown registration error occurred.'});
          }
      }

    } catch (error: any) {
      console.error('Registration submission error:', error);
      setErrors({ general: 'Registration failed. Please try again.' });
    } finally {
      setLoading(false);
    }
  };

  // 确认使用弱密码
  const confirmWeakPassword = async () => {
    setShowWeakPasswordConfirm(false);
    try {
      setLoading(true);
      const resultAction = await dispatch(register(formData));
      
      if (register.fulfilled.match(resultAction)) {
          if (resultAction.payload.token && resultAction.payload.user) {
              toast.success('Registration and login successful!');
              navigate('/');
          } else {
              toast.success(resultAction.payload.message || 'Registration successful! Please login');
              navigate('/login');
          }
      } else {
          if (resultAction.payload) {
               const errorMessage = resultAction.payload as string;
               if (errorMessage.includes('email has already been taken')) {
                   setErrors({ email: errorMessage });
               } else if (errorMessage.includes('verification code')) {
                   setErrors({ verification_code: errorMessage });
               } else {
                   setErrors({ general: errorMessage });
               }
          } else {
              setErrors({ general: 'An unknown registration error occurred.'});
          }
      }
    } catch (error: any) {
      console.error('Weak password confirm registration error:', error);
       setErrors({ general: 'Registration failed. Please try again.' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-neutral-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-primary">Create Account</h1>
          <p className="mt-2 text-neutral-600">Join us, start your shopping journey</p>
        </div>
        
        {/* 弱密码确认弹窗 */}
        {showWeakPasswordConfirm && (
          <div className="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <div className="bg-white rounded-lg p-6 max-w-sm mx-auto">
              <div className="flex items-center mb-4 text-warning">
                <svg className="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h2 className="text-lg font-semibold">Password Strength Weak</h2>
              </div>
              <p className="mb-4 text-neutral-700">Your password strength is weak, it's easy to guess. We recommend using a password that includes uppercase and lowercase letters, numbers, and special characters to increase security.</p>
              <div className="flex justify-end space-x-3">
                <button 
                  onClick={() => setShowWeakPasswordConfirm(false)} 
                  className="btn btn-outline-primary"
                >
                  Re-enter Password
                </button>
                <button 
                  onClick={confirmWeakPassword} 
                  className="btn btn-primary"
                >
                  Continue with Current Password
                </button>
              </div>
            </div>
          </div>
        )}
        
        <form onSubmit={handleSubmit} className="space-y-6">
          {/* General Error Message */}
          {errors.general && (
            <div className="bg-danger-light text-danger p-3 rounded-md text-sm">
              {errors.general}
            </div>
          )}
          
          {/* 姓名 */}
          <div>
            <label htmlFor="name" className="block text-sm font-medium text-neutral-700 mb-1">
              Name
            </label>
            <input
              id="name"
              name="name"
              type="text"
              value={formData.name}
              onChange={handleChange}
              className={`input ${errors.name ? 'border-danger' : ''}`}
              placeholder="Enter your name"
            />
            {errors.name && (
              <p className="mt-1 text-sm text-danger">{errors.name}</p>
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
              value={formData.email}
              onChange={handleChange}
              className={`input ${errors.email ? 'border-danger' : ''}`}
              placeholder="Enter your email"
            />
            {errors.email && (
              <p className="mt-1 text-sm text-danger">{errors.email}</p>
            )}
          </div>
          
          {/* 验证码 */}
          <div className="flex items-end space-x-2">
            <div className="flex-grow">
              <label htmlFor="verification_code" className="block text-sm font-medium text-neutral-700 mb-1">
                Verification Code
              </label>
              <input
                id="verification_code"
                name="verification_code"
                type="text"
                value={formData.verification_code}
                onChange={handleChange}
                className={`input ${errors.verification_code ? 'border-danger' : ''}`}
                placeholder="Enter code"
                disabled={!verificationSent}
              />
              {errors.verification_code && (
                <p className="mt-1 text-sm text-danger">{errors.verification_code}</p>
              )}
            </div>
            <button 
              type="button" 
              onClick={sendVerificationCode}
              disabled={loading || countdown > 0}
              className="btn btn-secondary whitespace-nowrap"
            >
              {countdown > 0 ? `Resend (${countdown}s)` : (loading ? <LoadingSpinner size="small" /> : 'Send Code')}
            </button>
          </div>
          
          {/* 密码 */}
          <div>
            <label htmlFor="password" className="block text-sm font-medium text-neutral-700 mb-1">
              Password
            </label>
            <input
              id="password"
              name="password"
              type="password"
              value={formData.password}
              onChange={handleChange}
              className={`input ${errors.password ? 'border-danger' : ''}`}
              placeholder="Enter your password"
            />
            
            {/* 密码强度指示器 */}
            {formData.password && (
              <div className="mt-1 flex items-center">
                <div className="w-full h-2 bg-neutral-200 rounded-full overflow-hidden">
                  <div 
                    className={`h-full transition-all duration-300 ease-in-out ${passwordStrength.color}`}
                    style={{ width: `${passwordStrength.score * 20}%` }} // Max score 5 = 100%
                  ></div>
                </div>
                <span className={`ml-2 text-xs font-medium ${passwordStrength.score < 3 ? 'text-danger' : (passwordStrength.score < 5 ? 'text-warning' : 'text-success')}`}>
                  {passwordStrength.message}
                </span>
              </div>
            )}
            
            {errors.password && (
              <p className="mt-1 text-sm text-danger">{errors.password}</p>
            )}
          </div>
          
          {/* 确认密码 */}
          <div>
            <label htmlFor="password_confirmation" className="block text-sm font-medium text-neutral-700 mb-1">
              Confirm Password
            </label>
            <input
              id="password_confirmation"
              name="password_confirmation"
              type="password"
              value={formData.password_confirmation}
              onChange={handleChange}
              className={`input ${errors.password_confirmation ? 'border-danger' : ''}`}
              placeholder="Confirm your password"
            />
            {errors.password_confirmation && (
              <p className="mt-1 text-sm text-danger">{errors.password_confirmation}</p>
            )}
          </div>
          
          {/* 联系号码 */}
          <div>
            <label htmlFor="contact_number" className="block text-sm font-medium text-neutral-700 mb-1">
              Contact Number (Optional)
            </label>
            <input
              id="contact_number"
              name="contact_number"
              type="tel" // Use tel type for phone numbers
              value={formData.contact_number}
              onChange={handleChange}
              className={`input ${errors.contact_number ? 'border-danger' : ''}`}
              placeholder="Enter your phone number"
            />
             {errors.contact_number && (
              <p className="mt-1 text-sm text-danger">{errors.contact_number}</p>
            )}
          </div>
          
          {/* 提交按钮 */}
          <div>
            <button
              type="submit"
              disabled={loading || authLoading}
              className="w-full btn btn-primary"
            >
              {(loading || authLoading) ? <LoadingSpinner size="small" /> : 'Sign Up'}
            </button>
          </div>
          
          {/* 已有账号？ */}
          <div className="text-center mt-4">
            <p className="text-sm text-neutral-600">
              Already have an account? 
              <Link to="/login" className="ml-1 text-primary hover:underline">
                Login
              </Link>
            </p>
          </div>
        </form>
      </div>
    </div>
  );
};

export default RegisterPage; 