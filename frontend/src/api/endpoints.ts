import { apiFetch } from './client';
import type {
  License,
  LicenseActivation,
  LicenseStats,
  AnalyticsData,
  AuditEntry,
  Webhook,
  Product,
  ServerSettings,
  PaginatedResponse,
} from '@/types';

// License endpoints
export const licenses = {
  list: (params?: {
    page?: number;
    per_page?: number;
    status?: string;
    tier?: string;
    search?: string;
  }) =>
    apiFetch<PaginatedResponse<License>>('/licenses', { params }),

  get: (id: number) =>
    apiFetch<License>(`/licenses/${id}`),

  create: (data: {
    email: string;
    tier?: string;
    max_activations?: number;
    expires_at?: string;
  }) =>
    apiFetch<License>('/licenses', {
      method: 'POST',
      body: JSON.stringify(data),
    }),

  update: (id: number, data: Partial<License>) =>
    apiFetch<License>(`/licenses/${id}`, {
      method: 'PATCH',
      body: JSON.stringify(data),
    }),

  delete: (id: number) =>
    apiFetch<{ success: boolean }>(`/licenses/${id}`, {
      method: 'DELETE',
    }),

  suspend: (id: number) =>
    apiFetch<License>(`/licenses/${id}/suspend`, {
      method: 'POST',
    }),

  reactivate: (id: number) =>
    apiFetch<License>(`/licenses/${id}/reactivate`, {
      method: 'POST',
    }),

  regenerate: (id: number) =>
    apiFetch<License>(`/licenses/${id}/regenerate`, {
      method: 'POST',
    }),

  transfer: (id: number, data: { email: string; name?: string; deactivate_sites?: boolean }) =>
    apiFetch<License>(`/licenses/${id}/transfer`, {
      method: 'POST',
      body: JSON.stringify(data),
    }),

  getActivations: (id: number) =>
    apiFetch<LicenseActivation[]>(`/licenses/${id}/activations`),
};

// Activation endpoints
export const activations = {
  deactivate: (id: number) =>
    apiFetch<{ success: boolean }>(`/activations/${id}`, {
      method: 'DELETE',
    }),
};

// Analytics endpoints
export const analytics = {
  getStats: () =>
    apiFetch<LicenseStats>('/analytics/stats'),

  getTimeline: (params?: { days?: number; metric?: string }) =>
    apiFetch<AnalyticsData>('/analytics/timeline', { params }),
};

// Audit endpoints
export const audit = {
  list: (params?: {
    page?: number;
    per_page?: number;
    event?: string;
    license_id?: number;
    user_id?: number;
  }) =>
    apiFetch<PaginatedResponse<AuditEntry>>('/audit', { params }),

  getLicenseAudit: (licenseId: number) =>
    apiFetch<AuditEntry[]>(`/audit/license/${licenseId}`),
};

// GDPR endpoints
export const gdpr = {
  export: (email: string) =>
    apiFetch<{ data: Record<string, unknown>; download_url?: string }>('/gdpr/export', {
      method: 'POST',
      body: JSON.stringify({ email }),
    }),

  anonymize: (email: string) =>
    apiFetch<{ success: boolean; affected: number }>('/gdpr/anonymize', {
      method: 'POST',
      body: JSON.stringify({ email }),
    }),

  delete: (email: string) =>
    apiFetch<{ success: boolean; deleted: number }>('/gdpr/delete', {
      method: 'POST',
      body: JSON.stringify({ email }),
    }),
};

// Webhook endpoints
export const webhooks = {
  list: () =>
    apiFetch<Webhook[]>('/webhooks'),

  create: (data: { url: string; events?: string[]; secret?: string }) =>
    apiFetch<Webhook>('/webhooks', {
      method: 'POST',
      body: JSON.stringify(data),
    }),

  delete: (id: string) =>
    apiFetch<{ success: boolean }>(`/webhooks/${id}`, {
      method: 'DELETE',
    }),

  test: (url: string, secret?: string) =>
    apiFetch<{ success: boolean; response?: unknown }>('/webhooks/test', {
      method: 'POST',
      body: JSON.stringify({ url, secret }),
    }),
};

// Batch operation endpoints
export const batch = {
  generate: (data: {
    count: number;
    customer_email: string;
    tier?: string;
    expires_at?: string;
  }) =>
    apiFetch<{ licenses: License[]; count: number }>('/batch/generate', {
      method: 'POST',
      body: JSON.stringify(data),
    }),

  export: (params?: { format?: 'csv' | 'json'; status?: string; tier?: string }) =>
    apiFetch<{ download_url: string }>('/batch/export', {
      method: 'POST',
      body: JSON.stringify(params),
    }),
};

// Settings endpoints (if available)
export const settings = {
  get: () =>
    apiFetch<ServerSettings>('/settings'),

  update: (data: Partial<ServerSettings>) =>
    apiFetch<ServerSettings>('/settings', {
      method: 'PATCH',
      body: JSON.stringify(data),
    }),
};

// Products/Updates endpoints
export const products = {
  list: () =>
    apiFetch<Product[]>('/products'),

  get: (slug: string) =>
    apiFetch<Product>(`/products/${slug}`),

  update: (slug: string, data: Partial<Product>) =>
    apiFetch<Product>(`/products/${slug}`, {
      method: 'PATCH',
      body: JSON.stringify(data),
    }),
};
