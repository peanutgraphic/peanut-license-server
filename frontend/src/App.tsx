import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ThemeProvider } from '@/contexts';
import { ToastProvider } from '@/components/common';
import ErrorBoundary from '@/components/common/ErrorBoundary';
import {
  Dashboard,
  Licenses,
  Analytics,
  Audit,
  Webhooks,
  Products,
  GDPR,
  Security,
  Settings,
} from '@/pages';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000, // 5 minutes
      retry: 1,
    },
  },
});

function App() {
  // Get the base path from WordPress if available
  const basePath = (window as unknown as { peanutLicenseServer?: { basePath?: string } })
    .peanutLicenseServer?.basePath || '/';

  return (
    <QueryClientProvider client={queryClient}>
      <ThemeProvider>
        <ToastProvider>
          <ErrorBoundary>
            <BrowserRouter basename={basePath}>
            <a
              href="#main-content"
              className="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:px-4 focus:py-2 focus:bg-white focus:text-blue-600 focus:border focus:border-blue-600 focus:rounded focus:shadow-lg focus:outline-none"
            >
              Skip to main content
            </a>
            <Routes>
              <Route path="/" element={<Dashboard />} />
              <Route path="/licenses" element={<Licenses />} />
              <Route path="/licenses/:id" element={<Licenses />} />
              <Route path="/analytics" element={<Analytics />} />
              <Route path="/audit" element={<Audit />} />
              <Route path="/webhooks" element={<Webhooks />} />
              <Route path="/products" element={<Products />} />
              <Route path="/gdpr" element={<GDPR />} />
              <Route path="/security" element={<Security />} />
              <Route path="/settings" element={<Settings />} />
              <Route path="*" element={<Navigate to="/" replace />} />
            </Routes>
            </BrowserRouter>
          </ErrorBoundary>
        </ToastProvider>
      </ThemeProvider>
    </QueryClientProvider>
  );
}

export default App;
