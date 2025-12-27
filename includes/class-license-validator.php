<?php
/**
 * License Validator Class
 *
 * Handles license validation and activation logic.
 *
 * @package Peanut_License_Server
 */

defined('ABSPATH') || exit;

class Peanut_License_Validator {

    /**
     * Error codes
     */
    public const ERROR_INVALID_KEY = 'invalid_license_key';
    public const ERROR_EXPIRED = 'license_expired';
    public const ERROR_SUSPENDED = 'license_suspended';
    public const ERROR_REVOKED = 'license_revoked';
    public const ERROR_ACTIVATION_LIMIT = 'activation_limit_reached';
    public const ERROR_INVALID_SITE = 'invalid_site_url';
    public const ERROR_SERVER = 'server_error';

    /**
     * Last error
     */
    private string $last_error = '';

    /**
     * Last error message
     */
    private string $last_error_message = '';

    /**
     * Validate and activate a license
     */
    public function validate_and_activate(string $license_key, array $site_data): array {
        // Validate site URL
        if (empty($site_data['site_url']) || !filter_var($site_data['site_url'], FILTER_VALIDATE_URL)) {
            return $this->error_response(
                self::ERROR_INVALID_SITE,
                __('Invalid site URL provided.', 'peanut-license-server')
            );
        }

        // Get license
        $license = Peanut_License_Manager::get_by_key($license_key);

        if (!$license) {
            return $this->error_response(
                self::ERROR_INVALID_KEY,
                __('Invalid license key.', 'peanut-license-server')
            );
        }

        // Check license status
        $status_check = $this->check_license_status($license);
        if (!$status_check['success']) {
            return $status_check;
        }

        // Check if this site is already activated
        $site_hash = md5(untrailingslashit(esc_url_raw($site_data['site_url'])));
        $existing_activation = null;

        foreach ($license->activations as $activation) {
            if ($activation->site_hash === $site_hash && $activation->is_active) {
                $existing_activation = $activation;
                break;
            }
        }

        // If not already activated, check activation limit
        if (!$existing_activation && !Peanut_License_Manager::can_activate($license)) {
            return $this->error_response(
                self::ERROR_ACTIVATION_LIMIT,
                sprintf(
                    __('Maximum activations (%d) reached. Please deactivate another site first.', 'peanut-license-server'),
                    $license->max_activations
                )
            );
        }

        // Add or update activation
        $activation = Peanut_License_Manager::add_activation($license->id, $site_data);

        if (!$activation) {
            return $this->error_response(
                self::ERROR_SERVER,
                __('Failed to activate license. Please try again.', 'peanut-license-server')
            );
        }

        // Refresh license data
        $license = Peanut_License_Manager::get_by_id($license->id);

        return $this->success_response($license);
    }

    /**
     * Validate license without activating
     */
    public function validate_only(string $license_key): array {
        $license = Peanut_License_Manager::get_by_key($license_key);

        if (!$license) {
            return $this->error_response(
                self::ERROR_INVALID_KEY,
                __('Invalid license key.', 'peanut-license-server')
            );
        }

        $status_check = $this->check_license_status($license);
        if (!$status_check['success']) {
            return $status_check;
        }

        return $this->success_response($license);
    }

    /**
     * Deactivate a site
     */
    public function deactivate(string $license_key, string $site_url): array {
        $license = Peanut_License_Manager::get_by_key($license_key);

        if (!$license) {
            return $this->error_response(
                self::ERROR_INVALID_KEY,
                __('Invalid license key.', 'peanut-license-server')
            );
        }

        $result = Peanut_License_Manager::remove_activation($license->id, $site_url);

        if (!$result) {
            return $this->error_response(
                self::ERROR_SERVER,
                __('Failed to deactivate site.', 'peanut-license-server')
            );
        }

        // Refresh license data
        $license = Peanut_License_Manager::get_by_id($license->id);

        return [
            'success' => true,
            'message' => __('Site deactivated successfully.', 'peanut-license-server'),
            'license' => $this->format_license_response($license),
        ];
    }

