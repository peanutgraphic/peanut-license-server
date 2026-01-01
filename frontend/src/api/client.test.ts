import { describe, it, expect, vi, beforeEach } from 'vitest';
import { apiFetch } from './client';

describe('apiFetch', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  // =========================================
  // Basic Request Tests
  // =========================================

  it('makes GET request to correct URL', async () => {
    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ data: 'test' }),
    } as Response);

    await apiFetch('/test-endpoint');

    expect(global.fetch).toHaveBeenCalledWith(
      '/wp-json/peanut-admin/v1/test-endpoint',
      expect.objectContaining({
        headers: expect.objectContaining({
          'Content-Type': 'application/json',
          'X-WP-Nonce': 'test-nonce-123',
        }),
      })
    );
  });

  it('returns JSON response', async () => {
    const mockData = { id: 1, name: 'Test' };
    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve(mockData),
    } as Response);

    const result = await apiFetch<typeof mockData>('/test');

    expect(result).toEqual(mockData);
  });

  // =========================================
  // Query Parameters Tests
  // =========================================

  it('appends query parameters to URL', async () => {
    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({}),
    } as Response);

    await apiFetch('/test', {
      params: { page: 1, per_page: 10, status: 'active' },
    });

    expect(global.fetch).toHaveBeenCalledWith(
      expect.stringContaining('?page=1&per_page=10&status=active'),
      expect.any(Object)
    );
  });

  it('ignores undefined parameters', async () => {
    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({}),
    } as Response);

    await apiFetch('/test', {
      params: { page: 1, status: undefined },
    });

    const calledUrl = vi.mocked(global.fetch).mock.calls[0][0] as string;
    expect(calledUrl).toContain('page=1');
    expect(calledUrl).not.toContain('status');
  });

  it('handles empty params object', async () => {
    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({}),
    } as Response);

    await apiFetch('/test', { params: {} });

    const calledUrl = vi.mocked(global.fetch).mock.calls[0][0] as string;
    expect(calledUrl).not.toContain('?');
  });

  // =========================================
  // HTTP Method Tests
  // =========================================

  it('supports POST requests', async () => {
    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ success: true }),
    } as Response);

    await apiFetch('/test', {
      method: 'POST',
      body: JSON.stringify({ name: 'Test' }),
    });

    expect(global.fetch).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify({ name: 'Test' }),
      })
    );
  });

  it('supports PATCH requests', async () => {
    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ success: true }),
    } as Response);

    await apiFetch('/test/1', {
      method: 'PATCH',
      body: JSON.stringify({ status: 'active' }),
    });

    expect(global.fetch).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({
        method: 'PATCH',
      })
    );
  });

  it('supports DELETE requests', async () => {
    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ success: true }),
    } as Response);

    await apiFetch('/test/1', { method: 'DELETE' });

    expect(global.fetch).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({
        method: 'DELETE',
      })
    );
  });

  // =========================================
  // Header Tests
  // =========================================

  it('includes WordPress nonce in headers', async () => {
    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({}),
    } as Response);

    await apiFetch('/test');

    expect(global.fetch).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({
        headers: expect.objectContaining({
          'X-WP-Nonce': 'test-nonce-123',
        }),
      })
    );
  });

  it('includes Content-Type header', async () => {
    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({}),
    } as Response);

    await apiFetch('/test');

    expect(global.fetch).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({
        headers: expect.objectContaining({
          'Content-Type': 'application/json',
        }),
      })
    );
  });

  it('merges custom headers', async () => {
    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({}),
    } as Response);

    await apiFetch('/test', {
      headers: { 'X-Custom': 'custom-value' },
    });

    expect(global.fetch).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({
        headers: expect.objectContaining({
          'Content-Type': 'application/json',
          'X-WP-Nonce': 'test-nonce-123',
          'X-Custom': 'custom-value',
        }),
      })
    );
  });

  // =========================================
  // Error Handling Tests
  // =========================================

  it('throws error for non-OK response', async () => {
    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: false,
      status: 404,
      json: () => Promise.resolve({ message: 'Not found' }),
    } as Response);

    await expect(apiFetch('/not-found')).rejects.toThrow('Not found');
  });

  it('throws generic error when response has no message', async () => {
    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: false,
      status: 500,
      json: () => Promise.resolve({}),
    } as Response);

    await expect(apiFetch('/error')).rejects.toThrow('HTTP 500');
  });

  it('handles JSON parse errors gracefully', async () => {
    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: false,
      status: 500,
      json: () => Promise.reject(new Error('Invalid JSON')),
    } as Response);

    await expect(apiFetch('/error')).rejects.toThrow('An error occurred');
  });

  // =========================================
  // Type Safety Tests
  // =========================================

  it('returns typed response', async () => {
    interface TestResponse {
      id: number;
      name: string;
    }

    vi.mocked(global.fetch).mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ id: 1, name: 'Test' }),
    } as Response);

    const result = await apiFetch<TestResponse>('/test');

    // TypeScript should infer the correct type
    expect(result.id).toBe(1);
    expect(result.name).toBe('Test');
  });
});
