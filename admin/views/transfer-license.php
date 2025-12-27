<?php
/**
 * Transfer License Admin View
 */
defined('ABSPATH') || exit;
?>
<div class="wrap">
    <h1><?php esc_html_e('Transfer License', 'peanut-license-server'); ?></h1>

    <p>
        <a href="<?php echo esc_url(admin_url('admin.php?page=peanut-licenses')); ?>">&larr; <?php esc_html_e('Back to Licenses', 'peanut-license-server'); ?></a>
    </p>

    <?php if ($message): ?>
        <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <?php if (!$license): ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('License not found. Please select a license from the licenses list.', 'peanut-license-server'); ?></p>
        </div>
    <?php else: ?>

        <!-- Current License Info -->
        <div class="card" style="max-width: 600px; margin-bottom: 20px;">
            <h2><?php esc_html_e('Current License Information', 'peanut-license-server'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('License Key', 'peanut-license-server'); ?></th>
                    <td><code><?php echo esc_html($license->license_key); ?></code></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Current Owner', 'peanut-license-server'); ?></th>
                    <td>
                        <?php echo esc_html($license->customer_name ?: '-'); ?><br>
                        <a href="mailto:<?php echo esc_attr($license->customer_email); ?>"><?php echo esc_html($license->customer_email); ?></a>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Tier', 'peanut-license-server'); ?></th>
                    <td><?php echo esc_html(ucfirst($license->tier)); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Status', 'peanut-license-server'); ?></th>
                    <td><?php echo esc_html(ucfirst($license->status)); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Active Sites', 'peanut-license-server'); ?></th>
                    <td>
                        <?php echo esc_html($license->activations_count ?? 0); ?> / <?php echo esc_html($license->max_activations); ?>
                        <?php if (!empty($license->activations)): ?>
                            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                                <?php foreach ($license->activations as $activation): ?>
                                    <?php if ($activation->is_active): ?>
                                        <li><code><?php echo esc_html($activation->site_url); ?></code></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Transfer Form -->
        <div class="card" style="max-width: 600px;">
            <h2><?php esc_html_e('Transfer to New Owner', 'peanut-license-server'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('peanut_transfer_license', 'peanut_transfer_nonce'); ?>
                <input type="hidden" name="license_id" value="<?php echo esc_attr($license->id); ?>">

                <table class="form-table">
                    <tr>
                        <th><label for="new_email"><?php esc_html_e('New Owner Email', 'peanut-license-server'); ?> <span style="color: red;">*</span></label></th>
                        <td>
                            <input type="email" name="new_email" id="new_email" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="new_name"><?php esc_html_e('New Owner Name', 'peanut-license-server'); ?></label></th>
                        <td>
                            <input type="text" name="new_name" id="new_name" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Options', 'peanut-license-server'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="deactivate_sites" value="1">
                                <?php esc_html_e('Deactivate all current site activations', 'peanut-license-server'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('If checked, all sites currently using this license will be deactivated. The new owner will need to reactivate.', 'peanut-license-server'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary" onclick="return confirm('<?php esc_attr_e('Are you sure you want to transfer this license?', 'peanut-license-server'); ?>')">
                        <?php esc_html_e('Transfer License', 'peanut-license-server'); ?>
                    </button>
                </p>
            </form>
        </div>

    <?php endif; ?>
</div>
