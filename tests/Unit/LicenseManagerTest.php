<?php
/**
 * License Manager Unit Tests
 *
 * Tests for the Peanut_License_Manager class.
 *
 * @package Peanut_License_Server\Tests\Unit
 */

namespace Peanut\LicenseServer\Tests\Unit;

use Peanut\LicenseServer\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers Peanut_License_Manager
 */
class LicenseManagerTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();

        // Include the license manager class
        require_once dirname(__DIR__, 2) . '/includes/class-license-manager.php';
    }

    // =========================================
    // License Key Generation Tests
    // =========================================

    /**
     * Test license key format is valid
     */
    public function test_generated_license_key_has_valid_format(): void {
        $key = \Peanut_License_Manager::generate_license_key();

        $this->assertMatchesRegularExpression(
            '/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/',
            $key,
            "Generated key '{$key}' should match format XXXX-XXXX-XXXX-XXXX"
        );
    }

    /**
     * Test generated keys are unique
     */
    public function test_generated_license_keys_are_unique(): void {
        $keys = [];

        for ($i = 0; $i < 100; $i++) {
            $keys[] = \Peanut_License_Manager::generate_license_key();
        }

        $unique_keys = array_unique($keys);

        $this->assertCount(
            100,
            $unique_keys,
            'All 100 generated keys should be unique'
        );
    }

    /**
     * Test license key has correct length
     */
    public function test_license_key_has_correct_length(): void {
        $key = \Peanut_License_Manager::generate_license_key();

        // Format: XXXX-XXXX-XXXX-XXXX = 19 characters
        $this->assertEquals(19, strlen($key));
    }

    // =========================================
    // License Key Hashing Tests
    // =========================================

    /**
     * Test hash is SHA256
     */
    public function test_hash_is_sha256(): void {
        $key = 'ABCD-1234-EFGH-5678';
        $hash = \Peanut_License_Manager::hash_license_key($key);

        // SHA256 produces 64 character hex string
        $this->assertEquals(64, strlen($hash));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);
    }

    /**
     * Test hash is consistent
     */
    public function test_hash_is_consistent(): void {
        $key = 'ABCD-1234-EFGH-5678';

        $hash1 = \Peanut_License_Manager::hash_license_key($key);
        $hash2 = \Peanut_License_Manager::hash_license_key($key);

        $this->assertEquals($hash1, $hash2);
    }

    /**
     * Test different keys produce different hashes
     */
    public function test_different_keys_produce_different_hashes(): void {
        $key1 = 'ABCD-1234-EFGH-5678';
        $key2 = 'WXYZ-9876-MNOP-4321';

        $hash1 = \Peanut_License_Manager::hash_license_key($key1);
        $hash2 = \Peanut_License_Manager::hash_license_key($key2);

        $this->assertNotEquals($hash1, $hash2);
    }

    // =========================================
    // Tier Configuration Tests
    // =========================================

    /**
     * Test default tiers exist
     */
    public function test_default_tiers_exist(): void {
        $this->assertArrayHasKey('free', \Peanut_License_Manager::TIERS);
        $this->assertArrayHasKey('pro', \Peanut_License_Manager::TIERS);
        $this->assertArrayHasKey('agency', \Peanut_License_Manager::TIERS);
    }

    /**
     * Test tier has required properties
     *
     * @dataProvider tierProvider
     */
    public function test_tier_has_required_properties(string $tier): void {
        $tier_config = \Peanut_License_Manager::TIERS[$tier];

        $this->assertArrayHasKey('name', $tier_config);
        $this->assertArrayHasKey('max_activations', $tier_config);
        $this->assertArrayHasKey('features', $tier_config);
        $this->assertIsString($tier_config['name']);
        $this->assertIsInt($tier_config['max_activations']);
        $this->assertIsArray($tier_config['features']);
    }

    /**
     * Test tier activation limits are correct
     */
    public function test_tier_activation_limits(): void {
        $this->assertEquals(1, \Peanut_License_Manager::TIERS['free']['max_activations']);
        $this->assertEquals(3, \Peanut_License_Manager::TIERS['pro']['max_activations']);
        $this->assertEquals(25, \Peanut_License_Manager::TIERS['agency']['max_activations']);
    }

    /**
     * Test higher tiers have more activations
     */
    public function test_higher_tiers_have_more_activations(): void {
        $free = \Peanut_License_Manager::TIERS['free']['max_activations'];
        $pro = \Peanut_License_Manager::TIERS['pro']['max_activations'];
        $agency = \Peanut_License_Manager::TIERS['agency']['max_activations'];

        $this->assertLessThan($pro, $free);
        $this->assertLessThan($agency, $pro);
    }

    // =========================================
    // Product Tier Tests
    // =========================================

    /**
     * Test product tiers exist for known products
     */
    public function test_product_tiers_exist(): void {
        $this->assertArrayHasKey('peanut-suite', \Peanut_License_Manager::PRODUCT_TIERS);
        $this->assertArrayHasKey('formflow', \Peanut_License_Manager::PRODUCT_TIERS);
        $this->assertArrayHasKey('peanut-booker', \Peanut_License_Manager::PRODUCT_TIERS);
    }

    /**
     * Test product tiers have all tier levels
     *
     * @dataProvider productProvider
     */
    public function test_product_has_all_tier_levels(string $product): void {
        $product_tiers = \Peanut_License_Manager::PRODUCT_TIERS[$product];

        $this->assertArrayHasKey('free', $product_tiers);
        $this->assertArrayHasKey('pro', $product_tiers);
        $this->assertArrayHasKey('agency', $product_tiers);
    }

    /**
     * Test get_tier_features returns array
     */
    public function test_get_tier_features_returns_array(): void {
        $features = \Peanut_License_Manager::get_tier_features('pro');

        $this->assertIsArray($features);
        $this->assertNotEmpty($features);
    }

    /**
     * Test get_tier_features with product returns product-specific features
     */
    public function test_get_tier_features_with_product(): void {
        $default_features = \Peanut_License_Manager::get_tier_features('pro');
        $peanut_suite_features = \Peanut_License_Manager::get_tier_features('pro', 'peanut-suite');

        // Product-specific features should include product-specific items
        $this->assertContains('utm', $peanut_suite_features);
        $this->assertContains('links', $peanut_suite_features);
    }

    /**
     * Test get_tier_features falls back to default for unknown product
     */
    public function test_get_tier_features_falls_back_for_unknown_product(): void {
        $features = \Peanut_License_Manager::get_tier_features('pro', 'unknown-product');
        $default_features = \Peanut_License_Manager::get_tier_features('pro');

        $this->assertEquals($default_features, $features);
    }

    /**
     * Test get_tier_features falls back for unknown tier
     */
    public function test_get_tier_features_falls_back_for_unknown_tier(): void {
        $features = \Peanut_License_Manager::get_tier_features('unknown-tier');
        $free_features = \Peanut_License_Manager::TIERS['free']['features'];

        $this->assertEquals($free_features, $features);
    }

    // =========================================
    // Has Feature Tests
    // =========================================

    /**
     * Test has_feature returns true for included feature
     */
    public function test_has_feature_returns_true_for_included(): void {
        // 'basic' is in the default free tier
        $this->assertTrue(
            \Peanut_License_Manager::has_feature('basic', 'free')
        );
    }

    /**
     * Test has_feature returns false for excluded feature
     */
    public function test_has_feature_returns_false_for_excluded(): void {
        // 'agency' features are not in free tier
        $this->assertFalse(
            \Peanut_License_Manager::has_feature('agency', 'free')
        );
    }

    /**
     * Test has_feature with product-specific features
     */
    public function test_has_feature_with_product(): void {
        // 'utm' is in peanut-suite free tier
        $this->assertTrue(
            \Peanut_License_Manager::has_feature('utm', 'free', 'peanut-suite')
        );

        // 'monitor' is only in agency tier for peanut-suite
        $this->assertFalse(
            \Peanut_License_Manager::has_feature('monitor', 'free', 'peanut-suite')
        );
        $this->assertTrue(
            \Peanut_License_Manager::has_feature('monitor', 'agency', 'peanut-suite')
        );
    }

    // =========================================
    // Get Product Tier Tests
    // =========================================

    /**
     * Test get_product_tier returns correct config
     */
    public function test_get_product_tier_returns_config(): void {
        $tier_config = \Peanut_License_Manager::get_product_tier('peanut-suite', 'pro');

        $this->assertArrayHasKey('name', $tier_config);
        $this->assertArrayHasKey('max_activations', $tier_config);
        $this->assertArrayHasKey('features', $tier_config);
        $this->assertEquals('Pro', $tier_config['name']);
        $this->assertEquals(3, $tier_config['max_activations']);
    }

    /**
     * Test get_product_tier falls back for unknown product
     */
    public function test_get_product_tier_falls_back_for_unknown_product(): void {
        $tier_config = \Peanut_License_Manager::get_product_tier('unknown', 'pro');

        $this->assertEquals(\Peanut_License_Manager::TIERS['pro'], $tier_config);
    }

    /**
     * Test get_product_tier falls back for unknown tier
     */
    public function test_get_product_tier_falls_back_for_unknown_tier(): void {
        $tier_config = \Peanut_License_Manager::get_product_tier('peanut-suite', 'unknown');

        $this->assertEquals(\Peanut_License_Manager::TIERS['free'], $tier_config);
    }

    /**
     * Test get_product_tiers returns all tiers for product
     */
    public function test_get_product_tiers_returns_all(): void {
        $tiers = \Peanut_License_Manager::get_product_tiers('peanut-suite');

        $this->assertCount(3, $tiers);
        $this->assertArrayHasKey('free', $tiers);
        $this->assertArrayHasKey('pro', $tiers);
        $this->assertArrayHasKey('agency', $tiers);
    }

    /**
     * Test get_product_tiers falls back for unknown product
     */
    public function test_get_product_tiers_falls_back(): void {
        $tiers = \Peanut_License_Manager::get_product_tiers('unknown');

        $this->assertEquals(\Peanut_License_Manager::TIERS, $tiers);
    }

    // =========================================
    // License Validation Tests (Pure Functions)
    // =========================================

    /**
     * Test is_valid returns true for active license
     */
    public function test_is_valid_returns_true_for_active_license(): void {
        $license = (object) [
            'status' => 'active',
            'expires_at' => null,
        ];

        $this->assertTrue(\Peanut_License_Manager::is_valid($license));
    }

    /**
     * Test is_valid returns false for suspended license
     */
    public function test_is_valid_returns_false_for_suspended(): void {
        $license = (object) [
            'status' => 'suspended',
            'expires_at' => null,
        ];

        $this->assertFalse(\Peanut_License_Manager::is_valid($license));
    }

    /**
     * Test is_valid returns false for revoked license
     */
    public function test_is_valid_returns_false_for_revoked(): void {
        $license = (object) [
            'status' => 'revoked',
            'expires_at' => null,
        ];

        $this->assertFalse(\Peanut_License_Manager::is_valid($license));
    }

    /**
     * Test is_valid returns false for expired license
     */
    public function test_is_valid_returns_false_for_expired(): void {
        $license = (object) [
            'status' => 'active',
            'expires_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ];

        $this->assertFalse(\Peanut_License_Manager::is_valid($license));
    }

    /**
     * Test is_valid returns true for license expiring in future
     */
    public function test_is_valid_returns_true_for_future_expiry(): void {
        $license = (object) [
            'status' => 'active',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 year')),
        ];

        $this->assertTrue(\Peanut_License_Manager::is_valid($license));
    }

    // =========================================
    // Can Activate Tests
    // =========================================

    /**
     * Test can_activate returns true when under limit
     */
    public function test_can_activate_returns_true_under_limit(): void {
        $license = (object) [
            'max_activations' => 3,
            'activations' => [
                (object) ['is_active' => true],
                (object) ['is_active' => true],
            ],
        ];

        $this->assertTrue(\Peanut_License_Manager::can_activate($license));
    }

    /**
     * Test can_activate returns false at limit
     */
    public function test_can_activate_returns_false_at_limit(): void {
        $license = (object) [
            'max_activations' => 3,
            'activations' => [
                (object) ['is_active' => true],
                (object) ['is_active' => true],
                (object) ['is_active' => true],
            ],
        ];

        $this->assertFalse(\Peanut_License_Manager::can_activate($license));
    }

    /**
     * Test can_activate ignores inactive activations
     */
    public function test_can_activate_ignores_inactive(): void {
        $license = (object) [
            'max_activations' => 2,
            'activations' => [
                (object) ['is_active' => true],
                (object) ['is_active' => false], // Deactivated
                (object) ['is_active' => false], // Deactivated
            ],
        ];

        $this->assertTrue(\Peanut_License_Manager::can_activate($license));
    }

    /**
     * Test can_activate handles empty activations
     */
    public function test_can_activate_handles_empty_activations(): void {
        $license = (object) [
            'max_activations' => 3,
            'activations' => [],
        ];

        $this->assertTrue(\Peanut_License_Manager::can_activate($license));
    }

    /**
     * Test can_activate handles null activations
     */
    public function test_can_activate_handles_null_activations(): void {
        $license = (object) [
            'max_activations' => 3,
        ];

        $this->assertTrue(\Peanut_License_Manager::can_activate($license));
    }

    // =========================================
    // Data Providers
    // =========================================

    public static function tierProvider(): array {
        return [
            'free tier' => ['free'],
            'pro tier' => ['pro'],
            'agency tier' => ['agency'],
        ];
    }

    public static function productProvider(): array {
        return [
            'peanut-suite' => ['peanut-suite'],
            'formflow' => ['formflow'],
            'peanut-booker' => ['peanut-booker'],
        ];
    }
}
