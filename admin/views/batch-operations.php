<?php
/**
 * Batch Operations Admin View
 */
defined('ABSPATH') || exit;
?>
<div class="wrap">
    <h1><?php esc_html_e('Batch Operations', 'peanut-license-server'); ?></h1>

    <!-- Info Card -->
    <?php if (!get_user_meta(get_current_user_id(), 'peanut_dismissed_batch_info', true)) : ?>
    <div class="peanut-info-card" data-card-id="batch_info">
        <button type="button" class="peanut-info-card-dismiss" aria-label="<?php esc_attr_e('Dismiss', 'peanut-license-server'); ?>">&times;</button>
        <div class="peanut-info-card-icon">
            <span class="dashicons dashicons-admin-generic"></span>
        </div>
        <div class="peanut-info-card-content">
            <h3><?php esc_html_e('Batch License Operations', 'peanut-license-server'); ?></h3>
            <p><?php esc_html_e('Manage licenses in bulk to save time:', 'peanut-license-server'); ?></p>
            <ul>
                <li><strong><?php esc_html_e('Export:', 'peanut-license-server'); ?></strong> <?php esc_html_e('Download licenses as CSV or JSON for backup or analysis', 'peanut-license-server'); ?></li>
                <li><strong><?php esc_html_e('Import:', 'peanut-license-server'); ?></strong> <?php esc_html_e('Create multiple licenses from a CSV/JSON file', 'peanut-license-server'); ?></li>
                <li><strong><?php esc_html_e('Bulk Generate:', 'peanut-license-server'); ?></strong> <?php esc_html_e('Create many licenses at once for a customer', 'peanut-license-server'); ?></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($results): ?>
        <div class="notice notice-<?php echo $results['failed'] > 0 ? 'warning' : 'success'; ?> is-dismissible">
            <p>
                <?php
                printf(
                    esc_html__('Operation complete: %d successful, %d failed, %d skipped.', 'peanut-license-server'),
                    $results['success'],
                    $results['failed'],
                    $results['skipped'] ?? 0
                );
                ?>
            </p>
            <?php if (!empty($results['errors'])): ?>
                <details>
                    <summary><?php esc_html_e('View errors', 'peanut-license-server'); ?></summary>
                    <ul>
                        <?php foreach (array_slice($results['errors'], 0, 10) as $error): ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>
            <?php if (!empty($results['licenses'])): ?>
                <details>
                    <summary><?php esc_html_e('Generated license keys', 'peanut-license-server'); ?></summary>
                    <textarea readonly style="width: 100%; height: 150px; font-family: monospace;"><?php echo esc_textarea(implode("\n", $results['licenses'])); ?></textarea>
                </details>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="peanut-batch-grid">
        <!-- Export Section -->
        <div class="card">
            <h2><?php esc_html_e('Export Licenses', 'peanut-license-server'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('peanut_batch_operations', 'peanut_batch_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('Status Filter', 'peanut-license-server'); ?></th>
                        <td>
                            <select name="status">
                                <option value=""><?php esc_html_e('All Statuses', 'peanut-license-server'); ?></option>
                                <option value="active"><?php esc_html_e('Active', 'peanut-license-server'); ?></option>
                                <option value="expired"><?php esc_html_e('Expired', 'peanut-license-server'); ?></option>
                                <option value="suspended"><?php esc_html_e('Suspended', 'peanut-license-server'); ?></option>
                                <option value="revoked"><?php esc_html_e('Revoked', 'peanut-license-server'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Tier Filter', 'peanut-license-server'); ?></th>
                        <td>
                            <select name="tier">
                                <option value=""><?php esc_html_e('All Tiers', 'peanut-license-server'); ?></option>
                                <option value="free"><?php esc_html_e('Free', 'peanut-license-server'); ?></option>
                                <option value="pro"><?php esc_html_e('Pro', 'peanut-license-server'); ?></option>
                                <option value="agency"><?php esc_html_e('Agency', 'peanut-license-server'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <p>
                    <button type="submit" name="batch_action" value="export_csv" class="button">
                        <?php esc_html_e('Export as CSV', 'peanut-license-server'); ?>
                    </button>
                    <button type="submit" name="batch_action" value="export_json" class="button">
                        <?php esc_html_e('Export as JSON', 'peanut-license-server'); ?>
                    </button>
                </p>
            </form>
        </div>

        <!-- Import Section -->
        <div class="card">
            <h2><?php esc_html_e('Import Licenses', 'peanut-license-server'); ?></h2>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('peanut_batch_operations', 'peanut_batch_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('File', 'peanut-license-server'); ?></th>
                        <td>
                            <input type="file" name="import_file" accept=".csv,.json" required>
                            <p class="description">
                                <?php esc_html_e('Upload a CSV or JSON file. Required columns: customer_email. Optional: customer_name, tier, max_activations, expires_at.', 'peanut-license-server'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <p>
                    <button type="submit" name="batch_action" value="import_csv" class="button">
                        <?php esc_html_e('Import CSV', 'peanut-license-server'); ?>
                    </button>
                    <button type="submit" name="batch_action" value="import_json" class="button">
                        <?php esc_html_e('Import JSON', 'peanut-license-server'); ?>
                    </button>
                </p>
            </form>
        </div>

        <!-- Bulk Generate Section -->
        <div class="card">
            <h2><?php esc_html_e('Bulk Generate Licenses', 'peanut-license-server'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('peanut_batch_operations', 'peanut_batch_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th><label for="bulk_count"><?php esc_html_e('Number of Licenses', 'peanut-license-server'); ?></label></th>
                        <td>
                            <input type="number" name="bulk_count" id="bulk_count" value="10" min="1" max="1000" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="bulk_email"><?php esc_html_e('Customer Email', 'peanut-license-server'); ?></label></th>
                        <td>
                            <input type="email" name="bulk_email" id="bulk_email" class="regular-text" required>
                            <p class="description"><?php esc_html_e('All generated licenses will be assigned to this email.', 'peanut-license-server'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="bulk_name"><?php esc_html_e('Customer Name', 'peanut-license-server'); ?></label></th>
                        <td>
                            <input type="text" name="bulk_name" id="bulk_name" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="bulk_tier"><?php esc_html_e('Tier', 'peanut-license-server'); ?></label></th>
                        <td>
                            <select name="bulk_tier" id="bulk_tier">
                                <option value="free"><?php esc_html_e('Free', 'peanut-license-server'); ?></option>
                                <option value="pro" selected><?php esc_html_e('Pro', 'peanut-license-server'); ?></option>
                                <option value="agency"><?php esc_html_e('Agency', 'peanut-license-server'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="bulk_expires"><?php esc_html_e('Expiration Date', 'peanut-license-server'); ?></label></th>
                        <td>
                            <input type="date" name="bulk_expires" id="bulk_expires">
                            <p class="description"><?php esc_html_e('Leave empty for no expiration.', 'peanut-license-server'); ?></p>
                        </td>
                    </tr>
                </table>

                <p>
                    <button type="submit" name="batch_action" value="generate_bulk" class="button button-primary">
                        <?php esc_html_e('Generate Licenses', 'peanut-license-server'); ?>
                    </button>
                </p>
            </form>
        </div>
    </div>
</div>

<style>
.peanut-batch-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 20px;
}
.peanut-batch-grid .card {
    padding: 20px;
}
.peanut-batch-grid .card h2 {
    margin-top: 0;
}
</style>
