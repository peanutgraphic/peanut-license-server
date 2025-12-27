import { useState } from 'react';
import { Layout } from '@/components/layout';
import {
  Card,
  Button,
  Input,
  Badge,
  CollapsibleBanner,
} from '@/components/common';
import {
  FileText,
  Search,
  Download,
  RefreshCw,
  Key,
  Globe,
  Settings,
  Shield,
  ChevronLeft,
  ChevronRight,
} from 'lucide-react';
import { format, formatDistanceToNow } from 'date-fns';

// Mock data
const mockAuditEntries = [
  { id: 1, action: 'license.created', entity: 'License #247', user: 'admin@site.com', details: 'Created Pro license for client@example.com', ip: '192.168.1.1', timestamp: new Date().toISOString() },
  { id: 2, action: 'license.validated', entity: 'License #189', user: 'system', details: 'Successful validation from clientsite.com', ip: '45.32.18.92', timestamp: new Date(Date.now() - 3600000).toISOString() },
  { id: 3, action: 'license.suspended', entity: 'License #42', user: 'admin@site.com', details: 'Suspended due to payment failure', ip: '192.168.1.1', timestamp: new Date(Date.now() - 7200000).toISOString() },
  { id: 4, action: 'settings.changed', entity: 'Settings', user: 'admin@site.com', details: 'Updated rate limit from 100 to 150', ip: '192.168.1.1', timestamp: new Date(Date.now() - 86400000).toISOString() },
  { id: 5, action: 'webhook.triggered', entity: 'Webhook #3', user: 'system', details: 'Sent license.activated to https://api.example.com/webhook', ip: null, timestamp: new Date(Date.now() - 90000000).toISOString() },
];

const actionIcons: Record<string, typeof Key> = {
  'license.created': Key,
  'license.validated': Shield,
  'license.suspended': Key,
  'settings.changed': Settings,
  'webhook.triggered': Globe,
};

const actionColors: Record<string, string> = {
  'license.created': 'bg-green-100 text-green-600',
  'license.validated': 'bg-blue-100 text-blue-600',
  'license.suspended': 'bg-amber-100 text-amber-600',
  'settings.changed': 'bg-purple-100 text-purple-600',
  'webhook.triggered': 'bg-slate-100 text-slate-600',
};

export default function Audit() {
  const [search, setSearch] = useState('');
  const [actionFilter, setActionFilter] = useState('');

  return (
    <Layout
      title="Audit Trail"
      description="Complete log of all system actions"
      action={
        <div className="flex items-center gap-2">
          <Button variant="outline" size="sm">
            <Download className="w-4 h-4 mr-1" />
            Export
          </Button>
          <Button variant="outline" size="sm">
            <RefreshCw className="w-4 h-4 mr-1" />
            Refresh
          </Button>
        </div>
      }
    >
      <div className="space-y-6">
        {/* Info Banner */}
        <CollapsibleBanner
          variant="amber"
          title="About Audit Trail"
          icon={<FileText className="w-4 h-4" />}
          defaultOpen={false}
          dismissible
        >
          <p>
            The audit trail records all significant actions in the system, including license
            operations, setting changes, and API requests. Use this for compliance, debugging,
            and security monitoring.
          </p>
        </CollapsibleBanner>

        {/* Filters */}
        <Card>
          <div className="flex flex-wrap items-center gap-4">
            <div className="flex-1 min-w-[200px]">
              <Input
                placeholder="Search actions, users, or details..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                leftIcon={<Search className="w-4 h-4" />}
              />
            </div>
            <select
              value={actionFilter}
              onChange={(e) => setActionFilter(e.target.value)}
              className="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">All Actions</option>
              <option value="license">License Actions</option>
              <option value="settings">Settings Changes</option>
              <option value="webhook">Webhooks</option>
            </select>
            <div className="flex items-center gap-2 text-sm text-slate-500">
              <FileText className="w-4 h-4" />
              {mockAuditEntries.length} entries
            </div>
          </div>
        </Card>

        {/* Audit Log */}
        <Card padding="none">
          <div className="divide-y divide-slate-100">
            {mockAuditEntries.map((entry) => {
              const Icon = actionIcons[entry.action] || FileText;
              const colorClass = actionColors[entry.action] || 'bg-slate-100 text-slate-600';

              return (
                <div key={entry.id} className="flex items-start gap-4 p-4 hover:bg-slate-50 transition-colors">
                  <div className={`p-2 rounded-lg ${colorClass}`}>
                    <Icon className="w-5 h-5" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2 mb-1">
                      <code className="text-sm font-medium text-slate-900">{entry.action}</code>
                      <Badge variant="default" size="sm">{entry.entity}</Badge>
                    </div>
                    <p className="text-sm text-slate-600">{entry.details}</p>
                    <div className="flex items-center gap-4 mt-2 text-xs text-slate-400">
                      <span>By: {entry.user}</span>
                      {entry.ip && <span>IP: {entry.ip}</span>}
                      <span>{formatDistanceToNow(new Date(entry.timestamp), { addSuffix: true })}</span>
                    </div>
                  </div>
                  <div className="text-xs text-slate-400">
                    {format(new Date(entry.timestamp), 'MMM d, h:mm a')}
                  </div>
                </div>
              );
            })}
          </div>

          {/* Pagination */}
          <div className="flex items-center justify-between px-4 py-3 border-t border-slate-200">
            <p className="text-sm text-slate-500">
              Showing 1 to {mockAuditEntries.length} of {mockAuditEntries.length} entries
            </p>
            <div className="flex items-center gap-2">
              <Button variant="outline" size="sm" disabled>
                <ChevronLeft className="w-4 h-4" />
              </Button>
              <Button variant="outline" size="sm" disabled>
                <ChevronRight className="w-4 h-4" />
              </Button>
            </div>
          </div>
        </Card>
      </div>
    </Layout>
  );
}
