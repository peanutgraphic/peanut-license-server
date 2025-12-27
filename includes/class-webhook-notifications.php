<?php
/**
 * Webhook Notifications Class
 *
 * Sends notifications to external systems when license events occur.
 *
 * @package Peanut_License_Server
 */

defined('ABSPATH') || exit;

class Peanut_Webhook_Notifications {

    /**
     * Event types
     */
    public const EVENT_LICENSE_CREATED = 'license.created';
    public const EVENT_LICENSE_ACTIVATED = 'license.activated';
    public const EVENT_LICENSE_DEACTIVATED = 'license.deactivated';
    public const EVENT_LICENSE_EXPIRED = 'license.expired';
    public const EVENT_LICENSE_SUSPENDED = 'license.suspended';
    public const EVENT_LICENSE_REVOKED = 'license.revoked';
    public const EVENT_LICENSE_RENEWED = 'license.renewed';
    public const EVENT_LICENSE_TRANSFERRED = 'license.transferred';
    public const EVENT_LICENSE_KEY_REGENERATED = 'license.key_regenerated';

    /**
     * Initialize hooks
     */
    public static function init(): void {
        // License lifecycle events
        add_action('peanut_license_created', [__CLASS__, 'on_license_created'], 10, 1);
        add_action('peanut_license_activated', [__CLASS__, 'on_license_activated'], 10, 3);
        add_action('peanut_license_deactivated', [__CLASS__, 'on_license_deactivated'], 10, 3);
        add_action('peanut_license_expired', [__CLASS__, 'on_license_expired'], 10, 1);
        add_action('peanut_license_suspended', [__CLASS__, 'on_license_suspended'], 10, 1);
        add_action('peanut_license_revoked', [__CLASS__, 'on_license_revoked'], 10, 1);
        add_action('peanut_license_renewed', [__CLASS__, 'on_license_renewed'], 10, 1);
        add_action('peanut_license_transferred', [__CLASS__, 'on_license_transferred'], 10, 3);
        add_action('peanut_license_key_regenerated', [__CLASS__, 'on_license_key_regenerated'], 10, 3);
    }

    /**
     * Handle license created
     */
    public static function on_license_created(object $license): void {
        self::dispatch(self::EVENT_LICENSE_CREATED, [
            'license_id' => $license->id,
            'license_key' => self::mask_license_key($license->license_key),
            'customer_email' => $license->customer_email,
            'customer_name' => $license->customer_name,
            'tier' => $license->tier,
            'max_activations' => $license->max_activations,
            'expires_at' => $license->expires_at,
        ]);
    }

    /**
     * Handle license activated
     */
    public static function on_license_activated(int $license_id, string $site_url, object $activation): void {
        $license = Peanut_License_Manager::get_by_id($license_id);
        if (!$license) {
            return;
        }

        self::dispatch(self::EVENT_LICENSE_ACTIVATED, [
            'license_id' => $license_id,
            'license_key' => self::mask_license_key($license->license_key),
            'customer_email' => $license->customer_email,
            'site_url' => $site_url,
            'activations_used' => $license->activations_count,
            'activations_limit' => $license->max_activations,
        ]);
    }

    /**
     * Handle license deactivated
     */
    public static function on_license_deactivated(int $license_id, string $site_url, object $activation): void {
        $license = Peanut_License_Manager::get_by_id($license_id);
        if (!$license) {
            return;
        }

        self::dispatch(self::EVENT_LICENSE_DEACTIVATED, [
            'license_id' => $license_id,
            'license_key' => self::mask_license_key($license->license_key),
            'customer_email' => $license->customer_email,
            'site_url' => $site_url,
            'activations_used' => $license->activations_count,
            'activations_limit' => $license->max_activations,
        ]);
    }

    /**
     * Handle license expired
     */
    public static function on_license_expired(object $license): void {
        self::dispatch(self::EVENT_LICENSE_EXPIRED, [
            'license_id' => $license->id,
            'license_key' => self::mask_license_key($license->license_key),
            'customer_email' => $license->customer_email,
            'expired_at' => $license->expires_at,
        ]);
    }

