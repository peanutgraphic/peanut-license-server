import { useState } from 'react';
import { Layout } from '@/components/layout';
import {
  Card,
  CardHeader,
  StatCard,
  Button,
  Badge,
  StatusBadge,
  TierBadge,
  CollapsibleBanner,
  HelpTooltip,
  DashboardSkeleton,
} from '@/components/common';
import {
  Key,
  CheckCircle2,
  XCircle,
  TrendingUp,
  RefreshCw,
  Plus,
  ExternalLink,
  Activity,
  Users,
  DollarSign,
  AlertTriangle,
} from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { formatDistanceToNow } from 'date-fns';

// Mock data for demo
const mockStats = {
  total: 247,
  active: 189,
  expired: 42,
  revoked: 16,
  by_tier: { free: 98, pro: 112, agency: 37 },
  activations: { total: 423, active: 312 },
  revenue: { total: 28450, this_month: 3240, last_month: 2980 },
};

const mockRecentLicenses = [
  { id: 1, license_key: 'PNUT-PRO-7A3B...', email: 'client@example.com', tier: 'pro' as const, status: 'active' as const, created_at: new Date().toISOString() },
  { id: 2, license_key: 'PNUT-AGY-9F2C...', email: 'agency@studio.com', tier: 'agency' as const, status: 'active' as const, created_at: new Date(Date.now() - 86400000).toISOString() },
  { id: 3, license_key: 'PNUT-FREE-4D1E...', email: 'user@domain.org', tier: 'free' as const, status: 'expired' as const, created_at: new Date(Date.now() - 172800000).toISOString() },
];

const mockRecentValidations = [
  { site: 'https://clientsite.com', success: true, timestamp: new Date().toISOString() },
  { site: 'https://agency-client1.com', success: true, timestamp: new Date(Date.now() - 3600000).toISOString() },
  { site: 'https://expired-license.net', success: false, timestamp: new Date(Date.now() - 7200000).toISOString() },
];

