import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@/test/test-utils';
import { Search } from 'lucide-react';
import Input from './Input';

describe('Input', () => {
  // =========================================
  // Basic Rendering Tests
  // =========================================

  it('renders input element', () => {
    render(<Input placeholder="Enter text" />);
    expect(screen.getByPlaceholderText('Enter text')).toBeInTheDocument();
  });

  it('renders as an input element', () => {
    render(<Input data-testid="test-input" />);
    const input = screen.getByTestId('test-input');
    expect(input.tagName).toBe('INPUT');
  });

  // =========================================
  // Label Tests
  // =========================================

  it('renders label when provided', () => {
    render(<Input label="Email" />);
    expect(screen.getByText('Email')).toBeInTheDocument();
  });

  it('does not render label when not provided', () => {
    render(<Input placeholder="No label" />);
    expect(screen.queryByRole('label')).not.toBeInTheDocument();
  });

  it('shows required indicator when required', () => {
    render(<Input label="Email" required />);
    expect(screen.getByText('*')).toBeInTheDocument();
  });

  // =========================================
  // Error State Tests
  // =========================================

  it('displays error message when error prop is provided', () => {
    render(<Input error="This field is required" />);
    expect(screen.getByText('This field is required')).toBeInTheDocument();
  });

  it('applies error styles when error prop is provided', () => {
    render(<Input data-testid="error-input" error="Error" />);
    const input = screen.getByTestId('error-input');
    expect(input).toHaveClass('border-red-300');
  });

  it('does not show help text when error is shown', () => {
    render(<Input error="Error" helpText="Help text" />);
    expect(screen.getByText('Error')).toBeInTheDocument();
    expect(screen.queryByText('Help text')).not.toBeInTheDocument();
  });

  // =========================================
  // Help Text Tests
  // =========================================

  it('displays help text when provided', () => {
    render(<Input helpText="Enter your email address" />);
    expect(screen.getByText('Enter your email address')).toBeInTheDocument();
  });

  it('help text has correct styling', () => {
    render(<Input helpText="Help text" />);
    const helpText = screen.getByText('Help text');
    expect(helpText).toHaveClass('text-slate-500');
  });

  // =========================================
  // Icon Tests
  // =========================================

  it('renders left icon when provided', () => {
    render(<Input leftIcon={<Search data-testid="search-icon" />} />);
    expect(screen.getByTestId('search-icon')).toBeInTheDocument();
  });

  it('applies padding for left icon', () => {
    render(<Input data-testid="icon-input" leftIcon={<Search />} />);
    const input = screen.getByTestId('icon-input');
    expect(input).toHaveClass('pl-10');
  });

  it('does not apply icon padding when no icon', () => {
    render(<Input data-testid="no-icon-input" />);
    const input = screen.getByTestId('no-icon-input');
    expect(input).not.toHaveClass('pl-10');
  });

  // =========================================
  // Disabled State Tests
  // =========================================

  it('can be disabled', () => {
    render(<Input data-testid="disabled-input" disabled />);
    const input = screen.getByTestId('disabled-input');
    expect(input).toBeDisabled();
  });

  it('applies disabled styles', () => {
    render(<Input data-testid="disabled-input" disabled />);
    const input = screen.getByTestId('disabled-input');
    expect(input).toHaveClass('disabled:bg-slate-50');
  });

  // =========================================
  // Event Tests
  // =========================================

  it('calls onChange handler when input changes', () => {
    const handleChange = vi.fn();
    render(<Input onChange={handleChange} />);

    const input = screen.getByRole('textbox');
    fireEvent.change(input, { target: { value: 'test' } });

    expect(handleChange).toHaveBeenCalled();
  });

  it('calls onBlur handler when input loses focus', () => {
    const handleBlur = vi.fn();
    render(<Input onBlur={handleBlur} />);

    const input = screen.getByRole('textbox');
    fireEvent.blur(input);

    expect(handleBlur).toHaveBeenCalled();
  });

  // =========================================
  // Props Passthrough Tests
  // =========================================

  it('passes through input type', () => {
    render(<Input data-testid="password-input" type="password" />);
    const input = screen.getByTestId('password-input');
    expect(input).toHaveAttribute('type', 'password');
  });

  it('passes through name attribute', () => {
    render(<Input data-testid="named-input" name="email" />);
    const input = screen.getByTestId('named-input');
    expect(input).toHaveAttribute('name', 'email');
  });

  it('passes through maxLength', () => {
    render(<Input data-testid="max-input" maxLength={100} />);
    const input = screen.getByTestId('max-input');
    expect(input).toHaveAttribute('maxLength', '100');
  });

  it('merges custom className', () => {
    render(<Input data-testid="custom-input" className="custom-class" />);
    const input = screen.getByTestId('custom-input');
    expect(input).toHaveClass('custom-class');
    expect(input).toHaveClass('rounded-lg'); // base style
  });
});
