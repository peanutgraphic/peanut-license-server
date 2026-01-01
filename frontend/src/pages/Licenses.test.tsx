import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';
import Licenses from './Licenses';
import { ThemeProvider } from '@/contexts';

const renderWithProviders = (ui: React.ReactElement) => {
  return render(
    <MemoryRouter>
      <ThemeProvider>{ui}</ThemeProvider>
    </MemoryRouter>
  );
};

// Mock clipboard API
Object.assign(navigator, {
  clipboard: {
    writeText: vi.fn().mockResolvedValue(undefined),
  },
});

describe('Licenses', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('rendering', () => {
    it('renders page title', () => {
      renderWithProviders(<Licenses />);

      expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Licenses');
    });

    it('renders page description', () => {
      renderWithProviders(<Licenses />);

      expect(screen.getByText('Manage license keys for your products')).toBeInTheDocument();
    });

    it('renders export button', () => {
      renderWithProviders(<Licenses />);

      expect(screen.getByRole('button', { name: /export/i })).toBeInTheDocument();
    });

    it('renders new license button', () => {
      renderWithProviders(<Licenses />);

      expect(screen.getByRole('button', { name: /new license/i })).toBeInTheDocument();
    });

    it('renders search input', () => {
      renderWithProviders(<Licenses />);

      expect(screen.getByPlaceholderText('Search by key or email...')).toBeInTheDocument();
    });
  });

  describe('filter dropdowns', () => {
    it('renders filter select elements', () => {
      renderWithProviders(<Licenses />);

      const selects = screen.getAllByRole('combobox');
      expect(selects.length).toBeGreaterThanOrEqual(2); // At least status and tier
    });

    it('renders status options', () => {
      renderWithProviders(<Licenses />);

      expect(screen.getByRole('option', { name: 'All Status' })).toBeInTheDocument();
      expect(screen.getByRole('option', { name: 'Active' })).toBeInTheDocument();
      expect(screen.getByRole('option', { name: 'Expired' })).toBeInTheDocument();
    });

    it('renders tier options', () => {
      renderWithProviders(<Licenses />);

      expect(screen.getByRole('option', { name: 'All Tiers' })).toBeInTheDocument();
      expect(screen.getByRole('option', { name: 'Free' })).toBeInTheDocument();
      expect(screen.getByRole('option', { name: 'Pro' })).toBeInTheDocument();
      expect(screen.getByRole('option', { name: 'Agency' })).toBeInTheDocument();
    });
  });

  describe('license list', () => {
    it('renders license table', () => {
      renderWithProviders(<Licenses />);

      expect(screen.getByRole('table')).toBeInTheDocument();
    });

    it('renders license key column', () => {
      renderWithProviders(<Licenses />);

      expect(screen.getByText('License Key')).toBeInTheDocument();
    });

    it('renders customer column', () => {
      renderWithProviders(<Licenses />);

      expect(screen.getByText('Customer')).toBeInTheDocument();
    });

    it('renders license entries', () => {
      renderWithProviders(<Licenses />);

      expect(screen.getByText('client@example.com')).toBeInTheDocument();
      expect(screen.getByText('agency@studio.com')).toBeInTheDocument();
    });

    it('renders license keys', () => {
      renderWithProviders(<Licenses />);

      expect(screen.getByText('PNUT-PRO-7A3BF92C')).toBeInTheDocument();
    });

    it('renders activation counts', () => {
      renderWithProviders(<Licenses />);

      expect(screen.getByText('2 / 3')).toBeInTheDocument();
    });
  });

  describe('search functionality', () => {
    it('filters licenses by email', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Licenses />);

      const searchInput = screen.getByPlaceholderText('Search by key or email...');
      await user.type(searchInput, 'client@example');

      expect(screen.getByText('client@example.com')).toBeInTheDocument();
      expect(screen.queryByText('agency@studio.com')).not.toBeInTheDocument();
    });

    it('filters licenses by key', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Licenses />);

      const searchInput = screen.getByPlaceholderText('Search by key or email...');
      await user.type(searchInput, 'PRO-7A3B');

      expect(screen.getByText('PNUT-PRO-7A3BF92C')).toBeInTheDocument();
    });

    it('shows no results when search has no matches', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Licenses />);

      const searchInput = screen.getByPlaceholderText('Search by key or email...');
      await user.type(searchInput, 'nonexistent@email.com');

      expect(screen.queryByText('client@example.com')).not.toBeInTheDocument();
    });
  });

  describe('add license modal', () => {
    it('opens add modal when clicking new license button', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Licenses />);

      await user.click(screen.getByRole('button', { name: /new license/i }));

      await waitFor(() => {
        expect(screen.getByText('Create New License')).toBeInTheDocument();
      });
    });

    it('closes modal when clicking cancel', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Licenses />);

      await user.click(screen.getByRole('button', { name: /new license/i }));

      await waitFor(() => {
        expect(screen.getByText('Create New License')).toBeInTheDocument();
      });

      await user.click(screen.getByRole('button', { name: /cancel/i }));

      await waitFor(() => {
        expect(screen.queryByText('Create New License')).not.toBeInTheDocument();
      });
    });
  });

  describe('copy license key', () => {
    it('has copy buttons for each license', () => {
      const { container } = renderWithProviders(<Licenses />);

      const copyIcons = container.querySelectorAll('.lucide-copy');
      expect(copyIcons.length).toBeGreaterThan(0);
    });
  });

  describe('action buttons', () => {
    it('has edit buttons', () => {
      const { container } = renderWithProviders(<Licenses />);

      // Edit2 icon renders with title "Edit license"
      const editButtons = container.querySelectorAll('[title="Edit license"]');
      expect(editButtons.length).toBeGreaterThan(0);
    });

    it('has view detail buttons', () => {
      const { container } = renderWithProviders(<Licenses />);

      const viewIcons = container.querySelectorAll('.lucide-external-link');
      expect(viewIcons.length).toBeGreaterThan(0);
    });
  });

  describe('pagination', () => {
    it('renders pagination controls', () => {
      const { container } = renderWithProviders(<Licenses />);

      const leftChevron = container.querySelector('.lucide-chevron-left');
      const rightChevron = container.querySelector('.lucide-chevron-right');

      expect(leftChevron).toBeInTheDocument();
      expect(rightChevron).toBeInTheDocument();
    });
  });
});
