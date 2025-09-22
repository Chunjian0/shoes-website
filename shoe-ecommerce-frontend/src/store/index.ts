import { configureStore } from '@reduxjs/toolkit';
import { TypedUseSelectorHook, useDispatch, useSelector } from 'react-redux';
import { combineReducers } from 'redux';
import authReducer from './slices/authSlice';
import cartReducer from './slices/cartSlice';
import uiReducer from './slices/uiSlice';
import productReducer from './slices/productSlice';

// 日志中间件
const logger = (store: any) => (next: any) => (action: any) => {
  // 开发环境打印action
  if (process.env.NODE_ENV !== 'production') {
    console.log('dispatching', action);
  }
  
  // 调用下一个中间件
  const result = next(action);
  
  // 开发环境打印新状态
  if (process.env.NODE_ENV !== 'production') {
    console.log('next state', store.getState());
  }
  
  return result;
};

// 根Reducer
const rootReducer = combineReducers({
  auth: authReducer,
  cart: cartReducer,
  ui: uiReducer,
  products: productReducer,
});

// 创建Store
export const store = configureStore({
  reducer: rootReducer,
  middleware: (getDefaultMiddleware) => 
    getDefaultMiddleware({
      serializableCheck: false, // 禁用可序列化检查，以允许处理非可序列化值
    }).concat(logger),
  devTools: process.env.NODE_ENV !== 'production', // 在开发环境启用Redux DevTools
});

// 导出类型
export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;

// 自定义Hook
export const useAppDispatch = () => useDispatch<AppDispatch>();
export const useAppSelector: TypedUseSelectorHook<RootState> = useSelector;

export default store; 