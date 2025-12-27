<?php
/**
 * Product Updates Admin Page
 *
 * Upload plugin zips and manage version info for auto-updates.
 *
 * @package Peanut_License_Server
 */

defined('ABSPATH') || exit;
?>

<div class="wrap peanut-product-updates">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-update" style="font-size: 28px; width: 28px; height: 28px; margin-right: 10px;"></span>
        <?php esc_html_e('Product Updates', 'peanut-license-server'); ?>
    </h1>

    <!-- Info Card -->
    <?php if (!get_user_meta(get_current_user_id(), 'peanut_dismissed_updates_info', true)) : ?>
    <div class="peanut-info-card" data-card-id="updates_info" style="margin-top: 20px;">
        <button type="button" class="peanut-info-card-dismiss" aria-label="<?php esc_attr_e('Dismiss', 'peanut-license-server'); ?>">&times;</button>
        <div class="peanut-info-card-icon">
            <span class="dashicons dashicons-cloud-upload"></span>
        </div>
        <div class="peanut-info-card-content">
            <h3><?php esc_html_e('Manage Plugin Updates', 'peanut-license-server'); ?></h3>
            <p><?php esc_html_e('Upload and manage plugin versions for automatic updates:', 'peanut-license-server'); ?></p>
            <ul>
                <li><?php esc_html_e('Upload new plugin ZIP files for each product', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('Set version numbers and WordPress/PHP requirements', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('Add changelog notes that users see before updating', 'peanut-license-server'); ?></li>
                <li><?php esc_html_e('Licensed sites automatically receive update notifications', 'peanut-license-server'); ?></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($message) : ?>
        <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Product Selector -->
    <div style="margin: 24px 0;">
        <form method="get" style="display: flex; gap: 12px; align-items: center;">
            <input type="hidden" name="page" value="peanut-product-updates">
            <label for="product" style="font-weight: 600;"><?php esc_html_e('Select Product:', 'peanut-license-server'); ?></label>
            <select name="product" id="product" onchange="this.form.submit()" style="min-width: 200px;">
                <?php foreach ($products as $slug => $product) : ?>
                    <option value="<?php echo esc_attr($slug); ?>" <?php selected($selected_product, $slug); ?>>
                        <?php echo esc_html($product['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

        <!-- Upload ZIP Card -->
        <div style="background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0; display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-upload" style="color: #2563eb;"></span>
                <?php esc_html_e('Upload Plugin ZIP', 'peanut-license-server'); ?>
            </h2>

            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('peanut_product_updates', 'peanut_updates_nonce'); ?>
                <input type="hidden" name="update_action" value="upload">
                <input type="hidden" name="product_slug" value="<?php echo esc_attr($selected_product); ?>">

                <!-- Current File Status -->
                <div style="background: #f9fafb; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 8px 0; font-size: 14px; color: #374151;">
                        <?php esc_html_e('Current Download File', 'peanut-license-server'); ?>
                    </h4>
                    <?php if ($file_info) : ?>
                        <p style="margin: 0;">
                            <span class="dashicons dashicons-yes-alt" style="color: #22c55e;"></span>
                            <strong><?php echo esc_html($file_info['filename']); ?></strong>
                            <span style="color: #6b7280;">(<?php echo esc_html(size_format($file_info['size'])); ?>)</span>
                        </p>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #6b7280;">
                            <?php printf(
                                esc_html__('Last modified: %s', 'peanut-license-server'),
                                date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $file_info['modified'])
                            ); ?>
                        </p>
                    <?php else : ?>
                        <p style="margin: 0; color: #f59e0b;">
                            <span class="dashicons dashicons-warning"></span>
                            <?php esc_html_e('No ZIP file uploaded yet. Users cannot download updates.', 'peanut-license-server'); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">
                        <?php esc_html_e('Select ZIP File', 'peanut-license-server'); ?>
                    </label>
                    <input type="file" name="plugin_zip" accept=".zip" style="width: 100%;">
                    <p class="description">
                        <?php printf(
                            esc_html__('Upload %s.zip - this will replace any existing file.', 'peanut-license-server'),
                            esc_html($selected_product)
                        ); ?>
                    </p>
                </div>

                <button type="submit" class="button button-primary" style="width: 100%;">
                    <span class="dashicons dashicons-cloud-upload" style="margin-right: 4px;"></span>
                    <?php esc_html_e('Upload ZIP', 'peanut-license-server'); ?>
                </button>
            </form>

            <!-- Upload Path Info -->
            <div style="margin-top: 20px; padding: 12px; background: #eff6ff; border-radius: 6px; font-size: 13px;">
                <strong><?php esc_html_e('Upload Location:', 'peanut-license-server'); ?></strong><br>
                <code style="font-size: 12px;"><?php echo esc_html(wp_upload_dir()['basedir'] . '/' . $selected_product . '/'); ?></code>
            </div>
        </div>

        <!-- Version Info Card -->
        <div style="background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0; display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-info-outline" style="color: #2563eb;"></span>
                <?php esc_html_e('Version Information', 'peanut-license-server'); ?>
            </h2>

            <form method="post">
                <?php wp_nonce_field('peanut_product_updates', 'peanut_updates_nonce'); ?>
                <input type="hidden" name="update_action" value="save">
                <input type="hidden" name="product_slug" value="<?php echo esc_attr($selected_product); ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 500;">
                            <?php esc_html_e('Version', 'peanut-license-server'); ?> <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" name="version" value="<?php echo esc_attr($product_settings['version']); ?>"
                               pattern="\d+\.\d+\.\d+" required class="regular-text" placeholder="1.5.1">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 500;">
                            <?php esc_html_e('Last Updated', 'peanut-license-server'); ?>
                        </label>
                        <input type="text" value="<?php echo esc_attr($product_settings['last_updated'] ?: 'Not set'); ?>"
                               disabled class="regular-text" style="background: #f3f4f6;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 500;">
                            <?php esc_html_e('Requires WP', 'peanut-license-server'); ?>
                        </label>
                        <input type="text" name="requires_wp" value="<?php echo esc_attr($product_settings['requires_wp']); ?>"
                               class="regular-text" placeholder="6.0">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 500;">
                            <?php esc_html_e('Tested Up To', 'peanut-license-server'); ?>
                        </label>
                        <input type="text" name="tested_wp" value="<?php echo esc_attr($product_settings['tested_wp']); ?>"
                               class="regular-text" placeholder="6.4">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 500;">
                            <?php esc_html_e('Requires PHP', 'peanut-license-server'); ?>
                        </label>
                        <input type="text" name="requires_php" value="<?php echo esc_attr($product_settings['requires_php']); ?>"
                               class="regular-text" placeholder="8.0">
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 4px; font-weight: 500;">
                        <?php esc_html_e('Changelog (for this version)', 'peanut-license-server'); ?>
                    </label>
                    <textarea name="changelog" rows="5" class="large-text"
                              placeholder="<?php esc_attr_e("Enter changelog items, one per line:\n- Added new feature\n- Fixed bug\n- Improved performance", 'peanut-license-server'); ?>"></textarea>
                    <p class="description">
                        <?php esc_html_e('Enter changes for this version. Will be prepended to existing changelog.', 'peanut-license-server'); ?>
                    </p>
                </div>

                <button type="submit" class="button button-primary" style="width: 100%;">
                    <span class="dashicons dashicons-saved" style="margin-right: 4px;"></span>
                    <?php esc_html_e('Save Version Info', 'peanut-license-server'); ?>
                </button>
            </form>
        </div>

    </div>

    <!-- Product Info Card -->
    <div style="background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 24px;">
        <h2 style="margin-top: 0;">
            <span class="dashicons dashicons-admin-plugins" style="margin-right: 8px;"></span>
            <?php echo esc_html($products[$selected_product]['name']); ?>
        </h2>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
            <div>
                <strong><?php esc_html_e('Slug', 'peanut-license-server'); ?></strong><br>
                <code><?php echo esc_html($selected_product); ?></code>
            </div>
            <div>
                <strong><?php esc_html_e('Main File', 'peanut-license-server'); ?></strong><br>
                <code><?php echo esc_html($products[$selected_product]['file']); ?></code>
            </div>
            <div>
                <strong><?php esc_html_e('Homepage', 'peanut-license-server'); ?></strong><br>
                <a href="<?php echo esc_url($products[$selected_product]['homepage']); ?>" target="_blank">
                    <?php echo esc_html($products[$selected_product]['homepage']); ?>
                </a>
            </div>
            <div>
                <strong><?php esc_html_e('Download URL', 'peanut-license-server'); ?></strong><br>
                <code style="font-size: 11px; word-break: break-all;">
                    <?php echo esc_html(rest_url('peanut-api/v1/updates/download?plugin=' . $selected_product)); ?>
                </code>
            </div>
        </div>

        <p style="margin-top: 16px; color: #6b7280;">
            <?php echo esc_html($products[$selected_product]['description']); ?>
        </p>
    </div>

    <!-- Existing Changelog -->
    <?php if (!empty($product_settings['changelog'])) : ?>
    <div style="background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 24px;">
        <h2 style="margin-top: 0;">
            <span class="dashicons dashicons-list-view" style="margin-right: 8px;"></span>
            <?php esc_html_e('Changelog History', 'peanut-license-server'); ?>
        </h2>
        <div style="max-height: 400px; overflow-y: auto; padding: 16px; background: #f9fafb; border-radius: 8px;">
            <?php echo wp_kses_post($product_settings['changelog']); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Help Box -->
    <div style="margin-top: 24px; padding: 20px; background: #eff6ff; border-radius: 8px; border: 1px solid #bfdbfe;">
        <h3 style="margin-top: 0; color: #1e40af;">
            <span class="dashicons dashicons-info" style="margin-right: 8px;"></span>
            <?php esc_html_e('How Updates Work', 'peanut-license-server'); ?>
        </h3>
        <ol style="margin-left: 20px; color: #1e40af;">
            <li><?php esc_html_e('Upload your plugin ZIP file using the form above', 'peanut-license-server'); ?></li>
            <li><?php esc_html_e('Set the version number and requirements', 'peanut-license-server'); ?></li>
            <li><?php esc_html_e('Add changelog notes for users to see what changed', 'peanut-license-server'); ?></li>
            <li><?php esc_html_e('Client sites will see the update in WordPress Dashboard > Updates', 'peanut-license-server'); ?></li>
            <li><?php esc_html_e('Licensed users can update directly from their WordPress admin', 'peanut-license-server'); ?></li>
        </ol>
        <p style="margin-bottom: 0; color: #1e40af;">
            <strong><?php esc_html_e('Note:', 'peanut-license-server'); ?></strong>
            <?php esc_html_e('The ZIP file must contain a folder with the plugin slug (e.g., peanut-booker/peanut-booker.php).', 'peanut-license-server'); ?>
        </p>
    </div>
</div>
