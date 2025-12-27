<?php
/**
 * Main Dashboard Page
 *
 * Central hub for the Peanut License Server with overview, quick actions, and recent activity.
 *
 * @package Peanut_License_Server
 */

defined('ABSPATH') || exit;

// Get statistics
$stats = Peanut_License_Manager::get_statistics();
$update_stats = Peanut_Update_Server::get_statistics(['days' => 30]);

// Get recent audit events
$recent_activity = [];
if (class_exists('Peanut_Audit_Trail')) {
    $audit_result = Peanut_Audit_Trail::get_logs(['per_page' => 8]);
    $recent_activity = $audit_result['data'] ?? [];
}

// Get health summary
global $wpdb;
$health_stats = $wpdb->get_row("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN health_status = 'healthy' THEN 1 ELSE 0 END) as healthy,
        SUM(CASE WHEN health_status = 'warning' THEN 1 ELSE 0 END) as warning,
        SUM(CASE WHEN health_status = 'critical' THEN 1 ELSE 0 END) as critical
    FROM {$wpdb->prefix}peanut_activations
    WHERE is_active = 1
");

// Check system status
$api_enabled = get_option('peanut_license_server_api_enabled', true);
$update_enabled = get_option('peanut_license_server_update_enabled', true);

// Get products
$products = Peanut_Update_Server::get_all_products();

// Check if first time (no licenses yet)
$is_first_time = ($stats['total'] ?? 0) === 0;
?>

<div class="wrap peanut-dashboard">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-admin-network" style="font-size: 28px; width: 28px; height: 28px; margin-right: 10px; color: var(--peanut-primary);"></span>
        <?php esc_html_e('License Server Dashboard', 'peanut-license-server'); ?>
    </h1>

    <?php if ($is_first_time) : ?>
    <!-- Welcome Card for First-Time Users -->
    <div class="peanut-welcome-card">
        <h2><?php esc_html_e('Welcome to Peanut License Server!', 'peanut-license-server'); ?></h2>
        <p><?php esc_html_e('Your license management system is ready. Follow these steps to get started:', 'peanut-license-server'); ?></p>
    </div>

    <!-- Getting Started Checklist -->
    <div class="peanut-card">
        <div class="peanut-card-header">
            <span class="dashicons dashicons-yes-alt"></span>
            <h2><?php esc_html_e('Getting Started Checklist', 'peanut-license-server'); ?></h2>
        </div>
        <div class="peanut-card-body">
            <div style="display: grid; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--peanut-gray-50); border-radius: var(--peanut-radius-sm);">
                    <span class="dashicons dashicons-yes" style="color: var(--peanut-success); font-size: 24px;"></span>
                    <div>
                        <strong><?php esc_html_e('1. Plugin Installed', 'peanut-license-server'); ?></strong>
                        <p style="margin: 4px 0 0; color: var(--peanut-gray-500); font-size: 13px;"><?php esc_html_e('License Server is active and running.', 'peanut-license-server'); ?></p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--peanut-gray-50); border-radius: var(--peanut-radius-sm);">
                    <span class="dashicons dashicons-marker" style="color: var(--peanut-warning); font-size: 24px;"></span>
                    <div>
                        <strong><?php esc_html_e('2. Create Your First License', 'peanut-license-server'); ?></strong>
                        <p style="margin: 4px 0 0; color: var(--peanut-gray-500); font-size: 13px;">
                            <?php esc_html_e('Create a license key for your customers.', 'peanut-license-server'); ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-licenses-add')); ?>"><?php esc_html_e('Add License', 'peanut-license-server'); ?> &rarr;</a>
                        </p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--peanut-gray-50); border-radius: var(--peanut-radius-sm);">
                    <span class="dashicons dashicons-marker" style="color: var(--peanut-gray-400); font-size: 24px;"></span>
                    <div>
                        <strong><?php esc_html_e('3. Upload Plugin Updates', 'peanut-license-server'); ?></strong>
                        <p style="margin: 4px 0 0; color: var(--peanut-gray-500); font-size: 13px;">
                            <?php esc_html_e('Upload ZIP files so customers can auto-update.', 'peanut-license-server'); ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-product-updates')); ?>"><?php esc_html_e('Product Updates', 'peanut-license-server'); ?> &rarr;</a>
                        </p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--peanut-gray-50); border-radius: var(--peanut-radius-sm);">
                    <span class="dashicons dashicons-marker" style="color: var(--peanut-gray-400); font-size: 24px;"></span>
                    <div>
                        <strong><?php esc_html_e('4. Configure Settings', 'peanut-license-server'); ?></strong>
                        <p style="margin: 4px 0 0; color: var(--peanut-gray-500); font-size: 13px;">
                            <?php esc_html_e('Review API and update server settings.', 'peanut-license-server'); ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-licenses-settings')); ?>"><?php esc_html_e('Settings', 'peanut-license-server'); ?> &rarr;</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else : ?>

    <!-- Stats Grid -->
    <div class="peanut-stats-grid">
        <div class="peanut-stat-card">
            <div class="peanut-stat-card-icon peanut-stat-card-icon--primary">
                <span class="dashicons dashicons-admin-network"></span>
            </div>
            <div class="peanut-stat-card-content">
                <h3><?php echo esc_html($stats['total'] ?? 0); ?></h3>
                <p><?php esc_html_e('Total Licenses', 'peanut-license-server'); ?></p>
                <?php if (!empty($stats['licenses_growth'])) : ?>
                <div class="peanut-stat-card-trend peanut-stat-card-trend--<?php echo $stats['licenses_growth'] >= 0 ? 'up' : 'down'; ?>">
                    <?php echo $stats['licenses_growth'] >= 0 ? '+' : ''; ?><?php echo esc_html($stats['licenses_growth']); ?>% this month
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="peanut-stat-card">
            <div class="peanut-stat-card-icon peanut-stat-card-icon--success">
                <span class="dashicons dashicons-admin-site"></span>
            </div>
            <div class="peanut-stat-card-content">
                <h3><?php echo esc_html($stats['activations_total'] ?? 0); ?></h3>
                <p><?php esc_html_e('Active Sites', 'peanut-license-server'); ?></p>
            </div>
        </div>

        <div class="peanut-stat-card">
            <div class="peanut-stat-card-icon peanut-stat-card-icon--<?php echo ($health_stats->warning ?? 0) + ($health_stats->critical ?? 0) > 0 ? 'warning' : 'success'; ?>">
                <span class="dashicons dashicons-heart"></span>
            </div>
            <div class="peanut-stat-card-content">
                <h3><?php echo esc_html($health_stats->healthy ?? 0); ?><span style="font-size: 14px; color: var(--peanut-gray-400);">/ <?php echo esc_html($health_stats->total ?? 0); ?></span></h3>
                <p><?php esc_html_e('Healthy Sites', 'peanut-license-server'); ?></p>
                <?php if (($health_stats->warning ?? 0) + ($health_stats->critical ?? 0) > 0) : ?>
                <div class="peanut-stat-card-trend peanut-stat-card-trend--down">
                    <?php echo esc_html(($health_stats->warning ?? 0) + ($health_stats->critical ?? 0)); ?> need attention
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="peanut-stat-card">
            <div class="peanut-stat-card-icon peanut-stat-card-icon--purple">
                <span class="dashicons dashicons-download"></span>
            </div>
            <div class="peanut-stat-card-content">
                <h3><?php echo esc_html($update_stats['total_downloads'] ?? 0); ?></h3>
                <p><?php esc_html_e('Downloads (30 days)', 'peanut-license-server'); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="peanut-card">
        <div class="peanut-card-header">
            <span class="dashicons dashicons-performance"></span>
            <h2><?php esc_html_e('Quick Actions', 'peanut-license-server'); ?></h2>
        </div>
        <div class="peanut-card-body">
            <div class="peanut-quick-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-licenses-add')); ?>" class="peanut-quick-action">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <span><?php esc_html_e('Add License', 'peanut-license-server'); ?></span>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-product-updates')); ?>" class="peanut-quick-action">
                    <span class="dashicons dashicons-upload"></span>
                    <span><?php esc_html_e('Upload Update', 'peanut-license-server'); ?></span>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-licenses-analytics')); ?>" class="peanut-quick-action">
                    <span class="dashicons dashicons-chart-area"></span>
                    <span><?php esc_html_e('View Analytics', 'peanut-license-server'); ?></span>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-site-health')); ?>" class="peanut-quick-action">
                    <span class="dashicons dashicons-heart"></span>
                    <span><?php esc_html_e('Site Health', 'peanut-license-server'); ?></span>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-licenses')); ?>" class="peanut-quick-action">
                    <span class="dashicons dashicons-admin-network"></span>
                    <span><?php esc_html_e('All Licenses', 'peanut-license-server'); ?></span>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-licenses-batch')); ?>" class="peanut-quick-action">
                    <span class="dashicons dashicons-database-export"></span>
                    <span><?php esc_html_e('Import/Export', 'peanut-license-server'); ?></span>
                </a>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Recent Activity -->
        <div class="peanut-card">
            <div class="peanut-card-header">
                <span class="dashicons dashicons-backup"></span>
                <h2><?php esc_html_e('Recent Activity', 'peanut-license-server'); ?></h2>
                <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-licenses-audit')); ?>" style="margin-left: auto; font-size: 13px;"><?php esc_html_e('View All', 'peanut-license-server'); ?> &rarr;</a>
            </div>
            <div class="peanut-card-body">
                <?php if (empty($recent_activity)) : ?>
                    <p style="color: var(--peanut-gray-500); text-align: center; padding: 32px 0;">
                        <?php esc_html_e('No recent activity to display.', 'peanut-license-server'); ?>
                    </p>
                <?php else : ?>
                    <ul class="peanut-activity-feed">
                        <?php foreach ($recent_activity as $event) :
                            // Determine icon based on event type
                            $event_type = $event->event ?? '';
                            $icon_class = 'info';
                            $icon = 'info';
                            if (strpos($event_type, 'created') !== false || strpos($event_type, 'activated') !== false) {
                                $icon_class = 'success';
                                $icon = 'yes';
                            } elseif (strpos($event_type, 'failed') !== false || strpos($event_type, 'deleted') !== false || strpos($event_type, 'revoked') !== false) {
                                $icon_class = 'danger';
                                $icon = 'no';
                            } elseif (strpos($event_type, 'suspended') !== false || strpos($event_type, 'expired') !== false) {
                                $icon_class = 'warning';
                                $icon = 'warning';
                            }
                        ?>
                        <li class="peanut-activity-item">
                            <div class="peanut-activity-icon peanut-activity-icon--<?php echo esc_attr($icon_class); ?>">
                                <span class="dashicons dashicons-<?php echo esc_attr($icon); ?>"></span>
                            </div>
                            <div class="peanut-activity-content">
                                <p><?php echo esc_html(ucwords(str_replace('_', ' ', $event_type))); ?></p>
                                <time><?php echo esc_html(human_time_diff(strtotime($event->created_at ?? 'now'), current_time('timestamp'))); ?> ago</time>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- System Status & Products -->
        <div>
            <!-- System Status -->
            <div class="peanut-card">
                <div class="peanut-card-header">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <h2><?php esc_html_e('System Status', 'peanut-license-server'); ?></h2>
                </div>
                <div class="peanut-card-body">
                    <div class="peanut-status-list">
                        <div class="peanut-status-item">
                            <span class="peanut-status-item-label"><?php esc_html_e('License API', 'peanut-license-server'); ?></span>
                            <span class="peanut-status-indicator peanut-status-indicator--<?php echo $api_enabled ? 'online' : 'offline'; ?>">
                                <?php echo $api_enabled ? esc_html__('Online', 'peanut-license-server') : esc_html__('Offline', 'peanut-license-server'); ?>
                            </span>
                        </div>
                        <div class="peanut-status-item">
                            <span class="peanut-status-item-label"><?php esc_html_e('Update Server', 'peanut-license-server'); ?></span>
                            <span class="peanut-status-indicator peanut-status-indicator--<?php echo $update_enabled ? 'online' : 'offline'; ?>">
                                <?php echo $update_enabled ? esc_html__('Online', 'peanut-license-server') : esc_html__('Offline', 'peanut-license-server'); ?>
                            </span>
                        </div>
                        <div class="peanut-status-item">
                            <span class="peanut-status-item-label"><?php esc_html_e('Database', 'peanut-license-server'); ?></span>
                            <span class="peanut-status-indicator peanut-status-indicator--online">
                                <?php esc_html_e('Connected', 'peanut-license-server'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registered Products -->
            <div class="peanut-card">
                <div class="peanut-card-header">
                    <span class="dashicons dashicons-admin-plugins"></span>
                    <h2><?php esc_html_e('Products', 'peanut-license-server'); ?></h2>
                </div>
                <div class="peanut-card-body">
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($products as $slug => $product) :
                            $version = get_option("peanut_{$slug}_version", '1.0.0');
                            $update_server = new Peanut_Update_Server($slug);
                            $has_file = $update_server->get_download_file_info() !== null;
                        ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; background: var(--peanut-gray-50); border-radius: var(--peanut-radius-sm);">
                            <div>
                                <strong style="font-size: 13px;"><?php echo esc_html($product['name']); ?></strong>
                                <span style="font-size: 12px; color: var(--peanut-gray-500); margin-left: 8px;">v<?php echo esc_html($version); ?></span>
                            </div>
                            <?php if ($has_file) : ?>
                                <span style="color: var(--peanut-success); font-size: 12px;">
                                    <span class="dashicons dashicons-yes" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
                                    <?php esc_html_e('Ready', 'peanut-license-server'); ?>
                                </span>
                            <?php else : ?>
                                <span style="color: var(--peanut-warning); font-size: 12px;">
                                    <span class="dashicons dashicons-warning" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
                                    <?php esc_html_e('No ZIP', 'peanut-license-server'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <p style="margin: 16px 0 0; text-align: center;">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-product-updates')); ?>" class="button">
                            <?php esc_html_e('Manage Products', 'peanut-license-server'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Box -->
    <div class="peanut-info-card" style="margin-top: 24px;">
        <div class="peanut-info-card-header">
            <span class="dashicons dashicons-editor-help"></span>
            <h3><?php esc_html_e('Need Help?', 'peanut-license-server'); ?></h3>
        </div>
        <p><?php esc_html_e('The Peanut License Server manages software licenses for your WordPress plugins. Here\'s what you can do:', 'peanut-license-server'); ?></p>
        <ul>
            <li><strong><?php esc_html_e('Licenses', 'peanut-license-server'); ?>:</strong> <?php esc_html_e('Create and manage license keys for customers', 'peanut-license-server'); ?></li>
            <li><strong><?php esc_html_e('Product Updates', 'peanut-license-server'); ?>:</strong> <?php esc_html_e('Upload plugin ZIPs for auto-updates', 'peanut-license-server'); ?></li>
            <li><strong><?php esc_html_e('Site Health', 'peanut-license-server'); ?>:</strong> <?php esc_html_e('Monitor all sites using your licenses', 'peanut-license-server'); ?></li>
            <li><strong><?php esc_html_e('Analytics', 'peanut-license-server'); ?>:</strong> <?php esc_html_e('Track license usage and download statistics', 'peanut-license-server'); ?></li>
        </ul>
        <button type="button" class="peanut-info-card-dismiss"><?php esc_html_e('Hide This', 'peanut-license-server'); ?></button>
    </div>
</div>

<style>
/* Dashboard-specific responsive styles */
@media screen and (max-width: 1200px) {
    .peanut-dashboard > div[style*="grid-template-columns: 2fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}

@media screen and (max-width: 782px) {
    .peanut-stats-grid {
        grid-template-columns: 1fr 1fr !important;
    }
    .peanut-quick-actions {
        grid-template-columns: 1fr 1fr !important;
    }
}

@media screen and (max-width: 480px) {
    .peanut-stats-grid {
        grid-template-columns: 1fr !important;
    }
    .peanut-quick-actions {
        grid-template-columns: 1fr !important;
    }
}
</style>
