<?php
/**
 * Analytics Class
 *
 * Provides comprehensive analytics and reporting for the license server.
 *
 * @package Peanut_License_Server
 */

defined('ABSPATH') || exit;

class Peanut_Analytics {

    /**
     * Get dashboard statistics
     */
    public static function get_dashboard_stats(): array {
        global $wpdb;
        $licenses_table = $wpdb->prefix . 'peanut_licenses';
        $activations_table = $wpdb->prefix . 'peanut_activations';
        $validation_table = $wpdb->prefix . 'peanut_validation_logs';
        $update_table = $wpdb->prefix . 'peanut_update_logs';

        // License stats
        $license_stats = $wpdb->get_row("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended,
                SUM(CASE WHEN status = 'revoked' THEN 1 ELSE 0 END) as revoked,
                SUM(CASE WHEN tier = 'free' THEN 1 ELSE 0 END) as free_tier,
                SUM(CASE WHEN tier = 'pro' THEN 1 ELSE 0 END) as pro_tier,
                SUM(CASE WHEN tier = 'agency' THEN 1 ELSE 0 END) as agency_tier
            FROM {$licenses_table}
        ");

        // Activation stats
        $activation_stats = $wpdb->get_row("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active
            FROM {$activations_table}
        ");

        // This week's new licenses
        $week_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
        $new_this_week = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$licenses_table} WHERE created_at >= %s",
            $week_ago
        ));

