import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { DangerZone, DangerAction, LockedAction } from './DangerZone';

describe('DangerZone', () => {
  describe('rendering', () => {
    it('renders with default title', () => {
      render(<DangerZone>Content</DangerZone>);
      expect(screen.getByText('Danger Zone')).toBeInTheDocument();
    });

    it('renders with custom title', () => {
      render(<DangerZone title="Custom Danger">Content</DangerZone>);
      expect(screen.getByText('Custom Danger')).toBeInTheDocument();
    });

    it('renders with default description', () => {
      render(<DangerZone>Content</DangerZone>);
      expect(screen.getByText('These actions are destructive and cannot be undone.')).toBeInTheDocument();
    });

    it('renders with custom description', () => {
      render(<DangerZone description="Custom warning">Content</DangerZone>);
      expect(screen.getByText('Custom warning')).toBeInTheDocument();
    });

    it('renders danger zone styling', () => {
      const { container } = render(<DangerZone>Content</DangerZone>);
      expect(container.firstChild).toHaveClass('border-red-200');
    });
  });

  describe('collapsible behavior', () => {
    it('is collapsed by default', () => {
      render(<DangerZone>Hidden content</DangerZone>);
      expect(screen.queryByText('Hidden content')).not.toBeInTheDocument();
    });

    it('can start expanded', () => {
      render(<DangerZone defaultCollapsed={false}>Visible content</DangerZone>);
      expect(screen.getByText('Visible content')).toBeInTheDocument();
    });

    it('expands when header is clicked', () => {
      render(<DangerZone>Hidden content</DangerZone>);

      expect(screen.queryByText('Hidden content')).not.toBeInTheDocument();

      fireEvent.click(screen.getByText('Danger Zone'));

      expect(screen.getByText('Hidden content')).toBeInTheDocument();
    });

    it('collapses when header is clicked again', () => {
      render(<DangerZone defaultCollapsed={false}>Visible content</DangerZone>);

      expect(screen.getByText('Visible content')).toBeInTheDocument();

      fireEvent.click(screen.getByText('Danger Zone'));

      expect(screen.queryByText('Visible content')).not.toBeInTheDocument();
    });

    it('shows down chevron when collapsed', () => {
      const { container } = render(<DangerZone>Content</DangerZone>);
      // ChevronDown is rendered when collapsed
      expect(container.querySelector('.lucide-chevron-down')).toBeInTheDocument();
    });

    it('shows up chevron when expanded', () => {
      const { container } = render(<DangerZone defaultCollapsed={false}>Content</DangerZone>);
      // ChevronUp is rendered when expanded
      expect(container.querySelector('.lucide-chevron-up')).toBeInTheDocument();
    });
  });
});

describe('DangerAction', () => {
  const defaultProps = {
    title: 'Delete Item',
    description: 'This will permanently delete the item.',
    buttonLabel: 'Delete',
    onAction: vi.fn(),
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('rendering', () => {
    it('renders title and description', () => {
      render(<DangerAction {...defaultProps} />);
      expect(screen.getByText('Delete Item')).toBeInTheDocument();
      expect(screen.getByText('This will permanently delete the item.')).toBeInTheDocument();
    });

    it('renders action button', () => {
      render(<DangerAction {...defaultProps} />);
      expect(screen.getByRole('button', { name: 'Delete' })).toBeInTheDocument();
    });

    it('renders button with danger variant styling', () => {
      render(<DangerAction {...defaultProps} />);
      const button = screen.getByRole('button', { name: 'Delete' });
      expect(button).toHaveClass('bg-red-600');
    });
  });

  describe('confirmation modal', () => {
    it('shows confirmation modal when button is clicked', () => {
      render(<DangerAction {...defaultProps} />);

      fireEvent.click(screen.getByRole('button', { name: 'Delete' }));

      // Modal should appear with Cancel button
      expect(screen.getByRole('button', { name: 'Cancel' })).toBeInTheDocument();
    });

    it('uses title as modal title by default', () => {
      render(<DangerAction {...defaultProps} />);

      fireEvent.click(screen.getByRole('button', { name: 'Delete' }));

      // Modal title should be visible (there are two - action title and modal title)
      const titles = screen.getAllByText('Delete Item');
      expect(titles.length).toBeGreaterThan(1);
    });

    it('uses custom confirmTitle when provided', () => {
      render(<DangerAction {...defaultProps} confirmTitle="Confirm Deletion" />);

      fireEvent.click(screen.getByRole('button', { name: 'Delete' }));

      expect(screen.getByText('Confirm Deletion')).toBeInTheDocument();
    });

    it('uses custom confirmMessage when provided', () => {
      render(<DangerAction {...defaultProps} confirmMessage="Are you absolutely sure?" />);

      fireEvent.click(screen.getByRole('button', { name: 'Delete' }));

      expect(screen.getByText('Are you absolutely sure?')).toBeInTheDocument();
    });

    it('calls onAction when confirmed', async () => {
      render(<DangerAction {...defaultProps} />);

      fireEvent.click(screen.getByRole('button', { name: 'Delete' }));

      // Find and click confirm button in modal
      const confirmButtons = screen.getAllByRole('button', { name: 'Delete' });
      fireEvent.click(confirmButtons[confirmButtons.length - 1]);

      await waitFor(() => {
        expect(defaultProps.onAction).toHaveBeenCalledTimes(1);
      });
    });

    it('closes modal when cancel is clicked', () => {
      render(<DangerAction {...defaultProps} />);

      fireEvent.click(screen.getByRole('button', { name: 'Delete' }));
      expect(screen.getByRole('button', { name: 'Cancel' })).toBeInTheDocument();

      fireEvent.click(screen.getByRole('button', { name: 'Cancel' }));
      expect(screen.queryByRole('button', { name: 'Cancel' })).not.toBeInTheDocument();
    });
  });

  describe('loading state', () => {
    it('disables button when loading', () => {
      render(<DangerAction {...defaultProps} loading />);
      expect(screen.getByRole('button', { name: 'Delete' })).toBeDisabled();
    });
  });
});

describe('LockedAction', () => {
  const defaultProps = {
    title: 'Delete Account',
    description: 'Permanently delete your account and all data.',
    reason: 'Active subscriptions',
  };

  it('renders title and description', () => {
    render(<LockedAction {...defaultProps} />);
    expect(screen.getByText('Delete Account')).toBeInTheDocument();
    expect(screen.getByText('Permanently delete your account and all data.')).toBeInTheDocument();
  });

  it('renders lock reason', () => {
    render(<LockedAction {...defaultProps} />);
    expect(screen.getByText('Active subscriptions')).toBeInTheDocument();
  });

  it('renders lock icon', () => {
    const { container } = render(<LockedAction {...defaultProps} />);
    expect(container.querySelector('.lucide-lock')).toBeInTheDocument();
  });

  it('has opacity styling to indicate disabled state', () => {
    const { container } = render(<LockedAction {...defaultProps} />);
    expect(container.firstChild).toHaveClass('opacity-75');
  });

  it('has slate/gray styling instead of red', () => {
    const { container } = render(<LockedAction {...defaultProps} />);
    expect(container.firstChild).toHaveClass('bg-slate-100', 'border-slate-200');
  });
});

// Import beforeEach for setup
import { beforeEach } from 'vitest';
