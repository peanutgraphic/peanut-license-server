import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { Layout } from './Layout';

const renderWithRouter = (ui: React.ReactElement, initialRoute = '/') => {
  return render(
    <MemoryRouter initialEntries={[initialRoute]}>
      {ui}
    </MemoryRouter>
  );
};

describe('Layout', () => {
  describe('rendering', () => {
    it('renders title', () => {
      renderWithRouter(<Layout title="Test Page">Content</Layout>);
      expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Test Page');
    });

    it('renders children', () => {
      renderWithRouter(<Layout title="Page">Child content here</Layout>);
      expect(screen.getByText('Child content here')).toBeInTheDocument();
    });

    it('renders description when provided', () => {
      renderWithRouter(
        <Layout title="Page" description="Page description">Content</Layout>
      );
      expect(screen.getByText('Page description')).toBeInTheDocument();
    });

    it('does not render description when not provided', () => {
      renderWithRouter(<Layout title="Page">Content</Layout>);
      expect(screen.queryByText('description')).not.toBeInTheDocument();
    });

    it('renders action when provided', () => {
      renderWithRouter(
        <Layout title="Page" action={<button>Action Button</button>}>Content</Layout>
      );
      expect(screen.getByRole('button', { name: 'Action Button' })).toBeInTheDocument();
    });
  });

  describe('navigation', () => {
    it('renders all navigation links', () => {
      renderWithRouter(<Layout title="Page">Content</Layout>);

      expect(screen.getByRole('link', { name: /Dashboard/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /Licenses/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /Analytics/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /Audit/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /Webhooks/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /Products/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /GDPR/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /Security/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /Settings/i })).toBeInTheDocument();
    });

    it('renders navigation links with correct hrefs', () => {
      renderWithRouter(<Layout title="Page">Content</Layout>);

      expect(screen.getByRole('link', { name: /Dashboard/i })).toHaveAttribute('href', '/');
      expect(screen.getByRole('link', { name: /Licenses/i })).toHaveAttribute('href', '/licenses');
      expect(screen.getByRole('link', { name: /Analytics/i })).toHaveAttribute('href', '/analytics');
      expect(screen.getByRole('link', { name: /Settings/i })).toHaveAttribute('href', '/settings');
    });

    it('highlights active navigation item', () => {
      renderWithRouter(<Layout title="Page">Content</Layout>, '/licenses');

      const licensesLink = screen.getByRole('link', { name: /Licenses/i });
      expect(licensesLink).toHaveClass('border-primary-600', 'text-primary-600');
    });

    it('does not highlight inactive navigation items', () => {
      renderWithRouter(<Layout title="Page">Content</Layout>, '/');

      const licensesLink = screen.getByRole('link', { name: /Licenses/i });
      expect(licensesLink).toHaveClass('border-transparent');
    });
  });

  describe('structure', () => {
    it('renders header element', () => {
      renderWithRouter(<Layout title="Page">Content</Layout>);
      expect(screen.getByRole('banner')).toBeInTheDocument();
    });

    it('renders main element', () => {
      renderWithRouter(<Layout title="Page">Content</Layout>);
      expect(screen.getByRole('main')).toBeInTheDocument();
    });

    it('renders navigation element', () => {
      renderWithRouter(<Layout title="Page">Content</Layout>);
      expect(screen.getByRole('navigation')).toBeInTheDocument();
    });

    it('children are rendered inside main element', () => {
      renderWithRouter(<Layout title="Page">Main content</Layout>);
      const main = screen.getByRole('main');
      expect(main).toHaveTextContent('Main content');
    });
  });

  describe('icons', () => {
    it('renders icons for each navigation item', () => {
      const { container } = renderWithRouter(<Layout title="Page">Content</Layout>);

      // Each nav item should have an icon (svg element)
      const navLinks = screen.getAllByRole('link');
      navLinks.forEach(link => {
        expect(link.querySelector('svg')).toBeInTheDocument();
      });
    });
  });
});
