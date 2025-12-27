<?php defined('ABSPATH') || exit; ?>
<div class="wrap">
    <h1><?php esc_html_e('Update Logs', 'peanut-license-server'); ?></h1>

    <!-- Info Card -->
    <?php if (!get_user_meta(get_current_user_id(), 'peanut_dismissed_logs_info', true)) : ?>
    <div class="peanut-info-card" data-card-id="logs_info">
        <button type="button" class="peanut-info-card-dismiss" aria-label="<?php esc_attr_e('Dismiss', 'peanut-license-server'); ?>">&times;</button>
        <div class="peanut-info-card-icon">
            <span class="dashicons dashicons-update"></span>
        </div>
        <div class="peanut-info-card-content">
            <h3><?php esc_html_e('About Update Logs', 'peanut-license-server'); ?></h3>
            <p><?php esc_html_e('Track how client sites interact with your update server:', 'peanut-license-server'); ?></p>
            <ul>
                <li><strong><?php esc_html_e('Update Checks:', 'peanut-license-server'); ?></strong> <?php esc_html_e('How often sites check for updates', 'peanut-license-server'); ?></li>
                <li><strong><?php esc_html_e('Downloads:', 'peanut-license-server'); ?></strong> <?php esc_html_e('Successful plugin downloads', 'peanut-license-server'); ?></li>
                <li><strong><?php esc_html_e('Version Distribution:', 'peanut-license-server'); ?></strong> <?php esc_html_e('See which versions sites are running', 'peanut-license-server'); ?></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <form method="get" class="peanut-filters">
        <input type="hidden" name="page" value="peanut-licenses-logs">
        <select name="days">
            <option value="7" <?php selected($days, 7); ?>><?php esc_html_e('Last 7 days', 'peanut-license-server'); ?></option>
            <option value="30" <?php selected($days, 30); ?>><?php esc_html_e('Last 30 days', 'peanut-license-server'); ?></option>
            <option value="90" <?php selected($days, 90); ?>><?php esc_html_e('Last 90 days', 'peanut-license-server'); ?></option>
        </select>
        <button type="submit" class="button"><?php esc_html_e('Filter', 'peanut-license-server'); ?></button>
    </form>

    <!-- Statistics -->
    <div class="peanut-stats">
        <div class="peanut-stat-box">
            <span class="stat-number"><?php echo esc_html($stats['total_checks']); ?></span>
            <span class="stat-label"><?php esc_html_e('Update Checks', 'peanut-license-server'); ?></span>
        </div>
        <div class="peanut-stat-box">
            <span class="stat-number"><?php echo esc_html($stats['total_downloads']); ?></span>
            <span class="stat-label"><?php esc_html_e('Downloads', 'peanut-license-server'); ?></span>
        </div>
        <div class="peanut-stat-box">
            <span class="stat-number"><?php echo esc_html($stats['unique_sites']); ?></span>
            <span class="stat-label"><?php esc_html_e('Unique Sites', 'peanut-license-server'); ?></span>
        </div>
    </div>

    <!-- Version Distribution -->
    <?php if (!empty($stats['by_version'])): ?>
        <h2><?php esc_html_e('Version Distribution', 'peanut-license-server'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Version', 'peanut-license-server'); ?></th>
                    <th><?php esc_html_e('Sites', 'peanut-license-server'); ?></th>
                    <th><?php esc_html_e('Percentage', 'peanut-license-server'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_versions = array_sum($stats['by_version']);
                foreach ($stats['by_version'] as $version => $count):
                    $percentage = $total_versions > 0 ? round(($count / $total_versions) * 100, 1) : 0;
                ?>
                    <tr>
                        <td><code><?php echo esc_html($version); ?></code></td>
                        <td><?php echo esc_html($count); ?></td>
                        <td>
                            <div class="peanut-progress-bar">
                                <div class="peanut-progress" style="width: <?php echo esc_attr($percentage); ?>%"></div>
                            </div>
                            <?php echo esc_html($percentage); ?>%
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Daily Activity -->
    <?php if (!empty($stats['by_day'])): ?>
        <h2><?php esc_html_e('Daily Activity', 'peanut-license-server'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Date', 'peanut-license-server'); ?></th>
                    <th><?php esc_html_e('Update Checks', 'peanut-license-server'); ?></th>
                    <th><?php esc_html_e('Downloads', 'peanut-license-server'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse($stats['by_day'], true) as $date => $data): ?>
                    <tr>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($date))); ?></td>
                        <td><?php echo esc_html($data['checks'] ?? 0); ?></td>
                        <td><?php echo esc_html($data['downloads'] ?? 0); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
