<?php defined('ABSPATH') || exit; ?>
<div class="wrap">
    <h1><?php esc_html_e('Add New License', 'peanut-license-server'); ?></h1>

    <!-- Info Card -->
    <?php if (!get_user_meta(get_current_user_id(), 'peanut_dismissed_add_license_info', true)) : ?>
    <div class="peanut-info-card" data-card-id="add_license_info">
        <button type="button" class="peanut-info-card-dismiss" aria-label="<?php esc_attr_e('Dismiss', 'peanut-license-server'); ?>">&times;</button>
        <div class="peanut-info-card-icon">
            <span class="dashicons dashicons-plus-alt"></span>
        </div>
        <div class="peanut-info-card-content">
            <h3><?php esc_html_e('Creating a License', 'peanut-license-server'); ?></h3>
            <p><?php esc_html_e('Create a new license key for a customer. The license tier determines how many sites can be activated:', 'peanut-license-server'); ?></p>
            <ul>
                <li><strong><?php esc_html_e('Pro:', 'peanut-license-server'); ?></strong> <?php esc_html_e('3 site activations', 'peanut-license-server'); ?></li>
                <li><strong><?php esc_html_e('Agency:', 'peanut-license-server'); ?></strong> <?php esc_html_e('25 site activations', 'peanut-license-server'); ?></li>
            </ul>
            <p><?php esc_html_e('An email with the license key will be sent to the customer automatically if enabled.', 'peanut-license-server'); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="notice notice-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo esc_html($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="notice notice-success">
            <p>
                <?php esc_html_e('License created successfully!', 'peanut-license-server'); ?>
                <strong><?php echo esc_html($success->license_key); ?></strong>
            </p>
        </div>
    <?php endif; ?>

    <form method="post" class="peanut-add-form">
        <?php wp_nonce_field('peanut_add_license', 'peanut_add_license_nonce'); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="customer_email"><?php esc_html_e('Customer Email', 'peanut-license-server'); ?> *</label>
                </th>
                <td>
                    <input type="email" name="customer_email" id="customer_email" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="customer_name"><?php esc_html_e('Customer Name', 'peanut-license-server'); ?></label>
                </th>
                <td>
                    <input type="text" name="customer_name" id="customer_name" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="tier"><?php esc_html_e('License Tier', 'peanut-license-server'); ?></label>
                </th>
                <td>
                    <select name="tier" id="tier">
                        <option value="pro"><?php esc_html_e('Pro (3 sites)', 'peanut-license-server'); ?></option>
                        <option value="agency"><?php esc_html_e('Agency (25 sites)', 'peanut-license-server'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="expires_at"><?php esc_html_e('Expires', 'peanut-license-server'); ?></label>
                </th>
                <td>
                    <input type="date" name="expires_at" id="expires_at" value="<?php echo esc_attr(date('Y-m-d', strtotime('+1 year'))); ?>">
                    <p class="description"><?php esc_html_e('Leave empty for default 1 year.', 'peanut-license-server'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Send Email', 'peanut-license-server'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="send_email" value="1" checked>
                        <?php esc_html_e('Send license key to customer via email', 'peanut-license-server'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary"><?php esc_html_e('Create License', 'peanut-license-server'); ?></button>
        </p>
    </form>
</div>
