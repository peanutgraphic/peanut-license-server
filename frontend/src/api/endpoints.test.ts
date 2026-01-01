import { describe, it, expect, vi, beforeEach } from 'vitest';
import { licenses, activations, analytics, audit, gdpr, webhooks, batch, products } from './endpoints';

describe('API Endpoints', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    vi.mocked(global.fetch).mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({}),
    } as Response);
  });

  // =========================================
  // License Endpoints Tests
  // =========================================

  describe('licenses', () => {
    it('list calls correct endpoint', async () => {
      await licenses.list();
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/licenses'),
        expect.any(Object)
      );
    });

    it('list with params includes query string', async () => {
      await licenses.list({ page: 2, per_page: 10, status: 'active' });
      const calledUrl = vi.mocked(global.fetch).mock.calls[0][0] as string;
      expect(calledUrl).toContain('page=2');
      expect(calledUrl).toContain('per_page=10');
      expect(calledUrl).toContain('status=active');
    });

    it('get calls correct endpoint with id', async () => {
      await licenses.get(123);
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/licenses/123'),
        expect.any(Object)
      );
    });

    it('create sends POST request with data', async () => {
      await licenses.create({
        email: 'test@example.com',
        tier: 'pro',
      });
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/licenses'),
        expect.objectContaining({
          method: 'POST',
          body: expect.stringContaining('test@example.com'),
        })
      );
    });

    it('update sends PATCH request', async () => {
      await licenses.update(123, { status: 'suspended' });
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/licenses/123'),
        expect.objectContaining({
          method: 'PATCH',
        })
      );
    });

    it('delete sends DELETE request', async () => {
      await licenses.delete(123);
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/licenses/123'),
        expect.objectContaining({
          method: 'DELETE',
        })
      );
    });

    it('suspend sends POST to suspend endpoint', async () => {
      await licenses.suspend(123);
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/licenses/123/suspend'),
        expect.objectContaining({
          method: 'POST',
        })
      );
    });

    it('reactivate sends POST to reactivate endpoint', async () => {
      await licenses.reactivate(123);
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/licenses/123/reactivate'),
        expect.objectContaining({
          method: 'POST',
        })
      );
    });

    it('regenerate sends POST to regenerate endpoint', async () => {
      await licenses.regenerate(123);
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/licenses/123/regenerate'),
        expect.objectContaining({
          method: 'POST',
        })
      );
    });

    it('transfer sends POST with transfer data', async () => {
      await licenses.transfer(123, {
        email: 'new@example.com',
        deactivate_sites: true,
      });
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/licenses/123/transfer'),
        expect.objectContaining({
          method: 'POST',
          body: expect.stringContaining('new@example.com'),
        })
      );
    });

    it('getActivations fetches license activations', async () => {
      await licenses.getActivations(123);
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/licenses/123/activations'),
        expect.any(Object)
      );
    });
  });

  // =========================================
  // Activation Endpoints Tests
  // =========================================

  describe('activations', () => {
    it('deactivate sends DELETE request', async () => {
      await activations.deactivate(456);
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/activations/456'),
        expect.objectContaining({
          method: 'DELETE',
        })
      );
    });
  });

  // =========================================
  // Analytics Endpoints Tests
  // =========================================

  describe('analytics', () => {
    it('getStats fetches analytics stats', async () => {
      await analytics.getStats();
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/analytics/stats'),
        expect.any(Object)
      );
    });

    it('getTimeline fetches timeline data', async () => {
      await analytics.getTimeline({ days: 30, metric: 'activations' });
      const calledUrl = vi.mocked(global.fetch).mock.calls[0][0] as string;
      expect(calledUrl).toContain('/analytics/timeline');
      expect(calledUrl).toContain('days=30');
      expect(calledUrl).toContain('metric=activations');
    });
  });

  // =========================================
  // Audit Endpoints Tests
  // =========================================

  describe('audit', () => {
    it('list fetches audit entries', async () => {
      await audit.list({ page: 1, event: 'license.created' });
      const calledUrl = vi.mocked(global.fetch).mock.calls[0][0] as string;
      expect(calledUrl).toContain('/audit');
    });

    it('getLicenseAudit fetches license-specific audit', async () => {
      await audit.getLicenseAudit(123);
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/audit/license/123'),
        expect.any(Object)
      );
    });
  });

  // =========================================
  // GDPR Endpoints Tests
  // =========================================

  describe('gdpr', () => {
    it('export sends POST with email', async () => {
      await gdpr.export('test@example.com');
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/gdpr/export'),
        expect.objectContaining({
          method: 'POST',
          body: expect.stringContaining('test@example.com'),
        })
      );
    });

    it('anonymize sends POST with email', async () => {
      await gdpr.anonymize('test@example.com');
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/gdpr/anonymize'),
        expect.objectContaining({
          method: 'POST',
        })
      );
    });

    it('delete sends POST with email', async () => {
      await gdpr.delete('test@example.com');
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/gdpr/delete'),
        expect.objectContaining({
          method: 'POST',
        })
      );
    });
  });

  // =========================================
  // Webhook Endpoints Tests
  // =========================================

  describe('webhooks', () => {
    it('list fetches all webhooks', async () => {
      await webhooks.list();
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/webhooks'),
        expect.any(Object)
      );
    });

    it('create sends POST with webhook data', async () => {
      await webhooks.create({
        url: 'https://example.com/webhook',
        events: ['license.created'],
      });
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/webhooks'),
        expect.objectContaining({
          method: 'POST',
          body: expect.stringContaining('https://example.com/webhook'),
        })
      );
    });

    it('delete sends DELETE request', async () => {
      await webhooks.delete('webhook-123');
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/webhooks/webhook-123'),
        expect.objectContaining({
          method: 'DELETE',
        })
      );
    });

    it('test sends POST to test endpoint', async () => {
      await webhooks.test('https://example.com/webhook', 'secret123');
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/webhooks/test'),
        expect.objectContaining({
          method: 'POST',
        })
      );
    });
  });

  // =========================================
  // Batch Endpoints Tests
  // =========================================

  describe('batch', () => {
    it('generate sends POST with batch data', async () => {
      await batch.generate({
        count: 10,
        customer_email: 'test@example.com',
        tier: 'pro',
      });
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/batch/generate'),
        expect.objectContaining({
          method: 'POST',
        })
      );
    });

    it('export sends POST with format options', async () => {
      await batch.export({ format: 'csv', status: 'active' });
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/batch/export'),
        expect.objectContaining({
          method: 'POST',
        })
      );
    });
  });

  // =========================================
  // Products Endpoints Tests
  // =========================================

  describe('products', () => {
    it('list fetches all products', async () => {
      await products.list();
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/products'),
        expect.any(Object)
      );
    });

    it('get fetches product by slug', async () => {
      await products.get('peanut-suite');
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/products/peanut-suite'),
        expect.any(Object)
      );
    });

    it('update sends PATCH with product data', async () => {
      await products.update('peanut-suite', { name: 'Updated Name' });
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/products/peanut-suite'),
        expect.objectContaining({
          method: 'PATCH',
        })
      );
    });
  });
});
