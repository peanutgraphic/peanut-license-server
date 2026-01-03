<?php
/**
 * WooCommerce Integration Class
 *
 * Adds license management to WooCommerce My Account.
 *
 * @package Peanut_License_Server
 */

defined('ABSPATH') || exit;

class Peanut_WooCommerce_Integration {

    /**
     * Constructor
     */
    public function __construct() {
        // Only load if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return;
        }

        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks(): void {
        // Add endpoint
        add_action('init', [$this, 'add_endpoint']);

        // Add menu item
        add_filter('woocommerce_account_menu_items', [$this, 'add_menu_item']);

        // Add content
        add_action('woocommerce_account_licenses_endpoint', [$this, 'render_licenses_page']);

        // Handle AJAX
        add_action('wp_ajax_peanut_deactivate_customer_site', [$this, 'ajax_deactivate_site']);

        // Enqueue scripts
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Flush rewrite rules on activation
        add_action('peanut_license_server_activate', [$this, 'flush_rewrite_rules']);
    }

    /**
     * Add WooCommerce endpoint
     */
    public function add_endpoint(): void {
        add_rewrite_endpoint('licenses', EP_ROOT | EP_PAGES);
    }

    /**
     * Flush rewrite rules
     */
    public function flush_rewrite_rules(): void {
        $this->add_endpoint();
        flush_rewrite_rules();
    }

    /**
     * Add menu item to My Account
     */
    public function add_menu_item(array $items): array {
        // Insert after dashboard
        $new_items = [];
        foreach ($items as $key => $label) {
            $new_items[$key] = $label;
            if ($key === 'dashboard') {
                $new_items['licenses'] = __('My Licenses', 'peanut-license-server');
            }
        }

        // If dashboard wasn't found, just append
        if (!isset($new_items['licenses'])) {
            $new_items['licenses'] = __('My Licenses', 'peanut-license-server');
        }

        return $new_items;
    }

    /**
     * Render licenses page
     */
    public function render_licenses_page(): void {
        $user_id = get_current_user_id();

        if (!$user_id) {
            echo '<p>' . esc_html__('Please log in to view your licenses.', 'peanut-license-server') . '</p>';
            return;
        }

        $licenses = Peanut_License_Manager::get_user_licenses($user_id);

        // If no licenses by user ID, try by email
        if (empty($licenses)) {
            $user = get_userdata($user_id);
            if ($user) {
                $licenses = Peanut_License_Manager::get_by_email($user->user_email);
            }
        }

        $this->render_licenses_template($licenses);
    }

