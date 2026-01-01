# Security Documentation

## Overview

Peanut License Server implements multiple layers of security to protect license validation, prevent abuse, and secure administrative operations.

## Authentication & Authorization

### Public API Endpoints

Public endpoints (`/peanut-api/v1/*`) are accessible without authentication but implement:

| Security Layer | Implementation |
|----------------|----------------|
| Rate Limiting | 60 requests/minute per IP |
| IP Blocking | Auto-block after 10 suspicious activities/hour |
| Input Validation | License key format validation, URL sanitization |
| Request Logging | All validation attempts logged for auditing |

### Admin API Endpoints

Admin endpoints (`/peanut-admin/v1/*`) require:

- WordPress authentication (logged-in user)
- `manage_options` capability
- WordPress nonce verification for state-changing operations

```php
// Permission check implementation
public static function admin_permission_check(): bool {
    return current_user_can('manage_options');
}
```

## Rate Limiting

Rate limiting is implemented using WordPress transients:

```php
// Configuration
'license_validate' => ['limit' => 60, 'window' => 60],  // 60/min
'site_health'      => ['limit' => 10, 'window' => 60],  // 10/min
'license_activations' => ['limit' => 30, 'window' => 60], // 30/min
```

### Headers Returned

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1704067200
```

### Rate Limit Exceeded Response

```json
{
  "code": "rate_limit_exceeded",
  "message": "Too many requests. Please try again later.",
  "data": {
    "status": 429,
    "retry_after": 45
  }
}
```

## IP Blocking

### Suspicious Activity Tracking

Activities that increment the suspicious counter:

- Invalid license key format
- Invalid site URL format
- Rapid repeated validation failures
- Attempted access to admin endpoints without auth

### Auto-Block Threshold

After **10 suspicious activities within 1 hour**, the IP is automatically blocked for **1 hour**.

### Manual Block/Unblock

Administrators can manually manage blocked IPs through the admin interface.

## Download Security

### Signed Download URLs

Plugin downloads use HMAC-SHA256 signed tokens:

```php
// Token generation
$data = $plugin . '|' . $expires . '|' . $license;
$signature = hash_hmac('sha256', $data, $secret);
$token = base64_encode($expires . '|' . $signature);
```

### Token Validation

1. Decode base64 token
2. Extract expiration timestamp
3. Verify timestamp is not expired (1-hour validity)
4. Reconstruct expected signature
5. Use `hash_equals()` for timing-safe comparison

### Protections

| Threat | Mitigation |
|--------|------------|
| Token guessing | 256-bit HMAC signature |
| Replay attacks | 1-hour expiration |
| Token reuse | Plugin and license bound |
| Timing attacks | `hash_equals()` comparison |

## Input Validation & Sanitization

### License Key Format

```php
// Valid format: XXXX-XXXX-XXXX-XXXX (alphanumeric)
public static function is_valid_format(string $key): bool {
    return preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key);
}
```

### URL Sanitization

All URLs are processed through:
1. `esc_url_raw()` - WordPress URL sanitization
2. `filter_var($url, FILTER_VALIDATE_URL)` - PHP validation
3. Protocol enforcement (https preferred)

### SQL Injection Prevention

All database queries use WordPress prepared statements:

```php
$wpdb->prepare(
    "SELECT * FROM {$table} WHERE license_key = %s AND site_hash = %s",
    $license_key,
    md5($site_url)
);
```

## API Security Class

The `Peanut_API_Security` class provides centralized security functions:

### Permission Callbacks

```php
// For public license endpoints
Peanut_API_Security::permission_public_license($request)

// For public read-only endpoints
Peanut_API_Security::permission_public_readonly($request)

// For admin endpoints
Peanut_API_Security::permission_admin($request)
```

### HMAC Signature Validation

For webhook-style requests:

```php
$is_valid = Peanut_API_Security::validate_signature($request, $secret);
```

Expected headers:
- `X-Peanut-Signature`: HMAC-SHA256 signature
- `X-Peanut-Timestamp`: Unix timestamp (must be within 5 minutes)

## Logging & Monitoring

### Validation Logging

All license validation attempts are logged:

```php
Peanut_Validation_Logger::log_failure(
    $license_key,
    $site_url,
    'invalid_format',
    'Invalid license key format',
    'validate'
);
```

### Security Event Logging

Security events trigger:

```php
Peanut_API_Security::log_security_event('ip_blocked', [
    'ip_hash' => md5($ip),
    'duration' => 3600,
]);
```

### Action Hook for External Monitoring

```php
do_action('peanut_license_server_security_event', $event, $data);
```

## Data Protection

### Sensitive Data Handling

| Data | Storage | Transmission |
|------|---------|--------------|
| License keys | Hashed in logs | Plain in request body |
| IP addresses | MD5 hashed | Not stored plain |
| Customer emails | Encrypted at rest | HTTPS only |

### Password/Secret Storage

Download secrets and API keys use WordPress options with encryption where supported:

```php
// Get or generate download secret
function peanut_get_download_secret(): string {
    $secret = get_option('peanut_download_secret');
    if (!$secret) {
        $secret = wp_generate_password(64, true, true);
        update_option('peanut_download_secret', $secret);
    }
    return $secret;
}
```

## Security Headers

The API sets appropriate security headers:

```php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
```

## Reporting Security Issues

If you discover a security vulnerability, please report it responsibly:

1. **Do NOT** create a public GitHub issue
2. Email: security@peanutgraphic.com
3. Include detailed reproduction steps
4. Allow 90 days for remediation before public disclosure

## Security Checklist

### For Developers

- [ ] All user input validated and sanitized
- [ ] All database queries use prepared statements
- [ ] Rate limiting applied to public endpoints
- [ ] Admin endpoints require capability checks
- [ ] Secrets not logged or exposed
- [ ] HTTPS enforced for sensitive operations

### For Deployment

- [ ] `WP_DEBUG` set to `false` in production
- [ ] Download secret generated and stored securely
- [ ] SSL certificate valid and not expiring
- [ ] Database backups encrypted
- [ ] Access logs monitored