        // This week's new activations
        $new_activations_week = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$activations_table} WHERE activated_at >= %s",
            $week_ago
        ));

        // Validation stats (last 30 days)
        $month_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
        $validation_stats = $wpdb->get_row($wpdb->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM {$validation_table}
            WHERE created_at >= %s
        ", $month_ago));

        // Update check stats (last 30 days)
        $update_stats = $wpdb->get_row($wpdb->prepare("
            SELECT
                SUM(CASE WHEN action = 'check' THEN 1 ELSE 0 END) as checks,
                SUM(CASE WHEN action = 'download' THEN 1 ELSE 0 END) as downloads
            FROM {$update_table}
            WHERE created_at >= %s
        ", $month_ago));

        // Revenue estimate (based on tier counts)
        $revenue_estimate = self::calculate_revenue_estimate([
            'pro' => (int) ($license_stats->pro_tier ?? 0),
            'agency' => (int) ($license_stats->agency_tier ?? 0),
        ]);

        return [
            'licenses' => [
                'total' => (int) ($license_stats->total ?? 0),
                'active' => (int) ($license_stats->active ?? 0),
                'expired' => (int) ($license_stats->expired ?? 0),
                'suspended' => (int) ($license_stats->suspended ?? 0),
                'revoked' => (int) ($license_stats->revoked ?? 0),
                'new_this_week' => $new_this_week,
            ],
            'tiers' => [
                'free' => (int) ($license_stats->free_tier ?? 0),
                'pro' => (int) ($license_stats->pro_tier ?? 0),
                'agency' => (int) ($license_stats->agency_tier ?? 0),
            ],
            'activations' => [
                'total' => (int) ($activation_stats->total ?? 0),
                'active' => (int) ($activation_stats->active ?? 0),
                'new_this_week' => $new_activations_week,
            ],
            'validations' => [
                'total' => (int) ($validation_stats->total ?? 0),
                'successful' => (int) ($validation_stats->successful ?? 0),
                'failed' => (int) ($validation_stats->failed ?? 0),
                'success_rate' => $validation_stats->total > 0
                    ? round(($validation_stats->successful / $validation_stats->total) * 100, 1)
                    : 100,
            ],
            'updates' => [
                'checks' => (int) ($update_stats->checks ?? 0),
                'downloads' => (int) ($update_stats->downloads ?? 0),
            ],
            'revenue' => $revenue_estimate,
        ];
    }

    /**
     * Get timeline data for charts
     */
    public static function get_timeline_data(int $days = 30, string $metric = 'licenses'): array {
        global $wpdb;

        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        $labels = [];
        $data = [];

        // Generate all dates in range
        for ($i = $days; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('M j', strtotime($date));
            $data[$date] = 0;
        }

        switch ($metric) {
            case 'licenses':
                $table = $wpdb->prefix . 'peanut_licenses';
                $results = $wpdb->get_results($wpdb->prepare(
                    "SELECT DATE(created_at) as date, COUNT(*) as count
                     FROM {$table}
                     WHERE created_at >= %s
                     GROUP BY DATE(created_at)",
                    $start_date
                ));
                break;

            case 'activations':
                $table = $wpdb->prefix . 'peanut_activations';
                $results = $wpdb->get_results($wpdb->prepare(
                    "SELECT DATE(activated_at) as date, COUNT(*) as count
                     FROM {$table}
                     WHERE activated_at >= %s
                     GROUP BY DATE(activated_at)",
                    $start_date
                ));
                break;

            case 'validations':
                $table = $wpdb->prefix . 'peanut_validation_logs';
                $results = $wpdb->get_results($wpdb->prepare(
                    "SELECT DATE(created_at) as date, COUNT(*) as count
                     FROM {$table}
                     WHERE created_at >= %s
                     GROUP BY DATE(created_at)",
                    $start_date
                ));
                break;

            case 'downloads':
                $table = $wpdb->prefix . 'peanut_update_logs';
                $results = $wpdb->get_results($wpdb->prepare(
                    "SELECT DATE(created_at) as date, COUNT(*) as count
                     FROM {$table}
                     WHERE action = 'download' AND created_at >= %s
                     GROUP BY DATE(created_at)",
                    $start_date
                ));
                break;

            default:
                $results = [];
        }

        foreach ($results as $row) {
            if (isset($data[$row->date])) {
                $data[$row->date] = (int) $row->count;
            }
        }

        return [
            'labels' => $labels,
            'data' => array_values($data),
        ];
    }

    /**
     * Get tier distribution data
     */
    public static function get_tier_distribution(): array {
        global $wpdb;
        $table = $wpdb->prefix . 'peanut_licenses';

        $results = $wpdb->get_results("
            SELECT tier, COUNT(*) as count
            FROM {$table}
            WHERE status = 'active'
            GROUP BY tier
        ");

        $distribution = [
            'labels' => [],
            'data' => [],
            'colors' => [],
        ];

        $tier_colors = [
            'free' => '#94a3b8',
            'pro' => '#3b82f6',
            'agency' => '#8b5cf6',
        ];

        foreach ($results as $row) {
            $distribution['labels'][] = ucfirst($row->tier);
            $distribution['data'][] = (int) $row->count;
            $distribution['colors'][] = $tier_colors[$row->tier] ?? '#64748b';
        }

        return $distribution;
    }

    /**
     * Get product distribution data
     */
    public static function get_product_distribution(): array {
        global $wpdb;
        $table = $wpdb->prefix . 'peanut_licenses';

        $products = Peanut_Update_Server::get_all_products();

        $distribution = [
            'labels' => [],
            'data' => [],
            'colors' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
        ];

        // For now, since product_id might not map directly to slugs,
        // we'll show a generic distribution
        // In a real scenario, you'd join with product data

        $results = $wpdb->get_results("
            SELECT product_id, COUNT(*) as count
            FROM {$table}
            WHERE status = 'active'
            GROUP BY product_id
            ORDER BY count DESC
            LIMIT 5
        ");

        foreach ($results as $index => $row) {
            $distribution['labels'][] = 'Product ' . $row->product_id;
            $distribution['data'][] = (int) $row->count;
        }

        // If we have product names from config, use those
        if (empty($distribution['labels'])) {
            foreach ($products as $slug => $product) {
                $distribution['labels'][] = $product['name'];
                $distribution['data'][] = 0;
            }
        }

        return $distribution;
    }

    /**
     * Get top activated sites
     */
    public static function get_top_sites(int $limit = 10): array {
        global $wpdb;
        $table = $wpdb->prefix . 'peanut_activations';

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT
                site_url,
                COUNT(*) as license_count,
                MAX(activated_at) as last_activity
            FROM {$table}
            WHERE is_active = 1
            GROUP BY site_url
            ORDER BY license_count DESC
            LIMIT %d
        ", $limit));

        return array_map(function ($row) {
            return [
                'site_url' => $row->site_url,
                'domain' => parse_url($row->site_url, PHP_URL_HOST),
                'license_count' => (int) $row->license_count,
                'last_activity' => $row->last_activity,
            ];
        }, $results);
    }

    /**
     * Get version adoption rates
     */
    public static function get_version_adoption(): array {
        global $wpdb;
        $table = $wpdb->prefix . 'peanut_activations';

        $results = $wpdb->get_results("
            SELECT
                plugin_version,
                COUNT(*) as count
            FROM {$table}
            WHERE is_active = 1 AND plugin_version IS NOT NULL
            GROUP BY plugin_version
            ORDER BY plugin_version DESC
            LIMIT 10
        ");

        $adoption = [
            'labels' => [],
            'data' => [],
        ];

        foreach ($results as $row) {
            $adoption['labels'][] = $row->plugin_version ?: 'Unknown';
            $adoption['data'][] = (int) $row->count;
        }

        return $adoption;
    }

    /**
     * Get geographic distribution (based on IP)
     */
    public static function get_geographic_distribution(): array {
        global $wpdb;
        $table = $wpdb->prefix . 'peanut_activations';

        // This is a simplified version - in production you'd use a GeoIP service
        $results = $wpdb->get_results("
            SELECT
                SUBSTRING_INDEX(ip_address, '.', 1) as ip_first_octet,
                COUNT(*) as count
            FROM {$table}
            WHERE is_active = 1 AND ip_address IS NOT NULL
            GROUP BY ip_first_octet
            ORDER BY count DESC
            LIMIT 10
        ");

        return $results;
    }

    /**
     * Get recent activity
     */
    public static function get_recent_activity(int $limit = 20): array {
        global $wpdb;
        $licenses_table = $wpdb->prefix . 'peanut_licenses';
        $activations_table = $wpdb->prefix . 'peanut_activations';
        $validation_table = $wpdb->prefix . 'peanut_validation_logs';

        $activities = [];

        // Recent licenses
        $recent_licenses = $wpdb->get_results($wpdb->prepare("
            SELECT 'license_created' as type, customer_email as detail, created_at as timestamp
            FROM {$licenses_table}
            ORDER BY created_at DESC
            LIMIT %d
        ", 10));

        foreach ($recent_licenses as $item) {
            $activities[] = [
                'type' => $item->type,
                'icon' => 'admin-network',
                'message' => sprintf(__('New license for %s', 'peanut-license-server'), $item->detail),
                'timestamp' => $item->timestamp,
                'color' => 'blue',
            ];
        }

        // Recent activations
        $recent_activations = $wpdb->get_results($wpdb->prepare("
            SELECT 'activation' as type, site_url as detail, activated_at as timestamp
            FROM {$activations_table}
            WHERE is_active = 1
            ORDER BY activated_at DESC
            LIMIT %d
        ", 10));

        foreach ($recent_activations as $item) {
            $activities[] = [
                'type' => $item->type,
                'icon' => 'admin-site',
                'message' => sprintf(__('Activated on %s', 'peanut-license-server'), parse_url($item->detail, PHP_URL_HOST)),
                'timestamp' => $item->timestamp,
                'color' => 'green',
            ];
        }

        // Recent failed validations
        $recent_failures = $wpdb->get_results($wpdb->prepare("
            SELECT 'validation_failed' as type, ip_address as detail, error_code, created_at as timestamp
            FROM {$validation_table}
            WHERE status = 'failed'
            ORDER BY created_at DESC
            LIMIT %d
        ", 5));

        foreach ($recent_failures as $item) {
            $activities[] = [
                'type' => $item->type,
                'icon' => 'warning',
                'message' => sprintf(__('Failed validation from %s (%s)', 'peanut-license-server'), $item->detail, $item->error_code),
                'timestamp' => $item->timestamp,
                'color' => 'red',
            ];
        }

        // Sort by timestamp
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        // Add human-readable time
        foreach ($activities as &$activity) {
            $activity['time_ago'] = human_time_diff(strtotime($activity['timestamp']), current_time('timestamp')) . ' ago';
        }

        return array_slice($activities, 0, $limit);
    }

    /**
     * Get expiring licenses
     */
    public static function get_expiring_licenses(int $days = 30): array {
        global $wpdb;
        $table = $wpdb->prefix . 'peanut_licenses';

        $future_date = date('Y-m-d H:i:s', strtotime("+{$days} days"));

        return $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$table}
            WHERE status = 'active'
                AND expires_at IS NOT NULL
                AND expires_at <= %s
                AND expires_at > NOW()
            ORDER BY expires_at ASC
            LIMIT 20
        ", $future_date));
    }

    /**
     * Calculate revenue estimate
     */
    private static function calculate_revenue_estimate(array $tier_counts): array {
        // These are placeholder prices - adjust to your actual pricing
        $prices = [
            'pro' => 99,
            'agency' => 249,
        ];

        $monthly = 0;
        $annual = 0;

        foreach ($tier_counts as $tier => $count) {
            if (isset($prices[$tier])) {
                $annual += $count * $prices[$tier];
            }
        }

        $monthly = round($annual / 12, 2);

        return [
            'monthly_estimate' => $monthly,
            'annual_estimate' => $annual,
            'currency' => 'USD',
        ];
    }

    /**
     * Export analytics data
     */
    public static function export_analytics(string $format = 'json', int $days = 30): string {
        $data = [
            'generated_at' => current_time('c'),
            'period_days' => $days,
            'stats' => self::get_dashboard_stats(),
            'timeline' => [
                'licenses' => self::get_timeline_data($days, 'licenses'),
                'activations' => self::get_timeline_data($days, 'activations'),
            ],
            'distribution' => [
                'tiers' => self::get_tier_distribution(),
                'versions' => self::get_version_adoption(),
            ],
            'top_sites' => self::get_top_sites(20),
            'expiring_licenses' => self::get_expiring_licenses(30),
        ];

        if ($format === 'csv') {
            return self::array_to_csv($data);
        }

        return wp_json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Convert array to CSV (simplified)
     */
    private static function array_to_csv(array $data): string {
        $output = "Peanut License Server Analytics Report\n";
        $output .= "Generated: " . ($data['generated_at'] ?? date('c')) . "\n\n";

        if (isset($data['stats'])) {
            $output .= "=== License Statistics ===\n";
            $output .= "Total Licenses," . ($data['stats']['licenses']['total'] ?? 0) . "\n";
            $output .= "Active Licenses," . ($data['stats']['licenses']['active'] ?? 0) . "\n";
            $output .= "Expired Licenses," . ($data['stats']['licenses']['expired'] ?? 0) . "\n";
            $output .= "Total Activations," . ($data['stats']['activations']['total'] ?? 0) . "\n";
        }

        return $output;
    }
}
