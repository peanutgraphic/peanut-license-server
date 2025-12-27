import { useState } from 'react';
import { Layout } from '@/components/layout';
import {
  Card,
  CardHeader,
  Button,
  Badge,
  HelpTooltip,
} from '@/components/common';
import {
  BarChart3,
  TrendingUp,
  TrendingDown,
  Download,
  Globe,
  CheckCircle2,
  XCircle,
} from 'lucide-react';
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  PieChart,
  Pie,
  Cell,
  Legend,
} from 'recharts';

// Mock data
const mockTimelineData = [
  { date: 'Mon', validations: 45, successes: 42, failures: 3 },
  { date: 'Tue', validations: 52, successes: 48, failures: 4 },
  { date: 'Wed', validations: 38, successes: 36, failures: 2 },
  { date: 'Thu', validations: 61, successes: 58, failures: 3 },
  { date: 'Fri', validations: 55, successes: 51, failures: 4 },
  { date: 'Sat', validations: 23, successes: 22, failures: 1 },
  { date: 'Sun', validations: 18, successes: 17, failures: 1 },
];

const mockTierDistribution = [
  { name: 'Free', value: 98, color: '#64748b' },
  { name: 'Pro', value: 112, color: '#3b82f6' },
  { name: 'Agency', value: 37, color: '#8b5cf6' },
];

const mockTopSites = [
  { site: 'https://enterprise.com', validations: 156, lastCheck: '2 min ago' },
  { site: 'https://agency-client.io', validations: 89, lastCheck: '15 min ago' },
  { site: 'https://developer.dev', validations: 72, lastCheck: '1 hour ago' },
  { site: 'https://startup.co', validations: 45, lastCheck: '3 hours ago' },
  { site: 'https://freelancer.net', validations: 34, lastCheck: '5 hours ago' },
];

const mockErrorTypes = [
  { type: 'Expired License', count: 23, percentage: 45 },
  { type: 'Invalid Key', count: 15, percentage: 29 },
  { type: 'Max Activations', count: 8, percentage: 16 },
  { type: 'Domain Mismatch', count: 5, percentage: 10 },
];

