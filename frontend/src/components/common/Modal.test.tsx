import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Modal, { ConfirmModal } from './Modal';

describe('Modal', () => {
  const defaultProps = {
    isOpen: true,
    onClose: vi.fn(),
    title: 'Test Modal',
    children: <p>Modal content</p>,
  };

  beforeEach(() => {
    vi.clearAllMocks();
    document.body.style.overflow = 'unset';
  });

  // =========================================
  // Visibility Tests
  // =========================================

  it('renders when isOpen is true', () => {
    render(<Modal {...defaultProps} />);
    expect(screen.getByText('Test Modal')).toBeInTheDocument();
    expect(screen.getByText('Modal content')).toBeInTheDocument();
  });

  it('does not render when isOpen is false', () => {
    render(<Modal {...defaultProps} isOpen={false} />);
    expect(screen.queryByText('Test Modal')).not.toBeInTheDocument();
  });

  // =========================================
  // Close Behavior Tests
  // =========================================

  it('calls onClose when close button is clicked', () => {
    const onClose = vi.fn();
    render(<Modal {...defaultProps} onClose={onClose} />);

    const closeButtons = document.querySelectorAll('button');
    // Find the close button (with X icon)
    const closeButton = Array.from(closeButtons).find(
      (btn) => btn.querySelector('svg')
    );
    if (closeButton) {
      fireEvent.click(closeButton);
    }

    expect(onClose).toHaveBeenCalled();
  });

  it('calls onClose when backdrop is clicked', () => {
    const onClose = vi.fn();
    render(<Modal {...defaultProps} onClose={onClose} />);

    // Click the backdrop (the fixed overlay)
    const backdrop = document.querySelector('.bg-black\\/50');
    if (backdrop) {
      fireEvent.click(backdrop);
    }

    expect(onClose).toHaveBeenCalled();
  });

  it('does not close when modal content is clicked', () => {
    const onClose = vi.fn();
    render(<Modal {...defaultProps} onClose={onClose} />);

    fireEvent.click(screen.getByText('Modal content'));

    expect(onClose).not.toHaveBeenCalled();
  });

  // =========================================
  // Size Tests
  // =========================================

  it('applies small size class', () => {
    render(<Modal {...defaultProps} size="sm" />);
    const modal = document.querySelector('.max-w-sm');
    expect(modal).toBeInTheDocument();
  });

  it('applies medium size class by default', () => {
    render(<Modal {...defaultProps} />);
    const modal = document.querySelector('.max-w-md');
    expect(modal).toBeInTheDocument();
  });

  it('applies large size class', () => {
    render(<Modal {...defaultProps} size="lg" />);
    const modal = document.querySelector('.max-w-lg');
    expect(modal).toBeInTheDocument();
  });

  it('applies extra-large size class', () => {
    render(<Modal {...defaultProps} size="xl" />);
    const modal = document.querySelector('.max-w-xl');
    expect(modal).toBeInTheDocument();
  });

  // =========================================
  // Footer Tests
  // =========================================

  it('renders footer when provided', () => {
    render(
      <Modal
        {...defaultProps}
        footer={<button>Save</button>}
      />
    );
    expect(screen.getByText('Save')).toBeInTheDocument();
  });

  it('does not render footer when not provided', () => {
    render(<Modal {...defaultProps} />);
    // Footer would have a border-t class
    const footer = document.querySelector('.border-t.flex.justify-end');
    expect(footer).not.toBeInTheDocument();
  });

  // =========================================
  // Body Overflow Tests
  // =========================================

  it('sets body overflow to hidden when open', () => {
    render(<Modal {...defaultProps} isOpen={true} />);
    expect(document.body.style.overflow).toBe('hidden');
  });

  it('resets body overflow when closed', () => {
    const { rerender } = render(<Modal {...defaultProps} isOpen={true} />);
    expect(document.body.style.overflow).toBe('hidden');

    rerender(<Modal {...defaultProps} isOpen={false} />);
    expect(document.body.style.overflow).toBe('unset');
  });
});

describe('ConfirmModal', () => {
  const defaultProps = {
    isOpen: true,
    onClose: vi.fn(),
    onConfirm: vi.fn(),
    title: 'Confirm Action',
    message: 'Are you sure you want to proceed?',
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  // =========================================
  // Rendering Tests
  // =========================================

  it('renders title and message', () => {
    render(<ConfirmModal {...defaultProps} />);
    expect(screen.getByText('Confirm Action')).toBeInTheDocument();
    expect(screen.getByText('Are you sure you want to proceed?')).toBeInTheDocument();
  });

  it('renders cancel and confirm buttons', () => {
    render(<ConfirmModal {...defaultProps} />);
    expect(screen.getByText('Cancel')).toBeInTheDocument();
    expect(screen.getByText('Confirm')).toBeInTheDocument();
  });

  it('uses custom confirm label', () => {
    render(<ConfirmModal {...defaultProps} confirmLabel="Delete" />);
    expect(screen.getByText('Delete')).toBeInTheDocument();
  });

  // =========================================
  // Action Tests
  // =========================================

  it('calls onClose when cancel is clicked', () => {
    const onClose = vi.fn();
    render(<ConfirmModal {...defaultProps} onClose={onClose} />);

    fireEvent.click(screen.getByText('Cancel'));
    expect(onClose).toHaveBeenCalled();
  });

  it('calls onConfirm when confirm is clicked', () => {
    const onConfirm = vi.fn();
    render(<ConfirmModal {...defaultProps} onConfirm={onConfirm} />);

    fireEvent.click(screen.getByText('Confirm'));
    expect(onConfirm).toHaveBeenCalled();
  });

  // =========================================
  // Loading State Tests
  // =========================================

  it('disables buttons when loading', () => {
    render(<ConfirmModal {...defaultProps} loading />);
    expect(screen.getByText('Cancel')).toBeDisabled();
    // Confirm button is also disabled when loading
    const confirmBtn = screen.getByText('Confirm').closest('button');
    expect(confirmBtn).toBeDisabled();
  });

  // =========================================
  // Variant Tests
  // =========================================

  it('applies danger variant to confirm button', () => {
    render(<ConfirmModal {...defaultProps} confirmVariant="danger" />);
    const confirmBtn = screen.getByText('Confirm').closest('button');
    expect(confirmBtn).toHaveClass('bg-red-600');
  });
});