export default function Dashboard() {
  const navigate = useNavigate();
  const [isLoading, setIsLoading] = useState(false);
  const stats = mockStats;

  const handleRefresh = () => {
    setIsLoading(true);
    setTimeout(() => setIsLoading(false), 1000);
  };

  if (isLoading) {
    return (
      <Layout
        title="Dashboard"
        description="License management overview"
      >
        <DashboardSkeleton />
      </Layout>
    );
  }

  return (
    <Layout
      title="Dashboard"
      description="License management overview"
      action={
        <div className="flex items-center gap-2">
          <Button variant="outline" size="sm" onClick={handleRefresh}>
            <RefreshCw className="w-4 h-4 mr-1" />
            Refresh
          </Button>
          <Button size="sm" onClick={() => navigate('/licenses?action=add')}>
            <Plus className="w-4 h-4 mr-1" />
            New License
          </Button>
        </div>
      }
    >
      <div className="space-y-6">
        {/* Welcome Banner */}
        <CollapsibleBanner
          variant="info"
          title="Welcome to License Server"
          icon={<Key className="w-4 h-4" />}
          defaultOpen={false}
          dismissible
        >
          <p className="mb-2">
            Manage licenses, track activations, and monitor your plugin ecosystem from this dashboard.
          </p>
          <ul className="list-disc list-inside space-y-1 text-sm">
            <li>Create and manage license keys for your products</li>
            <li>Track site activations and validation requests</li>
            <li>View analytics and revenue metrics</li>
            <li>Configure webhooks and integrations</li>
          </ul>
        </CollapsibleBanner>

        {/* Key Stats */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard
            label="Total Licenses"
            value={stats.total}
            icon={<Key className="w-5 h-5" />}
            color="blue"
          />
          <StatCard
            label="Active Licenses"
            value={stats.active}
            icon={<CheckCircle2 className="w-5 h-5" />}
            color="green"
            trend={{ value: 8, positive: true }}
          />
          <StatCard
            label="Active Activations"
            value={stats.activations.active}
            icon={<Activity className="w-5 h-5" />}
            color="purple"
          />
          <StatCard
            label="This Month Revenue"
            value={`$${stats.revenue.this_month.toLocaleString()}`}
            icon={<DollarSign className="w-5 h-5" />}
            color="green"
            trend={{
              value: Math.round(((stats.revenue.this_month - stats.revenue.last_month) / stats.revenue.last_month) * 100),
              positive: stats.revenue.this_month > stats.revenue.last_month,
            }}
          />
        </div>

        {/* License Distribution & Quick Actions */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* License Distribution */}
          <Card className="lg:col-span-2">
            <CardHeader
              title={
                <span className="flex items-center gap-2">
                  License Distribution
                  <HelpTooltip content="Breakdown of licenses by tier and status." />
                </span>
              }
            />
            <div className="grid grid-cols-2 gap-4">
              {/* By Status */}
              <div>
                <h4 className="text-sm font-medium text-slate-500 mb-3">By Status</h4>
                <div className="space-y-2">
                  <div className="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <span className="text-sm font-medium text-green-700">Active</span>
                    <span className="text-lg font-bold text-green-700">{stats.active}</span>
                  </div>
                  <div className="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
                    <span className="text-sm font-medium text-amber-700">Expired</span>
                    <span className="text-lg font-bold text-amber-700">{stats.expired}</span>
                  </div>
                  <div className="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                    <span className="text-sm font-medium text-red-700">Revoked</span>
                    <span className="text-lg font-bold text-red-700">{stats.revoked}</span>
                  </div>
                </div>
              </div>
              {/* By Tier */}
              <div>
                <h4 className="text-sm font-medium text-slate-500 mb-3">By Tier</h4>
                <div className="space-y-2">
                  <div className="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                    <span className="text-sm font-medium text-slate-700">Free</span>
                    <span className="text-lg font-bold text-slate-700">{stats.by_tier.free}</span>
                  </div>
                  <div className="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                    <span className="text-sm font-medium text-blue-700">Pro</span>
                    <span className="text-lg font-bold text-blue-700">{stats.by_tier.pro}</span>
                  </div>
                  <div className="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                    <span className="text-sm font-medium text-purple-700">Agency</span>
                    <span className="text-lg font-bold text-purple-700">{stats.by_tier.agency}</span>
                  </div>
                </div>
              </div>
            </div>
          </Card>

          {/* Quick Actions */}
          <Card>
            <CardHeader title="Quick Actions" />
            <div className="space-y-2">
              <button
                onClick={() => navigate('/licenses?action=add')}
                className="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors text-left"
              >
                <div className="p-2 bg-blue-100 rounded-lg text-blue-600">
                  <Plus className="w-4 h-4" />
                </div>
                <div>
                  <p className="font-medium text-slate-900">Create License</p>
                  <p className="text-xs text-slate-500">Generate a new license key</p>
                </div>
              </button>
              <button
                onClick={() => navigate('/licenses?action=batch')}
                className="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors text-left"
              >
                <div className="p-2 bg-purple-100 rounded-lg text-purple-600">
                  <Users className="w-4 h-4" />
                </div>
                <div>
                  <p className="font-medium text-slate-900">Batch Generate</p>
                  <p className="text-xs text-slate-500">Create multiple licenses</p>
                </div>
              </button>
              <button
                onClick={() => navigate('/analytics')}
                className="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors text-left"
              >
                <div className="p-2 bg-green-100 rounded-lg text-green-600">
                  <TrendingUp className="w-4 h-4" />
                </div>
                <div>
                  <p className="font-medium text-slate-900">View Analytics</p>
                  <p className="text-xs text-slate-500">Charts and insights</p>
                </div>
              </button>
            </div>
          </Card>
        </div>

        {/* Recent Activity */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Recent Licenses */}
          <Card>
            <CardHeader
              title="Recent Licenses"
              action={
                <Button variant="ghost" size="sm" onClick={() => navigate('/licenses')}>
                  View All
                  <ExternalLink className="w-3 h-3 ml-1" />
                </Button>
              }
            />
            <div className="space-y-3">
              {mockRecentLicenses.map((license) => (
                <div
                  key={license.id}
                  className="flex items-center justify-between p-3 bg-slate-50 rounded-lg hover:bg-slate-100 cursor-pointer transition-colors"
                  onClick={() => navigate(`/licenses/${license.id}`)}
                >
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-white rounded-lg flex items-center justify-center border border-slate-200">
                      <Key className="w-5 h-5 text-slate-400" />
                    </div>
                    <div>
                      <p className="font-mono text-sm text-slate-900">{license.license_key}</p>
                      <p className="text-xs text-slate-500">{license.email}</p>
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    <TierBadge tier={license.tier} />
                    <StatusBadge status={license.status} />
                  </div>
                </div>
              ))}
            </div>
          </Card>

          {/* Recent Validations */}
          <Card>
            <CardHeader
              title={
                <span className="flex items-center gap-2">
                  Recent Validations
                  <HelpTooltip content="License validation attempts from connected sites." />
                </span>
              }
              action={
                <Button variant="ghost" size="sm" onClick={() => navigate('/audit')}>
                  View Audit
                  <ExternalLink className="w-3 h-3 ml-1" />
                </Button>
              }
            />
            <div className="space-y-3">
              {mockRecentValidations.map((validation, i) => (
                <div
                  key={i}
                  className="flex items-center justify-between p-3 bg-slate-50 rounded-lg"
                >
                  <div className="flex items-center gap-3">
                    <div className={`w-10 h-10 rounded-lg flex items-center justify-center ${
                      validation.success ? 'bg-green-100' : 'bg-red-100'
                    }`}>
                      {validation.success ? (
                        <CheckCircle2 className="w-5 h-5 text-green-600" />
                      ) : (
                        <XCircle className="w-5 h-5 text-red-600" />
                      )}
                    </div>
                    <div>
                      <p className="text-sm font-medium text-slate-900">{validation.site}</p>
                      <p className="text-xs text-slate-500">
                        {formatDistanceToNow(new Date(validation.timestamp), { addSuffix: true })}
                      </p>
                    </div>
                  </div>
                  <Badge variant={validation.success ? 'success' : 'error'}>
                    {validation.success ? 'Valid' : 'Failed'}
                  </Badge>
                </div>
              ))}
            </div>
          </Card>
        </div>

        {/* Alerts */}
        {stats.expired > 0 && (
          <Card className="border-amber-200 bg-amber-50">
            <div className="flex items-start gap-3">
              <AlertTriangle className="w-5 h-5 text-amber-500 mt-0.5" />
              <div>
                <h4 className="font-medium text-amber-800">Expiring Licenses</h4>
                <p className="text-sm text-amber-700 mt-1">
                  You have {stats.expired} expired licenses. Consider sending renewal reminders or extending them.
                </p>
                <Button
                  variant="outline"
                  size="sm"
                  className="mt-3"
                  onClick={() => navigate('/licenses?status=expired')}
                >
                  View Expired Licenses
                </Button>
              </div>
            </div>
          </Card>
        )}
      </div>
    </Layout>
  );
}
