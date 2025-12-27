<?php defined('ABSPATH') || exit; ?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Peanut Licenses', 'peanut-license-server'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-licenses-add')); ?>" class="page-title-action">
        <?php esc_html_e('Add New', 'peanut-license-server'); ?>
    </a>
    <hr class="wp-header-end">

    <!-- Info Card -->
    <?php if (!get_user_meta(get_current_user_id(), 'peanut_dismissed_licenses_info', true)) : ?>
    <div class="peanut-info-card" data-card-id="licenses_info">
        <button type="button" class="peanut-info-card-dismiss" aria-label="<?php esc_attr_e('Dismiss', 'peanut-license-server'); ?>">&times;</button>
        <div class="peanut-info-card-icon">
            <span class="dashicons dashicons-admin-network"></span>
        </div>
        <div class="peanut-info-card-content">
            <h3><?php esc_html_e('About All Licenses', 'peanut-license-server'); ?></h3>
            <p><?php esc_html_e('This page shows all licenses in your system. From here you can:', 'peanut-license-server'); ?></p>
            <ul>
                <li><?php esc_html_e('View license status, tier, and activation counts', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('Filter by status (active, expired, suspended) or tier', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('Manage individual licenses via the Actions dropdown', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('Click any license key to copy it to your clipboard', 'peanut-license-server'); ?></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="peanut-stats">
        <div class="peanut-stat-box">
            <span class="stat-number"><?php echo esc_html($stats['total']); ?></span>
            <span class="stat-label"><?php esc_html_e('Total Licenses', 'peanut-license-server'); ?></span>
        </div>
        <div class="peanut-stat-box">
            <span class="stat-number"><?php echo esc_html($stats['active']); ?></span>
            <span class="stat-label"><?php esc_html_e('Active', 'peanut-license-server'); ?></span>
        </div>
        <div class="peanut-stat-box">
            <span class="stat-number"><?php echo esc_html($stats['expired']); ?></span>
            <span class="stat-label"><?php esc_html_e('Expired', 'peanut-license-server'); ?></span>
        </div>
        <div class="peanut-stat-box">
            <span class="stat-number"><?php echo esc_html($stats['activations_total']); ?></span>
            <span class="stat-label"><?php esc_html_e('Active Sites', 'peanut-license-server'); ?></span>
        </div>
    </div>

    <!-- Filters -->
    <form method="get" class="peanut-filters">
        <input type="hidden" name="page" value="peanut-licenses">

        <select name="status">
            <option value=""><?php esc_html_e('All Statuses', 'peanut-license-server'); ?></option>
            <option value="active" <?php selected($status, 'active'); ?>><?php esc_html_e('Active', 'peanut-license-server'); ?></option>
            <option value="expired" <?php selected($status, 'expired'); ?>><?php esc_html_e('Expired', 'peanut-license-server'); ?></option>
            <option value="suspended" <?php selected($status, 'suspended'); ?>><?php esc_html_e('Suspended', 'peanut-license-server'); ?></option>
            <option value="revoked" <?php selected($status, 'revoked'); ?>><?php esc_html_e('Revoked', 'peanut-license-server'); ?></option>
        </select>

        <select name="tier">
            <option value=""><?php esc_html_e('All Tiers', 'peanut-license-server'); ?></option>
            <option value="free" <?php selected($tier, 'free'); ?>><?php esc_html_e('Free', 'peanut-license-server'); ?></option>
            <option value="pro" <?php selected($tier, 'pro'); ?>><?php esc_html_e('Pro', 'peanut-license-server'); ?></option>
            <option value="agency" <?php selected($tier, 'agency'); ?>><?php esc_html_e('Agency', 'peanut-license-server'); ?></option>
        </select>

        <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search...', 'peanut-license-server'); ?>">

        <button type="submit" class="button"><?php esc_html_e('Filter', 'peanut-license-server'); ?></button>
    </form>

    <!-- Licenses Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="column-license"><?php esc_html_e('License Key', 'peanut-license-server'); ?></th>
                <th scope="col" class="column-customer"><?php esc_html_e('Customer', 'peanut-license-server'); ?></th>
                <th scope="col" class="column-tier"><?php esc_html_e('Tier', 'peanut-license-server'); ?></th>
                <th scope="col" class="column-status"><?php esc_html_e('Status', 'peanut-license-server'); ?></th>
                <th scope="col" class="column-activations"><?php esc_html_e('Activations', 'peanut-license-server'); ?></th>
                <th scope="col" class="column-expires"><?php esc_html_e('Expires', 'peanut-license-server'); ?></th>
                <th scope="col" class="column-actions"><?php esc_html_e('Actions', 'peanut-license-server'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($licenses)): ?>
                <tr>
                    <td colspan="7"><?php esc_html_e('No licenses found.', 'peanut-license-server'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($licenses as $license): ?>
                    <tr>
                        <td class="column-license">
                            <code><?php echo esc_html($license->license_key); ?></code>
                        </td>
                        <td class="column-customer">
                            <strong><?php echo esc_html($license->customer_name ?: '-'); ?></strong><br>
                            <small><?php echo esc_html($license->customer_email); ?></small>
                        </td>
                        <td class="column-tier">
                            <span class="peanut-tier tier-<?php echo esc_attr($license->tier); ?>">
                                <?php echo esc_html(ucfirst($license->tier)); ?>
                            </span>
                        </td>
                        <td class="column-status">
                            <span class="peanut-status status-<?php echo esc_attr($license->status); ?>">
                                <?php echo esc_html(ucfirst($license->status)); ?>
                            </span>
                        </td>
                        <td class="column-activations">
                            <?php echo esc_html($license->activations_count); ?> / <?php echo esc_html($license->max_activations); ?>
                            <?php if (!empty($license->activations)): ?>
                                <div class="peanut-sites-list">
                                    <?php foreach ($license->activations as $activation): ?>
                                        <?php if ($activation->is_active): ?>
                                            <div class="peanut-site">
                                                <span><?php echo esc_html(parse_url($activation->site_url, PHP_URL_HOST)); ?></span>
                                                <button type="button" class="peanut-deactivate-site" data-id="<?php echo esc_attr($activation->id); ?>">×</button>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="column-expires">
                            <?php if ($license->expires_at): ?>
                                <?php
                                $expires = strtotime($license->expires_at);
                                $is_expired = $expires < time();
                                ?>
                                <span class="<?php echo $is_expired ? 'expired' : ''; ?>">
                                    <?php echo esc_html(date_i18n(get_option('date_format'), $expires)); ?>
                                </span>
                            <?php else: ?>
                                <span class="never"><?php esc_html_e('Never', 'peanut-license-server'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-actions">
                            <?php
                            $action_nonce = wp_create_nonce('peanut_license_action');
                            $base_url = admin_url('admin.php?page=peanut-licenses');
                            ?>

                            <div class="peanut-dropdown">
                                <button type="button" class="button button-small peanut-dropdown-toggle">
                                    <?php esc_html_e('Actions', 'peanut-license-server'); ?>
                                    <span class="dashicons dashicons-arrow-down-alt2" style="font-size: 14px; vertical-align: middle; margin-left: 2px;"></span>
                                </button>
                                <div class="peanut-dropdown-menu">
                                    <!-- Status Actions -->
                                    <?php if ($license->status === 'active'): ?>
                                        <a href="<?php echo esc_url(add_query_arg(['action' => 'suspend', 'license_id' => $license->id, '_wpnonce' => $action_nonce], $base_url)); ?>" class="peanut-dropdown-item">
                                            <span class="dashicons dashicons-controls-pause"></span>
                                            <?php esc_html_e('Suspend License', 'peanut-license-server'); ?>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo esc_url(add_query_arg(['action' => 'reactivate', 'license_id' => $license->id, '_wpnonce' => $action_nonce], $base_url)); ?>" class="peanut-dropdown-item">
                                            <span class="dashicons dashicons-controls-play"></span>
                                            <?php esc_html_e('Reactivate License', 'peanut-license-server'); ?>
                                        </a>
                                    <?php endif; ?>

                                    <a href="<?php echo esc_url(add_query_arg(['action' => 'resend_email', 'license_id' => $license->id, '_wpnonce' => $action_nonce], $base_url)); ?>" class="peanut-dropdown-item">
                                        <span class="dashicons dashicons-email"></span>
                                        <?php esc_html_e('Resend Email', 'peanut-license-server'); ?>
                                    </a>

                                    <div class="peanut-dropdown-divider"></div>

                                    <!-- Key Actions -->
                                    <a href="<?php echo esc_url(add_query_arg(['action' => 'regenerate', 'license_id' => $license->id, '_wpnonce' => $action_nonce], $base_url)); ?>" class="peanut-dropdown-item" onclick="return confirm('<?php esc_attr_e('Regenerate license key? The old key will no longer work.', 'peanut-license-server'); ?>')">
                                        <span class="dashicons dashicons-image-rotate"></span>
                                        <?php esc_html_e('Regenerate Key', 'peanut-license-server'); ?>
                                    </a>

                                    <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-licenses-transfer&license_id=' . $license->id)); ?>" class="peanut-dropdown-item">
                                        <span class="dashicons dashicons-randomize"></span>
                                        <?php esc_html_e('Transfer License', 'peanut-license-server'); ?>
                                    </a>

                                    <div class="peanut-dropdown-divider"></div>

                                    <!-- Danger Zone -->
                                    <a href="<?php echo esc_url(add_query_arg(['action' => 'delete', 'license_id' => $license->id, '_wpnonce' => $action_nonce], $base_url)); ?>" class="peanut-dropdown-item peanut-dropdown-item-danger" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this license? This cannot be undone.', 'peanut-license-server'); ?>')">
                                        <span class="dashicons dashicons-trash"></span>
                                        <?php esc_html_e('Delete License', 'peanut-license-server'); ?>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                echo paginate_links([
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_pages,
                    'current' => $page,
                ]);
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>
