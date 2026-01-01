import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';
import Analytics from './Analytics';
import { ThemeProvider } from '@/contexts';

// Mock recharts to avoid rendering issues in tests
vi.mock('recharts', () => ({
  ResponsiveContainer: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="responsive-container">{children}</div>
  ),
  LineChart: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="line-chart">{children}</div>
  ),
  Line: () => <div data-testid="line" />,
  XAxis: () => <div data-testid="x-axis" />,
  YAxis: () => <div data-testid="y-axis" />,
  CartesianGrid: () => <div data-testid="cartesian-grid" />,
  Tooltip: () => <div data-testid="tooltip" />,
  Legend: () => <div data-testid="legend" />,
  PieChart: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="pie-chart">{children}</div>
  ),
  Pie: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="pie">{children}</div>
  ),
  Cell: () => <div data-testid="cell" />,
}));

const renderWithProviders = (ui: React.ReactElement) => {
  return render(
    <MemoryRouter>
      <ThemeProvider>{ui}</ThemeProvider>
    </MemoryRouter>
  );
};

describe('Analytics', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('rendering', () => {
    it('renders page title', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Analytics');
    });

    it('renders page description', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('License usage and validation statistics')).toBeInTheDocument();
    });

    it('renders export button', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByRole('button', { name: /export/i })).toBeInTheDocument();
    });

    it('renders time range selector', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByRole('combobox')).toBeInTheDocument();
    });
  });

  describe('time range options', () => {
    it('renders all time range options', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByRole('option', { name: 'Last 7 days' })).toBeInTheDocument();
      expect(screen.getByRole('option', { name: 'Last 30 days' })).toBeInTheDocument();
      expect(screen.getByRole('option', { name: 'Last 90 days' })).toBeInTheDocument();
      expect(screen.getByRole('option', { name: 'Last year' })).toBeInTheDocument();
    });

    it('defaults to 7 days', () => {
      renderWithProviders(<Analytics />);

      const select = screen.getByRole('combobox');
      expect(select).toHaveValue('7d');
    });

    it('changes time range on selection', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Analytics />);

      const select = screen.getByRole('combobox');
      await user.selectOptions(select, '30d');

      expect(select).toHaveValue('30d');
    });
  });

  describe('summary stats', () => {
    it('renders Total Validations stat', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('Total Validations')).toBeInTheDocument();
      expect(screen.getByText('1,247')).toBeInTheDocument();
    });

    it('renders Success Rate stat', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('Success Rate')).toBeInTheDocument();
      expect(screen.getByText('96.2%')).toBeInTheDocument();
    });

    it('renders Failed Validations stat', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('Failed Validations')).toBeInTheDocument();
      expect(screen.getByText('47')).toBeInTheDocument();
    });

    it('renders Active Sites stat', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('Active Sites')).toBeInTheDocument();
      // 312 appears in multiple places - use getAllByText
      const values = screen.getAllByText('312');
      expect(values.length).toBeGreaterThanOrEqual(1);
    });

    it('renders trend indicators', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('+12% from last week')).toBeInTheDocument();
      expect(screen.getByText('+2.1% from last week')).toBeInTheDocument();
      expect(screen.getByText('-8% from last week')).toBeInTheDocument();
      expect(screen.getByText('+5 new this week')).toBeInTheDocument();
    });
  });

  describe('charts', () => {
    it('renders Validation Timeline chart section', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('Validation Timeline')).toBeInTheDocument();
    });

    it('renders License Tiers chart section', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('License Tiers')).toBeInTheDocument();
    });

    it('renders line chart', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByTestId('line-chart')).toBeInTheDocument();
    });

    it('renders pie chart', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByTestId('pie-chart')).toBeInTheDocument();
    });
  });

  describe('Most Active Sites table', () => {
    it('renders Most Active Sites header', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('Most Active Sites')).toBeInTheDocument();
    });

    it('renders top sites', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('https://enterprise.com')).toBeInTheDocument();
      expect(screen.getByText('https://agency-client.io')).toBeInTheDocument();
      expect(screen.getByText('https://developer.dev')).toBeInTheDocument();
      expect(screen.getByText('https://startup.co')).toBeInTheDocument();
      expect(screen.getByText('https://freelancer.net')).toBeInTheDocument();
    });

    it('renders validation counts for sites', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('156 validations')).toBeInTheDocument();
      expect(screen.getByText('89 validations')).toBeInTheDocument();
      expect(screen.getByText('72 validations')).toBeInTheDocument();
    });

    it('renders last check times', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('Last check: 2 min ago')).toBeInTheDocument();
      expect(screen.getByText('Last check: 15 min ago')).toBeInTheDocument();
      expect(screen.getByText('Last check: 1 hour ago')).toBeInTheDocument();
    });

    it('renders ranking numbers', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('1')).toBeInTheDocument();
      expect(screen.getByText('2')).toBeInTheDocument();
      expect(screen.getByText('3')).toBeInTheDocument();
      expect(screen.getByText('4')).toBeInTheDocument();
      expect(screen.getByText('5')).toBeInTheDocument();
    });
  });

  describe('Validation Errors table', () => {
    it('renders Validation Errors header', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('Validation Errors')).toBeInTheDocument();
    });

    it('renders error types', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('Expired License')).toBeInTheDocument();
      expect(screen.getByText('Invalid Key')).toBeInTheDocument();
      expect(screen.getByText('Max Activations')).toBeInTheDocument();
      expect(screen.getByText('Domain Mismatch')).toBeInTheDocument();
    });

    it('renders error counts and percentages', () => {
      renderWithProviders(<Analytics />);

      expect(screen.getByText('23 (45%)')).toBeInTheDocument();
      expect(screen.getByText('15 (29%)')).toBeInTheDocument();
      expect(screen.getByText('8 (16%)')).toBeInTheDocument();
      expect(screen.getByText('5 (10%)')).toBeInTheDocument();
    });

    it('renders progress bars for error types', () => {
      const { container } = renderWithProviders(<Analytics />);

      // Check for progress bar elements with bg-red-500 class
      const progressBars = container.querySelectorAll('.bg-red-500.rounded-full.h-2');
      expect(progressBars.length).toBe(4);
    });
  });

  describe('help tooltips', () => {
    it('renders help tooltip for Validation Timeline', () => {
      const { container } = renderWithProviders(<Analytics />);

      // HelpTooltip component renders a help icon
      const helpIcons = container.querySelectorAll('.lucide-circle-help');
      expect(helpIcons.length).toBeGreaterThanOrEqual(1);
    });
  });

  describe('styling', () => {
    it('renders stat cards with icons', () => {
      const { container } = renderWithProviders(<Analytics />);

      // Check for SVG icons in stat cards
      const svgIcons = container.querySelectorAll('svg.lucide');
      expect(svgIcons.length).toBeGreaterThanOrEqual(4); // At least 4 stat card icons
    });

    it('renders trend up icons', () => {
      const { container } = renderWithProviders(<Analytics />);

      const trendUpIcons = container.querySelectorAll('.lucide-trending-up');
      expect(trendUpIcons.length).toBeGreaterThanOrEqual(3);
    });

    it('renders trend down icon', () => {
      const { container } = renderWithProviders(<Analytics />);

      const trendDownIcon = container.querySelector('.lucide-trending-down');
      expect(trendDownIcon).toBeInTheDocument();
    });
  });
});
