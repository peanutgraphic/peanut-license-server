import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { render, screen, fireEvent, act, waitFor } from '@testing-library/react';
import ToastProvider, { useToast } from './Toast';

// Test component that uses the toast hook
function TestComponent() {
  const { success, error, warning, info } = useToast();
  return (
    <div>
      <button onClick={() => success('Success message')}>Show Success</button>
      <button onClick={() => error('Error message')}>Show Error</button>
      <button onClick={() => warning('Warning message')}>Show Warning</button>
      <button onClick={() => info('Info message')}>Show Info</button>
    </div>
  );
}

describe('Toast', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  // =========================================
  // Basic Rendering Tests
  // =========================================

  it('renders children', () => {
    render(
      <ToastProvider>
        <div>App content</div>
      </ToastProvider>
    );
    expect(screen.getByText('App content')).toBeInTheDocument();
  });

  it('does not show toasts initially', () => {
    render(
      <ToastProvider>
        <TestComponent />
      </ToastProvider>
    );
    expect(screen.queryByText('Success message')).not.toBeInTheDocument();
  });

  // =========================================
  // Toast Type Tests
  // =========================================

  it('shows success toast when success is called', async () => {
    render(
      <ToastProvider>
        <TestComponent />
      </ToastProvider>
    );

    await act(async () => {
      fireEvent.click(screen.getByText('Show Success'));
    });

    expect(screen.getByText('Success message')).toBeInTheDocument();
  });

  it('shows error toast when error is called', async () => {
    render(
      <ToastProvider>
        <TestComponent />
      </ToastProvider>
    );

    await act(async () => {
      fireEvent.click(screen.getByText('Show Error'));
    });

    expect(screen.getByText('Error message')).toBeInTheDocument();
  });

  it('shows warning toast when warning is called', async () => {
    render(
      <ToastProvider>
        <TestComponent />
      </ToastProvider>
    );

    await act(async () => {
      fireEvent.click(screen.getByText('Show Warning'));
    });

    expect(screen.getByText('Warning message')).toBeInTheDocument();
  });

  it('shows info toast when info is called', async () => {
    render(
      <ToastProvider>
        <TestComponent />
      </ToastProvider>
    );

    await act(async () => {
      fireEvent.click(screen.getByText('Show Info'));
    });

    expect(screen.getByText('Info message')).toBeInTheDocument();
  });

  // =========================================
  // Auto-Dismiss Tests
  // =========================================

  it('auto-dismisses toast after duration', async () => {
    render(
      <ToastProvider>
        <TestComponent />
      </ToastProvider>
    );

    await act(async () => {
      fireEvent.click(screen.getByText('Show Success'));
    });

    expect(screen.getByText('Success message')).toBeInTheDocument();

    // Fast-forward past the default 5 second duration
    await act(async () => {
      vi.advanceTimersByTime(5001);
    });

    expect(screen.queryByText('Success message')).not.toBeInTheDocument();
  });

  // =========================================
  // Manual Dismiss Tests
  // =========================================

  it('dismisses toast when close button is clicked', async () => {
    render(
      <ToastProvider>
        <TestComponent />
      </ToastProvider>
    );

    await act(async () => {
      fireEvent.click(screen.getByText('Show Success'));
    });

    expect(screen.getByText('Success message')).toBeInTheDocument();

    // Find and click the close button
    const closeButton = document.querySelector('button > svg.w-4');
    if (closeButton?.parentElement) {
      await act(async () => {
        fireEvent.click(closeButton.parentElement!);
      });
    }

    expect(screen.queryByText('Success message')).not.toBeInTheDocument();
  });

  // =========================================
  // Multiple Toasts Tests
  // =========================================

  it('can show multiple toasts', async () => {
    render(
      <ToastProvider>
        <TestComponent />
      </ToastProvider>
    );

    await act(async () => {
      fireEvent.click(screen.getByText('Show Success'));
      fireEvent.click(screen.getByText('Show Error'));
    });

    expect(screen.getByText('Success message')).toBeInTheDocument();
    expect(screen.getByText('Error message')).toBeInTheDocument();
  });

  // =========================================
  // Error Handling Tests
  // =========================================

  it('throws error when useToast is used outside provider', () => {
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

    expect(() => {
      render(<TestComponent />);
    }).toThrow('useToast must be used within a ToastProvider');

    consoleSpy.mockRestore();
  });
});
