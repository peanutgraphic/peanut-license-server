<?php defined('ABSPATH') || exit; ?>
<div class="wrap peanut-audit-page">
    <h1 class="wp-heading-inline"><?php esc_html_e('Audit Trail', 'peanut-license-server'); ?></h1>
    <hr class="wp-header-end">

    <!-- Info Card -->
    <?php if (!get_user_meta(get_current_user_id(), 'peanut_dismissed_audit_info', true)) : ?>
    <div class="peanut-info-card" data-card-id="audit_info">
        <button type="button" class="peanut-info-card-dismiss" aria-label="<?php esc_attr_e('Dismiss', 'peanut-license-server'); ?>">&times;</button>
        <div class="peanut-info-card-icon">
            <span class="dashicons dashicons-clipboard"></span>
        </div>
        <div class="peanut-info-card-content">
            <h3><?php esc_html_e('Audit Trail', 'peanut-license-server'); ?></h3>
            <p><?php esc_html_e('Complete history of all license-related actions for accountability and debugging:', 'peanut-license-server'); ?></p>
            <ul>
                <li><?php esc_html_e('License creation, updates, and deletions', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('Site activations and deactivations', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('Status changes, transfers, and key regenerations', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('Filter by event type, license ID, or user', 'peanut-license-server'); ?></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="get" class="peanut-filters">
        <input type="hidden" name="page" value="peanut-licenses-audit">

        <select name="event">
            <option value=""><?php esc_html_e('All Events', 'peanut-license-server'); ?></option>
            <?php foreach ($event_types as $event_key => $event_label): ?>
                <option value="<?php echo esc_attr($event_key); ?>" <?php selected($event_filter, $event_key); ?>>
                    <?php echo esc_html($event_label); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="number" name="license_id" value="<?php echo $license_filter ? esc_attr($license_filter) : ''; ?>" placeholder="<?php esc_attr_e('License ID', 'peanut-license-server'); ?>" style="width: 120px;">

        <input type="number" name="user_id" value="<?php echo $user_filter ? esc_attr($user_filter) : ''; ?>" placeholder="<?php esc_attr_e('User ID', 'peanut-license-server'); ?>" style="width: 100px;">

        <button type="submit" class="button"><?php esc_html_e('Filter', 'peanut-license-server'); ?></button>
        <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-licenses-audit')); ?>" class="button"><?php esc_html_e('Reset', 'peanut-license-server'); ?></a>
    </form>

    <!-- Audit Log Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="column-time" style="width: 160px;"><?php esc_html_e('Time', 'peanut-license-server'); ?></th>
                <th scope="col" class="column-event" style="width: 150px;"><?php esc_html_e('Event', 'peanut-license-server'); ?></th>
                <th scope="col" class="column-license" style="width: 100px;"><?php esc_html_e('License', 'peanut-license-server'); ?></th>
                <th scope="col" class="column-user" style="width: 140px;"><?php esc_html_e('User', 'peanut-license-server'); ?></th>
                <th scope="col" class="column-details"><?php esc_html_e('Details', 'peanut-license-server'); ?></th>
                <th scope="col" class="column-ip" style="width: 130px;"><?php esc_html_e('IP Address', 'peanut-license-server'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($result['data'])): ?>
                <tr>
                    <td colspan="6"><?php esc_html_e('No audit logs found.', 'peanut-license-server'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($result['data'] as $log): ?>
                    <?php
                    $data = json_decode($log->data, true) ?: [];
                    $event_class = peanut_get_event_class($log->event);
                    ?>
                    <tr>
                        <td class="column-time">
                            <span title="<?php echo esc_attr($log->created_at); ?>">
                                <?php echo esc_html(human_time_diff(strtotime($log->created_at), current_time('timestamp'))); ?> <?php esc_html_e('ago', 'peanut-license-server'); ?>
                            </span>
                            <br>
                            <small style="color: #666;">
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->created_at))); ?>
                            </small>
                        </td>
                        <td class="column-event">
                            <span class="peanut-event-badge event-<?php echo esc_attr($event_class); ?>">
                                <?php echo esc_html($event_types[$log->event] ?? $log->event); ?>
                            </span>
                        </td>
                        <td class="column-license">
                            <?php if ($log->license_id): ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-licenses-audit&license_id=' . $log->license_id)); ?>">
                                    #<?php echo esc_html($log->license_id); ?>
                                </a>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="column-user">
                            <?php if ($log->user_id): ?>
                                <?php $user = get_userdata($log->user_id); ?>
                                <?php if ($user): ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-licenses-audit&user_id=' . $log->user_id)); ?>">
                                        <?php echo esc_html($user->display_name); ?>
                                    </a>
                                <?php else: ?>
                                    <?php esc_html_e('User #', 'peanut-license-server'); ?><?php echo esc_html($log->user_id); ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: #666;"><?php esc_html_e('System', 'peanut-license-server'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-details">
                            <?php echo peanut_format_audit_details($log->event, $data); ?>
                        </td>
                        <td class="column-ip">
                            <?php if (!empty($log->ip_address)): ?>
                                <code><?php echo esc_html($log->ip_address); ?></code>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($result['total_pages'] > 1): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php printf(
                        esc_html(_n('%s item', '%s items', $result['total'], 'peanut-license-server')),
                        number_format_i18n($result['total'])
                    ); ?>
                </span>
                <?php
                echo paginate_links([
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $result['total_pages'],
                    'current' => $page,
                ]);
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
/**
 * Get CSS class for event type
 */
if (!function_exists('peanut_get_event_class')) {
    function peanut_get_event_class(string $event): string {
        $classes = [
            'license_created' => 'success',
            'license_updated' => 'info',
            'license_deleted' => 'danger',
            'status_changed' => 'warning',
            'site_activated' => 'success',
            'site_deactivated' => 'warning',
            'license_validated' => 'info',
            'validation_failed' => 'danger',
            'license_transferred' => 'info',
            'key_regenerated' => 'warning',
            'tier_changed' => 'info',
            'expiry_changed' => 'info',
        ];
        return $classes[$event] ?? 'default';
    }
}

/**
 * Format audit details for display
 */
if (!function_exists('peanut_format_audit_details')) {
function peanut_format_audit_details(string $event, array $data): string {
    $output = [];

    switch ($event) {
        case 'license_created':
            if (!empty($data['customer_email'])) {
                $output[] = sprintf(__('Customer: %s', 'peanut-license-server'), esc_html($data['customer_email']));
            }
            if (!empty($data['tier'])) {
                $output[] = sprintf(__('Tier: %s', 'peanut-license-server'), esc_html(ucfirst($data['tier'])));
            }
            break;

        case 'license_updated':
        case 'status_changed':
        case 'tier_changed':
            if (isset($data['old_value']) && isset($data['new_value'])) {
                $output[] = sprintf(
                    '%s &rarr; %s',
                    '<del>' . esc_html($data['old_value']) . '</del>',
                    '<strong>' . esc_html($data['new_value']) . '</strong>'
                );
            }
            break;

        case 'site_activated':
        case 'site_deactivated':
            if (!empty($data['site_url'])) {
                $output[] = sprintf(__('Site: %s', 'peanut-license-server'), esc_html(parse_url($data['site_url'], PHP_URL_HOST)));
            }
            break;

        case 'license_validated':
        case 'validation_failed':
            if (!empty($data['site_url'])) {
                $output[] = sprintf(__('From: %s', 'peanut-license-server'), esc_html(parse_url($data['site_url'], PHP_URL_HOST)));
            }
            if (!empty($data['error_code'])) {
                $output[] = sprintf(__('Error: %s', 'peanut-license-server'), esc_html($data['error_code']));
            }
            break;

        case 'license_transferred':
            if (!empty($data['from_email'])) {
                $output[] = sprintf(__('From: %s', 'peanut-license-server'), esc_html($data['from_email']));
            }
            if (!empty($data['to_email'])) {
                $output[] = sprintf(__('To: %s', 'peanut-license-server'), esc_html($data['to_email']));
            }
            break;

        case 'key_regenerated':
            $output[] = __('License key was regenerated', 'peanut-license-server');
            break;

        case 'expiry_changed':
            if (!empty($data['old_expiry'])) {
                $output[] = sprintf(__('Old: %s', 'peanut-license-server'), esc_html($data['old_expiry']));
            }
            if (!empty($data['new_expiry'])) {
                $output[] = sprintf(__('New: %s', 'peanut-license-server'), esc_html($data['new_expiry']));
            }
            break;

        default:
            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    if (is_scalar($value)) {
                        $output[] = sprintf('%s: %s', esc_html($key), esc_html($value));
                    }
                }
            }
    }

    return !empty($output) ? implode('<br>', $output) : '<span style="color: #999;">-</span>';
}
}
?>

<style>
.peanut-audit-page .peanut-filters {
    margin: 20px 0;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.peanut-event-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.event-success { background: #d1fae5; color: #065f46; }
.event-info { background: #dbeafe; color: #1e40af; }
.event-warning { background: #fef3c7; color: #92400e; }
.event-danger { background: #fee2e2; color: #991b1b; }
.event-default { background: #f3f4f6; color: #374151; }

.column-details del { color: #999; }
.column-details strong { color: #1e293b; }
</style>
