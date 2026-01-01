import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import { Star } from 'lucide-react';
import Card, { CardHeader, StatCard } from './Card';

describe('Card', () => {
  describe('rendering', () => {
    it('renders children correctly', () => {
      render(<Card>Card content</Card>);
      expect(screen.getByText('Card content')).toBeInTheDocument();
    });

    it('renders with default styling', () => {
      render(<Card>Content</Card>);
      const card = screen.getByText('Content').closest('div');
      expect(card).toHaveClass('bg-white', 'rounded-lg', 'border', 'shadow-sm');
    });
  });

  describe('padding', () => {
    it('applies no padding when padding is none', () => {
      render(<Card padding="none">Content</Card>);
      const card = screen.getByText('Content').closest('div');
      expect(card).not.toHaveClass('p-3', 'p-4', 'p-6');
    });

    it('applies small padding', () => {
      render(<Card padding="sm">Content</Card>);
      const card = screen.getByText('Content').closest('div');
      expect(card).toHaveClass('p-3');
    });

    it('applies medium padding by default', () => {
      render(<Card>Content</Card>);
      const card = screen.getByText('Content').closest('div');
      expect(card).toHaveClass('p-4');
    });

    it('applies large padding', () => {
      render(<Card padding="lg">Content</Card>);
      const card = screen.getByText('Content').closest('div');
      expect(card).toHaveClass('p-6');
    });
  });

  describe('custom className', () => {
    it('applies custom className', () => {
      render(<Card className="custom-class">Content</Card>);
      const card = screen.getByText('Content').closest('div');
      expect(card).toHaveClass('custom-class');
    });

    it('merges custom className with default classes', () => {
      render(<Card className="custom-class">Content</Card>);
      const card = screen.getByText('Content').closest('div');
      expect(card).toHaveClass('bg-white', 'custom-class');
    });
  });
});

describe('CardHeader', () => {
  describe('rendering', () => {
    it('renders title', () => {
      render(<CardHeader title="Test Title" />);
      expect(screen.getByText('Test Title')).toBeInTheDocument();
    });

    it('renders title as ReactNode', () => {
      render(<CardHeader title={<span data-testid="custom-title">Custom</span>} />);
      expect(screen.getByTestId('custom-title')).toBeInTheDocument();
    });

    it('renders title with correct heading level', () => {
      render(<CardHeader title="Test Title" />);
      expect(screen.getByRole('heading', { level: 3 })).toHaveTextContent('Test Title');
    });

    it('renders description when provided', () => {
      render(<CardHeader title="Title" description="Test description" />);
      expect(screen.getByText('Test description')).toBeInTheDocument();
    });

    it('does not render description when not provided', () => {
      render(<CardHeader title="Title" />);
      const descriptionElement = document.querySelector('.text-slate-500.mt-0\\.5');
      expect(descriptionElement).not.toBeInTheDocument();
    });

    it('renders action when provided', () => {
      render(<CardHeader title="Title" action={<button>Action</button>} />);
      expect(screen.getByRole('button', { name: 'Action' })).toBeInTheDocument();
    });

    it('does not render action when not provided', () => {
      render(<CardHeader title="Title" />);
      // When no action is provided, there should only be the title container
      expect(screen.queryByRole('button')).not.toBeInTheDocument();
    });
  });

  describe('styling', () => {
    it('applies flex layout', () => {
      const { container } = render(<CardHeader title="Title" />);
      expect(container.firstChild).toHaveClass('flex', 'items-start', 'justify-between');
    });
  });
});

describe('StatCard', () => {
  describe('rendering', () => {
    it('renders label and value', () => {
      render(<StatCard label="Total Users" value={100} />);
      expect(screen.getByText('Total Users')).toBeInTheDocument();
      expect(screen.getByText('100')).toBeInTheDocument();
    });

    it('renders string value', () => {
      render(<StatCard label="Revenue" value="$1,234" />);
      expect(screen.getByText('$1,234')).toBeInTheDocument();
    });

    it('renders icon when provided', () => {
      render(<StatCard label="Rating" value={4.5} icon={<Star data-testid="star-icon" />} />);
      expect(screen.getByTestId('star-icon')).toBeInTheDocument();
    });

    it('does not render icon container when icon not provided', () => {
      const { container } = render(<StatCard label="Title" value={0} />);
      expect(container.querySelector('.p-2.rounded-lg')).not.toBeInTheDocument();
    });
  });

  describe('trend indicator', () => {
    it('renders positive trend with plus sign', () => {
      render(
        <StatCard
          label="Users"
          value={100}
          trend={{ value: 15, positive: true }}
        />
      );
      expect(screen.getByText('+15%')).toBeInTheDocument();
    });

    it('applies green color for positive trend', () => {
      render(
        <StatCard
          label="Users"
          value={100}
          trend={{ value: 15, positive: true }}
        />
      );
      expect(screen.getByText('+15%')).toHaveClass('text-green-600');
    });

    it('renders negative trend without plus sign', () => {
      render(
        <StatCard
          label="Users"
          value={100}
          trend={{ value: 10, positive: false }}
        />
      );
      expect(screen.getByText('10%')).toBeInTheDocument();
    });

    it('applies red color for negative trend', () => {
      render(
        <StatCard
          label="Users"
          value={100}
          trend={{ value: 10, positive: false }}
        />
      );
      expect(screen.getByText('10%')).toHaveClass('text-red-600');
    });

    it('does not render trend when not provided', () => {
      render(<StatCard label="Users" value={100} />);
      expect(screen.queryByText('%')).not.toBeInTheDocument();
    });
  });

  describe('colors', () => {
    it('applies blue color by default', () => {
      render(<StatCard label="Title" value={0} icon={<Star />} />);
      const iconContainer = document.querySelector('.p-2.rounded-lg');
      expect(iconContainer).toHaveClass('bg-blue-50', 'text-blue-600');
    });

    it('applies green color', () => {
      render(<StatCard label="Title" value={0} icon={<Star />} color="green" />);
      const iconContainer = document.querySelector('.p-2.rounded-lg');
      expect(iconContainer).toHaveClass('bg-green-50', 'text-green-600');
    });

    it('applies amber color', () => {
      render(<StatCard label="Title" value={0} icon={<Star />} color="amber" />);
      const iconContainer = document.querySelector('.p-2.rounded-lg');
      expect(iconContainer).toHaveClass('bg-amber-50', 'text-amber-600');
    });

    it('applies red color', () => {
      render(<StatCard label="Title" value={0} icon={<Star />} color="red" />);
      const iconContainer = document.querySelector('.p-2.rounded-lg');
      expect(iconContainer).toHaveClass('bg-red-50', 'text-red-600');
    });

    it('applies purple color', () => {
      render(<StatCard label="Title" value={0} icon={<Star />} color="purple" />);
      const iconContainer = document.querySelector('.p-2.rounded-lg');
      expect(iconContainer).toHaveClass('bg-purple-50', 'text-purple-600');
    });
  });

  describe('wrapper', () => {
    it('renders inside a Card component', () => {
      const { container } = render(<StatCard label="Title" value={0} />);
      expect(container.querySelector('.bg-white.rounded-lg')).toBeInTheDocument();
    });
  });
});
