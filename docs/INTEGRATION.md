# Peanut Plugin Integration Guide

## Overview

The Peanut plugin ecosystem consists of several interconnected WordPress plugins that share licensing, data, and functionality. This guide documents how these plugins integrate with each other.

## Plugin Ecosystem

```
┌─────────────────────────────────────────────────────────────────┐
│                    PEANUT LICENSE SERVER                        │
│              (peanutgraphic.com - central hub)                  │
└──────────────────────────┬──────────────────────────────────────┘
                           │ License Validation
                           │ Updates Distribution
                           ▼
    ┌──────────────────────┼──────────────────────┐
    │                      │                      │
    ▼                      ▼                      ▼
┌──────────┐        ┌──────────┐          ┌──────────┐
│  PEANUT  │        │  PEANUT  │          │  PEANUT  │
│  SUITE   │        │ FESTIVAL │◄────────►│  BOOKER  │
└──────────┘        └──────────┘          └──────────┘
                    Performer Sync

┌──────────────────┐
│ PEANUT CONNECT   │ ─── Remote Site Monitoring
└──────────────────┘
```

## License Server Integration

### Client Plugin Registration

All premium Peanut plugins integrate with the License Server for:

1. **License Validation** - Verify license keys on activation
2. **Activation Management** - Track per-site activations
3. **Update Distribution** - Receive plugin updates
4. **Health Reporting** - Report site status

### Integration Points

#### 1. License Validation

Plugins call the License Server to validate licenses:

```php
// In client plugin (e.g., Peanut Suite)
$response = wp_remote_post(
    'https://peanutgraphic.com/wp-json/peanut-api/v1/license/validate',
    [
        'body' => json_encode([
            'license_key' => $license_key,
            'site_url'    => home_url(),
            'site_name'   => get_bloginfo('name'),
            'plugin_version' => PEANUT_SUITE_VERSION,
        ]),
        'headers' => ['Content-Type' => 'application/json'],
    ]
);
```

**Response Format:**
```json
{
    "success": true,
    "license": {
        "status": "active",
        "tier": "pro",
        "expires_at": "2025-12-31",
        "features": ["advanced_analytics", "white_label", "priority_support"]
    },
    "activations": {
        "current": 2,
        "max": 5,
        "remaining": 3
    }
}
```

#### 2. Automatic Updates

Plugins hook into WordPress update mechanism:

```php
// Filter WordPress update check
add_filter('pre_set_site_transient_update_plugins', function($transient) {
    $update_check = wp_remote_get(
        'https://peanutgraphic.com/wp-json/peanut-api/v1/updates/check?' . http_build_query([
            'plugin'   => 'peanut-suite',
            'version'  => PEANUT_SUITE_VERSION,
            'license'  => get_option('peanut_license_key'),
            'site_url' => home_url(),
        ])
    );

    if ($update_available) {
        $transient->response['peanut-suite/peanut-suite.php'] = (object) [
            'slug'        => 'peanut-suite',
            'plugin'      => 'peanut-suite/peanut-suite.php',
            'new_version' => $response['new_version'],
            'package'     => $response['download_url'], // Signed URL
        ];
    }

    return $transient;
});
```

#### 3. Health Reporting

Plugins periodically report health status:

```php
// Scheduled health check (cron)
add_action('peanut_daily_health_check', function() {
    wp_remote_post(
        'https://peanutgraphic.com/wp-json/peanut-api/v1/site/health',
        [
            'body' => json_encode([
                'license_key'    => get_option('peanut_license_key'),
                'site_url'       => home_url(),
                'plugin_version' => PEANUT_SUITE_VERSION,
                'wp_version'     => get_bloginfo('version'),
                'php_version'    => PHP_VERSION,
                'is_multisite'   => is_multisite(),
                'active_plugins' => count(get_option('active_plugins')),
                'health_status'  => Peanut_Health::get_status(),
                'errors'         => Peanut_Health::get_errors(),
            ]),
            'headers' => ['Content-Type' => 'application/json'],
        ]
    );
});
```

---

## Festival ↔ Booker Integration

Peanut Festival can integrate with Peanut Booker to link festival performers with their marketplace profiles.

### Integration Benefits

| Feature | Description |
|---------|-------------|
| **Profile Sync** | Performer data syncs between systems |
| **Rating Import** | Booker ratings/achievements display in Festival |
| **Cross-Platform Booking** | Festival performers can accept Booker bookings |
| **Unified Identity** | Single performer profile across platforms |

