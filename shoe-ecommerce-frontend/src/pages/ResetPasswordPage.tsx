import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
// import authService from '../../services/authService'; // Assuming authService exists
import authService from '@/services/authService'; // <-- Use alias path
import { toast } from 'react-toastify';
// Import apiService directly
import { apiService } from '../services/api';
import { AxiosError } from 'axios';

const ResetPasswordPage: React.FC = () => {
  const { token } = useParams<{ token: string }>();
  const navigate = useNavigate();
  
  // Use separate states
  const [email, setEmail] = useState(''); 
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // Add handlers for email and password states if they don't exist
  const handleEmailChange = (e: React.ChangeEvent<HTMLInputElement>) => setEmail(e.target.value);
  const handlePasswordChange = (e: React.ChangeEvent<HTMLInputElement>) => setPassword(e.target.value);
  const handlePasswordConfirmationChange = (e: React.ChangeEvent<HTMLInputElement>) => setPasswordConfirmation(e.target.value);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    
    // Add basic validation
    if (!token) {
      setError('Invalid or missing reset token.');
      return;
    }
    if (password.length < 8) {
        setError('Password must be at least 8 characters long.');
        return;
    }
    if (password !== passwordConfirmation) {
        setError('Passwords do not match.');
        return;
    }
    
    setIsLoading(true);
    setError(null);

    try {
      // Call apiService directly using correct state variables
      await apiService.auth.resetPassword({
        token,
        email, // Use email state
        password, // Use password state
        password_confirmation: passwordConfirmation, // Use passwordConfirmation state
      });
      toast.success('Password reset successfully! You can now log in.');
      navigate('/login');
    } catch (error) {
      console.error("Password reset failed:", error);
      const errorMessage = error instanceof AxiosError ? error.response?.data?.message || error.message || 'Failed to reset password. Please try again.' : 'An error occurred. Please try again later.';
      setError(errorMessage);
      toast.error(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8 bg-white p-10 rounded-lg shadow-lg">
        <div>
          <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">Set New Password</h2>
        </div>
        <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
          {error && (
              <div className="p-3 bg-red-100 text-red-700 rounded-md">
                  {error}
              </div>
          )}
          <input type="hidden" name="token" value={token} />
          
          {/* Email Input */}
          <div className="rounded-md shadow-sm">
             <label htmlFor="email" className="sr-only">Email address</label>
             <input id="email" name="email" type="email" autoComplete="email" required 
                    className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                    placeholder="Email address" 
                    value={email} 
                    onChange={handleEmailChange} 
                    disabled={isLoading} />
          </div>
          
          {/* Password Input */}
          <div className="rounded-md shadow-sm -space-y-px">
            <label htmlFor="password" className="sr-only">Password</label>
            <input id="password" name="password" type="password" required 
                   className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                   placeholder="New Password" 
                   value={password} 
                   onChange={handlePasswordChange} 
                   disabled={isLoading} />
          </div>

          {/* Confirm Password Input */}
          <div className="rounded-md shadow-sm -space-y-px">
             <label htmlFor="password-confirmation" className="sr-only">Confirm Password</label>
             <input id="password-confirmation" name="password_confirmation" type="password" required 
                    className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                    placeholder="Confirm New Password" 
                    value={passwordConfirmation} 
                    onChange={handlePasswordConfirmationChange} 
                    disabled={isLoading} />
          </div>
          
          {/* Submit Button */}
          <div>
            <button type="submit" disabled={isLoading} className={`group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 ${isLoading ? 'opacity-50 cursor-not-allowed' : ''}`}>
              {isLoading ? (
                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              ) : (
                'Reset Password'
              )}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default ResetPasswordPage; 