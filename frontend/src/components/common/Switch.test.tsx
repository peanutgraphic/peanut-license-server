import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Switch from './Switch';

describe('Switch', () => {
  // =========================================
  // Basic Rendering Tests
  // =========================================

  it('renders switch element', () => {
    render(<Switch checked={false} onChange={() => {}} />);
    expect(screen.getByRole('switch')).toBeInTheDocument();
  });

  it('renders with correct aria-checked when unchecked', () => {
    render(<Switch checked={false} onChange={() => {}} />);
    expect(screen.getByRole('switch')).toHaveAttribute('aria-checked', 'false');
  });

  it('renders with correct aria-checked when checked', () => {
    render(<Switch checked={true} onChange={() => {}} />);
    expect(screen.getByRole('switch')).toHaveAttribute('aria-checked', 'true');
  });

  // =========================================
  // Label and Description Tests
  // =========================================

  it('renders label when provided', () => {
    render(<Switch checked={false} onChange={() => {}} label="Enable feature" />);
    expect(screen.getByText('Enable feature')).toBeInTheDocument();
  });

  it('renders description when provided', () => {
    render(
      <Switch
        checked={false}
        onChange={() => {}}
        description="This enables the cool feature"
      />
    );
    expect(screen.getByText('This enables the cool feature')).toBeInTheDocument();
  });

  it('renders both label and description', () => {
    render(
      <Switch
        checked={false}
        onChange={() => {}}
        label="Enable feature"
        description="This enables the cool feature"
      />
    );
    expect(screen.getByText('Enable feature')).toBeInTheDocument();
    expect(screen.getByText('This enables the cool feature')).toBeInTheDocument();
  });

  // =========================================
  // Toggle Behavior Tests
  // =========================================

  it('calls onChange with true when clicking unchecked switch', () => {
    const handleChange = vi.fn();
    render(<Switch checked={false} onChange={handleChange} />);

    fireEvent.click(screen.getByRole('switch'));
    expect(handleChange).toHaveBeenCalledWith(true);
  });

  it('calls onChange with false when clicking checked switch', () => {
    const handleChange = vi.fn();
    render(<Switch checked={true} onChange={handleChange} />);

    fireEvent.click(screen.getByRole('switch'));
    expect(handleChange).toHaveBeenCalledWith(false);
  });

  // =========================================
  // Styling Tests
  // =========================================

  it('applies checked styles when checked', () => {
    render(<Switch checked={true} onChange={() => {}} />);
    const switchEl = screen.getByRole('switch');
    expect(switchEl).toHaveClass('bg-primary-600');
  });

  it('applies unchecked styles when unchecked', () => {
    render(<Switch checked={false} onChange={() => {}} />);
    const switchEl = screen.getByRole('switch');
    expect(switchEl).toHaveClass('bg-slate-200');
  });

  // =========================================
  // Disabled State Tests
  // =========================================

  it('disables switch when disabled prop is true', () => {
    render(<Switch checked={false} onChange={() => {}} disabled />);
    expect(screen.getByRole('switch')).toBeDisabled();
  });

  it('does not call onChange when disabled and clicked', () => {
    const handleChange = vi.fn();
    render(<Switch checked={false} onChange={handleChange} disabled />);

    fireEvent.click(screen.getByRole('switch'));
    expect(handleChange).not.toHaveBeenCalled();
  });

  it('applies disabled styles', () => {
    const { container } = render(
      <Switch checked={false} onChange={() => {}} disabled />
    );
    const label = container.querySelector('label');
    expect(label).toHaveClass('opacity-50', 'cursor-not-allowed');
  });
});