export default function Analytics() {
  const [timeRange, setTimeRange] = useState('7d');

  return (
    <Layout
      title="Analytics"
      description="License usage and validation statistics"
      action={
        <div className="flex items-center gap-2">
          <select
            value={timeRange}
            onChange={(e) => setTimeRange(e.target.value)}
            className="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="7d">Last 7 days</option>
            <option value="30d">Last 30 days</option>
            <option value="90d">Last 90 days</option>
            <option value="1y">Last year</option>
          </select>
          <Button variant="outline" size="sm">
            <Download className="w-4 h-4 mr-1" />
            Export
          </Button>
        </div>
      }
    >
      <div className="space-y-6">
        {/* Summary Stats */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card>
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-slate-500">Total Validations</p>
                <p className="text-2xl font-bold text-slate-900">1,247</p>
                <div className="flex items-center gap-1 mt-1 text-green-600 text-sm">
                  <TrendingUp className="w-4 h-4" />
                  <span>+12% from last week</span>
                </div>
              </div>
              <div className="p-3 bg-blue-100 rounded-lg">
                <BarChart3 className="w-6 h-6 text-blue-600" />
              </div>
            </div>
          </Card>
          <Card>
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-slate-500">Success Rate</p>
                <p className="text-2xl font-bold text-green-600">96.2%</p>
                <div className="flex items-center gap-1 mt-1 text-green-600 text-sm">
                  <TrendingUp className="w-4 h-4" />
                  <span>+2.1% from last week</span>
                </div>
              </div>
              <div className="p-3 bg-green-100 rounded-lg">
                <CheckCircle2 className="w-6 h-6 text-green-600" />
              </div>
            </div>
          </Card>
          <Card>
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-slate-500">Failed Validations</p>
                <p className="text-2xl font-bold text-red-600">47</p>
                <div className="flex items-center gap-1 mt-1 text-red-600 text-sm">
                  <TrendingDown className="w-4 h-4" />
                  <span>-8% from last week</span>
                </div>
              </div>
              <div className="p-3 bg-red-100 rounded-lg">
                <XCircle className="w-6 h-6 text-red-600" />
              </div>
            </div>
          </Card>
          <Card>
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-slate-500">Active Sites</p>
                <p className="text-2xl font-bold text-slate-900">312</p>
                <div className="flex items-center gap-1 mt-1 text-green-600 text-sm">
                  <TrendingUp className="w-4 h-4" />
                  <span>+5 new this week</span>
                </div>
              </div>
              <div className="p-3 bg-purple-100 rounded-lg">
                <Globe className="w-6 h-6 text-purple-600" />
              </div>
            </div>
          </Card>
        </div>

        {/* Charts */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Validation Timeline */}
          <Card className="lg:col-span-2">
            <CardHeader
              title={
                <span className="flex items-center gap-2">
                  Validation Timeline
                  <HelpTooltip content="Number of license validation requests over time." />
                </span>
              }
            />
            <div className="h-72">
              <ResponsiveContainer width="100%" height="100%">
                <LineChart data={mockTimelineData}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" />
                  <XAxis dataKey="date" stroke="#64748b" fontSize={12} />
                  <YAxis stroke="#64748b" fontSize={12} />
                  <Tooltip
                    contentStyle={{
                      backgroundColor: '#fff',
                      border: '1px solid #e2e8f0',
                      borderRadius: '8px',
                    }}
                  />
                  <Line
                    type="monotone"
                    dataKey="validations"
                    stroke="#3b82f6"
                    strokeWidth={2}
                    dot={false}
                  />
                  <Line
                    type="monotone"
                    dataKey="successes"
                    stroke="#22c55e"
                    strokeWidth={2}
                    dot={false}
                  />
                  <Line
                    type="monotone"
                    dataKey="failures"
                    stroke="#ef4444"
                    strokeWidth={2}
                    dot={false}
                  />
                  <Legend />
                </LineChart>
              </ResponsiveContainer>
            </div>
          </Card>

          {/* Tier Distribution */}
          <Card>
            <CardHeader
              title="License Tiers"
            />
            <div className="h-72">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={mockTierDistribution}
                    cx="50%"
                    cy="50%"
                    innerRadius={60}
                    outerRadius={80}
                    paddingAngle={5}
                    dataKey="value"
                  >
                    {mockTierDistribution.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip />
                  <Legend />
                </PieChart>
              </ResponsiveContainer>
            </div>
          </Card>
        </div>

        {/* Tables */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Top Sites */}
          <Card>
            <CardHeader
              title={
                <span className="flex items-center gap-2">
                  Most Active Sites
                  <HelpTooltip content="Sites with the most validation requests." />
                </span>
              }
            />
            <div className="space-y-3">
              {mockTopSites.map((site, i) => (
                <div
                  key={i}
                  className="flex items-center justify-between p-3 bg-slate-50 rounded-lg"
                >
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 bg-white rounded-lg flex items-center justify-center border border-slate-200 text-sm font-bold text-slate-500">
                      {i + 1}
                    </div>
                    <div>
                      <p className="text-sm font-medium text-slate-900">{site.site}</p>
                      <p className="text-xs text-slate-500">Last check: {site.lastCheck}</p>
                    </div>
                  </div>
                  <Badge variant="info">{site.validations} validations</Badge>
                </div>
              ))}
            </div>
          </Card>

          {/* Error Types */}
          <Card>
            <CardHeader
              title={
                <span className="flex items-center gap-2">
                  Validation Errors
                  <HelpTooltip content="Breakdown of failed validation reasons." />
                </span>
              }
            />
            <div className="space-y-3">
              {mockErrorTypes.map((error, i) => (
                <div key={i} className="p-3 bg-slate-50 rounded-lg">
                  <div className="flex items-center justify-between mb-2">
                    <span className="text-sm font-medium text-slate-900">{error.type}</span>
                    <span className="text-sm text-slate-500">{error.count} ({error.percentage}%)</span>
                  </div>
                  <div className="w-full bg-slate-200 rounded-full h-2">
                    <div
                      className="bg-red-500 rounded-full h-2 transition-all"
                      style={{ width: `${error.percentage}%` }}
                    />
                  </div>
                </div>
              ))}
            </div>
          </Card>
        </div>
      </div>
    </Layout>
  );
}
