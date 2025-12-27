<?php
/**
 * Product Bundles Class
 *
 * Allows creating license bundles that cover multiple products.
 *
 * @package Peanut_License_Server
 */

defined('ABSPATH') || exit;

class Peanut_Product_Bundles {

    /**
     * Get bundles table name
     */
    public static function get_bundles_table(): string {
        global $wpdb;
        return $wpdb->prefix . 'peanut_bundles';
    }

    /**
     * Get bundle products table name
     */
    public static function get_bundle_products_table(): string {
        global $wpdb;
        return $wpdb->prefix . 'peanut_bundle_products';
    }

    /**
     * Get license bundles table name
     */
    public static function get_license_bundles_table(): string {
        global $wpdb;
        return $wpdb->prefix . 'peanut_license_bundles';
    }

    /**
     * Create a new bundle
     */
    public static function create_bundle(array $data): ?int {
        global $wpdb;

        $result = $wpdb->insert(
            self::get_bundles_table(),
            [
                'name' => sanitize_text_field($data['name']),
                'slug' => sanitize_title($data['slug'] ?? $data['name']),
                'description' => sanitize_textarea_field($data['description'] ?? ''),
                'tier' => $data['tier'] ?? 'pro',
                'max_activations' => intval($data['max_activations'] ?? 3),
                'is_active' => 1,
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%d', '%d', '%s']
        );

        if (!$result) {
            return null;
        }

        $bundle_id = $wpdb->insert_id;

        // Add products to bundle
        if (!empty($data['products'])) {
            self::set_bundle_products($bundle_id, $data['products']);
        }

        return $bundle_id;
    }

    /**
     * Update a bundle
     */
    public static function update_bundle(int $bundle_id, array $data): bool {
        global $wpdb;

        $update_data = [];
        $format = [];

        if (isset($data['name'])) {
            $update_data['name'] = sanitize_text_field($data['name']);
            $format[] = '%s';
        }

        if (isset($data['slug'])) {
            $update_data['slug'] = sanitize_title($data['slug']);
            $format[] = '%s';
        }

        if (isset($data['description'])) {
            $update_data['description'] = sanitize_textarea_field($data['description']);
            $format[] = '%s';
        }

        if (isset($data['tier'])) {
            $update_data['tier'] = $data['tier'];
            $format[] = '%s';
        }

        if (isset($data['max_activations'])) {
            $update_data['max_activations'] = intval($data['max_activations']);
            $format[] = '%d';
        }

        if (isset($data['is_active'])) {
            $update_data['is_active'] = $data['is_active'] ? 1 : 0;
            $format[] = '%d';
        }

        if (empty($update_data)) {
            return true;
        }

        $update_data['updated_at'] = current_time('mysql');
        $format[] = '%s';

        $result = $wpdb->update(
            self::get_bundles_table(),
            $update_data,
            ['id' => $bundle_id],
            $format,
            ['%d']
        );

        // Update products if provided
        if (isset($data['products'])) {
            self::set_bundle_products($bundle_id, $data['products']);
        }

        return $result !== false;
    }

    /**
     * Delete a bundle
     */
    public static function delete_bundle(int $bundle_id): bool {
        global $wpdb;

        // Delete bundle products
        $wpdb->delete(self::get_bundle_products_table(), ['bundle_id' => $bundle_id], ['%d']);

        // Delete license bundle associations
        $wpdb->delete(self::get_license_bundles_table(), ['bundle_id' => $bundle_id], ['%d']);

        // Delete bundle
        return $wpdb->delete(self::get_bundles_table(), ['id' => $bundle_id], ['%d']) !== false;
    }

    /**
     * Get a bundle by ID
     */
    public static function get_bundle(int $bundle_id): ?object {
        global $wpdb;

        $bundle = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::get_bundles_table() . " WHERE id = %d",
            $bundle_id
        ));

        if ($bundle) {
            $bundle->products = self::get_bundle_products($bundle_id);
        }

