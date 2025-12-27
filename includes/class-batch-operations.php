<?php
/**
 * Batch Operations Class
 *
 * Handles bulk import/export of licenses.
 *
 * @package Peanut_License_Server
 */

defined('ABSPATH') || exit;

class Peanut_Batch_Operations {

    /**
     * Export licenses to CSV
     */
    public static function export_csv(array $args = []): string {
        $licenses = self::get_licenses_for_export($args);

        $csv_lines = [];

        // Header row
        $csv_lines[] = implode(',', [
            'license_key',
            'customer_email',
            'customer_name',
            'product_id',
            'tier',
            'status',
            'max_activations',
            'activations_used',
            'created_at',
            'expires_at',
            'order_id',
            'subscription_id',
        ]);

        // Data rows
        foreach ($licenses as $license) {
            $csv_lines[] = implode(',', [
                self::escape_csv($license->license_key),
                self::escape_csv($license->customer_email),
                self::escape_csv($license->customer_name),
                $license->product_id,
                $license->tier,
                $license->status,
                $license->max_activations,
                $license->activations_count ?? 0,
                $license->created_at,
                $license->expires_at ?? '',
                $license->order_id ?? '',
                $license->subscription_id ?? '',
            ]);
        }

        return implode("\n", $csv_lines);
    }

    /**
     * Export licenses to JSON
     */
    public static function export_json(array $args = []): string {
        $licenses = self::get_licenses_for_export($args);

        $export_data = [
            'exported_at' => current_time('c'),
            'total_licenses' => count($licenses),
            'licenses' => [],
        ];

        foreach ($licenses as $license) {
            $export_data['licenses'][] = [
                'license_key' => $license->license_key,
                'customer_email' => $license->customer_email,
                'customer_name' => $license->customer_name,
                'product_id' => (int) $license->product_id,
                'tier' => $license->tier,
                'status' => $license->status,
                'max_activations' => (int) $license->max_activations,
                'activations_used' => (int) ($license->activations_count ?? 0),
                'created_at' => $license->created_at,
                'expires_at' => $license->expires_at,
                'order_id' => $license->order_id ? (int) $license->order_id : null,
                'subscription_id' => $license->subscription_id ? (int) $license->subscription_id : null,
                'activations' => array_map(function ($activation) {
                    return [
                        'site_url' => $activation->site_url,
                        'site_name' => $activation->site_name,
                        'activated_at' => $activation->activated_at,
                        'is_active' => (bool) $activation->is_active,
                    ];
                }, $license->activations ?? []),
            ];
        }

        return wp_json_encode($export_data, JSON_PRETTY_PRINT);
    }