    /**
     * Check license status
     */
    private function check_license_status(object $license): array {
        // Check expiration
        if ($license->expires_at && strtotime($license->expires_at) < time()) {
            // Update status if not already expired
            if ($license->status === 'active') {
                Peanut_License_Manager::update($license->id, ['status' => 'expired']);
            }

            return $this->error_response(
                self::ERROR_EXPIRED,
                sprintf(
                    __('License expired on %s. Please renew to continue using premium features.', 'peanut-license-server'),
                    date_i18n(get_option('date_format'), strtotime($license->expires_at))
                )
            );
        }

        // Check status
        switch ($license->status) {
            case 'suspended':
                return $this->error_response(
                    self::ERROR_SUSPENDED,
                    __('This license has been suspended. Please contact support.', 'peanut-license-server')
                );

            case 'revoked':
                return $this->error_response(
                    self::ERROR_REVOKED,
                    __('This license has been revoked.', 'peanut-license-server')
                );

            case 'expired':
                return $this->error_response(
                    self::ERROR_EXPIRED,
                    __('This license has expired. Please renew to continue using premium features.', 'peanut-license-server')
                );
        }

        return ['success' => true];
    }

    /**
     * Format success response
     */
    private function success_response(object $license): array {
        return [
            'success' => true,
            'license' => $this->format_license_response($license),
        ];
    }

    /**
     * Format error response
     */
    private function error_response(string $code, string $message): array {
        $this->last_error = $code;
        $this->last_error_message = $message;

        return [
            'success' => false,
            'error' => $code,
            'message' => $message,
        ];
    }

    /**
     * Format license data for API response
     */
    private function format_license_response(object $license): array {
        $tier_features = Peanut_License_Manager::get_tier_features($license->tier);

        return [
            'status' => $license->status,
            'tier' => $license->tier,
            'tier_name' => Peanut_License_Manager::TIERS[$license->tier]['name'] ?? 'Free',
            'expires_at' => $license->expires_at,
            'expires_at_formatted' => $license->expires_at
                ? date_i18n(get_option('date_format'), strtotime($license->expires_at))
                : null,
            'activations_used' => $license->activations_count ?? 0,
            'activations_limit' => (int) $license->max_activations,
            'features' => $this->format_features($tier_features),
            'activated_sites' => array_map(function ($activation) {
                return [
                    'site_url' => $activation->site_url,
                    'site_name' => $activation->site_name,
                    'activated_at' => $activation->activated_at,
                    'is_active' => (bool) $activation->is_active,
                ];
            }, array_filter($license->activations ?? [], fn($a) => $a->is_active)),
        ];
    }

    /**
     * Format features as boolean array
     */
    private function format_features(array $features): array {
        $all_features = [
            'utm' => false,
            'links' => false,
            'contacts' => false,
            'dashboard' => false,
            'popups' => false,
            'analytics' => false,
            'export' => false,
            'monitor' => false,
            'white_label' => false,
            'priority_support' => false,
        ];

        foreach ($features as $feature) {
            if (isset($all_features[$feature])) {
                $all_features[$feature] = true;
            }
        }

        return $all_features;
    }

    /**
     * Get last error code
     */
    public function get_last_error(): string {
        return $this->last_error;
    }

    /**
     * Get last error message
     */
    public function get_last_error_message(): string {
        return $this->last_error_message;
    }

    /**
     * Validate license key format
     */
    public static function is_valid_format(string $key): bool {
        // Format: XXXX-XXXX-XXXX-XXXX (4 groups of 4 alphanumeric characters)
        return (bool) preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/i', $key);
    }

    /**
     * Sanitize license key
     */
    public static function sanitize_key(string $key): string {
        // Remove whitespace and convert to uppercase
        $key = strtoupper(trim($key));

        // Remove any characters that aren't alphanumeric or hyphens
        $key = preg_replace('/[^A-Z0-9-]/', '', $key);

        return $key;
    }
}
