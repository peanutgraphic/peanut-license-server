import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';
import Settings from './Settings';
import { ThemeProvider } from '@/contexts';

const renderWithProviders = (ui: React.ReactElement) => {
  return render(
    <MemoryRouter>
      <ThemeProvider>{ui}</ThemeProvider>
    </MemoryRouter>
  );
};

describe('Settings', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('rendering', () => {
    it('renders page title', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Settings');
    });

    it('renders page description', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByText('Configure server behavior and security options')).toBeInTheDocument();
    });

    it('renders save settings button', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByRole('button', { name: /save settings/i })).toBeInTheDocument();
    });
  });

  describe('API Settings section', () => {
    it('renders API Settings header', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByText('API Settings')).toBeInTheDocument();
    });

    it('renders API Settings description', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByText('Configure license validation API behavior')).toBeInTheDocument();
    });

    it('renders Enable License API switch', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByText('Enable License API')).toBeInTheDocument();
      expect(screen.getByText('Allow external sites to validate licenses via REST API')).toBeInTheDocument();
    });

    it('renders Enable Update Server switch', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByText('Enable Update Server')).toBeInTheDocument();
      expect(screen.getByText('Serve plugin updates to licensed sites')).toBeInTheDocument();
    });

    it('renders cache duration input', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByText('Cache Duration (hours)')).toBeInTheDocument();
      expect(screen.getByText('How long to cache validation results')).toBeInTheDocument();
    });

    it('renders rate limit input', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByText('Rate Limit (requests/hour)')).toBeInTheDocument();
      expect(screen.getByText('Max validation requests per IP per hour')).toBeInTheDocument();
    });

    it('has correct default values', () => {
      renderWithProviders(<Settings />);

      // Check number inputs have correct default values
      const cacheDurationInput = screen.getByDisplayValue('12');
      const rateLimitInput = screen.getByDisplayValue('100');

      expect(cacheDurationInput).toBeInTheDocument();
      expect(rateLimitInput).toBeInTheDocument();
    });
  });

  describe('Security section', () => {
    it('renders Security header', () => {
      renderWithProviders(<Settings />);

      // "Security" appears in navigation and card header - use getAllByText
      const securityElements = screen.getAllByText('Security');
      expect(securityElements.length).toBeGreaterThanOrEqual(1);
    });

    it('renders Require SSL switch', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByText('Require SSL/HTTPS')).toBeInTheDocument();
      expect(screen.getByText('Only accept validation requests from HTTPS sites')).toBeInTheDocument();
    });

    it('renders Allowed Domains input', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByText('Allowed Domains')).toBeInTheDocument();
      expect(screen.getByPlaceholderText('example.com, *.agency.com')).toBeInTheDocument();
    });

    it('renders help text for allowed domains', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByText('Comma-separated list of allowed domains (wildcards supported). Leave empty to allow all.')).toBeInTheDocument();
    });
  });

  describe('Webhook Security section', () => {
    it('renders Webhook Security header', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByText('Webhook Security')).toBeInTheDocument();
    });

    it('renders Webhook Secret label', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByText('Webhook Secret')).toBeInTheDocument();
    });

    it('renders webhook secret help text', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByText(/This secret is used to sign webhook payloads/)).toBeInTheDocument();
    });

    it('renders refresh button for webhook secret', () => {
      const { container } = renderWithProviders(<Settings />);

      const refreshIcon = container.querySelector('.lucide-refresh-cw');
      expect(refreshIcon).toBeInTheDocument();
    });
  });

  describe('Danger Zone', () => {
    it('renders danger zone section', () => {
      renderWithProviders(<Settings />);

      expect(screen.getByText('Danger Zone')).toBeInTheDocument();
    });

    it('renders danger zone collapsed by default', () => {
      renderWithProviders(<Settings />);

      // When collapsed, action titles should not be visible
      expect(screen.queryByText('Clear Validation Cache')).not.toBeInTheDocument();
    });

    it('expands danger zone when clicking header', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      // Click on danger zone header to expand
      await user.click(screen.getByText('Danger Zone'));

      // Now actions should be visible
      expect(screen.getByText('Clear Validation Cache')).toBeInTheDocument();
      expect(screen.getByText('Reset Rate Limits')).toBeInTheDocument();
      expect(screen.getByText('Revoke All Licenses')).toBeInTheDocument();
    });

    it('shows action descriptions when expanded', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      await user.click(screen.getByText('Danger Zone'));

      expect(screen.getByText('Force all sites to re-validate their licenses on next request.')).toBeInTheDocument();
      expect(screen.getByText('Clear all rate limit counters for all IPs.')).toBeInTheDocument();
      expect(screen.getByText('Immediately revoke all active licenses. This cannot be undone.')).toBeInTheDocument();
    });

    it('renders action buttons when expanded', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      await user.click(screen.getByText('Danger Zone'));

      expect(screen.getByRole('button', { name: 'Clear Cache' })).toBeInTheDocument();
      expect(screen.getByRole('button', { name: 'Reset Limits' })).toBeInTheDocument();
      expect(screen.getByRole('button', { name: 'Revoke All' })).toBeInTheDocument();
    });
  });

  describe('switch interactions', () => {
    it('toggles Enable License API switch', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      // Find all switches - first one is License API
      const switches = screen.getAllByRole('switch');
      expect(switches[0]).toHaveAttribute('aria-checked', 'true');

      await user.click(switches[0]);

      expect(switches[0]).toHaveAttribute('aria-checked', 'false');
    });

    it('toggles Enable Update Server switch', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      // Second switch is Update Server
      const switches = screen.getAllByRole('switch');
      expect(switches[1]).toHaveAttribute('aria-checked', 'true');

      await user.click(switches[1]);

      expect(switches[1]).toHaveAttribute('aria-checked', 'false');
    });

    it('toggles Require SSL switch', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      // Third switch is Require SSL
      const switches = screen.getAllByRole('switch');
      expect(switches[2]).toHaveAttribute('aria-checked', 'true');

      await user.click(switches[2]);

      expect(switches[2]).toHaveAttribute('aria-checked', 'false');
    });
  });

  describe('input interactions', () => {
    it('updates cache duration input', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      const input = screen.getByDisplayValue('12');
      await user.clear(input);
      await user.type(input, '24');

      expect(input).toHaveValue(24);
    });

    it('updates rate limit input', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      const input = screen.getByDisplayValue('100');
      await user.clear(input);
      await user.type(input, '200');

      expect(input).toHaveValue(200);
    });

    it('updates allowed domains input', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      const input = screen.getByPlaceholderText('example.com, *.agency.com');
      await user.type(input, 'mysite.com');

      expect(input).toHaveValue('mysite.com');
    });
  });

  describe('save functionality', () => {
    it('shows loading state when saving', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      const saveButton = screen.getByRole('button', { name: /save settings/i });
      await user.click(saveButton);

      // Button should show loading state (may contain spinner)
      await waitFor(() => {
        expect(saveButton).toBeDisabled();
      });

      // Wait for save to complete
      await waitFor(() => {
        expect(saveButton).not.toBeDisabled();
      }, { timeout: 2000 });
    });
  });

  describe('danger zone confirmation', () => {
    it('opens confirmation dialog for Clear Cache', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      // First expand danger zone
      await user.click(screen.getByText('Danger Zone'));
      await user.click(screen.getByRole('button', { name: 'Clear Cache' }));

      await waitFor(() => {
        expect(screen.getByText('This will clear all cached validation results. Sites will need to re-validate their licenses on the next request.')).toBeInTheDocument();
      });
    });

    it('opens confirmation dialog for Reset Limits', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      await user.click(screen.getByText('Danger Zone'));
      await user.click(screen.getByRole('button', { name: 'Reset Limits' }));

      await waitFor(() => {
        expect(screen.getByText('This will reset rate limits for all IP addresses. Use with caution.')).toBeInTheDocument();
      });
    });

    it('opens confirmation dialog for Revoke All', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      await user.click(screen.getByText('Danger Zone'));
      await user.click(screen.getByRole('button', { name: 'Revoke All' }));

      await waitFor(() => {
        expect(screen.getByText(/WARNING: This will immediately revoke ALL licenses/)).toBeInTheDocument();
      });
    });
  });

  describe('styling', () => {
    it('renders cards for each settings section', () => {
      const { container } = renderWithProviders(<Settings />);

      // Should have multiple card sections
      const cards = container.querySelectorAll('.rounded-lg.border');
      expect(cards.length).toBeGreaterThanOrEqual(3);
    });
  });
});