    /**
     * Import licenses from CSV
     */
    public static function import_csv(string $csv_content): array {
        $lines = explode("\n", trim($csv_content));
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        if (count($lines) < 2) {
            $results['errors'][] = 'CSV file is empty or has no data rows.';
            return $results;
        }

        // Parse header
        $header = str_getcsv(array_shift($lines));
        $header = array_map('trim', $header);
        $header = array_map('strtolower', $header);

        // Map common variations
        $field_map = [
            'email' => 'customer_email',
            'name' => 'customer_name',
            'product' => 'product_id',
            'activations' => 'max_activations',
            'expiry' => 'expires_at',
            'expiration' => 'expires_at',
        ];

        $header = array_map(function ($field) use ($field_map) {
            return $field_map[$field] ?? $field;
        }, $header);

        // Process each row
        foreach ($lines as $line_num => $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $row = str_getcsv($line);

            if (count($row) !== count($header)) {
                $results['errors'][] = "Row " . ($line_num + 2) . ": Column count mismatch.";
                $results['failed']++;
                continue;
            }

            $data = array_combine($header, $row);

            // Validate required fields
            if (empty($data['customer_email'])) {
                $results['errors'][] = "Row " . ($line_num + 2) . ": Missing customer email.";
                $results['failed']++;
                continue;
            }

            // Check for existing license
            if (!empty($data['license_key'])) {
                $existing = Peanut_License_Manager::get_by_key($data['license_key']);
                if ($existing) {
                    $results['skipped']++;
                    continue;
                }
            }

            // Create the license
            try {
                $license = Peanut_License_Manager::create([
                    'customer_email' => sanitize_email($data['customer_email']),
                    'customer_name' => sanitize_text_field($data['customer_name'] ?? ''),
                    'product_id' => absint($data['product_id'] ?? 0),
                    'tier' => sanitize_key($data['tier'] ?? 'free'),
                    'max_activations' => absint($data['max_activations'] ?? 1),
                    'expires_at' => !empty($data['expires_at']) ? sanitize_text_field($data['expires_at']) : null,
                ]);

                if ($license) {
                    $results['success']++;
                } else {
                    $results['errors'][] = "Row " . ($line_num + 2) . ": Failed to create license.";
                    $results['failed']++;
                }
            } catch (Exception $e) {
                $results['errors'][] = "Row " . ($line_num + 2) . ": " . $e->getMessage();
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Import licenses from JSON
     */
    public static function import_json(string $json_content): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $data = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $results['errors'][] = 'Invalid JSON: ' . json_last_error_msg();
            return $results;
        }

        $licenses = $data['licenses'] ?? $data;

        if (!is_array($licenses)) {
            $results['errors'][] = 'JSON must contain an array of licenses.';
            return $results;
        }

        foreach ($licenses as $index => $license_data) {
            // Validate required fields
            if (empty($license_data['customer_email'])) {
                $results['errors'][] = "Item " . ($index + 1) . ": Missing customer email.";
                $results['failed']++;
                continue;
            }

            // Check for existing license
            if (!empty($license_data['license_key'])) {
                $existing = Peanut_License_Manager::get_by_key($license_data['license_key']);
                if ($existing) {
                    $results['skipped']++;
                    continue;
                }
            }

            try {
                $license = Peanut_License_Manager::create([
                    'customer_email' => sanitize_email($license_data['customer_email']),
                    'customer_name' => sanitize_text_field($license_data['customer_name'] ?? ''),
                    'product_id' => absint($license_data['product_id'] ?? 0),
                    'tier' => sanitize_key($license_data['tier'] ?? 'free'),
                    'max_activations' => absint($license_data['max_activations'] ?? 1),
                    'expires_at' => !empty($license_data['expires_at']) ? sanitize_text_field($license_data['expires_at']) : null,
                    'user_id' => !empty($license_data['user_id']) ? absint($license_data['user_id']) : null,
                ]);

                if ($license) {
                    $results['success']++;
                } else {
                    $results['errors'][] = "Item " . ($index + 1) . ": Failed to create license.";
                    $results['failed']++;
                }
            } catch (Exception $e) {
                $results['errors'][] = "Item " . ($index + 1) . ": " . $e->getMessage();
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Bulk update licenses
     */
    public static function bulk_update(array $license_ids, array $updates): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($license_ids as $id) {
            $result = Peanut_License_Manager::update((int) $id, $updates);

            if ($result) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "License ID {$id}: Update failed.";
            }
        }

        return $results;
    }

    /**
     * Bulk delete licenses
     */
    public static function bulk_delete(array $license_ids): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($license_ids as $id) {
            $result = Peanut_License_Manager::delete((int) $id);

            if ($result) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "License ID {$id}: Delete failed.";
            }
        }

        return $results;
    }

    /**
     * Generate bulk licenses
     */
    public static function generate_bulk(int $count, array $template): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'licenses' => [],
            'errors' => [],
        ];

        for ($i = 0; $i < $count; $i++) {
            try {
                $license = Peanut_License_Manager::create($template);

                if ($license) {
                    $results['success']++;
                    $results['licenses'][] = $license->license_key;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "License " . ($i + 1) . ": Creation failed.";
                }
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = "License " . ($i + 1) . ": " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get licenses for export
     */
    private static function get_licenses_for_export(array $args): array {
        $defaults = [
            'status' => '',
            'tier' => '',
            'product_id' => '',
            'date_from' => '',
            'date_to' => '',
            'per_page' => 10000,
            'page' => 1,
        ];

        $args = wp_parse_args($args, $defaults);

        $result = Peanut_License_Manager::get_all($args);

        return $result['data'];
    }

    /**
     * Escape value for CSV
     */
    private static function escape_csv($value): string {
        $value = (string) $value;

        // If contains comma, quote, or newline, wrap in quotes
        if (preg_match('/[,"\n\r]/', $value)) {
            $value = '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }

    /**
     * Download export file
     */
    public static function download_export(string $content, string $filename, string $type = 'csv'): void {
        $mime_types = [
            'csv' => 'text/csv',
            'json' => 'application/json',
        ];

        $mime = $mime_types[$type] ?? 'text/plain';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $content;
        exit;
    }
}
