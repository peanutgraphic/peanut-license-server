<?php
defined('ABSPATH') || exit;
$products = Peanut_Update_Server::get_all_products();
$current_product = isset($_GET['product']) ? sanitize_text_field($_GET['product']) : 'peanut-suite';
if (!isset($products[$current_product])) {
    $current_product = 'peanut-suite';
}
?>
<div class="wrap">
    <h1><?php esc_html_e('License Server Settings', 'peanut-license-server'); ?></h1>

    <!-- Info Card -->
    <?php if (!get_user_meta(get_current_user_id(), 'peanut_dismissed_settings_info', true)) : ?>
    <div class="peanut-info-card" data-card-id="settings_info">
        <button type="button" class="peanut-info-card-dismiss" aria-label="<?php esc_attr_e('Dismiss', 'peanut-license-server'); ?>">&times;</button>
        <div class="peanut-info-card-icon">
            <span class="dashicons dashicons-admin-settings"></span>
        </div>
        <div class="peanut-info-card-content">
            <h3><?php esc_html_e('Server Configuration', 'peanut-license-server'); ?></h3>
            <p><?php esc_html_e('Configure your license server and update server settings:', 'peanut-license-server'); ?></p>
            <ul>
                <li><strong><?php esc_html_e('License API:', 'peanut-license-server'); ?></strong> <?php esc_html_e('Enable/disable license validation for client sites', 'peanut-license-server'); ?></li>
                <li><strong><?php esc_html_e('Update Server:', 'peanut-license-server'); ?></strong> <?php esc_html_e('Serve plugin updates to licensed sites', 'peanut-license-server'); ?></li>
                <li><strong><?php esc_html_e('Cache Duration:', 'peanut-license-server'); ?></strong> <?php esc_html_e('How long clients cache validation results', 'peanut-license-server'); ?></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($saved): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved.', 'peanut-license-server'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post">
        <?php wp_nonce_field('peanut_save_settings', 'peanut_settings_nonce'); ?>

        <h2><?php esc_html_e('API Settings', 'peanut-license-server'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('License API', 'peanut-license-server'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="api_enabled" value="1" <?php checked($settings['api_enabled']); ?>>
                        <?php esc_html_e('Enable license validation API', 'peanut-license-server'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('API Endpoint:', 'peanut-license-server'); ?>
                        <code><?php echo esc_html(rest_url('peanut-api/v1/license/validate')); ?></code>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Update Server', 'peanut-license-server'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="update_enabled" value="1" <?php checked($settings['update_enabled']); ?>>
                        <?php esc_html_e('Enable plugin update server', 'peanut-license-server'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Update Check Endpoint:', 'peanut-license-server'); ?>
                        <code><?php echo esc_html(rest_url('peanut-api/v1/updates/check?plugin=PLUGIN_SLUG')); ?></code>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cache_duration"><?php esc_html_e('Cache Duration', 'peanut-license-server'); ?></label>
                </th>
                <td>
                    <input type="number" name="cache_duration" id="cache_duration" value="<?php echo esc_attr($settings['cache_duration']); ?>" min="1" max="168" class="small-text">
                    <?php esc_html_e('hours', 'peanut-license-server'); ?>
                    <p class="description"><?php esc_html_e('How long client sites should cache license validation results.', 'peanut-license-server'); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('Product Settings', 'peanut-license-server'); ?></h2>

        <!-- Product Tabs -->
        <nav class="nav-tab-wrapper">
            <?php foreach ($products as $slug => $product): ?>
                <a href="<?php echo esc_url(add_query_arg('product', $slug)); ?>" class="nav-tab <?php echo $current_product === $slug ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html($product['name']); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <input type="hidden" name="product_slug" value="<?php echo esc_attr($current_product); ?>">

        <?php
        $product_config = $products[$current_product];
        $product_version = get_option("peanut_{$current_product}_version", '1.0.0');
        $product_requires_wp = get_option("peanut_{$current_product}_requires_wp", '6.0');
        $product_requires_php = get_option("peanut_{$current_product}_requires_php", '8.0');
        $product_tested_wp = get_option("peanut_{$current_product}_tested_wp", '6.4');
        ?>

        <div class="product-settings" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin-top: 0;">
            <h3 style="margin-top: 0;"><?php echo esc_html($product_config['name']); ?></h3>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="product_version"><?php esc_html_e('Current Version', 'peanut-license-server'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="product_version" id="product_version" value="<?php echo esc_attr($product_version); ?>" class="regular-text" pattern="\d+\.\d+\.\d+">
                        <p class="description"><?php printf(esc_html__('The current version of %s available for download.', 'peanut-license-server'), $product_config['name']); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="product_requires_wp"><?php esc_html_e('Requires WordPress', 'peanut-license-server'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="product_requires_wp" id="product_requires_wp" value="<?php echo esc_attr($product_requires_wp); ?>" class="small-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="product_requires_php"><?php esc_html_e('Requires PHP', 'peanut-license-server'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="product_requires_php" id="product_requires_php" value="<?php echo esc_attr($product_requires_php); ?>" class="small-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="product_tested_wp"><?php esc_html_e('Tested up to', 'peanut-license-server'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="product_tested_wp" id="product_tested_wp" value="<?php echo esc_attr($product_tested_wp); ?>" class="small-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="new_changelog"><?php esc_html_e('Add Changelog Entry', 'peanut-license-server'); ?></label>
                    </th>
                    <td>
                        <textarea name="new_changelog" id="new_changelog" rows="5" class="large-text" placeholder="<?php esc_attr_e("- New feature X\n- Bug fix Y\n- Improvement Z", 'peanut-license-server'); ?>"></textarea>
                        <p class="description"><?php esc_html_e('Add changelog notes for the current version (will be prepended to existing changelog).', 'peanut-license-server'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Upload Location', 'peanut-license-server'); ?></th>
                <td>
                    <?php
                    $upload_dir = wp_upload_dir();
                    $update_server = new Peanut_Update_Server($current_product);
                    $file_info = $update_server->get_download_file_info();
                    ?>
                    <p>
                        <code><?php echo esc_html($upload_dir['basedir'] . '/' . $current_product . '/'); ?></code>
                    </p>
                    <div id="peanut-file-status">
                        <?php if ($file_info): ?>
                            <p>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                                <?php
                                printf(
                                    esc_html__('%s found (%s)', 'peanut-license-server'),
                                    '<strong>' . esc_html($file_info['filename']) . '</strong>',
                                    size_format($file_info['size'])
                                );
                                ?>
                                <br>
                                <small style="color: #666;">
                                    <?php
                                    printf(
                                        esc_html__('Last modified: %s', 'peanut-license-server'),
                                        date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $file_info['modified'])
                                    );
                                    ?>
                                </small>
                            </p>
                        <?php else: ?>
                            <p>
                                <span class="dashicons dashicons-warning" style="color: orange;"></span>
                                <?php printf(esc_html__('No plugin ZIP found. Upload %s.zip or %s-x.x.x.zip to enable downloads.', 'peanut-license-server'), $current_product, $current_product); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <p>
                        <button type="button" id="peanut-recheck-file" class="button" data-product="<?php echo esc_attr($current_product); ?>">
                            <span class="dashicons dashicons-update" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Recheck Directory', 'peanut-license-server'); ?>
                        </button>
                    </p>
                    <p class="description">
                        <?php printf(esc_html__('Accepts: %s.zip or %s-1.0.0.zip (versioned files supported)', 'peanut-license-server'), $current_product, $current_product); ?>
                    </p>
                </td>
            </tr>
            </table>
        </div><!-- .product-settings -->

        <p class="submit">
            <button type="submit" class="button button-primary"><?php esc_html_e('Save Settings', 'peanut-license-server'); ?></button>
        </p>
    </form>
</div>
