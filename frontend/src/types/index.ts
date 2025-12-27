// License types
export type LicenseStatus = 'active' | 'expired' | 'revoked' | 'suspended';
export type LicenseTier = 'free' | 'pro' | 'agency';

export interface License {
  id: number;
  license_key: string;
  status: LicenseStatus;
  tier: LicenseTier;
  email: string;
  order_id?: number;
  max_activations: number;
  activations_count: number;
  expires_at: string | null;
  created_at: string;
  last_validated_at?: string;
  metadata?: Record<string, unknown>;
}

export interface LicenseActivation {
  id: number;
  license_id: number;
  site_url: string;
  site_name?: string;
  activated_at: string;
  last_check: string;
  ip_address?: string;
  is_local: boolean;
}

// Statistics
export interface LicenseStats {
  total: number;
  active: number;
  expired: number;
  revoked: number;
  suspended: number;
  by_tier: {
    free: number;
    pro: number;
    agency: number;
  };
  activations: {
    total: number;
    active: number;
  };
  revenue: {
    total: number;
    this_month: number;
    last_month: number;
  };
}

// Analytics
export interface AnalyticsData {
  daily_validations: Array<{
    date: string;
    count: number;
    success: number;
    failed: number;
  }>;
  top_sites: Array<{
    site_url: string;
    validations: number;
    last_check: string;
  }>;
  validation_errors: Array<{
    error_type: string;
    count: number;
  }>;
  geographic: Array<{
    country: string;
    count: number;
  }>;
}

// Audit trail
export interface AuditEntry {
  id: number;
  action: string;
  entity_type: 'license' | 'activation' | 'settings' | 'webhook';
  entity_id: number;
  user_id?: number;
  user_email?: string;
  details: string;
  ip_address?: string;
  created_at: string;
}

// Webhook
export interface Webhook {
  id: number;
  name: string;
  url: string;
  events: string[];
  is_active: boolean;
  secret?: string;
  last_triggered_at?: string;
  failure_count: number;
  created_at: string;
}

// Product/Update
export interface Product {
  id: number;
  slug: string;
  name: string;
  version: string;
  download_url?: string;
  changelog?: string;
  requires_php?: string;
  requires_wp?: string;
  tested_up_to?: string;
  updated_at: string;
}

// Settings
export interface ServerSettings {
  api_enabled: boolean;
  update_enabled: boolean;
  cache_duration: number;
  rate_limit: number;
  require_ssl: boolean;
  allowed_domains: string[];
  webhook_secret: string;
  gdpr_enabled: boolean;
  data_retention_days: number;
}

// API Response types
export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  total: number;
  page: number;
  per_page: number;
  total_pages: number;
}

// Activity log
export type ActivityStatus = 'success' | 'warning' | 'error' | 'info';
export type ActivityType =
  | 'license_created'
  | 'license_activated'
  | 'license_revoked'
  | 'license_expired'
  | 'validation_success'
  | 'validation_failed'
  | 'webhook_triggered'
  | 'settings_changed';

export interface ActivityLogEntry {
  id: string;
  type: ActivityType;
  status: ActivityStatus;
  title: string;
  description: string;
  timestamp: string;
  metadata?: Record<string, unknown>;
}
