import { render, type RenderOptions } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { BrowserRouter } from 'react-router-dom';
import { ThemeProvider } from '@/contexts/ThemeContext';
import type { ReactNode } from 'react';

// Create a custom render function that includes all providers
interface CustomRenderOptions extends Omit<RenderOptions, 'wrapper'> {
  route?: string;
}

function AllProviders({ children }: { children: ReactNode }) {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        retry: false,
      },
    },
  });

  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <ThemeProvider>{children}</ThemeProvider>
      </BrowserRouter>
    </QueryClientProvider>
  );
}

function customRender(ui: React.ReactElement, options?: CustomRenderOptions) {
  if (options?.route) {
    window.history.pushState({}, 'Test page', options.route);
  }
  return render(ui, { wrapper: AllProviders, ...options });
}

// Re-export everything
export * from '@testing-library/react';
export { customRender as render };

// Helper to create mock API responses
export function createMockResponse<T>(data: T, ok = true, status = 200) {
  return Promise.resolve({
    ok,
    status,
    json: () => Promise.resolve(data),
  } as Response);
}

// Helper to create mock license data
export function createMockLicense(overrides = {}) {
  return {
    id: 1,
    license_key: 'ABCD-1234-EFGH-5678',
    customer_email: 'test@example.com',
    customer_name: 'Test User',
    tier: 'pro',
    status: 'active',
    max_activations: 3,
    activations_count: 1,
    expires_at: '2025-12-31',
    created_at: '2024-01-01',
    ...overrides,
  };
}

// Helper to wait for async operations
export const waitForLoadingToFinish = () =>
  new Promise((resolve) => setTimeout(resolve, 0));
