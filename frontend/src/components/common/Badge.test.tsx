import { describe, it, expect } from 'vitest';
import { render, screen } from '@/test/test-utils';
import Badge, { StatusBadge, TierBadge } from './Badge';

describe('Badge', () => {
  // =========================================
  // Basic Rendering Tests
  // =========================================

  it('renders children correctly', () => {
    render(<Badge>Test Badge</Badge>);
    expect(screen.getByText('Test Badge')).toBeInTheDocument();
  });

  it('renders as a span element', () => {
    render(<Badge>Test</Badge>);
    const badge = screen.getByText('Test');
    expect(badge.tagName).toBe('SPAN');
  });

  // =========================================
  // Variant Tests
  // =========================================

  it('applies default variant styles', () => {
    render(<Badge>Default</Badge>);
    const badge = screen.getByText('Default');
    expect(badge).toHaveClass('bg-slate-100', 'text-slate-700');
  });

  it('applies success variant styles', () => {
    render(<Badge variant="success">Success</Badge>);
    const badge = screen.getByText('Success');
    expect(badge).toHaveClass('bg-green-100', 'text-green-700');
  });

  it('applies warning variant styles', () => {
    render(<Badge variant="warning">Warning</Badge>);
    const badge = screen.getByText('Warning');
    expect(badge).toHaveClass('bg-amber-100', 'text-amber-700');
  });

  it('applies error variant styles', () => {
    render(<Badge variant="error">Error</Badge>);
    const badge = screen.getByText('Error');
    expect(badge).toHaveClass('bg-red-100', 'text-red-700');
  });

  it('applies info variant styles', () => {
    render(<Badge variant="info">Info</Badge>);
    const badge = screen.getByText('Info');
    expect(badge).toHaveClass('bg-blue-100', 'text-blue-700');
  });

  it('applies purple variant styles', () => {
    render(<Badge variant="purple">Purple</Badge>);
    const badge = screen.getByText('Purple');
    expect(badge).toHaveClass('bg-purple-100', 'text-purple-700');
  });

  // =========================================
  // Size Tests
  // =========================================

  it('applies medium size by default', () => {
    render(<Badge>Medium</Badge>);
    const badge = screen.getByText('Medium');
    expect(badge).toHaveClass('px-2', 'py-1');
  });

  it('applies small size styles', () => {
    render(<Badge size="sm">Small</Badge>);
    const badge = screen.getByText('Small');
    expect(badge).toHaveClass('px-1.5', 'py-0.5');
  });

  // =========================================
  // Custom ClassName Tests
  // =========================================

  it('merges custom className', () => {
    render(<Badge className="custom-class">Custom</Badge>);
    const badge = screen.getByText('Custom');
    expect(badge).toHaveClass('custom-class');
    expect(badge).toHaveClass('rounded-full'); // base style
  });
});

describe('StatusBadge', () => {
  // =========================================
  // Status Variant Tests
  // =========================================

  it('renders Active status correctly', () => {
    render(<StatusBadge status="active" />);
    const badge = screen.getByText('Active');
    expect(badge).toHaveClass('bg-green-100', 'text-green-700');
  });

  it('renders Expired status correctly', () => {
    render(<StatusBadge status="expired" />);
    const badge = screen.getByText('Expired');
    expect(badge).toHaveClass('bg-amber-100', 'text-amber-700');
  });

  it('renders Revoked status correctly', () => {
    render(<StatusBadge status="revoked" />);
    const badge = screen.getByText('Revoked');
    expect(badge).toHaveClass('bg-red-100', 'text-red-700');
  });

  it('renders Suspended status correctly', () => {
    render(<StatusBadge status="suspended" />);
    const badge = screen.getByText('Suspended');
    expect(badge).toHaveClass('bg-red-100', 'text-red-700');
  });

  it('renders Pending status correctly', () => {
    render(<StatusBadge status="pending" />);
    const badge = screen.getByText('Pending');
    expect(badge).toHaveClass('bg-blue-100', 'text-blue-700');
  });
});

describe('TierBadge', () => {
  // =========================================
  // Tier Variant Tests
  // =========================================

  it('renders Free tier correctly', () => {
    render(<TierBadge tier="free" />);
    const badge = screen.getByText('Free');
    expect(badge).toHaveClass('bg-slate-100', 'text-slate-700');
  });

  it('renders Pro tier correctly', () => {
    render(<TierBadge tier="pro" />);
    const badge = screen.getByText('Pro');
    expect(badge).toHaveClass('bg-blue-100', 'text-blue-700');
  });

  it('renders Agency tier correctly', () => {
    render(<TierBadge tier="agency" />);
    const badge = screen.getByText('Agency');
    expect(badge).toHaveClass('bg-purple-100', 'text-purple-700');
  });
});
