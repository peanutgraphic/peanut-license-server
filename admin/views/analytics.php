<?php defined('ABSPATH') || exit; ?>
<div class="wrap peanut-analytics-page">
    <h1 class="wp-heading-inline"><?php esc_html_e('License Analytics', 'peanut-license-server'); ?></h1>
    <hr class="wp-header-end">

    <!-- Info Card -->
    <?php if (!get_user_meta(get_current_user_id(), 'peanut_dismissed_analytics_info', true)) : ?>
    <div class="peanut-info-card" data-card-id="analytics_info">
        <button type="button" class="peanut-info-card-dismiss" aria-label="<?php esc_attr_e('Dismiss', 'peanut-license-server'); ?>">&times;</button>
        <div class="peanut-info-card-icon">
            <span class="dashicons dashicons-chart-area"></span>
        </div>
        <div class="peanut-info-card-content">
            <h3><?php esc_html_e('License Analytics', 'peanut-license-server'); ?></h3>
            <p><?php esc_html_e('Gain insights into your license business with visual analytics:', 'peanut-license-server'); ?></p>
            <ul>
                <li><?php esc_html_e('Track license growth and activation trends over time', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('Monitor validation success rates and API usage', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('See tier distribution and top active sites', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('Get alerts for licenses expiring soon', 'peanut-license-server'); ?></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- Time Period Selector -->
    <div class="peanut-period-selector">
        <form method="get">
            <input type="hidden" name="page" value="peanut-licenses-analytics">
            <select name="days" onchange="this.form.submit()">
                <option value="7" <?php selected($days, 7); ?>><?php esc_html_e('Last 7 days', 'peanut-license-server'); ?></option>
                <option value="30" <?php selected($days, 30); ?>><?php esc_html_e('Last 30 days', 'peanut-license-server'); ?></option>
                <option value="90" <?php selected($days, 90); ?>><?php esc_html_e('Last 90 days', 'peanut-license-server'); ?></option>
                <option value="365" <?php selected($days, 365); ?>><?php esc_html_e('Last year', 'peanut-license-server'); ?></option>
            </select>
        </form>
    </div>

    <!-- Key Metrics -->
    <div class="peanut-stats-grid">
        <div class="peanut-stat-card">
            <div class="stat-icon" style="background: #0073aa;">
                <span class="dashicons dashicons-admin-network"></span>
            </div>
            <div class="stat-content">
                <span class="stat-number"><?php echo esc_html(number_format($stats['total_licenses'] ?? 0)); ?></span>
                <span class="stat-label"><?php esc_html_e('Total Licenses', 'peanut-license-server'); ?></span>
            </div>
        </div>

        <div class="peanut-stat-card">
            <div class="stat-icon" style="background: #10b981;">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="stat-content">
                <span class="stat-number"><?php echo esc_html(number_format($stats['active_licenses'] ?? 0)); ?></span>
                <span class="stat-label"><?php esc_html_e('Active Licenses', 'peanut-license-server'); ?></span>
            </div>
        </div>

        <div class="peanut-stat-card">
            <div class="stat-icon" style="background: #8b5cf6;">
                <span class="dashicons dashicons-admin-site"></span>
            </div>
            <div class="stat-content">
                <span class="stat-number"><?php echo esc_html(number_format($stats['total_activations'] ?? 0)); ?></span>
                <span class="stat-label"><?php esc_html_e('Active Sites', 'peanut-license-server'); ?></span>
            </div>
        </div>

        <div class="peanut-stat-card">
            <div class="stat-icon" style="background: #f59e0b;">
                <span class="dashicons dashicons-chart-bar"></span>
            </div>
            <div class="stat-content">
                <span class="stat-number"><?php echo esc_html(number_format($stats['total_validations'] ?? 0)); ?></span>
                <span class="stat-label"><?php esc_html_e('Total Validations', 'peanut-license-server'); ?></span>
            </div>
        </div>
    </div>

    <!-- Growth Metrics -->
    <div class="peanut-growth-metrics">
        <div class="peanut-metric">
            <span class="metric-value <?php echo ($stats['licenses_growth'] ?? 0) >= 0 ? 'positive' : 'negative'; ?>">
                <?php echo ($stats['licenses_growth'] ?? 0) >= 0 ? '+' : ''; ?><?php echo esc_html(number_format($stats['licenses_growth'] ?? 0, 1)); ?>%
            </span>
            <span class="metric-label"><?php esc_html_e('License Growth', 'peanut-license-server'); ?></span>
        </div>
        <div class="peanut-metric">
            <span class="metric-value <?php echo ($stats['activations_growth'] ?? 0) >= 0 ? 'positive' : 'negative'; ?>">
                <?php echo ($stats['activations_growth'] ?? 0) >= 0 ? '+' : ''; ?><?php echo esc_html(number_format($stats['activations_growth'] ?? 0, 1)); ?>%
            </span>
            <span class="metric-label"><?php esc_html_e('Activation Growth', 'peanut-license-server'); ?></span>
        </div>
        <div class="peanut-metric">
            <span class="metric-value"><?php echo esc_html(number_format($stats['validation_success_rate'] ?? 0, 1)); ?>%</span>
            <span class="metric-label"><?php esc_html_e('Validation Success Rate', 'peanut-license-server'); ?></span>
        </div>
        <div class="peanut-metric">
            <span class="metric-value"><?php echo esc_html(number_format($stats['avg_activations_per_license'] ?? 0, 1)); ?></span>
            <span class="metric-label"><?php esc_html_e('Avg Sites per License', 'peanut-license-server'); ?></span>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="peanut-charts-row">
        <div class="peanut-chart-card">
            <h3><?php esc_html_e('Licenses Over Time', 'peanut-license-server'); ?></h3>
            <canvas id="licensesChart"></canvas>
        </div>
        <div class="peanut-chart-card">
            <h3><?php esc_html_e('API Validations', 'peanut-license-server'); ?></h3>
            <canvas id="validationsChart"></canvas>
        </div>
    </div>

    <!-- Distribution Charts -->
    <div class="peanut-charts-row">
        <div class="peanut-chart-card peanut-chart-small">
            <h3><?php esc_html_e('Tier Distribution', 'peanut-license-server'); ?></h3>
            <canvas id="tierChart"></canvas>
        </div>
        <div class="peanut-chart-card peanut-chart-small">
            <h3><?php esc_html_e('Product Distribution', 'peanut-license-server'); ?></h3>
            <canvas id="productChart"></canvas>
        </div>
        <div class="peanut-chart-card peanut-chart-small">
            <h3><?php esc_html_e('Version Adoption', 'peanut-license-server'); ?></h3>
            <canvas id="versionChart"></canvas>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="peanut-tables-row">
        <!-- Top Sites -->
        <div class="peanut-table-card">
            <h3><?php esc_html_e('Top Active Sites', 'peanut-license-server'); ?></h3>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Site', 'peanut-license-server'); ?></th>
                        <th><?php esc_html_e('Validations', 'peanut-license-server'); ?></th>
                        <th><?php esc_html_e('Last Active', 'peanut-license-server'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($top_sites)): ?>
                        <tr><td colspan="3"><?php esc_html_e('No data available.', 'peanut-license-server'); ?></td></tr>
                    <?php else: ?>
                        <?php foreach ($top_sites as $site): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url($site->site_url); ?>" target="_blank">
                                        <?php echo esc_html(parse_url($site->site_url, PHP_URL_HOST)); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html(number_format($site->validation_count)); ?></td>
                                <td><?php echo esc_html(human_time_diff(strtotime($site->last_checked), current_time('timestamp'))); ?> <?php esc_html_e('ago', 'peanut-license-server'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Expiring Licenses -->
        <div class="peanut-table-card">
            <h3><?php esc_html_e('Expiring Soon (30 days)', 'peanut-license-server'); ?></h3>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Customer', 'peanut-license-server'); ?></th>
                        <th><?php esc_html_e('Tier', 'peanut-license-server'); ?></th>
                        <th><?php esc_html_e('Expires', 'peanut-license-server'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expiring_licenses)): ?>
                        <tr><td colspan="3"><?php esc_html_e('No licenses expiring soon.', 'peanut-license-server'); ?></td></tr>
                    <?php else: ?>
                        <?php foreach ($expiring_licenses as $license): ?>
                            <tr>
                                <td>
                                    <?php echo esc_html($license->customer_name ?: $license->customer_email); ?>
                                </td>
                                <td>
                                    <span class="peanut-tier tier-<?php echo esc_attr($license->tier); ?>">
                                        <?php echo esc_html(ucfirst($license->tier)); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $days_left = ceil((strtotime($license->expires_at) - time()) / 86400);
                                    $class = $days_left <= 7 ? 'expiring-urgent' : 'expiring-soon';
                                    ?>
                                    <span class="<?php echo esc_attr($class); ?>">
                                        <?php echo esc_html($days_left); ?> <?php esc_html_e('days', 'peanut-license-server'); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="peanut-activity-card">
        <h3><?php esc_html_e('Recent Activity', 'peanut-license-server'); ?></h3>
        <ul class="peanut-activity-list">
            <?php if (empty($recent_activity)): ?>
                <li><?php esc_html_e('No recent activity.', 'peanut-license-server'); ?></li>
            <?php else: ?>
                <?php foreach ($recent_activity as $activity): ?>
                    <li class="activity-item activity-<?php echo esc_attr($activity->type); ?>">
                        <span class="activity-icon">
                            <?php
                            $icon = 'admin-generic';
                            switch ($activity->type) {
                                case 'license_created': $icon = 'plus-alt2'; break;
                                case 'site_activated': $icon = 'admin-site'; break;
                                case 'site_deactivated': $icon = 'dismiss'; break;
                                case 'license_validated': $icon = 'yes'; break;
                                case 'license_expired': $icon = 'clock'; break;
                            }
                            ?>
                            <span class="dashicons dashicons-<?php echo esc_attr($icon); ?>"></span>
                        </span>
                        <span class="activity-text"><?php echo esc_html($activity->description); ?></span>
                        <span class="activity-time"><?php echo esc_html(human_time_diff(strtotime($activity->created_at), current_time('timestamp'))); ?> <?php esc_html_e('ago', 'peanut-license-server'); ?></span>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>

<script>
// Pass PHP data to JavaScript for charts
var peanutAnalyticsData = {
    timeline: <?php echo wp_json_encode($timeline_data); ?>,
    validations: <?php echo wp_json_encode($validations_timeline); ?>,
    tierDistribution: <?php echo wp_json_encode($tier_distribution); ?>,
    productDistribution: <?php echo wp_json_encode($product_distribution); ?>,
    versionAdoption: <?php echo wp_json_encode($version_adoption); ?>
};
</script>
