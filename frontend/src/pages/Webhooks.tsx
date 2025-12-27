import { useState } from 'react';
import { Layout } from '@/components/layout';
import {
  Card,
  Button,
  Input,
  Badge,
  Modal,
  HelpTooltip,
  InfoPanel,
} from '@/components/common';
import {
  Webhook,
  Plus,
  Trash2,
  Play,
  Copy,
} from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';

// Mock data
const mockWebhooks = [
  { id: '1', name: 'License Updates', url: 'https://api.example.com/webhooks/license', events: ['license.created', 'license.activated'], is_active: true, last_triggered_at: new Date().toISOString(), failure_count: 0 },
  { id: '2', name: 'Analytics Sync', url: 'https://analytics.company.io/hooks', events: ['validation.success', 'validation.failed'], is_active: true, last_triggered_at: new Date(Date.now() - 86400000).toISOString(), failure_count: 0 },
  { id: '3', name: 'CRM Integration', url: 'https://crm.sales.com/api/webhook', events: ['license.expired'], is_active: false, last_triggered_at: null, failure_count: 3 },
];

const availableEvents = [
  { value: 'license.created', label: 'License Created' },
  { value: 'license.activated', label: 'License Activated' },
  { value: 'license.deactivated', label: 'License Deactivated' },
  { value: 'license.expired', label: 'License Expired' },
  { value: 'license.revoked', label: 'License Revoked' },
  { value: 'validation.success', label: 'Validation Success' },
  { value: 'validation.failed', label: 'Validation Failed' },
];

export default function Webhooks() {
  const [showAddModal, setShowAddModal] = useState(false);
  const [newWebhook, setNewWebhook] = useState({ name: '', url: '', events: [] as string[] });

  return (
    <Layout
      title="Webhooks"
      description="Configure webhook integrations"
      action={
        <Button onClick={() => setShowAddModal(true)}>
          <Plus className="w-4 h-4 mr-1" />
          Add Webhook
        </Button>
      }
    >
      <div className="space-y-6">
        {/* Info */}
        <InfoPanel variant="info" title="About Webhooks">
          <p>
            Webhooks notify external services when events occur in your license system.
            Configure endpoints to receive real-time notifications for license changes,
            validation events, and more.
          </p>
        </InfoPanel>

        {/* Webhooks List */}
        {mockWebhooks.length === 0 ? (
          <Card>
            <div className="text-center py-12">
              <Webhook className="w-12 h-12 text-slate-300 mx-auto mb-4" />
              <h3 className="text-lg font-semibold text-slate-900 mb-2">No Webhooks</h3>
              <p className="text-slate-500 mb-4">
                Create your first webhook to receive real-time notifications.
              </p>
              <Button onClick={() => setShowAddModal(true)}>
                <Plus className="w-4 h-4 mr-1" />
                Add Webhook
              </Button>
            </div>
          </Card>
        ) : (
          <div className="space-y-4">
            {mockWebhooks.map((webhook) => (
              <Card key={webhook.id}>
                <div className="flex items-start justify-between">
                  <div className="flex items-start gap-4">
                    <div className={`p-3 rounded-lg ${webhook.is_active ? 'bg-green-100' : 'bg-slate-100'}`}>
                      <Webhook className={`w-6 h-6 ${webhook.is_active ? 'text-green-600' : 'text-slate-400'}`} />
                    </div>
                    <div>
                      <div className="flex items-center gap-2 mb-1">
                        <h3 className="font-semibold text-slate-900">{webhook.name}</h3>
                        <Badge variant={webhook.is_active ? 'success' : 'default'}>
                          {webhook.is_active ? 'Active' : 'Inactive'}
                        </Badge>
                        {webhook.failure_count > 0 && (
                          <Badge variant="error">{webhook.failure_count} failures</Badge>
                        )}
                      </div>
                      <div className="flex items-center gap-2 text-sm text-slate-500 mb-2">
                        <code className="bg-slate-100 px-2 py-0.5 rounded">{webhook.url}</code>
                        <button className="text-slate-400 hover:text-slate-600">
                          <Copy className="w-3.5 h-3.5" />
                        </button>
                      </div>
                      <div className="flex flex-wrap gap-1">
                        {webhook.events.map((event) => (
                          <Badge key={event} variant="default" size="sm">
                            {event}
                          </Badge>
                        ))}
                      </div>
                      {webhook.last_triggered_at && (
                        <p className="text-xs text-slate-400 mt-2">
                          Last triggered: {formatDistanceToNow(new Date(webhook.last_triggered_at), { addSuffix: true })}
                        </p>
                      )}
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    <Button variant="outline" size="sm" title="Test webhook">
                      <Play className="w-4 h-4" />
                    </Button>
                    <Button variant="ghost" size="sm" className="text-red-500 hover:text-red-600 hover:bg-red-50">
                      <Trash2 className="w-4 h-4" />
                    </Button>
                  </div>
                </div>
              </Card>
            ))}
          </div>
        )}

        {/* Add Webhook Modal */}
        <Modal
          isOpen={showAddModal}
          onClose={() => setShowAddModal(false)}
          title="Add Webhook"
          size="md"
          footer={
            <>
              <Button variant="outline" onClick={() => setShowAddModal(false)}>Cancel</Button>
              <Button onClick={() => setShowAddModal(false)}>Create Webhook</Button>
            </>
          }
        >
          <div className="space-y-4">
            <Input
              label="Webhook Name"
              placeholder="e.g., CRM Integration"
              value={newWebhook.name}
              onChange={(e) => setNewWebhook({ ...newWebhook, name: e.target.value })}
            />
            <Input
              label="Endpoint URL"
              type="url"
              placeholder="https://api.example.com/webhook"
              value={newWebhook.url}
              onChange={(e) => setNewWebhook({ ...newWebhook, url: e.target.value })}
            />
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-2">
                Events to Subscribe
                <HelpTooltip content="Select which events should trigger this webhook." />
              </label>
              <div className="space-y-2 max-h-48 overflow-y-auto border border-slate-200 rounded-lg p-3">
                {availableEvents.map((event) => (
                  <label key={event.value} className="flex items-center gap-2 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={newWebhook.events.includes(event.value)}
                      onChange={(e) => {
                        if (e.target.checked) {
                          setNewWebhook({ ...newWebhook, events: [...newWebhook.events, event.value] });
                        } else {
                          setNewWebhook({ ...newWebhook, events: newWebhook.events.filter((ev) => ev !== event.value) });
                        }
                      }}
                      className="rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                    />
                    <span className="text-sm text-slate-700">{event.label}</span>
                  </label>
                ))}
              </div>
            </div>
          </div>
        </Modal>
      </div>
    </Layout>
  );
}
