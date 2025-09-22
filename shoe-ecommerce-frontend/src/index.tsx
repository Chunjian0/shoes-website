import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import App from './App';
import { StyleSheetManager } from 'styled-components';
import './index.css';
import './styles/luxuryTheme.css';
import { AuthProvider } from './contexts/AuthContext';

const container = document.getElementById('root') as HTMLElement;
const root = createRoot(container);

root.render(
  <React.StrictMode>
    <BrowserRouter>
      <StyleSheetManager /* shouldForwardProp={(prop: string) => !prop.startsWith('$')} */>
        <AuthProvider>
          <App />
        </AuthProvider>
      </StyleSheetManager>
    </BrowserRouter>
  </React.StrictMode>
);

// Remove call to missing function
// reportWebVitals(); 