    /**
     * Handle license suspended
     */
    public static function on_license_suspended(object $license): void {
        self::dispatch(self::EVENT_LICENSE_SUSPENDED, [
            'license_id' => $license->id,
            'license_key' => self::mask_license_key($license->license_key),
            'customer_email' => $license->customer_email,
        ]);
    }

    /**
     * Handle license revoked
     */
    public static function on_license_revoked(object $license): void {
        self::dispatch(self::EVENT_LICENSE_REVOKED, [
            'license_id' => $license->id,
            'license_key' => self::mask_license_key($license->license_key),
            'customer_email' => $license->customer_email,
        ]);
    }

    /**
     * Handle license renewed
     */
    public static function on_license_renewed(object $license): void {
        self::dispatch(self::EVENT_LICENSE_RENEWED, [
            'license_id' => $license->id,
            'license_key' => self::mask_license_key($license->license_key),
            'customer_email' => $license->customer_email,
            'new_expiry' => $license->expires_at,
        ]);
    }

    /**
     * Handle license transferred
     */
    public static function on_license_transferred(int $license_id, object $old_license, array $new_owner): void {
        self::dispatch(self::EVENT_LICENSE_TRANSFERRED, [
            'license_id' => $license_id,
            'license_key' => self::mask_license_key($old_license->license_key),
            'previous_email' => $old_license->customer_email,
            'new_email' => $new_owner['email'] ?? $old_license->customer_email,
            'new_name' => $new_owner['name'] ?? $old_license->customer_name,
        ]);
    }

    /**
     * Handle license key regenerated
     */
    public static function on_license_key_regenerated(int $license_id, string $old_key, string $new_key): void {
        $license = Peanut_License_Manager::get_by_id($license_id);
        if (!$license) {
            return;
        }

        self::dispatch(self::EVENT_LICENSE_KEY_REGENERATED, [
            'license_id' => $license_id,
            'old_key_masked' => self::mask_license_key($old_key),
            'new_key_masked' => self::mask_license_key($new_key),
            'customer_email' => $license->customer_email,
        ]);
    }

    /**
     * Dispatch webhook to all registered endpoints
     */
    public static function dispatch(string $event, array $data): void {
        $endpoints = self::get_webhook_endpoints();

        if (empty($endpoints)) {
            return;
        }

        $payload = [
            'event' => $event,
            'timestamp' => current_time('c'),
            'data' => $data,
        ];

        foreach ($endpoints as $endpoint) {
            // Check if endpoint is enabled for this event
            if (!empty($endpoint['events']) && !in_array($event, $endpoint['events'], true)) {
                continue;
            }

            // Queue the webhook
            self::queue_webhook($endpoint['url'], $payload, $endpoint['secret'] ?? '');
        }
    }

    /**
     * Queue a webhook for async delivery
     */
    private static function queue_webhook(string $url, array $payload, string $secret = ''): void {
        // For immediate delivery, use wp_remote_post
        // For production, consider using Action Scheduler or WP Cron

        $body = wp_json_encode($payload);
        $signature = !empty($secret) ? hash_hmac('sha256', $body, $secret) : '';

        $args = [
            'body' => $body,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Peanut-Event' => $payload['event'],
                'X-Peanut-Timestamp' => $payload['timestamp'],
            ],
            'timeout' => 15,
            'blocking' => false, // Non-blocking for performance
        ];

        if (!empty($signature)) {
            $args['headers']['X-Peanut-Signature'] = $signature;
        }

        wp_remote_post($url, $args);

