import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ThemeProvider } from '@/contexts';
import { ToastProvider } from '@/components/common';
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
          <BrowserRouter basename={basePath}>
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
        </ToastProvider>
      </ThemeProvider>
    </QueryClientProvider>
  );
}

export default App;
