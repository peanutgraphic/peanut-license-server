<?php
/**
 * Webhooks Admin View
 */
defined('ABSPATH') || exit;
?>
<div class="wrap">
    <h1><?php esc_html_e('Webhook Notifications', 'peanut-license-server'); ?></h1>

    <!-- Info Card -->
    <?php if (!get_user_meta(get_current_user_id(), 'peanut_dismissed_webhooks_info', true)) : ?>
    <div class="peanut-info-card" data-card-id="webhooks_info">
        <button type="button" class="peanut-info-card-dismiss" aria-label="<?php esc_attr_e('Dismiss', 'peanut-license-server'); ?>">&times;</button>
        <div class="peanut-info-card-icon">
            <span class="dashicons dashicons-rss"></span>
        </div>
        <div class="peanut-info-card-content">
            <h3><?php esc_html_e('Webhook Integrations', 'peanut-license-server'); ?></h3>
            <p><?php esc_html_e('Webhooks send real-time notifications to external services when events occur:', 'peanut-license-server'); ?></p>
            <ul>
                <li><?php esc_html_e('Notify your CRM when a license is created or activated', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('Integrate with Slack, Discord, or email services', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('Trigger automations in Zapier, Make, or custom systems', 'peanut-license-server'); ?></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <div class="peanut-webhooks-grid">
        <!-- Add Webhook -->
        <div class="card">
            <h2><?php esc_html_e('Add Webhook Endpoint', 'peanut-license-server'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('peanut_webhook_action', 'peanut_webhook_nonce'); ?>
                <input type="hidden" name="webhook_action" value="add">

                <table class="form-table">
                    <tr>
                        <th><label for="webhook_url"><?php esc_html_e('Endpoint URL', 'peanut-license-server'); ?></label></th>
                        <td>
                            <input type="url" name="webhook_url" id="webhook_url" class="regular-text" placeholder="https://example.com/webhook" required>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Events', 'peanut-license-server'); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php esc_html_e('Events', 'peanut-license-server'); ?></legend>
                                <?php foreach ($available_events as $event => $label): ?>
                                    <label style="display: block; margin-bottom: 5px;">
                                        <input type="checkbox" name="webhook_events[]" value="<?php echo esc_attr($event); ?>">
                                        <?php echo esc_html($label); ?>
                                        <code style="font-size: 11px; color: #666;"><?php echo esc_html($event); ?></code>
                                    </label>
                                <?php endforeach; ?>
                                <p class="description"><?php esc_html_e('Leave all unchecked to receive all events.', 'peanut-license-server'); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                </table>

                <p>
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Add Endpoint', 'peanut-license-server'); ?>
                    </button>
                </p>
            </form>
        </div>

        <!-- Test Webhook -->
        <div class="card">
            <h2><?php esc_html_e('Test Webhook', 'peanut-license-server'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('peanut_webhook_action', 'peanut_webhook_nonce'); ?>
                <input type="hidden" name="webhook_action" value="test">

                <table class="form-table">
                    <tr>
                        <th><label for="test_url"><?php esc_html_e('URL', 'peanut-license-server'); ?></label></th>
                        <td>
                            <input type="url" name="test_url" id="test_url" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="test_secret"><?php esc_html_e('Secret (optional)', 'peanut-license-server'); ?></label></th>
                        <td>
                            <input type="text" name="test_secret" id="test_secret" class="regular-text">
                        </td>
                    </tr>
                </table>

                <p>
                    <button type="submit" class="button">
                        <?php esc_html_e('Send Test Webhook', 'peanut-license-server'); ?>
                    </button>
                </p>
            </form>
        </div>
    </div>

    <!-- Registered Endpoints -->
    <div class="card" style="margin-top: 20px;">
        <h2><?php esc_html_e('Registered Endpoints', 'peanut-license-server'); ?></h2>

        <?php if (empty($endpoints)): ?>
            <p><?php esc_html_e('No webhook endpoints configured.', 'peanut-license-server'); ?></p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('URL', 'peanut-license-server'); ?></th>
                        <th><?php esc_html_e('Events', 'peanut-license-server'); ?></th>
                        <th><?php esc_html_e('Secret', 'peanut-license-server'); ?></th>
                        <th><?php esc_html_e('Created', 'peanut-license-server'); ?></th>
                        <th><?php esc_html_e('Actions', 'peanut-license-server'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($endpoints as $endpoint): ?>
                        <tr>
                            <td>
                                <code><?php echo esc_url($endpoint['url']); ?></code>
                            </td>
                            <td>
                                <?php
                                if (empty($endpoint['events'])) {
                                    echo '<em>' . esc_html__('All events', 'peanut-license-server') . '</em>';
                                } else {
                                    echo esc_html(implode(', ', $endpoint['events']));
                                }
                                ?>
                            </td>
                            <td>
                                <code style="font-size: 11px;"><?php echo esc_html(substr($endpoint['secret'] ?? '', 0, 8) . '...'); ?></code>
                                <button type="button" class="button-link" onclick="prompt('Secret:', '<?php echo esc_js($endpoint['secret'] ?? ''); ?>')">
                                    <?php esc_html_e('View', 'peanut-license-server'); ?>
                                </button>
                            </td>
                            <td><?php echo esc_html($endpoint['created_at'] ?? '-'); ?></td>
                            <td>
                                <form method="post" style="display: inline;">
                                    <?php wp_nonce_field('peanut_webhook_action', 'peanut_webhook_nonce'); ?>
                                    <input type="hidden" name="webhook_action" value="delete">
                                    <input type="hidden" name="webhook_id" value="<?php echo esc_attr($endpoint['id']); ?>">
                                    <button type="submit" class="button-link" style="color: #b32d2e;" onclick="return confirm('<?php esc_attr_e('Delete this endpoint?', 'peanut-license-server'); ?>')">
                                        <?php esc_html_e('Delete', 'peanut-license-server'); ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Webhook Documentation -->
    <div class="card" style="margin-top: 20px;">
        <h2><?php esc_html_e('Webhook Documentation', 'peanut-license-server'); ?></h2>
        <p><?php esc_html_e('Webhooks are sent as POST requests with a JSON body. The following headers are included:', 'peanut-license-server'); ?></p>
        <ul>
            <li><code>Content-Type: application/json</code></li>
            <li><code>X-Peanut-Event: {event_type}</code></li>
            <li><code>X-Peanut-Timestamp: {ISO 8601 timestamp}</code></li>
            <li><code>X-Peanut-Signature: {HMAC-SHA256 signature}</code> (if secret is configured)</li>
        </ul>
        <p><strong><?php esc_html_e('Signature Verification (PHP):', 'peanut-license-server'); ?></strong></p>
        <pre style="background: #f1f1f1; padding: 10px; overflow-x: auto;">
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PEANUT_SIGNATURE'] ?? '';
$expected = hash_hmac('sha256', $payload, $your_secret);
$valid = hash_equals($expected, $signature);</pre>
    </div>
</div>

<style>
.peanut-webhooks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 20px;
}
.peanut-webhooks-grid .card {
    padding: 20px;
}
.peanut-webhooks-grid .card h2 {
    margin-top: 0;
}
</style>