    /**
     * Render licenses template
     */
    private function render_licenses_template(array $licenses): void {
        ?>
        <div class="peanut-licenses-portal">
            <?php if (empty($licenses)): ?>
                <div class="peanut-no-licenses">
                    <p><?php esc_html_e("You don't have any licenses yet.", 'peanut-license-server'); ?></p>
                    <a href="<?php echo esc_url(home_url('/peanut-suite/pricing/')); ?>" class="button">
                        <?php esc_html_e('Get Peanut Suite', 'peanut-license-server'); ?>
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($licenses as $license): ?>
                    <div class="peanut-license-card" data-license-id="<?php echo esc_attr($license->id); ?>">
                        <div class="license-header">
                            <div class="license-key">
                                <label><?php esc_html_e('License Key', 'peanut-license-server'); ?></label>
                                <code class="copyable" data-copy="<?php echo esc_attr($license->license_key); ?>">
                                    <?php echo esc_html($license->license_key); ?>
                                </code>
                                <button type="button" class="copy-btn" title="<?php esc_attr_e('Copy to clipboard', 'peanut-license-server'); ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                    </svg>
                                </button>
                            </div>
                            <span class="license-tier tier-<?php echo esc_attr($license->tier); ?>">
                                <?php echo esc_html(Peanut_License_Manager::TIERS[$license->tier]['name'] ?? ucfirst($license->tier)); ?>
                            </span>
                        </div>

                        <div class="license-meta">
                            <div class="meta-item">
                                <span class="meta-label"><?php esc_html_e('Status', 'peanut-license-server'); ?></span>
                                <span class="license-status status-<?php echo esc_attr($license->status); ?>">
                                    <?php echo esc_html(ucfirst($license->status)); ?>
                                </span>
                            </div>

                            <div class="meta-item">
                                <span class="meta-label"><?php esc_html_e('Activations', 'peanut-license-server'); ?></span>
                                <span class="activations-count">
                                    <?php echo esc_html($license->activations_count); ?> / <?php echo esc_html($license->max_activations); ?>
                                </span>
                            </div>

                            <div class="meta-item">
                                <span class="meta-label"><?php esc_html_e('Expires', 'peanut-license-server'); ?></span>
                                <span class="expires-date <?php echo $license->expires_at && strtotime($license->expires_at) < time() ? 'expired' : ''; ?>">
                                    <?php
                                    if ($license->expires_at) {
                                        echo esc_html(date_i18n(get_option('date_format'), strtotime($license->expires_at)));
                                    } else {
                                        esc_html_e('Never', 'peanut-license-server');
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>

                        <?php $active_sites = array_filter($license->activations, fn($a) => $a->is_active); ?>
                        <?php if (!empty($active_sites)): ?>
                            <div class="license-sites">
                                <h4><?php esc_html_e('Activated Sites', 'peanut-license-server'); ?></h4>
                                <ul class="sites-list">
                                    <?php foreach ($active_sites as $site): ?>
                                        <li class="site-item" data-activation-id="<?php echo esc_attr($site->id); ?>">
                                            <div class="site-info">
                                                <span class="site-url"><?php echo esc_html($site->site_url); ?></span>
                                                <?php if ($site->site_name): ?>
                                                    <span class="site-name"><?php echo esc_html($site->site_name); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <button type="button" class="deactivate-site-btn" data-activation-id="<?php echo esc_attr($site->id); ?>">
                                                <?php esc_html_e('Deactivate', 'peanut-license-server'); ?>
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="license-actions">
                            <?php $download_url = rest_url('peanut-api/v1/updates/download?license=' . $license->license_key); ?>
                            <a href="<?php echo esc_url($download_url); ?>" class="button download-btn">
                                <?php esc_html_e('Download Plugin', 'peanut-license-server'); ?>
                            </a>

                            <?php if ($license->status === 'expired'): ?>
                                <a href="<?php echo esc_url(wc_get_account_endpoint_url('subscriptions')); ?>" class="button renew-btn">
                                    <?php esc_html_e('Renew License', 'peanut-license-server'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="peanut-help-text">
                    <h4><?php esc_html_e('How to activate your license', 'peanut-license-server'); ?></h4>
                    <ol>
                        <li><?php esc_html_e('Download and install the Peanut Suite plugin on your WordPress site', 'peanut-license-server'); ?></li>
                        <li><?php esc_html_e('Go to Peanut Suite → Settings in your WordPress admin', 'peanut-license-server'); ?></li>
                        <li><?php esc_html_e('Enter your license key and click Activate', 'peanut-license-server'); ?></li>
                    </ol>
                </div>
            <?php endif; ?>
        </div>
        <?php
        // CSS is loaded via wp_enqueue_style('peanut-woocommerce-portal') in enqueue_scripts()
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts(): void {
        if (!is_account_page()) {
            return;
        }

        // Enqueue portal styles
        wp_enqueue_style(
            'peanut-woocommerce-portal',
            PEANUT_LICENSE_SERVER_URL . 'assets/css/woocommerce-portal.css',
            [],
            PEANUT_LICENSE_SERVER_VERSION
        );

        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                // Copy license key
                $(".copy-btn").on("click", function() {
                    var code = $(this).siblings("code").data("copy");
                    navigator.clipboard.writeText(code).then(function() {
                        alert("' . esc_js(__('License key copied to clipboard!', 'peanut-license-server')) . '");
                    });
                });

                // Deactivate site
                $(".deactivate-site-btn").on("click", function() {
                    if (!confirm("' . esc_js(__('Are you sure you want to deactivate this site?', 'peanut-license-server')) . '")) {
                        return;
                    }

                    var $btn = $(this);
                    var $item = $btn.closest(".site-item");
                    var activationId = $btn.data("activation-id");

                    $btn.prop("disabled", true).text("' . esc_js(__('Deactivating...', 'peanut-license-server')) . '");

                    $.ajax({
                        url: "' . esc_url(admin_url('admin-ajax.php')) . '",
                        type: "POST",
                        data: {
                            action: "peanut_deactivate_customer_site",
                            nonce: "' . wp_create_nonce('peanut_customer_portal') . '",
                            activation_id: activationId
                        },
                        success: function(response) {
                            if (response.success) {
                                $item.slideUp(200, function() {
                                    $(this).remove();
                                    // Update count
                                    var $card = $btn.closest(".peanut-license-card");
                                    var $count = $card.find(".activations-count");
                                    var text = $count.text();
                                    var match = text.match(/(\d+) \/ (\d+)/);
                                    if (match) {
                                        var used = parseInt(match[1]) - 1;
                                        $count.text(used + " / " + match[2]);
                                    }
                                });
                            } else {
                                alert(response.data.message || "' . esc_js(__('Failed to deactivate site.', 'peanut-license-server')) . '");
                                $btn.prop("disabled", false).text("' . esc_js(__('Deactivate', 'peanut-license-server')) . '");
                            }
                        },
                        error: function() {
                            alert("' . esc_js(__('An error occurred.', 'peanut-license-server')) . '");
                            $btn.prop("disabled", false).text("' . esc_js(__('Deactivate', 'peanut-license-server')) . '");
                        }
                    });
                });
            });
        ');
    }

    /**
     * AJAX handler for deactivating site
     */
    public function ajax_deactivate_site(): void {
        check_ajax_referer('peanut_customer_portal', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in.', 'peanut-license-server')]);
        }

        $activation_id = intval($_POST['activation_id'] ?? 0);

        if (!$activation_id) {
            wp_send_json_error(['message' => __('Invalid activation.', 'peanut-license-server')]);
        }

        // Verify user owns this activation
        global $wpdb;
        $activation = $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, l.user_id, l.customer_email
             FROM {$wpdb->prefix}peanut_activations a
             JOIN {$wpdb->prefix}peanut_licenses l ON a.license_id = l.id
             WHERE a.id = %d",
            $activation_id
        ));

        if (!$activation) {
            wp_send_json_error(['message' => __('Activation not found.', 'peanut-license-server')]);
        }

        $user = wp_get_current_user();
        $user_owns = $activation->user_id == $user->ID || $activation->customer_email === $user->user_email;

        if (!$user_owns) {
            wp_send_json_error(['message' => __('You do not have permission to deactivate this site.', 'peanut-license-server')]);
        }

        $result = Peanut_License_Manager::deactivate_site($activation_id);

        if ($result) {
            wp_send_json_success(['message' => __('Site deactivated.', 'peanut-license-server')]);
        } else {
            wp_send_json_error(['message' => __('Failed to deactivate site.', 'peanut-license-server')]);
        }
    }
}

// Initialize
new Peanut_WooCommerce_Integration();