### Database Link

The `pf_booker_links` table connects Festival performers to Booker:

```sql
CREATE TABLE pf_booker_links (
    festival_performer_id BIGINT UNSIGNED NOT NULL,
    booker_performer_id BIGINT UNSIGNED DEFAULT NULL,
    booker_user_id BIGINT UNSIGNED DEFAULT NULL,
    sync_direction VARCHAR(20) DEFAULT 'both',
    sync_status VARCHAR(20) DEFAULT 'active',
    booker_achievement_level VARCHAR(20) DEFAULT NULL,
    booker_rating DECIMAL(3,2) DEFAULT NULL,
    booker_completed_bookings INT DEFAULT 0,
    last_synced_at DATETIME DEFAULT NULL,
    ...
);
```

### Sync Process

```php
// Festival plugin detects Booker and syncs data
add_action('pf_after_performer_save', function($performer_id) {
    // Check if Booker is active
    if (!class_exists('Peanut_Booker')) {
        return;
    }

    $performer = pf_get_performer($performer_id);
    $link = pf_get_booker_link($performer_id);

    if ($link && $link->sync_status === 'active') {
        // Sync to Booker
        if (in_array($link->sync_direction, ['both', 'to_booker'])) {
            Peanut_Booker_Performer::update($link->booker_performer_id, [
                'bio'       => $performer->bio,
                'photo_url' => $performer->photo_url,
            ]);
        }

        // Sync from Booker
        if (in_array($link->sync_direction, ['both', 'to_festival'])) {
            $booker_data = Peanut_Booker_Performer::get($link->booker_performer_id);
            pf_update_booker_stats($performer_id, [
                'achievement_level'   => $booker_data->achievement_level,
                'rating'              => $booker_data->average_rating,
                'completed_bookings'  => $booker_data->completed_bookings,
            ]);
        }
    }
});
```

### Auto-Discovery

When a performer registers for a festival using an email that matches a Booker account:

```php
add_action('pf_performer_registered', function($performer_id, $email) {
    if (!class_exists('Peanut_Booker')) {
        return;
    }

    // Check for matching Booker performer
    $booker_performer = Peanut_Booker_Performer::get_by_email($email);

    if ($booker_performer) {
        // Suggest link to admin
        pf_create_link_suggestion($performer_id, $booker_performer->id);

        // Or auto-link if settings allow
        if (pf_get_setting('auto_link_booker_performers')) {
            pf_create_booker_link($performer_id, $booker_performer->id);
        }
    }
}, 10, 2);
```

---

## Peanut Connect Integration

Peanut Connect runs on remote client sites and reports back to Peanut Suite on the monitoring server.

### Architecture

```
┌─────────────────────┐         ┌─────────────────────┐
│   CLIENT SITE       │         │   MONITORING SITE   │
│   (any domain)      │   ───►  │ (peanutgraphic.com) │
│                     │         │                     │
│ ┌─────────────────┐ │  REST   │ ┌─────────────────┐ │
│ │ PEANUT CONNECT  │─┼────────►│─│  PEANUT SUITE   │ │
│ └─────────────────┘ │   API   │ └─────────────────┘ │
└─────────────────────┘         └─────────────────────┘
```

### Data Flow

**Connect → Suite:**
- Site health status
- PHP/WordPress versions
- Plugin update status
- Error logs
- Performance metrics

**Suite → Connect:**
- Configuration updates
- Remote commands
- Update triggers

### API Authentication

```php
// Connect generates secure token
$token = Peanut_Connect_Auth::generate_token($site_id);

// Suite validates incoming requests
add_action('rest_api_init', function() {
    register_rest_route('peanut/v1', '/connect/health', [
        'methods'  => 'POST',
        'callback' => 'handle_connect_health',
        'permission_callback' => function($request) {
            $token = $request->get_header('X-Peanut-Connect-Token');
            return Peanut_Connect_Auth::validate_token($token);
        },
    ]);
});
```

---

## Shared Components

### Shared Security Library

All Peanut plugins can use the shared security library:

```php
// In any Peanut plugin
require_once WP_PLUGIN_DIR . '/peanut-shared-libs/security/peanut-security.php';

// Rate limiting
if (!peanut_rate_limit('my_action', 60, 60)) {
    return new WP_Error('rate_limited', 'Too many requests');
}

// IP blocking check
if (peanut_is_ip_blocked()) {
    return new WP_Error('blocked', 'Access denied');
}
```

