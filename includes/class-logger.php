<?php
/**
 * Centralized Logger Class
 *
 * Provides consistent logging across the License Server plugin.
 *
 * @package Peanut_License_Server
 * @since 1.3.2
 */

defined('ABSPATH') || exit;

class Peanut_Logger {

    /**
     * Log levels
     */
    public const DEBUG = 'debug';
    public const INFO = 'info';
    public const WARNING = 'warning';
    public const ERROR = 'error';

    /**
     * Minimum log level (can be filtered)
     */
    private static ?string $min_level = null;

    /**
     * Level hierarchy for filtering
     */
    private const LEVEL_HIERARCHY = [
        self::DEBUG => 0,
        self::INFO => 1,
        self::WARNING => 2,
        self::ERROR => 3,
    ];

    /**
     * Log a debug message
     */
    public static function debug(string $message, array $context = []): void {
        self::log(self::DEBUG, $message, $context);
    }

    /**
     * Log an info message
     */
    public static function info(string $message, array $context = []): void {
        self::log(self::INFO, $message, $context);
    }

    /**
     * Log a warning message
     */
    public static function warning(string $message, array $context = []): void {
        self::log(self::WARNING, $message, $context);
    }

    /**
     * Log an error message
     */
    public static function error(string $message, array $context = []): void {
        self::log(self::ERROR, $message, $context);
    }

    /**
     * Main logging method
     */
    public static function log(string $level, string $message, array $context = []): void {
        // Check if this level should be logged
        if (!self::should_log($level)) {
            return;
        }

        // Build the log entry
        $entry = self::build_entry($level, $message, $context);

        // Write to error log
        error_log($entry);

        // Allow other handlers via action
        do_action('peanut_license_log', $level, $message, $context, $entry);
    }

    /**
     * Check if the given level should be logged
     */
    private static function should_log(string $level): bool {
        $min_level = self::get_min_level();
        
        $level_value = self::LEVEL_HIERARCHY[$level] ?? 0;
        $min_value = self::LEVEL_HIERARCHY[$min_level] ?? 0;

        return $level_value >= $min_value;
    }

    /**
     * Get minimum log level
     */
    private static function get_min_level(): string {
        if (self::$min_level === null) {
            // Default to INFO in production, DEBUG in WP_DEBUG mode
            $default = defined('WP_DEBUG') && WP_DEBUG ? self::DEBUG : self::INFO;
            self::$min_level = apply_filters('peanut_license_log_level', $default);
        }

        return self::$min_level;
    }

    /**
     * Build log entry string
     */
    private static function build_entry(string $level, string $message, array $context = []): string {
        $timestamp = current_time('Y-m-d H:i:s');
        $level_upper = strtoupper($level);
        
        $entry = "[{$timestamp}] Peanut License Server [{$level_upper}]: {$message}";

        // Add context if provided
        if (!empty($context)) {
            // Filter out sensitive data
            $safe_context = self::filter_sensitive_data($context);
            $entry .= ' | Context: ' . wp_json_encode($safe_context, JSON_UNESCAPED_SLASHES);
        }

        return $entry;
    }

    /**
     * Filter sensitive data from context
     */
    private static function filter_sensitive_data(array $context): array {
        $sensitive_keys = ['password', 'secret', 'token', 'key', 'auth', 'credential'];
        
        foreach ($context as $key => $value) {
            $key_lower = strtolower($key);
            
            foreach ($sensitive_keys as $sensitive) {
                if (str_contains($key_lower, $sensitive)) {
                    $context[$key] = '[REDACTED]';
                    break;
                }
            }

            // Recursively filter nested arrays
            if (is_array($value)) {
                $context[$key] = self::filter_sensitive_data($value);
            }
        }

        return $context;
    }

    /**
     * Log a license-related event
     */
    public static function license(string $action, string $license_key, array $context = []): void {
        // Mask the license key for privacy
        $masked_key = self::mask_license_key($license_key);
        $context['license_key'] = $masked_key;
        
        self::info("License {$action}", $context);
    }

    /**
     * Log an API request
     */
    public static function api(string $endpoint, string $method, int $status_code, array $context = []): void {
        $context['endpoint'] = $endpoint;
        $context['method'] = $method;
        $context['status'] = $status_code;
        
        $level = $status_code >= 400 ? self::WARNING : self::DEBUG;
        self::log($level, "API Request: {$method} {$endpoint} -> {$status_code}", $context);
    }

    /**
     * Mask license key for logging
     */
    private static function mask_license_key(string $key): string {
        if (strlen($key) < 8) {
            return '***';
        }
        
        return substr($key, 0, 4) . '****' . substr($key, -4);
    }

    /**
     * Reset cached min level (useful for testing)
     */
    public static function reset(): void {
        self::$min_level = null;
    }
}
