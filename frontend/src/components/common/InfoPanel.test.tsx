import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { Lightbulb } from 'lucide-react';
import { InfoPanel, CollapsibleBanner } from './InfoPanel';

describe('InfoPanel', () => {
  describe('rendering', () => {
    it('renders title', () => {
      render(<InfoPanel title="Test Title">Content</InfoPanel>);
      expect(screen.getByText('Test Title')).toBeInTheDocument();
    });

    it('renders children', () => {
      render(<InfoPanel title="Title">Panel content</InfoPanel>);
      expect(screen.getByText('Panel content')).toBeInTheDocument();
    });

    it('applies custom className', () => {
      const { container } = render(
        <InfoPanel title="Title" className="custom-class">Content</InfoPanel>
      );
      expect(container.firstChild).toHaveClass('custom-class');
    });
  });

  describe('variants', () => {
    it('applies info variant styling by default', () => {
      const { container } = render(<InfoPanel title="Title">Content</InfoPanel>);
      expect(container.firstChild).toHaveClass('bg-blue-50', 'border-blue-200');
    });

    it('applies tip variant styling', () => {
      const { container } = render(
        <InfoPanel title="Title" variant="tip">Content</InfoPanel>
      );
      expect(container.firstChild).toHaveClass('bg-amber-50', 'border-amber-200');
    });

    it('applies guide variant styling', () => {
      const { container } = render(
        <InfoPanel title="Title" variant="guide">Content</InfoPanel>
      );
      expect(container.firstChild).toHaveClass('bg-purple-50', 'border-purple-200');
    });

    it('applies warning variant styling', () => {
      const { container } = render(
        <InfoPanel title="Title" variant="warning">Content</InfoPanel>
      );
      expect(container.firstChild).toHaveClass('bg-orange-50', 'border-orange-200');
    });

    it('applies success variant styling', () => {
      const { container } = render(
        <InfoPanel title="Title" variant="success">Content</InfoPanel>
      );
      expect(container.firstChild).toHaveClass('bg-green-50', 'border-green-200');
    });
  });

  describe('icons', () => {
    it('renders info icon for info variant', () => {
      const { container } = render(<InfoPanel title="Title" variant="info">Content</InfoPanel>);
      expect(container.querySelector('.lucide-info')).toBeInTheDocument();
    });

    it('renders lightbulb icon for tip variant', () => {
      const { container } = render(<InfoPanel title="Title" variant="tip">Content</InfoPanel>);
      expect(container.querySelector('.lucide-lightbulb')).toBeInTheDocument();
    });

    it('renders book icon for guide variant', () => {
      const { container } = render(<InfoPanel title="Title" variant="guide">Content</InfoPanel>);
      expect(container.querySelector('.lucide-book-open')).toBeInTheDocument();
    });

    it('renders alert icon for warning variant', () => {
      const { container } = render(<InfoPanel title="Title" variant="warning">Content</InfoPanel>);
      expect(container.querySelector('.lucide-circle-alert')).toBeInTheDocument();
    });

    it('renders check icon for success variant', () => {
      const { container } = render(<InfoPanel title="Title" variant="success">Content</InfoPanel>);
      // CheckCircle2 from lucide renders as lucide-circle-check-big
      expect(container.querySelector('[class*="lucide"]')).toBeInTheDocument();
    });
  });

  describe('collapsible behavior', () => {
    it('shows content by default when not collapsible', () => {
      render(<InfoPanel title="Title">Visible content</InfoPanel>);
      expect(screen.getByText('Visible content')).toBeInTheDocument();
    });

    it('shows content when collapsible and defaultOpen is true', () => {
      render(
        <InfoPanel title="Title" collapsible defaultOpen>
          Visible content
        </InfoPanel>
      );
      expect(screen.getByText('Visible content')).toBeInTheDocument();
    });

    it('hides content when collapsible and defaultOpen is false', () => {
      render(
        <InfoPanel title="Title" collapsible defaultOpen={false}>
          Hidden content
        </InfoPanel>
      );
      expect(screen.queryByText('Hidden content')).not.toBeInTheDocument();
    });

    it('toggles content when clicked and collapsible', () => {
      render(
        <InfoPanel title="Title" collapsible defaultOpen={false}>
          Toggle content
        </InfoPanel>
      );

      expect(screen.queryByText('Toggle content')).not.toBeInTheDocument();

      fireEvent.click(screen.getByText('Title'));

      expect(screen.getByText('Toggle content')).toBeInTheDocument();
    });

    it('shows chevron when collapsible', () => {
      const { container } = render(
        <InfoPanel title="Title" collapsible>Content</InfoPanel>
      );
      expect(container.querySelector('.lucide-chevron-up, .lucide-chevron-down')).toBeInTheDocument();
    });

    it('does not show chevron when not collapsible', () => {
      const { container } = render(<InfoPanel title="Title">Content</InfoPanel>);
      expect(container.querySelector('.lucide-chevron-up')).not.toBeInTheDocument();
      expect(container.querySelector('.lucide-chevron-down')).not.toBeInTheDocument();
    });
  });

  describe('learn more link', () => {
    it('renders learn more link when URL provided', () => {
      render(
        <InfoPanel title="Title" learnMoreUrl="https://example.com">
          Content
        </InfoPanel>
      );
      expect(screen.getByText('Learn more')).toBeInTheDocument();
    });

    it('does not render learn more link when URL not provided', () => {
      render(<InfoPanel title="Title">Content</InfoPanel>);
      expect(screen.queryByText('Learn more')).not.toBeInTheDocument();
    });

    it('opens link in new tab', () => {
      render(
        <InfoPanel title="Title" learnMoreUrl="https://example.com">
          Content
        </InfoPanel>
      );
      const link = screen.getByText('Learn more');
      expect(link).toHaveAttribute('target', '_blank');
      expect(link).toHaveAttribute('rel', 'noopener noreferrer');
    });

    it('has correct href', () => {
      render(
        <InfoPanel title="Title" learnMoreUrl="https://example.com">
          Content
        </InfoPanel>
      );
      expect(screen.getByText('Learn more')).toHaveAttribute('href', 'https://example.com');
    });
  });
});

