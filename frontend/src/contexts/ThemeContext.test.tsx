import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, act } from '@testing-library/react';
import { ThemeProvider, useTheme } from './ThemeContext';

// Test component that uses the theme hook
function TestComponent() {
  const { theme, setTheme, resolvedTheme } = useTheme();
  return (
    <div>
      <span data-testid="theme">{theme}</span>
      <span data-testid="resolved">{resolvedTheme}</span>
      <button onClick={() => setTheme('dark')}>Set Dark</button>
      <button onClick={() => setTheme('light')}>Set Light</button>
      <button onClick={() => setTheme('system')}>Set System</button>
    </div>
  );
}

describe('ThemeContext', () => {
  beforeEach(() => {
    // Reset document classes
    document.documentElement.classList.remove('dark');
    // Reset localStorage mock
    vi.mocked(localStorage.getItem).mockReturnValue(null);
  });

  // =========================================
  // Provider Tests
  // =========================================

  it('provides default theme as system', () => {
    render(
      <ThemeProvider>
        <TestComponent />
      </ThemeProvider>
    );

    expect(screen.getByTestId('theme')).toHaveTextContent('system');
  });

  it('resolves system theme to light when matchMedia returns false', () => {
    render(
      <ThemeProvider>
        <TestComponent />
      </ThemeProvider>
    );

    expect(screen.getByTestId('resolved')).toHaveTextContent('light');
  });

  it('resolves system theme to dark when matchMedia returns true', () => {
    // Mock matchMedia to return dark preference
    Object.defineProperty(window, 'matchMedia', {
      writable: true,
      value: vi.fn().mockImplementation((query: string) => ({
        matches: query === '(prefers-color-scheme: dark)',
        media: query,
        onchange: null,
        addListener: vi.fn(),
        removeListener: vi.fn(),
        addEventListener: vi.fn(),
        removeEventListener: vi.fn(),
        dispatchEvent: vi.fn(),
      })),
    });

    render(
      <ThemeProvider>
        <TestComponent />
      </ThemeProvider>
    );

    expect(screen.getByTestId('resolved')).toHaveTextContent('dark');
  });

  // =========================================
  // Theme Switching Tests
  // =========================================

  it('allows setting theme to dark', async () => {
    render(
      <ThemeProvider>
        <TestComponent />
      </ThemeProvider>
    );

    await act(async () => {
      fireEvent.click(screen.getByText('Set Dark'));
    });

    expect(screen.getByTestId('theme')).toHaveTextContent('dark');
    expect(screen.getByTestId('resolved')).toHaveTextContent('dark');
  });

  it('allows setting theme to light', async () => {
    render(
      <ThemeProvider>
        <TestComponent />
      </ThemeProvider>
    );

    // First set to dark
    await act(async () => {
      fireEvent.click(screen.getByText('Set Dark'));
    });

    // Then set to light
    await act(async () => {
      fireEvent.click(screen.getByText('Set Light'));
    });

    expect(screen.getByTestId('theme')).toHaveTextContent('light');
    expect(screen.getByTestId('resolved')).toHaveTextContent('light');
  });

  it('allows setting theme to system', async () => {
    render(
      <ThemeProvider>
        <TestComponent />
      </ThemeProvider>
    );

    // First set to dark
    await act(async () => {
      fireEvent.click(screen.getByText('Set Dark'));
    });

    // Then set to system
    await act(async () => {
      fireEvent.click(screen.getByText('Set System'));
    });

    expect(screen.getByTestId('theme')).toHaveTextContent('system');
  });

  // =========================================
  // localStorage Tests
  // =========================================

  it('persists theme to localStorage', async () => {
    render(
      <ThemeProvider>
        <TestComponent />
      </ThemeProvider>
    );

    await act(async () => {
      fireEvent.click(screen.getByText('Set Dark'));
    });

    expect(localStorage.setItem).toHaveBeenCalledWith('peanut-license-theme', 'dark');
  });

  it('loads theme from localStorage', () => {
    vi.mocked(localStorage.getItem).mockReturnValue('dark');

    render(
      <ThemeProvider>
        <TestComponent />
      </ThemeProvider>
    );

    expect(screen.getByTestId('theme')).toHaveTextContent('dark');
  });

  // =========================================
  // Document Class Tests
  // =========================================

  it('adds dark class to document when dark theme', async () => {
    render(
      <ThemeProvider>
        <TestComponent />
      </ThemeProvider>
    );

    await act(async () => {
      fireEvent.click(screen.getByText('Set Dark'));
    });

    expect(document.documentElement.classList.contains('dark')).toBe(true);
  });

  it('removes dark class from document when light theme', async () => {
    document.documentElement.classList.add('dark');

    render(
      <ThemeProvider>
        <TestComponent />
      </ThemeProvider>
    );

    await act(async () => {
      fireEvent.click(screen.getByText('Set Light'));
    });

    expect(document.documentElement.classList.contains('dark')).toBe(false);
  });

  // =========================================
  // Error Handling Tests
  // =========================================

  it('throws error when useTheme is used outside provider', () => {
    // Suppress console.error for this test
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

    expect(() => {
      render(<TestComponent />);
    }).toThrow('useTheme must be used within a ThemeProvider');

    consoleSpy.mockRestore();
  });
});
