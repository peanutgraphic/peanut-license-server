import { useState } from 'react';
import { Layout } from '@/components/layout';
import {
  Card,
  CardHeader,
  Button,
  Input,
  Switch,
  InfoPanel,
  DangerZone,
  DangerAction,
  HelpTooltip,
} from '@/components/common';
import {
  Users,
  Download,
  EyeOff,
  Clock,
} from 'lucide-react';

export default function GDPR() {
  const [email, setEmail] = useState('');
  const [dataRetention, setDataRetention] = useState(365);
  const [autoAnonymize, setAutoAnonymize] = useState(true);

  return (
    <Layout
      title="GDPR Tools"
      description="Data privacy and compliance tools"
    >
      <div className="space-y-6 max-w-3xl">
        {/* Info Banner */}
        <InfoPanel variant="info" title="GDPR Compliance">
          <p>
            These tools help you comply with GDPR requirements for data subject rights.
            You can export, anonymize, or delete user data upon request.
          </p>
        </InfoPanel>

        {/* User Data Lookup */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                <Users className="w-5 h-5 text-slate-400" />
                User Data Lookup
                <HelpTooltip content="Search for all data associated with an email address." />
              </span>
            }
          />
          <div className="space-y-4">
            <Input
              label="Customer Email"
              type="email"
              placeholder="customer@example.com"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              helpText="Enter the email address to look up associated data"
            />
            <div className="flex gap-2">
              <Button variant="outline" disabled={!email}>
                <Download className="w-4 h-4 mr-1" />
                Export Data
              </Button>
              <Button variant="outline" disabled={!email}>
                <EyeOff className="w-4 h-4 mr-1" />
                Anonymize
              </Button>
            </div>
          </div>
        </Card>

        {/* Data Retention */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                <Clock className="w-5 h-5 text-slate-400" />
                Data Retention Policy
              </span>
            }
          />
          <div className="space-y-4">
            <Input
              label="Retention Period (days)"
              type="number"
              value={dataRetention}
              onChange={(e) => setDataRetention(parseInt(e.target.value))}
              helpText="How long to keep validation logs and audit data"
            />
            <Switch
              checked={autoAnonymize}
              onChange={setAutoAnonymize}
              label="Auto-Anonymize Expired Data"
              description="Automatically anonymize personal data in expired license records"
            />
          </div>
        </Card>

        {/* Data Deletion */}
        <DangerZone title="Data Deletion" description="Permanently delete user data. These actions cannot be undone.">
          <DangerAction
            title="Delete User Data"
            description={`Delete all data for: ${email || '(enter email above)'}`}
            buttonLabel="Delete Data"
            confirmTitle="Delete User Data"
            confirmMessage={`This will permanently delete all licenses, activations, and audit logs for ${email}. This action cannot be undone.`}
            onAction={() => {}}
          />
          <DangerAction
            title="Purge Old Validation Logs"
            description="Delete validation logs older than the retention period."
            buttonLabel="Purge Logs"
            confirmMessage="This will permanently delete all validation logs older than your retention period. This cannot be undone."
            onAction={() => {}}
          />
        </DangerZone>
      </div>
    </Layout>
  );
}
