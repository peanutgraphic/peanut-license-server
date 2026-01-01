import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import Sidebar from './Sidebar';

const renderWithRouter = (initialRoute = '/') => {
  return render(
    <MemoryRouter initialEntries={[initialRoute]}>
      <Sidebar />
    </MemoryRouter>
  );
};

describe('Sidebar', () => {
  describe('branding', () => {
    it('renders logo section', () => {
      renderWithRouter();
      expect(screen.getByText('License')).toBeInTheDocument();
      expect(screen.getByText('Server')).toBeInTheDocument();
    });

    it('renders version number', () => {
      renderWithRouter();
      expect(screen.getByText(/Peanut License Server v/)).toBeInTheDocument();
    });
  });

  describe('navigation', () => {
    it('renders all navigation links', () => {
      renderWithRouter();

      expect(screen.getByRole('link', { name: /Dashboard/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /Licenses/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /Analytics/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /Audit Trail/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /Webhooks/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /Products/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /GDPR Tools/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /Security/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /Settings/i })).toBeInTheDocument();
    });

    it('renders correct number of navigation items', () => {
      renderWithRouter();
      const navLinks = screen.getAllByRole('link');
      expect(navLinks).toHaveLength(9);
    });

    it('renders navigation links with correct hrefs', () => {
      renderWithRouter();

      expect(screen.getByRole('link', { name: /Dashboard/i })).toHaveAttribute('href', '/');
      expect(screen.getByRole('link', { name: /Licenses/i })).toHaveAttribute('href', '/licenses');
      expect(screen.getByRole('link', { name: /Analytics/i })).toHaveAttribute('href', '/analytics');
      expect(screen.getByRole('link', { name: /Audit Trail/i })).toHaveAttribute('href', '/audit');
      expect(screen.getByRole('link', { name: /Webhooks/i })).toHaveAttribute('href', '/webhooks');
      expect(screen.getByRole('link', { name: /Products/i })).toHaveAttribute('href', '/products');
      expect(screen.getByRole('link', { name: /GDPR Tools/i })).toHaveAttribute('href', '/gdpr');
      expect(screen.getByRole('link', { name: /Security/i })).toHaveAttribute('href', '/security');
      expect(screen.getByRole('link', { name: /Settings/i })).toHaveAttribute('href', '/settings');
    });
  });

  describe('active state', () => {
    it('highlights Dashboard when on home route', () => {
      renderWithRouter('/');
      const dashboardLink = screen.getByRole('link', { name: /Dashboard/i });
      expect(dashboardLink).toHaveClass('bg-primary-600', 'text-white');
    });

    it('highlights Licenses when on licenses route', () => {
      renderWithRouter('/licenses');
      const licensesLink = screen.getByRole('link', { name: /Licenses/i });
      expect(licensesLink).toHaveClass('bg-primary-600', 'text-white');
    });

    it('highlights Analytics when on analytics route', () => {
      renderWithRouter('/analytics');
      const analyticsLink = screen.getByRole('link', { name: /Analytics/i });
      expect(analyticsLink).toHaveClass('bg-primary-600', 'text-white');
    });

    it('highlights Settings when on settings route', () => {
      renderWithRouter('/settings');
      const settingsLink = screen.getByRole('link', { name: /Settings/i });
      expect(settingsLink).toHaveClass('bg-primary-600', 'text-white');
    });

    it('does not highlight inactive items', () => {
      renderWithRouter('/');
      const licensesLink = screen.getByRole('link', { name: /Licenses/i });
      expect(licensesLink).not.toHaveClass('bg-primary-600');
      expect(licensesLink).toHaveClass('text-slate-300');
    });
  });

  describe('icons', () => {
    it('renders an icon for each navigation item', () => {
      renderWithRouter();
      const navLinks = screen.getAllByRole('link');

      navLinks.forEach(link => {
        const icon = link.querySelector('svg');
        expect(icon).toBeInTheDocument();
        expect(icon).toHaveClass('w-5', 'h-5');
      });
    });
  });

  describe('structure', () => {
    it('renders as aside element', () => {
      renderWithRouter();
      expect(screen.getByRole('complementary')).toBeInTheDocument();
    });

    it('renders navigation element', () => {
      renderWithRouter();
      expect(screen.getByRole('navigation')).toBeInTheDocument();
    });

    it('has dark background styling', () => {
      renderWithRouter();
      const sidebar = screen.getByRole('complementary');
      expect(sidebar).toHaveClass('bg-slate-900');
    });
  });
});