        return $bundle;
    }

    /**
     * Get bundle by slug
     */
    public static function get_bundle_by_slug(string $slug): ?object {
        global $wpdb;

        $bundle = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::get_bundles_table() . " WHERE slug = %s",
            $slug
        ));

        if ($bundle) {
            $bundle->products = self::get_bundle_products($bundle->id);
        }

        return $bundle;
    }

    /**
     * Get all bundles
     */
    public static function get_bundles(array $args = []): array {
        global $wpdb;
        $table = self::get_bundles_table();

        $where = '1=1';
        $params = [];

        if (isset($args['is_active'])) {
            $where .= ' AND is_active = %d';
            $params[] = $args['is_active'] ? 1 : 0;
        }

        $query = "SELECT * FROM {$table} WHERE {$where} ORDER BY name ASC";

        if (!empty($params)) {
            $query = $wpdb->prepare($query, ...$params);
        }

        $bundles = $wpdb->get_results($query);

        // Add products to each bundle
        foreach ($bundles as $bundle) {
            $bundle->products = self::get_bundle_products($bundle->id);
        }

        return $bundles;
    }

    /**
     * Set products for a bundle
     */
    public static function set_bundle_products(int $bundle_id, array $products): void {
        global $wpdb;
        $table = self::get_bundle_products_table();

        // Clear existing
        $wpdb->delete($table, ['bundle_id' => $bundle_id], ['%d']);

        // Add new
        foreach ($products as $product) {
            $wpdb->insert(
                $table,
                [
                    'bundle_id' => $bundle_id,
                    'product_slug' => sanitize_title($product['slug'] ?? $product),
                    'product_name' => sanitize_text_field($product['name'] ?? $product),
                ],
                ['%d', '%s', '%s']
            );
        }
    }

    /**
     * Get products for a bundle
     */
    public static function get_bundle_products(int $bundle_id): array {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . self::get_bundle_products_table() . " WHERE bundle_id = %d",
            $bundle_id
        ));
    }

    /**
     * Assign bundle to license
     */
    public static function assign_bundle_to_license(int $license_id, int $bundle_id): bool {
        global $wpdb;

        // Check if already assigned
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM " . self::get_license_bundles_table() . " WHERE license_id = %d AND bundle_id = %d",
            $license_id,
            $bundle_id
        ));

        if ($existing) {
            return true;
        }

        return $wpdb->insert(
            self::get_license_bundles_table(),
            [
                'license_id' => $license_id,
                'bundle_id' => $bundle_id,
                'created_at' => current_time('mysql'),
            ],
            ['%d', '%d', '%s']
        ) !== false;
    }

    /**
     * Remove bundle from license
     */
    public static function remove_bundle_from_license(int $license_id, int $bundle_id): bool {
        global $wpdb;

        return $wpdb->delete(
            self::get_license_bundles_table(),
            ['license_id' => $license_id, 'bundle_id' => $bundle_id],
            ['%d', '%d']
        ) !== false;
    }

    /**
     * Get bundles for a license
     */
    public static function get_license_bundles(int $license_id): array {
        global $wpdb;
        $bundles_table = self::get_bundles_table();
        $license_bundles_table = self::get_license_bundles_table();

        $bundles = $wpdb->get_results($wpdb->prepare(
            "SELECT b.* FROM {$bundles_table} b
             JOIN {$license_bundles_table} lb ON b.id = lb.bundle_id
             WHERE lb.license_id = %d",
            $license_id
        ));

        foreach ($bundles as $bundle) {
            $bundle->products = self::get_bundle_products($bundle->id);
        }

        return $bundles;
    }

    /**
     * Check if license has access to a product
     */
    public static function license_has_product_access(int $license_id, string $product_slug): bool {
        global $wpdb;
        $bundles_table = self::get_bundles_table();
        $license_bundles_table = self::get_license_bundles_table();
        $bundle_products_table = self::get_bundle_products_table();

        $has_access = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$bundles_table} b
             JOIN {$license_bundles_table} lb ON b.id = lb.bundle_id
             JOIN {$bundle_products_table} bp ON b.id = bp.bundle_id
             WHERE lb.license_id = %d AND bp.product_slug = %s AND b.is_active = 1",
            $license_id,
            $product_slug
        ));

        return $has_access > 0;
    }

    /**
     * Get all products a license has access to
     */
    public static function get_license_accessible_products(int $license_id): array {
        global $wpdb;
        $bundles_table = self::get_bundles_table();
        $license_bundles_table = self::get_license_bundles_table();
        $bundle_products_table = self::get_bundle_products_table();

        return $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT bp.product_slug, bp.product_name FROM {$bundle_products_table} bp
             JOIN {$bundles_table} b ON bp.bundle_id = b.id
             JOIN {$license_bundles_table} lb ON b.id = lb.bundle_id
             WHERE lb.license_id = %d AND b.is_active = 1",
            $license_id
        ));
    }

    /**
     * Create a bundle license
     */
    public static function create_bundle_license(int $bundle_id, array $customer_data): ?object {
        $bundle = self::get_bundle($bundle_id);

        if (!$bundle) {
            return null;
        }

        // Create the license
        $license = Peanut_License_Manager::create([
            'customer_email' => $customer_data['email'],
            'customer_name' => $customer_data['name'] ?? '',
            'tier' => $bundle->tier,
            'max_activations' => $bundle->max_activations,
            'product_id' => 0,
            'expires_at' => $customer_data['expires_at'] ?? null,
        ]);

        if (!$license) {
            return null;
        }

        // Assign bundle to license
        self::assign_bundle_to_license($license->id, $bundle_id);

        // Log
        if (class_exists('Peanut_Audit_Trail')) {
            Peanut_Audit_Trail::log('bundle_license_created', [
                'license_id' => $license->id,
                'bundle_id' => $bundle_id,
                'bundle_name' => $bundle->name,
                'products' => wp_list_pluck($bundle->products, 'product_slug'),
            ]);
        }

        return $license;
    }

    /**
     * Create database tables
     */
    public static function create_tables(): void {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Bundles table
        $bundles_sql = "CREATE TABLE IF NOT EXISTS " . self::get_bundles_table() . " (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT DEFAULT NULL,
            tier ENUM('free', 'pro', 'agency') DEFAULT 'pro',
            max_activations INT UNSIGNED DEFAULT 3,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_slug (slug),
            KEY idx_is_active (is_active)
        ) {$charset_collate};";

        // Bundle products table
        $bundle_products_sql = "CREATE TABLE IF NOT EXISTS " . self::get_bundle_products_table() . " (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            bundle_id BIGINT UNSIGNED NOT NULL,
            product_slug VARCHAR(255) NOT NULL,
            product_name VARCHAR(255) DEFAULT NULL,
            KEY idx_bundle_id (bundle_id),
            KEY idx_product_slug (product_slug),
            UNIQUE KEY unique_bundle_product (bundle_id, product_slug)
        ) {$charset_collate};";

        // License bundles table
        $license_bundles_sql = "CREATE TABLE IF NOT EXISTS " . self::get_license_bundles_table() . " (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            license_id BIGINT UNSIGNED NOT NULL,
            bundle_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY idx_license_id (license_id),
            KEY idx_bundle_id (bundle_id),
            UNIQUE KEY unique_license_bundle (license_id, bundle_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($bundles_sql);
        dbDelta($bundle_products_sql);
        dbDelta($license_bundles_sql);
    }
}
