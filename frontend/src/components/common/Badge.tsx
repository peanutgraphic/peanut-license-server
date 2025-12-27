import type { ReactNode } from 'react';

interface BadgeProps {
  children: ReactNode;
  variant?: 'default' | 'success' | 'warning' | 'error' | 'info' | 'purple';
  size?: 'sm' | 'md';
  className?: string;
}

export default function Badge({
  children,
  variant = 'default',
  size = 'md',
  className = '',
}: BadgeProps) {
  const variants = {
    default: 'bg-slate-100 text-slate-700',
    success: 'bg-green-100 text-green-700',
    warning: 'bg-amber-100 text-amber-700',
    error: 'bg-red-100 text-red-700',
    info: 'bg-blue-100 text-blue-700',
    purple: 'bg-purple-100 text-purple-700',
  };

  const sizes = {
    sm: 'px-1.5 py-0.5 text-xs',
    md: 'px-2 py-1 text-xs',
  };

  return (
    <span
      className={`inline-flex items-center font-medium rounded-full ${variants[variant]} ${sizes[size]} ${className}`}
    >
      {children}
    </span>
  );
}

interface StatusBadgeProps {
  status: 'active' | 'expired' | 'revoked' | 'suspended' | 'pending';
}

export function StatusBadge({ status }: StatusBadgeProps) {
  const config = {
    active: { variant: 'success' as const, label: 'Active' },
    expired: { variant: 'warning' as const, label: 'Expired' },
    revoked: { variant: 'error' as const, label: 'Revoked' },
    suspended: { variant: 'error' as const, label: 'Suspended' },
    pending: { variant: 'info' as const, label: 'Pending' },
  };

  const { variant, label } = config[status] || config.pending;

  return <Badge variant={variant}>{label}</Badge>;
}

interface TierBadgeProps {
  tier: 'free' | 'pro' | 'agency';
}

export function TierBadge({ tier }: TierBadgeProps) {
  const config = {
    free: { variant: 'default' as const, label: 'Free' },
    pro: { variant: 'info' as const, label: 'Pro' },
    agency: { variant: 'purple' as const, label: 'Agency' },
  };

  const { variant, label } = config[tier] || config.free;

  return <Badge variant={variant}>{label}</Badge>;
}