        // Log the webhook
        self::log_webhook($url, $payload['event'], $body);
    }

    /**
     * Send webhook synchronously (for testing)
     */
    public static function send_test_webhook(string $url, string $secret = ''): array {
        $payload = [
            'event' => 'webhook.test',
            'timestamp' => current_time('c'),
            'data' => [
                'message' => 'This is a test webhook from Peanut License Server.',
            ],
        ];

        $body = wp_json_encode($payload);
        $signature = !empty($secret) ? hash_hmac('sha256', $body, $secret) : '';

        $args = [
            'body' => $body,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Peanut-Event' => 'webhook.test',
                'X-Peanut-Timestamp' => $payload['timestamp'],
            ],
            'timeout' => 15,
        ];

        if (!empty($signature)) {
            $args['headers']['X-Peanut-Signature'] = $signature;
        }

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error' => $response->get_error_message(),
            ];
        }

        return [
            'success' => true,
            'status_code' => wp_remote_retrieve_response_code($response),
            'body' => wp_remote_retrieve_body($response),
        ];
    }

    /**
     * Get registered webhook endpoints
     */
    public static function get_webhook_endpoints(): array {
        return get_option('peanut_webhook_endpoints', []);
    }

    /**
     * Add webhook endpoint
     */
    public static function add_endpoint(string $url, array $options = []): bool {
        $endpoints = self::get_webhook_endpoints();

        $endpoint = [
            'id' => wp_generate_uuid4(),
            'url' => esc_url_raw($url),
            'secret' => $options['secret'] ?? wp_generate_password(32, false),
            'events' => $options['events'] ?? [], // Empty = all events
            'created_at' => current_time('c'),
            'enabled' => true,
        ];

        $endpoints[] = $endpoint;

        return update_option('peanut_webhook_endpoints', $endpoints);
    }

    /**
     * Remove webhook endpoint
     */
    public static function remove_endpoint(string $id): bool {
        $endpoints = self::get_webhook_endpoints();

        $endpoints = array_filter($endpoints, function ($endpoint) use ($id) {
            return $endpoint['id'] !== $id;
        });

        return update_option('peanut_webhook_endpoints', array_values($endpoints));
    }

    /**
     * Update webhook endpoint
     */
    public static function update_endpoint(string $id, array $updates): bool {
        $endpoints = self::get_webhook_endpoints();

        foreach ($endpoints as &$endpoint) {
            if ($endpoint['id'] === $id) {
                $endpoint = array_merge($endpoint, $updates);
                break;
            }
        }

        return update_option('peanut_webhook_endpoints', $endpoints);
    }

    /**
     * Log webhook delivery
     */
    private static function log_webhook(string $url, string $event, string $payload): void {
        global $wpdb;

        $table = $wpdb->prefix . 'peanut_webhook_logs';

        // Check if table exists, create if not
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            self::create_log_table();
        }

        $wpdb->insert(
            $table,
            [
                'endpoint_url' => $url,
                'event' => $event,
                'payload' => $payload,
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s']
        );
    }

    /**
     * Create webhook log table
     */
    public static function create_log_table(): void {
        global $wpdb;

        $table = $wpdb->prefix . 'peanut_webhook_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            endpoint_url VARCHAR(255) NOT NULL,
            event VARCHAR(64) NOT NULL,
            payload TEXT,
            status_code INT DEFAULT NULL,
            response TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY idx_event (event),
            KEY idx_created_at (created_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Mask license key for webhook payload
     */
    private static function mask_license_key(string $key): string {
        if (strlen($key) < 10) {
            return '****';
        }

        return substr($key, 0, 4) . '-****-****-' . substr($key, -4);
    }

    /**
     * Get available events
     */
    public static function get_available_events(): array {
        return [
            self::EVENT_LICENSE_CREATED => __('License Created', 'peanut-license-server'),
            self::EVENT_LICENSE_ACTIVATED => __('License Activated', 'peanut-license-server'),
            self::EVENT_LICENSE_DEACTIVATED => __('License Deactivated', 'peanut-license-server'),
            self::EVENT_LICENSE_EXPIRED => __('License Expired', 'peanut-license-server'),
            self::EVENT_LICENSE_SUSPENDED => __('License Suspended', 'peanut-license-server'),
            self::EVENT_LICENSE_REVOKED => __('License Revoked', 'peanut-license-server'),
            self::EVENT_LICENSE_RENEWED => __('License Renewed', 'peanut-license-server'),
            self::EVENT_LICENSE_TRANSFERRED => __('License Transferred', 'peanut-license-server'),
            self::EVENT_LICENSE_KEY_REGENERATED => __('License Key Regenerated', 'peanut-license-server'),
        ];
    }
}