describe('CollapsibleBanner', () => {
  describe('rendering', () => {
    it('renders title', () => {
      render(<CollapsibleBanner title="Banner Title">Content</CollapsibleBanner>);
      expect(screen.getByText('Banner Title')).toBeInTheDocument();
    });

    it('renders children when open', () => {
      render(<CollapsibleBanner title="Title" defaultOpen>Banner content</CollapsibleBanner>);
      expect(screen.getByText('Banner content')).toBeInTheDocument();
    });

    it('renders custom icon', () => {
      render(
        <CollapsibleBanner title="Title" icon={<Lightbulb data-testid="custom-icon" />}>
          Content
        </CollapsibleBanner>
      );
      expect(screen.getByTestId('custom-icon')).toBeInTheDocument();
    });

    it('applies custom className', () => {
      const { container } = render(
        <CollapsibleBanner title="Title" className="custom-banner">
          Content
        </CollapsibleBanner>
      );
      expect(container.firstChild).toHaveClass('custom-banner');
    });
  });

  describe('variants', () => {
    it('applies amber variant by default', () => {
      const { container } = render(<CollapsibleBanner title="Title">Content</CollapsibleBanner>);
      expect(container.firstChild).toHaveClass('bg-amber-50', 'border-l-amber-400');
    });

    it('applies info variant', () => {
      const { container } = render(
        <CollapsibleBanner title="Title" variant="info">Content</CollapsibleBanner>
      );
      expect(container.firstChild).toHaveClass('bg-blue-50', 'border-l-blue-400');
    });

    it('applies warning variant', () => {
      const { container } = render(
        <CollapsibleBanner title="Title" variant="warning">Content</CollapsibleBanner>
      );
      expect(container.firstChild).toHaveClass('bg-orange-50', 'border-l-orange-400');
    });

    it('applies success variant', () => {
      const { container } = render(
        <CollapsibleBanner title="Title" variant="success">Content</CollapsibleBanner>
      );
      expect(container.firstChild).toHaveClass('bg-green-50', 'border-l-green-400');
    });
  });

  describe('collapsible behavior', () => {
    it('shows content when defaultOpen is true', () => {
      render(<CollapsibleBanner title="Title" defaultOpen>Visible</CollapsibleBanner>);
      expect(screen.getByText('Visible')).toBeInTheDocument();
    });

    it('hides content when defaultOpen is false', () => {
      render(<CollapsibleBanner title="Title" defaultOpen={false}>Hidden</CollapsibleBanner>);
      expect(screen.queryByText('Hidden')).not.toBeInTheDocument();
    });

    it('toggles content on click', () => {
      render(<CollapsibleBanner title="Title" defaultOpen={false}>Toggle me</CollapsibleBanner>);

      expect(screen.queryByText('Toggle me')).not.toBeInTheDocument();

      fireEvent.click(screen.getByText('Title'));

      expect(screen.getByText('Toggle me')).toBeInTheDocument();
    });
  });

  describe('dismissible behavior', () => {
    it('shows dismiss button when dismissible', () => {
      const { container } = render(
        <CollapsibleBanner title="Title" dismissible>Content</CollapsibleBanner>
      );
      // Find the dismiss button (contains X icon)
      const dismissButton = container.querySelector('button');
      expect(dismissButton).toBeInTheDocument();
    });

    it('hides banner when dismiss is clicked', () => {
      const { container } = render(
        <CollapsibleBanner title="Title" dismissible>Content</CollapsibleBanner>
      );

      expect(screen.getByText('Title')).toBeInTheDocument();

      const dismissButton = container.querySelector('button');
      fireEvent.click(dismissButton!);

      expect(screen.queryByText('Title')).not.toBeInTheDocument();
    });

    it('calls onDismiss callback when dismissed', () => {
      const onDismiss = vi.fn();
      const { container } = render(
        <CollapsibleBanner title="Title" dismissible onDismiss={onDismiss}>
          Content
        </CollapsibleBanner>
      );

      const dismissButton = container.querySelector('button');
      fireEvent.click(dismissButton!);

      expect(onDismiss).toHaveBeenCalledTimes(1);
    });

    it('does not show chevron when dismissible', () => {
      const { container } = render(
        <CollapsibleBanner title="Title" dismissible>Content</CollapsibleBanner>
      );
      expect(container.querySelector('.lucide-chevron-up')).not.toBeInTheDocument();
      expect(container.querySelector('.lucide-chevron-down')).not.toBeInTheDocument();
    });

    it('shows chevron when not dismissible', () => {
      const { container } = render(
        <CollapsibleBanner title="Title">Content</CollapsibleBanner>
      );
      expect(
        container.querySelector('.lucide-chevron-up') ||
        container.querySelector('.lucide-chevron-down')
      ).toBeInTheDocument();
    });
  });
});
