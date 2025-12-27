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
  Save,
  RefreshCw,
  Key,
  Globe,
  Shield,
} from 'lucide-react';

export default function Settings() {
  const [settings, setSettings] = useState({
    api_enabled: true,
    update_enabled: true,
    cache_duration: 12,
    rate_limit: 100,
    require_ssl: true,
    allowed_domains: '',
    webhook_secret: 'wh_secret_abc123...',
  });
  const [isSaving, setIsSaving] = useState(false);

  const handleSave = async () => {
    setIsSaving(true);
    await new Promise((r) => setTimeout(r, 1000));
    setIsSaving(false);
  };

  return (
    <Layout
      title="Settings"
      description="Configure server behavior and security options"
      action={
        <Button onClick={handleSave} loading={isSaving}>
          <Save className="w-4 h-4 mr-1" />
          Save Settings
        </Button>
      }
    >
      <div className="space-y-6 max-w-3xl">
        {/* API Settings */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                <Key className="w-5 h-5 text-slate-400" />
                API Settings
              </span>
            }
            description="Configure license validation API behavior"
          />
          <div className="space-y-4">
            <Switch
              checked={settings.api_enabled}
              onChange={(checked) => setSettings({ ...settings, api_enabled: checked })}
              label="Enable License API"
              description="Allow external sites to validate licenses via REST API"
            />
            <Switch
              checked={settings.update_enabled}
              onChange={(checked) => setSettings({ ...settings, update_enabled: checked })}
              label="Enable Update Server"
              description="Serve plugin updates to licensed sites"
            />
            <div className="grid grid-cols-2 gap-4">
              <Input
                label="Cache Duration (hours)"
                type="number"
                value={settings.cache_duration}
                onChange={(e) => setSettings({ ...settings, cache_duration: parseInt(e.target.value) })}
                helpText="How long to cache validation results"
              />
              <Input
                label="Rate Limit (requests/hour)"
                type="number"
                value={settings.rate_limit}
                onChange={(e) => setSettings({ ...settings, rate_limit: parseInt(e.target.value) })}
                helpText="Max validation requests per IP per hour"
              />
            </div>
          </div>
        </Card>

        {/* Security Settings */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                <Shield className="w-5 h-5 text-slate-400" />
                Security
                <HelpTooltip content="Configure security options for license validation." />
              </span>
            }
          />
          <div className="space-y-4">
            <Switch
              checked={settings.require_ssl}
              onChange={(checked) => setSettings({ ...settings, require_ssl: checked })}
              label="Require SSL/HTTPS"
              description="Only accept validation requests from HTTPS sites"
            />
            <Input
              label="Allowed Domains"
              placeholder="example.com, *.agency.com"
              value={settings.allowed_domains}
              onChange={(e) => setSettings({ ...settings, allowed_domains: e.target.value })}
              helpText="Comma-separated list of allowed domains (wildcards supported). Leave empty to allow all."
            />
          </div>
        </Card>

        {/* Webhook Settings */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                <Globe className="w-5 h-5 text-slate-400" />
                Webhook Security
              </span>
            }
          />
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">
                Webhook Secret
                <HelpTooltip content="Used to sign webhook payloads for verification." />
              </label>
              <div className="flex gap-2">
                <Input
                  type="password"
                  value={settings.webhook_secret}
                  readOnly
                  className="font-mono"
                />
                <Button variant="outline">
                  <RefreshCw className="w-4 h-4" />
                </Button>
              </div>
              <p className="text-sm text-slate-500 mt-1">
                This secret is used to sign webhook payloads. Regenerating will invalidate all existing webhook integrations.
              </p>
            </div>
          </div>
        </Card>

        {/* Danger Zone */}
        <DangerZone>
          <DangerAction
            title="Clear Validation Cache"
            description="Force all sites to re-validate their licenses on next request."
            buttonLabel="Clear Cache"
            confirmMessage="This will clear all cached validation results. Sites will need to re-validate their licenses on the next request."
            onAction={() => {}}
          />
          <DangerAction
            title="Reset Rate Limits"
            description="Clear all rate limit counters for all IPs."
            buttonLabel="Reset Limits"
            confirmMessage="This will reset rate limits for all IP addresses. Use with caution."
            onAction={() => {}}
          />
          <DangerAction
            title="Revoke All Licenses"
            description="Immediately revoke all active licenses. This cannot be undone."
            buttonLabel="Revoke All"
            confirmMessage="WARNING: This will immediately revoke ALL licenses and deactivate ALL sites. This action cannot be undone. Are you absolutely sure?"
            onAction={() => {}}
          />
        </DangerZone>
      </div>
    </Layout>
  );
}
