<?php defined('ABSPATH') || exit; ?>
<div class="wrap peanut-gdpr-page">
    <h1 class="wp-heading-inline"><?php esc_html_e('GDPR Tools', 'peanut-license-server'); ?></h1>
    <hr class="wp-header-end">

    <!-- Info Card -->
    <?php if (!get_user_meta(get_current_user_id(), 'peanut_dismissed_gdpr_info', true)) : ?>
    <div class="peanut-info-card" data-card-id="gdpr_info">
        <button type="button" class="peanut-info-card-dismiss" aria-label="<?php esc_attr_e('Dismiss', 'peanut-license-server'); ?>">&times;</button>
        <div class="peanut-info-card-icon">
            <span class="dashicons dashicons-shield-alt"></span>
        </div>
        <div class="peanut-info-card-content">
            <h3><?php esc_html_e('GDPR Compliance Tools', 'peanut-license-server'); ?></h3>
            <p><?php esc_html_e('Handle customer data requests in compliance with GDPR regulations:', 'peanut-license-server'); ?></p>
            <ul>
                <li><strong><?php esc_html_e('Export:', 'peanut-license-server'); ?></strong> <?php esc_html_e('View or download all data for a customer', 'peanut-license-server'); ?></li>
                <li><strong><?php esc_html_e('Anonymize:', 'peanut-license-server'); ?></strong> <?php esc_html_e('Replace personal data while keeping licenses', 'peanut-license-server'); ?></li>
                <li><strong><?php esc_html_e('Delete:', 'peanut-license-server'); ?></strong> <?php esc_html_e('Permanently remove all customer data', 'peanut-license-server'); ?></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <div class="peanut-gdpr-grid">
        <!-- Customer Data Request -->
        <div class="peanut-card">
            <h2><?php esc_html_e('Customer Data Request', 'peanut-license-server'); ?></h2>
            <p class="description"><?php esc_html_e('Look up, export, anonymize, or delete customer data.', 'peanut-license-server'); ?></p>

            <form method="post">
                <?php wp_nonce_field('peanut_gdpr_action', 'peanut_gdpr_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="customer_email"><?php esc_html_e('Customer Email', 'peanut-license-server'); ?></label>
                        </th>
                        <td>
                            <input type="email" name="customer_email" id="customer_email" class="regular-text" required>
                            <p class="description"><?php esc_html_e('Enter the customer email to process.', 'peanut-license-server'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Action', 'peanut-license-server'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="gdpr_action" value="export" checked>
                                    <strong><?php esc_html_e('Export Data', 'peanut-license-server'); ?></strong>
                                    <span class="description"><?php esc_html_e('View all stored customer data', 'peanut-license-server'); ?></span>
                                </label>
                                <br><br>
                                <label>
                                    <input type="radio" name="gdpr_action" value="download">
                                    <strong><?php esc_html_e('Download Export', 'peanut-license-server'); ?></strong>
                                    <span class="description"><?php esc_html_e('Download data as JSON file', 'peanut-license-server'); ?></span>
                                </label>
                                <br><br>
                                <label>
                                    <input type="radio" name="gdpr_action" value="anonymize">
                                    <strong><?php esc_html_e('Anonymize Data', 'peanut-license-server'); ?></strong>
                                    <span class="description" style="color: #b45309;"><?php esc_html_e('Replace personal data with anonymous identifiers (preserves licenses)', 'peanut-license-server'); ?></span>
                                </label>
                                <br><br>
                                <label>
                                    <input type="radio" name="gdpr_action" value="delete">
                                    <strong style="color: #dc2626;"><?php esc_html_e('Delete All Data', 'peanut-license-server'); ?></strong>
                                    <span class="description" style="color: #dc2626;"><?php esc_html_e('Permanently delete all customer data including licenses', 'peanut-license-server'); ?></span>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr class="confirm-row" style="display: none;">
                        <th scope="row"><?php esc_html_e('Confirmation', 'peanut-license-server'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="confirm_action" value="1">
                                <strong style="color: #dc2626;"><?php esc_html_e('I understand this action cannot be undone', 'peanut-license-server'); ?></strong>
                            </label>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Process Request', 'peanut-license-server'); ?></button>
                </p>
            </form>
        </div>

        <!-- Data Export Preview -->
        <?php if (!empty($export_data)): ?>
            <div class="peanut-card peanut-export-preview">
                <h2><?php esc_html_e('Customer Data', 'peanut-license-server'); ?></h2>
                <p class="description">
                    <?php printf(
                        esc_html__('Data exported at %s for %s', 'peanut-license-server'),
                        esc_html($export_data['exported_at']),
                        esc_html($export_data['customer_email'])
                    ); ?>
                </p>

                <!-- Licenses -->
                <h3><?php esc_html_e('Licenses', 'peanut-license-server'); ?> (<?php echo count($export_data['licenses']); ?>)</h3>
                <?php if (!empty($export_data['licenses'])): ?>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('License Key', 'peanut-license-server'); ?></th>
                                <th><?php esc_html_e('Tier', 'peanut-license-server'); ?></th>
                                <th><?php esc_html_e('Status', 'peanut-license-server'); ?></th>
                                <th><?php esc_html_e('Created', 'peanut-license-server'); ?></th>
                                <th><?php esc_html_e('Expires', 'peanut-license-server'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($export_data['licenses'] as $license): ?>
                                <tr>
                                    <td><code><?php echo esc_html($license->license_key); ?></code></td>
                                    <td><?php echo esc_html(ucfirst($license->tier)); ?></td>
                                    <td><?php echo esc_html(ucfirst($license->status)); ?></td>
                                    <td><?php echo esc_html($license->created_at); ?></td>
                                    <td><?php echo esc_html($license->expires_at ?: 'Never'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php esc_html_e('No licenses found.', 'peanut-license-server'); ?></p>
                <?php endif; ?>

                <!-- Activations -->
                <h3><?php esc_html_e('Activations', 'peanut-license-server'); ?> (<?php echo count($export_data['activations']); ?>)</h3>
                <?php if (!empty($export_data['activations'])): ?>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Site URL', 'peanut-license-server'); ?></th>
                                <th><?php esc_html_e('IP Address', 'peanut-license-server'); ?></th>
                                <th><?php esc_html_e('Plugin Version', 'peanut-license-server'); ?></th>
                                <th><?php esc_html_e('Activated', 'peanut-license-server'); ?></th>
                                <th><?php esc_html_e('Active', 'peanut-license-server'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($export_data['activations'] as $activation): ?>
                                <tr>
                                    <td><?php echo esc_html($activation->site_url); ?></td>
                                    <td><?php echo esc_html($activation->ip_address); ?></td>
                                    <td><?php echo esc_html($activation->plugin_version); ?></td>
                                    <td><?php echo esc_html($activation->activated_at); ?></td>
                                    <td><?php echo $activation->is_active ? '&#10003;' : '&#10007;'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php esc_html_e('No activations found.', 'peanut-license-server'); ?></p>
                <?php endif; ?>

                <!-- Quick Stats -->
                <div class="export-stats">
                    <span><?php printf(esc_html__('%d update logs', 'peanut-license-server'), count($export_data['update_logs'])); ?></span>
                    <span><?php printf(esc_html__('%d validation logs', 'peanut-license-server'), count($export_data['validation_logs'])); ?></span>
                    <span><?php printf(esc_html__('%d audit entries', 'peanut-license-server'), count($export_data['audit_logs'])); ?></span>
                </div>

                <p>
                    <form method="post" style="display: inline;">
                        <?php wp_nonce_field('peanut_gdpr_action', 'peanut_gdpr_nonce'); ?>
                        <input type="hidden" name="customer_email" value="<?php echo esc_attr($export_data['customer_email']); ?>">
                        <input type="hidden" name="gdpr_action" value="download">
                        <button type="submit" class="button button-secondary">
                            <span class="dashicons dashicons-download" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Download JSON Export', 'peanut-license-server'); ?>
                        </button>
                    </form>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pending Requests -->
    <?php if (!empty($pending_requests)): ?>
        <div class="peanut-card" style="margin-top: 30px;">
            <h2><?php esc_html_e('Pending GDPR Requests', 'peanut-license-server'); ?></h2>
            <p class="description"><?php esc_html_e('Customer-submitted requests awaiting processing.', 'peanut-license-server'); ?></p>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Email', 'peanut-license-server'); ?></th>
                        <th><?php esc_html_e('Request Type', 'peanut-license-server'); ?></th>
                        <th><?php esc_html_e('Status', 'peanut-license-server'); ?></th>
                        <th><?php esc_html_e('Submitted', 'peanut-license-server'); ?></th>
                        <th><?php esc_html_e('Actions', 'peanut-license-server'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_requests as $request): ?>
                        <tr>
                            <td><?php echo esc_html($request->email); ?></td>
                            <td>
                                <span class="peanut-request-type type-<?php echo esc_attr($request->request_type); ?>">
                                    <?php echo esc_html(ucfirst($request->request_type)); ?>
                                </span>
                            </td>
                            <td>
                                <span class="peanut-status status-<?php echo esc_attr($request->status); ?>">
                                    <?php echo esc_html(ucfirst($request->status)); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo esc_html(human_time_diff(strtotime($request->created_at), current_time('timestamp'))); ?>
                                <?php esc_html_e('ago', 'peanut-license-server'); ?>
                            </td>
                            <td>
                                <form method="post" style="display: inline;">
                                    <?php wp_nonce_field('peanut_gdpr_action', 'peanut_gdpr_nonce'); ?>
                                    <input type="hidden" name="customer_email" value="<?php echo esc_attr($request->email); ?>">
                                    <input type="hidden" name="gdpr_action" value="<?php echo esc_attr($request->request_type); ?>">
                                    <input type="hidden" name="confirm_action" value="1">
                                    <button type="submit" class="button button-small" onclick="return confirm('<?php esc_attr_e('Process this request?', 'peanut-license-server'); ?>')">
                                        <?php esc_html_e('Process', 'peanut-license-server'); ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- GDPR Information -->
    <div class="peanut-card peanut-info-card" style="margin-top: 30px;">
        <h2><?php esc_html_e('GDPR Compliance Information', 'peanut-license-server'); ?></h2>

        <div class="gdpr-info-grid">
            <div class="gdpr-info-item">
                <h4><?php esc_html_e('Data We Store', 'peanut-license-server'); ?></h4>
                <ul>
                    <li><?php esc_html_e('Customer email and name', 'peanut-license-server'); ?></li>
                    <li><?php esc_html_e('License keys and tiers', 'peanut-license-server'); ?></li>
                    <li><?php esc_html_e('Site URLs where licenses are activated', 'peanut-license-server'); ?></li>
                    <li><?php esc_html_e('IP addresses from activations and validations', 'peanut-license-server'); ?></li>
                    <li><?php esc_html_e('Plugin version information', 'peanut-license-server'); ?></li>
                </ul>
            </div>

            <div class="gdpr-info-item">
                <h4><?php esc_html_e('Data Retention', 'peanut-license-server'); ?></h4>
                <ul>
                    <li><?php esc_html_e('License data: Until deletion requested', 'peanut-license-server'); ?></li>
                    <li><?php esc_html_e('Validation logs: 90 days', 'peanut-license-server'); ?></li>
                    <li><?php esc_html_e('Update logs: 90 days', 'peanut-license-server'); ?></li>
                    <li><?php esc_html_e('Audit trail: 1 year', 'peanut-license-server'); ?></li>
                </ul>
            </div>

            <div class="gdpr-info-item">
                <h4><?php esc_html_e('WordPress Privacy Tools', 'peanut-license-server'); ?></h4>
                <p><?php esc_html_e('This plugin integrates with WordPress privacy tools. Customer data can be exported and erased through the standard WordPress Privacy tools under Tools > Export/Erase Personal Data.', 'peanut-license-server'); ?></p>
            </div>
        </div>
    </div>
</div>
