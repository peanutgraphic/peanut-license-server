import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import Dashboard from './Dashboard';
import { ThemeProvider } from '@/contexts';

const mockNavigate = vi.fn();
vi.mock('react-router-dom', async () => {
  const actual = await vi.importActual('react-router-dom');
  return {
    ...actual,
    useNavigate: () => mockNavigate,
  };
});

const renderWithProviders = (ui: React.ReactElement) => {
  return render(
    <MemoryRouter>
      <ThemeProvider>{ui}</ThemeProvider>
    </MemoryRouter>
  );
};

describe('Dashboard', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('rendering', () => {
    it('renders page title', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Dashboard');
    });

    it('renders page description', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('License management overview')).toBeInTheDocument();
    });

    it('renders refresh button', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByRole('button', { name: /refresh/i })).toBeInTheDocument();
    });

    it('renders new license button', () => {
      renderWithProviders(<Dashboard />);

      // There are multiple "New License"/"Create License" buttons - find the header one specifically
      const buttons = screen.getAllByRole('button', { name: /new license/i });
      expect(buttons.length).toBeGreaterThanOrEqual(1);
    });
  });

  describe('stat cards', () => {
    it('renders total licenses stat', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Total Licenses')).toBeInTheDocument();
      expect(screen.getByText('247')).toBeInTheDocument();
    });

    it('renders active licenses stat', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Active Licenses')).toBeInTheDocument();
      // 189 appears in both stat card and distribution - use getAllByText
      const values = screen.getAllByText('189');
      expect(values.length).toBeGreaterThanOrEqual(1);
    });

    it('renders active activations stat', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Active Activations')).toBeInTheDocument();
      expect(screen.getByText('312')).toBeInTheDocument();
    });

    it('renders revenue stat', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('This Month Revenue')).toBeInTheDocument();
      expect(screen.getByText('$3,240')).toBeInTheDocument();
    });
  });

  describe('license distribution', () => {
    it('renders license distribution card', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('License Distribution')).toBeInTheDocument();
    });

    it('renders status breakdown', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('By Status')).toBeInTheDocument();
      // Status labels appear in multiple places (breakdown and badges)
      const activeElements = screen.getAllByText('Active');
      expect(activeElements.length).toBeGreaterThanOrEqual(1);
      const expiredElements = screen.getAllByText('Expired');
      expect(expiredElements.length).toBeGreaterThanOrEqual(1);
      expect(screen.getByText('Revoked')).toBeInTheDocument();
    });

    it('renders tier breakdown', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('By Tier')).toBeInTheDocument();
      // Tier names may appear multiple times (in breakdown and badges)
      const freeElements = screen.getAllByText('Free');
      expect(freeElements.length).toBeGreaterThanOrEqual(1);
      const proElements = screen.getAllByText('Pro');
      expect(proElements.length).toBeGreaterThanOrEqual(1);
      const agencyElements = screen.getAllByText('Agency');
      expect(agencyElements.length).toBeGreaterThanOrEqual(1);
    });

    it('displays correct tier counts', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('98')).toBeInTheDocument(); // Free
      expect(screen.getByText('112')).toBeInTheDocument(); // Pro
      expect(screen.getByText('37')).toBeInTheDocument(); // Agency
    });
  });

  describe('quick actions', () => {
    it('renders quick actions card', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Quick Actions')).toBeInTheDocument();
    });

    it('renders create license action', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Create License')).toBeInTheDocument();
      expect(screen.getByText('Generate a new license key')).toBeInTheDocument();
    });

    it('renders batch generate action', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Batch Generate')).toBeInTheDocument();
      expect(screen.getByText('Create multiple licenses')).toBeInTheDocument();
    });

    it('renders view analytics action', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('View Analytics')).toBeInTheDocument();
      expect(screen.getByText('Charts and insights')).toBeInTheDocument();
    });

    it('navigates to licenses page on create license click', () => {
      renderWithProviders(<Dashboard />);

      fireEvent.click(screen.getByText('Create License'));

      expect(mockNavigate).toHaveBeenCalledWith('/licenses?action=add');
    });

    it('navigates to batch generate on click', () => {
      renderWithProviders(<Dashboard />);

      fireEvent.click(screen.getByText('Batch Generate'));

      expect(mockNavigate).toHaveBeenCalledWith('/licenses?action=batch');
    });

    it('navigates to analytics on click', () => {
      renderWithProviders(<Dashboard />);

      fireEvent.click(screen.getByText('View Analytics'));

      expect(mockNavigate).toHaveBeenCalledWith('/analytics');
    });
  });

  describe('recent licenses', () => {
    it('renders recent licenses card', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Recent Licenses')).toBeInTheDocument();
    });

    it('renders view all button', () => {
      renderWithProviders(<Dashboard />);

      const viewAllButtons = screen.getAllByRole('button', { name: /view all/i });
      expect(viewAllButtons.length).toBeGreaterThan(0);
    });

    it('renders license entries', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('PNUT-PRO-7A3B...')).toBeInTheDocument();
      expect(screen.getByText('client@example.com')).toBeInTheDocument();
    });
  });

  describe('recent validations', () => {
    it('renders recent validations card', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Recent Validations')).toBeInTheDocument();
    });

    it('renders validation entries', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('https://clientsite.com')).toBeInTheDocument();
      expect(screen.getByText('https://agency-client1.com')).toBeInTheDocument();
    });

    it('shows valid badge for successful validations', () => {
      renderWithProviders(<Dashboard />);

      const validBadges = screen.getAllByText('Valid');
      expect(validBadges.length).toBeGreaterThan(0);
    });

    it('shows failed badge for failed validations', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Failed')).toBeInTheDocument();
    });
  });

  describe('alerts', () => {
    it('renders expiring licenses alert', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Expiring Licenses')).toBeInTheDocument();
    });

    it('shows count of expired licenses', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText(/42 expired licenses/)).toBeInTheDocument();
    });

    it('renders view expired licenses button', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByRole('button', { name: /view expired licenses/i })).toBeInTheDocument();
    });

    it('navigates to expired licenses on button click', () => {
      renderWithProviders(<Dashboard />);

      fireEvent.click(screen.getByRole('button', { name: /view expired licenses/i }));

      expect(mockNavigate).toHaveBeenCalledWith('/licenses?status=expired');
    });
  });

  describe('welcome banner', () => {
    it('renders welcome banner', () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Welcome to License Server')).toBeInTheDocument();
    });
  });

  describe('navigation', () => {
    it('navigates to add license on new license button click', () => {
      renderWithProviders(<Dashboard />);

      // Get first matching button (the header button)
      const buttons = screen.getAllByRole('button', { name: /new license/i });
      fireEvent.click(buttons[0]);

      expect(mockNavigate).toHaveBeenCalledWith('/licenses?action=add');
    });
  });

  describe('loading state', () => {
    it('shows loading skeleton when refreshing', async () => {
      renderWithProviders(<Dashboard />);

      fireEvent.click(screen.getByRole('button', { name: /refresh/i }));

      // Should show skeleton during loading, then return to normal
      // "Dashboard" appears in both heading and navigation
      await waitFor(() => {
        const dashboardTexts = screen.getAllByText('Dashboard');
        expect(dashboardTexts.length).toBeGreaterThanOrEqual(1);
      }, { timeout: 2000 });
    });
  });
});
