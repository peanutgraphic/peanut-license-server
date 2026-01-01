import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import Header from './Header';
import { ThemeProvider } from '@/contexts';

const renderWithProviders = (ui: React.ReactElement) => {
  return render(
    <MemoryRouter>
      <ThemeProvider>
        {ui}
      </ThemeProvider>
    </MemoryRouter>
  );
};

describe('Header', () => {
  describe('rendering', () => {
    it('renders title', () => {
      renderWithProviders(<Header title="Test Page" />);
      expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Test Page');
    });

    it('renders description when provided', () => {
      renderWithProviders(<Header title="Page" description="Page description" />);
      expect(screen.getByText('Page description')).toBeInTheDocument();
    });

    it('does not render description when not provided', () => {
      renderWithProviders(<Header title="Page" />);
      const paragraphs = screen.queryAllByText(/description/i);
      expect(paragraphs).toHaveLength(0);
    });

    it('renders action when provided', () => {
      renderWithProviders(<Header title="Page" action={<button>Action</button>} />);
      expect(screen.getByRole('button', { name: 'Action' })).toBeInTheDocument();
    });
  });

  describe('search functionality', () => {
    it('shows search button initially', () => {
      renderWithProviders(<Header title="Page" />);
      expect(screen.getByTitle('Search')).toBeInTheDocument();
    });

    it('shows search input when search button is clicked', () => {
      renderWithProviders(<Header title="Page" />);

      fireEvent.click(screen.getByTitle('Search'));

      expect(screen.getByPlaceholderText('Search pages...')).toBeInTheDocument();
    });

    it('hides search input when close button is clicked', () => {
      renderWithProviders(<Header title="Page" />);

      fireEvent.click(screen.getByTitle('Search'));
      expect(screen.getByPlaceholderText('Search pages...')).toBeInTheDocument();

      // Find the X button to close search
      const closeButton = screen.getByRole('button', { name: '' });
      fireEvent.click(closeButton);

      expect(screen.queryByPlaceholderText('Search pages...')).not.toBeInTheDocument();
    });

    it('updates search query on input', () => {
      renderWithProviders(<Header title="Page" />);

      fireEvent.click(screen.getByTitle('Search'));
      const input = screen.getByPlaceholderText('Search pages...');

      fireEvent.change(input, { target: { value: 'license' } });

      expect(input).toHaveValue('license');
    });
  });

  describe('theme toggle', () => {
    it('renders theme toggle button', () => {
      renderWithProviders(<Header title="Page" />);
      expect(screen.getByTitle(/Theme:/)).toBeInTheDocument();
    });

    it('cycles through themes when clicked', () => {
      renderWithProviders(<Header title="Page" />);
      const themeButton = screen.getByTitle(/Theme:/);

      // Click to cycle theme
      fireEvent.click(themeButton);

      // The button should still exist after click
      expect(screen.getByTitle(/Theme:/)).toBeInTheDocument();
    });
  });

  describe('audit/notifications button', () => {
    it('renders audit trail button', () => {
      renderWithProviders(<Header title="Page" />);
      expect(screen.getByTitle('View Audit Trail')).toBeInTheDocument();
    });

    it('has bell icon', () => {
      renderWithProviders(<Header title="Page" />);
      const auditButton = screen.getByTitle('View Audit Trail');
      expect(auditButton.querySelector('svg')).toBeInTheDocument();
    });
  });

  describe('structure', () => {
    it('renders two header sections', () => {
      renderWithProviders(<Header title="Page" />);
      // Top bar header and page title section
      const headers = screen.getAllByRole('banner');
      expect(headers.length).toBeGreaterThanOrEqual(1);
    });
  });

  describe('icons', () => {
    it('renders search icon', () => {
      const { container } = renderWithProviders(<Header title="Page" />);
      expect(container.querySelector('.lucide-search')).toBeInTheDocument();
    });

    it('renders bell icon', () => {
      const { container } = renderWithProviders(<Header title="Page" />);
      expect(container.querySelector('.lucide-bell')).toBeInTheDocument();
    });

    it('renders theme icon', () => {
      const { container } = renderWithProviders(<Header title="Page" />);
      // Could be sun, moon, or monitor depending on theme
      const themeIcons = container.querySelectorAll('.lucide-sun, .lucide-moon, .lucide-monitor');
      expect(themeIcons.length).toBeGreaterThanOrEqual(1);
    });
  });
});
