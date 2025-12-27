import { useState } from 'react';
import { Layout } from '@/components/layout';
import {
  Card,
  CardHeader,
  Button,
  Input,
  Switch,
  HelpTooltip,
  DangerZone,
  DangerAction,
} from '@/components/common';
import {
  Shield,
  AlertTriangle,
  Ban,
  RefreshCw,
  Globe,
  Lock,
  Eye,
} from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';

// Mock data
const mockBlockedIPs = [
  { ip: '45.33.32.156', reason: 'Brute force attempt', blocked_at: new Date(Date.now() - 86400000).toISOString(), attempts: 147 },
  { ip: '192.241.143.77', reason: 'Rate limit exceeded', blocked_at: new Date(Date.now() - 172800000).toISOString(), attempts: 89 },
  { ip: '104.236.112.222', reason: 'Invalid license abuse', blocked_at: new Date(Date.now() - 259200000).toISOString(), attempts: 234 },
];

const mockSecurityEvents = [
  { type: 'warning', message: 'Unusual validation pattern detected from 45.33.32.156', timestamp: new Date().toISOString() },
  { type: 'blocked', message: 'Blocked IP 192.241.143.77 after 89 failed attempts', timestamp: new Date(Date.now() - 3600000).toISOString() },
  { type: 'info', message: 'Rate limit temporarily reduced for high traffic', timestamp: new Date(Date.now() - 7200000).toISOString() },
];

export default function Security() {
  const [autoBlock, setAutoBlock] = useState(true);
  const [blockThreshold, setBlockThreshold] = useState(100);

  return (
    <Layout
      title="Security"
      description="Security monitoring and threat protection"
    >
      <div className="space-y-6">
        {/* Security Overview */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Card>
            <div className="flex items-center gap-3">
              <div className="p-3 bg-green-100 rounded-lg">
                <Shield className="w-6 h-6 text-green-600" />
              </div>
              <div>
                <p className="text-sm text-slate-500">Security Status</p>
                <p className="text-lg font-bold text-green-600">Protected</p>
              </div>
            </div>
          </Card>
          <Card>
            <div className="flex items-center gap-3">
              <div className="p-3 bg-red-100 rounded-lg">
                <Ban className="w-6 h-6 text-red-600" />
              </div>
              <div>
                <p className="text-sm text-slate-500">Blocked IPs</p>
                <p className="text-lg font-bold text-slate-900">{mockBlockedIPs.length}</p>
              </div>
            </div>
          </Card>
          <Card>
            <div className="flex items-center gap-3">
              <div className="p-3 bg-amber-100 rounded-lg">
                <AlertTriangle className="w-6 h-6 text-amber-600" />
              </div>
              <div>
                <p className="text-sm text-slate-500">Threats Blocked (24h)</p>
                <p className="text-lg font-bold text-slate-900">47</p>
              </div>
            </div>
          </Card>
        </div>

        {/* Auto-Block Settings */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                <Lock className="w-5 h-5 text-slate-400" />
                Auto-Block Settings
                <HelpTooltip content="Automatically block IPs that exceed the threshold." />
              </span>
            }
          />
          <div className="space-y-4">
            <Switch
              checked={autoBlock}
              onChange={setAutoBlock}
              label="Enable Auto-Block"
              description="Automatically block IPs after exceeding the failure threshold"
            />
            <div className="max-w-xs">
              <Input
                label="Block Threshold (failed attempts)"
                type="number"
                value={blockThreshold}
                onChange={(e) => setBlockThreshold(parseInt(e.target.value))}
                helpText="Number of failed attempts before auto-blocking"
                disabled={!autoBlock}
              />
            </div>
          </div>
        </Card>

        {/* Blocked IPs */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                <Ban className="w-5 h-5 text-slate-400" />
                Blocked IP Addresses
              </span>
            }
            action={
              <Button variant="outline" size="sm">
                <RefreshCw className="w-4 h-4 mr-1" />
                Refresh
              </Button>
            }
          />
          {mockBlockedIPs.length === 0 ? (
            <div className="text-center py-8 text-slate-500">
              No blocked IPs
            </div>
          ) : (
            <div className="space-y-3">
              {mockBlockedIPs.map((entry, i) => (
                <div
                  key={i}
                  className="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-100"
                >
                  <div className="flex items-center gap-3">
                    <div className="p-2 bg-red-100 rounded">
                      <Globe className="w-4 h-4 text-red-600" />
                    </div>
                    <div>
                      <code className="text-sm font-bold text-red-700">{entry.ip}</code>
                      <p className="text-xs text-red-600">{entry.reason}</p>
                      <p className="text-xs text-red-500 mt-1">
                        {entry.attempts} attempts · Blocked {formatDistanceToNow(new Date(entry.blocked_at), { addSuffix: true })}
                      </p>
                    </div>
                  </div>
                  <Button variant="outline" size="sm">Unblock</Button>
                </div>
              ))}
            </div>
          )}
        </Card>

        {/* Recent Security Events */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                <Eye className="w-5 h-5 text-slate-400" />
                Recent Security Events
              </span>
            }
          />
          <div className="space-y-3">
            {mockSecurityEvents.map((event, i) => (
              <div
                key={i}
                className={`flex items-start gap-3 p-3 rounded-lg ${
                  event.type === 'warning' ? 'bg-amber-50' :
                  event.type === 'blocked' ? 'bg-red-50' : 'bg-blue-50'
                }`}
              >
                <AlertTriangle className={`w-4 h-4 mt-0.5 ${
                  event.type === 'warning' ? 'text-amber-500' :
                  event.type === 'blocked' ? 'text-red-500' : 'text-blue-500'
                }`} />
                <div className="flex-1">
                  <p className={`text-sm ${
                    event.type === 'warning' ? 'text-amber-700' :
                    event.type === 'blocked' ? 'text-red-700' : 'text-blue-700'
                  }`}>
                    {event.message}
                  </p>
                  <p className="text-xs text-slate-400 mt-1">
                    {formatDistanceToNow(new Date(event.timestamp), { addSuffix: true })}
                  </p>
                </div>
              </div>
            ))}
          </div>
        </Card>

        {/* Danger Zone */}
        <DangerZone>
          <DangerAction
            title="Clear All Blocked IPs"
            description="Remove all IP blocks. Blocked IPs will be able to make requests again."
            buttonLabel="Clear Blocks"
            confirmMessage="This will unblock all currently blocked IP addresses. Are you sure?"
            onAction={() => {}}
          />
        </DangerZone>
      </div>
    </Layout>
  );
}