### Shared Database Functions

Plugins share utility functions:

```php
// Secure prepared queries
$results = peanut_db_query(
    "SELECT * FROM {$table} WHERE status = %s",
    [$status]
);

// Encrypted option storage
peanut_set_encrypted_option('api_key', $secret);
$secret = peanut_get_encrypted_option('api_key');
```

---

## Cross-Plugin Hooks

### Action Hooks

Plugins fire actions for cross-plugin communication:

```php
// Festival fires when performer approved
do_action('pf_performer_approved', $performer_id, $festival_id);

// Booker can listen and auto-create marketplace listing
add_action('pf_performer_approved', function($performer_id, $festival_id) {
    if (!class_exists('Peanut_Booker')) return;

    $link = pf_get_booker_link($performer_id);
    if ($link) {
        // Promote performer on Booker marketplace
        Peanut_Booker_Performer::add_festival_badge($link->booker_performer_id, $festival_id);
    }
}, 10, 2);
```

### Filter Hooks

```php
// Suite provides license tier info to other plugins
add_filter('peanut_license_tier', function($tier) {
    $license = Peanut_License::get_current();
    return $license ? $license->tier : 'free';
});

// Festival uses tier for feature gating
$tier = apply_filters('peanut_license_tier', 'free');
if ($tier === 'agency') {
    // Enable agency features
}
```

---

## WooCommerce Integration

Both Booker and Festival integrate with WooCommerce for payments.

### Shared Payment Flow

```php
// Create WooCommerce order for booking/ticket
$order = wc_create_order();
$order->add_product($product_id, 1);
$order->set_customer_id($user_id);
$order->calculate_totals();

// Track order source
$order->update_meta_data('_peanut_source', 'booker'); // or 'festival'
$order->update_meta_data('_peanut_booking_id', $booking_id);
$order->save();
```

### Order Completion Hooks

```php
add_action('woocommerce_order_status_completed', function($order_id) {
    $order = wc_get_order($order_id);
    $source = $order->get_meta('_peanut_source');

    switch ($source) {
        case 'booker':
            Peanut_Booker_Booking::mark_paid($order->get_meta('_peanut_booking_id'));
            break;
        case 'festival':
            Peanut_Festival_Ticket::confirm($order->get_meta('_peanut_ticket_id'));
            break;
    }
});
```

---

## Multi-Site Considerations

### Network Activation

When plugins are network-activated in WordPress Multisite:

```php
// License applies to entire network
add_filter('peanut_license_site_url', function($url) {
    if (is_multisite()) {
        return network_home_url();
    }
    return $url;
});
```

### Sub-site Data Isolation

Each sub-site has isolated data:

```php
// Tables use WordPress table prefix which includes blog ID
global $wpdb;
$table = $wpdb->prefix . 'pf_festivals'; // e.g., wp_2_pf_festivals
```

---

## Development & Testing

### Mock License Server

For development, use the mock license server:

```php
// In wp-config.php
define('PEANUT_LICENSE_SERVER', 'http://localhost:8080');
```

### Integration Test Example

```php
class Integration_Test extends WP_UnitTestCase {

    public function test_festival_booker_performer_link() {
        // Create Booker performer
        $booker_id = Peanut_Booker_Performer::create([
            'user_id' => 1,
            'name'    => 'Test Performer',
            'email'   => 'test@example.com',
        ]);

        // Create Festival performer with same email
        $festival_id = pf_create_performer([
            'name'  => 'Test Performer',
            'email' => 'test@example.com',
        ]);

        // Link should be suggested
        $suggestions = pf_get_link_suggestions($festival_id);
        $this->assertCount(1, $suggestions);
        $this->assertEquals($booker_id, $suggestions[0]->booker_performer_id);
    }
}
```

---

## Troubleshooting

### Common Integration Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| License validation fails | SSL/network issues | Check `wp_remote_post` works |
| Booker link not syncing | Plugin not active | Check both plugins active |
| Updates not showing | Cache issue | Clear `update_plugins` transient |
| Health reports missing | Cron not running | Check WP-Cron status |

### Debug Logging

```php
// Enable integration debug logging
define('PEANUT_INTEGRATION_DEBUG', true);

// Logs written to wp-content/debug.log
if (defined('PEANUT_INTEGRATION_DEBUG') && PEANUT_INTEGRATION_DEBUG) {
    error_log('[Peanut Integration] ' . print_r($data, true));
}
```
