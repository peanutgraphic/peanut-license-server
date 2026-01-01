import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import {
  Skeleton,
  SkeletonText,
  SkeletonCard,
  SkeletonStatCard,
  SkeletonTable,
  SkeletonList,
  DashboardSkeleton,
  LicensesSkeleton,
} from './Skeleton';

describe('Skeleton', () => {
  it('renders with default styling', () => {
    const { container } = render(<Skeleton />);
    expect(container.firstChild).toHaveClass('animate-shimmer', 'rounded', 'bg-slate-200');
  });

  it('applies custom className', () => {
    const { container } = render(<Skeleton className="h-4 w-full" />);
    expect(container.firstChild).toHaveClass('h-4', 'w-full');
  });

  it('merges custom className with default classes', () => {
    const { container } = render(<Skeleton className="h-4" />);
    expect(container.firstChild).toHaveClass('animate-shimmer', 'bg-slate-200', 'h-4');
  });
});

describe('SkeletonText', () => {
  it('renders 3 lines by default', () => {
    const { container } = render(<SkeletonText />);
    const skeletons = container.querySelectorAll('.animate-shimmer');
    expect(skeletons).toHaveLength(3);
  });

  it('renders specified number of lines', () => {
    const { container } = render(<SkeletonText lines={5} />);
    const skeletons = container.querySelectorAll('.animate-shimmer');
    expect(skeletons).toHaveLength(5);
  });

  it('renders last line with w-3/4 class', () => {
    const { container } = render(<SkeletonText lines={3} />);
    const skeletons = container.querySelectorAll('.animate-shimmer');
    expect(skeletons[2]).toHaveClass('w-3/4');
  });

  it('renders non-last lines with w-full class', () => {
    const { container } = render(<SkeletonText lines={3} />);
    const skeletons = container.querySelectorAll('.animate-shimmer');
    expect(skeletons[0]).toHaveClass('w-full');
    expect(skeletons[1]).toHaveClass('w-full');
  });

  it('applies h-4 height to all lines', () => {
    const { container } = render(<SkeletonText lines={2} />);
    const skeletons = container.querySelectorAll('.animate-shimmer');
    skeletons.forEach(skeleton => {
      expect(skeleton).toHaveClass('h-4');
    });
  });
});

describe('SkeletonCard', () => {
  it('renders card container', () => {
    const { container } = render(<SkeletonCard />);
    expect(container.querySelector('.bg-white.rounded-lg.border')).toBeInTheDocument();
  });

  it('renders skeleton elements inside', () => {
    const { container } = render(<SkeletonCard />);
    const skeletons = container.querySelectorAll('.animate-shimmer');
    expect(skeletons.length).toBeGreaterThan(0);
  });
});

describe('SkeletonStatCard', () => {
  it('renders card container', () => {
    const { container } = render(<SkeletonStatCard />);
    expect(container.querySelector('.bg-white.rounded-lg.border')).toBeInTheDocument();
  });

  it('renders skeleton elements for label and value', () => {
    const { container } = render(<SkeletonStatCard />);
    const skeletons = container.querySelectorAll('.animate-shimmer');
    expect(skeletons.length).toBeGreaterThanOrEqual(2);
  });

  it('renders icon placeholder', () => {
    const { container } = render(<SkeletonStatCard />);
    const iconSkeleton = container.querySelector('.h-10.w-10.rounded-lg');
    expect(iconSkeleton).toBeInTheDocument();
  });
});

describe('SkeletonTable', () => {
  it('renders 5 rows by default', () => {
    const { container } = render(<SkeletonTable />);
    const rows = container.querySelectorAll('.border-b.border-slate-100');
    expect(rows).toHaveLength(5);
  });

  it('renders specified number of rows', () => {
    const { container } = render(<SkeletonTable rows={10} />);
    const rows = container.querySelectorAll('.border-b.border-slate-100');
    expect(rows).toHaveLength(10);
  });

  it('renders table container', () => {
    const { container } = render(<SkeletonTable />);
    expect(container.querySelector('.bg-white.rounded-lg.border')).toBeInTheDocument();
  });

  it('renders header row', () => {
    const { container } = render(<SkeletonTable />);
    const header = container.querySelector('.border-b.border-slate-200.p-4');
    expect(header).toBeInTheDocument();
  });
});

describe('SkeletonList', () => {
  it('renders 5 items by default', () => {
    const { container } = render(<SkeletonList />);
    const items = container.querySelectorAll('.bg-white.rounded-lg.border');
    expect(items).toHaveLength(5);
  });

  it('renders specified number of items', () => {
    const { container } = render(<SkeletonList items={3} />);
    const items = container.querySelectorAll('.bg-white.rounded-lg.border');
    expect(items).toHaveLength(3);
  });

  it('renders avatar placeholder for each item', () => {
    const { container } = render(<SkeletonList items={2} />);
    const avatars = container.querySelectorAll('.h-10.w-10.rounded-full');
    expect(avatars).toHaveLength(2);
  });

  it('renders badge placeholder for each item', () => {
    const { container } = render(<SkeletonList items={2} />);
    const badges = container.querySelectorAll('.h-6.w-16.rounded-full');
    expect(badges).toHaveLength(2);
  });
});

describe('DashboardSkeleton', () => {
  it('renders stat cards grid', () => {
    const { container } = render(<DashboardSkeleton />);
    const statsGrid = container.querySelector('.grid');
    expect(statsGrid).toBeInTheDocument();
  });

  it('renders 4 stat card skeletons', () => {
    const { container } = render(<DashboardSkeleton />);
    // SkeletonStatCard has specific structure
    const statCards = container.querySelectorAll('.grid > div');
    expect(statCards).toHaveLength(4);
  });

  it('renders chart placeholder', () => {
    const { container } = render(<DashboardSkeleton />);
    const chartPlaceholder = container.querySelector('.h-64');
    expect(chartPlaceholder).toBeInTheDocument();
  });

  it('renders table skeleton', () => {
    const { container } = render(<DashboardSkeleton />);
    // SkeletonTable has 5 rows by default
    const tableRows = container.querySelectorAll('.border-b.border-slate-100');
    expect(tableRows).toHaveLength(5);
  });
});

describe('LicensesSkeleton', () => {
  it('renders filters placeholder', () => {
    const { container } = render(<LicensesSkeleton />);
    const filterBar = container.querySelectorAll('.bg-white.rounded-lg.border')[0];
    expect(filterBar).toBeInTheDocument();
  });

  it('renders table with 10 rows', () => {
    const { container } = render(<LicensesSkeleton />);
    const tableRows = container.querySelectorAll('.border-b.border-slate-100');
    expect(tableRows).toHaveLength(10);
  });

  it('renders search input placeholder in filters', () => {
    const { container } = render(<LicensesSkeleton />);
    const searchPlaceholder = container.querySelector('.h-10.w-64');
    expect(searchPlaceholder).toBeInTheDocument();
  });
});
