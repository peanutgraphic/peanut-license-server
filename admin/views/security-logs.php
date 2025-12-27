<?php
/**
 * Security Logs Admin View
 */
defined('ABSPATH') || exit;
?>
<div class="wrap">
    <h1><?php esc_html_e('Security Logs', 'peanut-license-server'); ?></h1>

    <!-- Info Card -->
    <?php if (!get_user_meta(get_current_user_id(), 'peanut_dismissed_security_info', true)) : ?>
    <div class="peanut-info-card" data-card-id="security_info">
        <button type="button" class="peanut-info-card-dismiss" aria-label="<?php esc_attr_e('Dismiss', 'peanut-license-server'); ?>">&times;</button>
        <div class="peanut-info-card-icon">
            <span class="dashicons dashicons-shield"></span>
        </div>
        <div class="peanut-info-card-content">
            <h3><?php esc_html_e('Security Monitoring', 'peanut-license-server'); ?></h3>
            <p><?php esc_html_e('Monitor API validation attempts and detect suspicious activity:', 'peanut-license-server'); ?></p>
            <ul>
                <li><?php esc_html_e('Track successful and failed validation attempts', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('Identify IPs with multiple failed attempts (potential abuse)', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('View common error codes to troubleshoot client issues', 'peanut-license-server'); ?></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="peanut-stats-grid">
        <div class="peanut-stat-card">
            <span class="peanut-stat-number"><?php echo number_format($stats['total_attempts']); ?></span>
            <span class="peanut-stat-label"><?php esc_html_e('Total Attempts', 'peanut-license-server'); ?></span>
        </div>
        <div class="peanut-stat-card peanut-stat-success">
            <span class="peanut-stat-number"><?php echo number_format($stats['successful']); ?></span>
            <span class="peanut-stat-label"><?php esc_html_e('Successful', 'peanut-license-server'); ?></span>
        </div>
        <div class="peanut-stat-card peanut-stat-danger">
            <span class="peanut-stat-number"><?php echo number_format($stats['failed']); ?></span>
            <span class="peanut-stat-label"><?php esc_html_e('Failed', 'peanut-license-server'); ?></span>
        </div>
        <div class="peanut-stat-card">
            <span class="peanut-stat-number"><?php echo number_format($stats['unique_ips']); ?></span>
            <span class="peanut-stat-label"><?php esc_html_e('Unique IPs', 'peanut-license-server'); ?></span>
        </div>
    </div>

    <!-- Filters -->
    <div class="tablenav top">
        <form method="get">
            <input type="hidden" name="page" value="peanut-licenses-security">
            <select name="status">
                <option value=""><?php esc_html_e('All Statuses', 'peanut-license-server'); ?></option>
                <option value="success" <?php selected($status_filter, 'success'); ?>><?php esc_html_e('Success', 'peanut-license-server'); ?></option>
                <option value="failed" <?php selected($status_filter, 'failed'); ?>><?php esc_html_e('Failed', 'peanut-license-server'); ?></option>
            </select>
            <select name="days">
                <option value="7" <?php selected($days, 7); ?>><?php esc_html_e('Last 7 days', 'peanut-license-server'); ?></option>
                <option value="30" <?php selected($days, 30); ?>><?php esc_html_e('Last 30 days', 'peanut-license-server'); ?></option>
                <option value="90" <?php selected($days, 90); ?>><?php esc_html_e('Last 90 days', 'peanut-license-server'); ?></option>
            </select>
            <button type="submit" class="button"><?php esc_html_e('Filter', 'peanut-license-server'); ?></button>
        </form>
    </div>

    <?php if (!empty($stats['suspicious_ips'])): ?>
        <!-- Suspicious IPs Alert -->
        <div class="notice notice-warning">
            <p><strong><?php esc_html_e('Suspicious Activity Detected', 'peanut-license-server'); ?></strong></p>
            <p><?php esc_html_e('The following IPs have made multiple failed validation attempts:', 'peanut-license-server'); ?></p>
            <ul>
                <?php foreach (array_slice($stats['suspicious_ips'], 0, 5, true) as $ip => $count): ?>
                    <li><code><?php echo esc_html($ip); ?></code> - <?php printf(esc_html__('%d failed attempts', 'peanut-license-server'), $count); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($stats['common_errors'])): ?>
        <!-- Common Errors -->
        <div class="card" style="margin-bottom: 20px;">
            <h3><?php esc_html_e('Common Error Codes', 'peanut-license-server'); ?></h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Error Code', 'peanut-license-server'); ?></th>
                        <th><?php esc_html_e('Count', 'peanut-license-server'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['common_errors'] as $code => $count): ?>
                        <tr>
                            <td><code><?php echo esc_html($code); ?></code></td>
                            <td><?php echo number_format($count); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Validation Logs Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 150px;"><?php esc_html_e('Date', 'peanut-license-server'); ?></th>
                <th><?php esc_html_e('License Key', 'peanut-license-server'); ?></th>
                <th><?php esc_html_e('Site URL', 'peanut-license-server'); ?></th>
                <th style="width: 120px;"><?php esc_html_e('IP Address', 'peanut-license-server'); ?></th>
                <th style="width: 100px;"><?php esc_html_e('Action', 'peanut-license-server'); ?></th>
                <th style="width: 80px;"><?php esc_html_e('Status', 'peanut-license-server'); ?></th>
                <th><?php esc_html_e('Error', 'peanut-license-server'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs['data'])): ?>
                <tr>
                    <td colspan="7"><?php esc_html_e('No validation logs found.', 'peanut-license-server'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs['data'] as $log): ?>
                    <tr>
                        <td><?php echo esc_html(date_i18n('Y-m-d H:i:s', strtotime($log->created_at))); ?></td>
                        <td><code><?php echo esc_html($log->license_key_partial ?: '-'); ?></code></td>
                        <td style="word-break: break-all;"><?php echo esc_html($log->site_url ?: '-'); ?></td>
                        <td><code><?php echo esc_html($log->ip_address); ?></code></td>
                        <td><?php echo esc_html($log->action); ?></td>
                        <td>
                            <?php if ($log->status === 'success'): ?>
                                <span style="color: green;">&#10003; <?php esc_html_e('OK', 'peanut-license-server'); ?></span>
                            <?php else: ?>
                                <span style="color: red;">&#10007; <?php esc_html_e('Failed', 'peanut-license-server'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($log->error_code): ?>
                                <code title="<?php echo esc_attr($log->error_message); ?>"><?php echo esc_html($log->error_code); ?></code>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($logs['total_pages'] > 1): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                $base_url = add_query_arg(['page' => 'peanut-licenses-security', 'days' => $days, 'status' => $status_filter], admin_url('admin.php'));

                echo paginate_links([
                    'base' => $base_url . '%_%',
                    'format' => '&paged=%#%',
                    'current' => $logs['page'],
                    'total' => $logs['total_pages'],
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                ]);
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.peanut-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin: 20px 0;
}
.peanut-stat-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    text-align: center;
    border-radius: 4px;
}
.peanut-stat-number {
    display: block;
    font-size: 28px;
    font-weight: 600;
    color: #1d2327;
}
.peanut-stat-label {
    display: block;
    font-size: 13px;
    color: #50575e;
    margin-top: 5px;
}
.peanut-stat-success .peanut-stat-number { color: #00a32a; }
.peanut-stat-danger .peanut-stat-number { color: #d63638; }
</style>
