<?php
/**
 * License Manager Unit Tests
 *
 * Tests license CRUD operations, key generation, expiration handling, and tier management.
 *
 * @package Peanut_License_Server
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers Peanut_License_Manager
 */
class LicenseManagerTest extends TestCase {

    /**
     * Set up test fixtures
     */
    protected function setUp(): void {
        parent::setUp();
        PeanutTestHelper::clearTransients();
        PeanutTestHelper::clearOptions();
    }

    /**
     * Tear down test fixtures
     */
    protected function tearDown(): void {
        parent::tearDown();
        PeanutTestHelper::clearTransients();
        PeanutTestHelper::clearOptions();
    }

    // =========================================================================
    // License Key Generation Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_License_Manager::generate_license_key
     */
    public function generate_license_key_returns_formatted_key(): void {
        $key = Peanut_License_Manager::generate_license_key();

        $this->assertNotEmpty($key);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key);
    }

    /**
     * @test
     * @covers Peanut_License_Manager::generate_license_key
     */
    public function generate_license_key_produces_unique_keys(): void {
        $keys = [];
        for ($i = 0; $i < 100; $i++) {
            $keys[] = Peanut_License_Manager::generate_license_key();
        }

        $uniqueKeys = array_unique($keys);
        $this->assertCount(100, $uniqueKeys, 'All generated keys should be unique');
    }

    /**
     * @test
     * @covers Peanut_License_Manager::generate_license_key
     */
    public function generate_license_key_uses_uppercase(): void {
        $key = Peanut_License_Manager::generate_license_key();

        $this->assertEquals(strtoupper($key), $key, 'License key should be uppercase');
    }

    /**
     * @test
     * @covers Peanut_License_Manager::generate_license_key
     */
    public function generate_license_key_has_correct_length(): void {
        $key = Peanut_License_Manager::generate_license_key();

        // 4 segments of 4 chars + 3 dashes = 19 chars
        $this->assertEquals(19, strlen($key));
    }

    // =========================================================================
    // License Key Hashing Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_License_Manager::hash_license_key
     */
    public function hash_license_key_returns_sha256_hash(): void {
        $key = 'ABCD-EFGH-IJKL-MNOP';
        $hash = Peanut_License_Manager::hash_license_key($key);

        $this->assertEquals(64, strlen($hash), 'SHA256 hash should be 64 characters');
        $this->assertEquals(hash('sha256', $key), $hash);
    }

    /**
     * @test
     * @covers Peanut_License_Manager::hash_license_key
     */
    public function hash_license_key_is_deterministic(): void {
        $key = 'TEST-KEY1-ABCD-1234';

        $hash1 = Peanut_License_Manager::hash_license_key($key);
        $hash2 = Peanut_License_Manager::hash_license_key($key);

        $this->assertEquals($hash1, $hash2, 'Same key should produce same hash');
    }

    /**
     * @test
     * @covers Peanut_License_Manager::hash_license_key
     */
    public function hash_license_key_different_keys_produce_different_hashes(): void {
        $key1 = 'ABCD-EFGH-IJKL-MNOP';
        $key2 = 'QRST-UVWX-YZAB-CDEF';

        $hash1 = Peanut_License_Manager::hash_license_key($key1);
        $hash2 = Peanut_License_Manager::hash_license_key($key2);

        $this->assertNotEquals($hash1, $hash2);
    }

    // =========================================================================
    // Table Name Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_License_Manager::get_table_name
     */
    public function get_table_name_returns_prefixed_licenses_table(): void {
        global $wpdb;
        $table = Peanut_License_Manager::get_table_name();

        $this->assertEquals($wpdb->prefix . 'peanut_licenses', $table);
    }

    /**
     * @test
     * @covers Peanut_License_Manager::get_activations_table
     */
    public function get_activations_table_returns_prefixed_activations_table(): void {
        global $wpdb;
        $table = Peanut_License_Manager::get_activations_table();

        $this->assertEquals($wpdb->prefix . 'peanut_activations', $table);
    }

    // =========================================================================
    // Tier Configuration Tests
    // =========================================================================

    /**
     * @test
     */
    public function tiers_constant_contains_free_tier(): void {
        $this->assertArrayHasKey('free', Peanut_License_Manager::TIERS);
        $this->assertEquals(1, Peanut_License_Manager::TIERS['free']['max_activations']);
        $this->assertContains('basic', Peanut_License_Manager::TIERS['free']['features']);
    }

    /**
     * @test
     */
    public function tiers_constant_contains_pro_tier(): void {
        $this->assertArrayHasKey('pro', Peanut_License_Manager::TIERS);
        $this->assertEquals(3, Peanut_License_Manager::TIERS['pro']['max_activations']);
        $this->assertContains('pro', Peanut_License_Manager::TIERS['pro']['features']);
    }

    /**
     * @test
     */
    public function tiers_constant_contains_agency_tier(): void {
        $this->assertArrayHasKey('agency', Peanut_License_Manager::TIERS);
        $this->assertEquals(25, Peanut_License_Manager::TIERS['agency']['max_activations']);
        $this->assertContains('agency', Peanut_License_Manager::TIERS['agency']['features']);
    }

    /**
     * @test
     * @covers Peanut_License_Manager::get_tier_features
     */
    public function get_tier_features_returns_correct_features_for_free(): void {
        $features = Peanut_License_Manager::get_tier_features('free');

        $this->assertIsArray($features);
        $this->assertContains('basic', $features);
    }

    /**
     * @test
     * @covers Peanut_License_Manager::get_tier_features
     */
    public function get_tier_features_returns_correct_features_for_pro(): void {
        $features = Peanut_License_Manager::get_tier_features('pro');

        $this->assertIsArray($features);
        $this->assertContains('basic', $features);
        $this->assertContains('pro', $features);
    }

    /**
     * @test
     * @covers Peanut_License_Manager::get_tier_features
     */
    public function get_tier_features_returns_free_features_for_invalid_tier(): void {
        $features = Peanut_License_Manager::get_tier_features('invalid_tier');

        $this->assertEquals(Peanut_License_Manager::TIERS['free']['features'], $features);
    }

    /**
     * @test
     * @covers Peanut_License_Manager::get_tier_features
     */
    public function get_tier_features_supports_product_specific_tiers(): void {
        $features = Peanut_License_Manager::get_tier_features('pro', 'peanut-suite');

        $this->assertIsArray($features);
        $this->assertContains('utm', $features);
        $this->assertContains('analytics', $features);
    }

    /**
     * @test
     * @covers Peanut_License_Manager::has_feature
     */
    public function has_feature_returns_true_for_included_feature(): void {
        $this->assertTrue(Peanut_License_Manager::has_feature('basic', 'free'));
        $this->assertTrue(Peanut_License_Manager::has_feature('pro', 'pro'));
        $this->assertTrue(Peanut_License_Manager::has_feature('agency', 'agency'));
    }

    /**
     * @test
     * @covers Peanut_License_Manager::has_feature
     */
    public function has_feature_returns_false_for_excluded_feature(): void {
        $this->assertFalse(Peanut_License_Manager::has_feature('pro', 'free'));
        $this->assertFalse(Peanut_License_Manager::has_feature('agency', 'pro'));
    }

    /**
     * @test
     * @covers Peanut_License_Manager::get_product_tier
     */
    public function get_product_tier_returns_product_specific_config(): void {
        $tier = Peanut_License_Manager::get_product_tier('peanut-suite', 'pro');

        $this->assertArrayHasKey('max_activations', $tier);
        $this->assertArrayHasKey('features', $tier);
        $this->assertEquals(3, $tier['max_activations']);
    }

    /**
     * @test
     * @covers Peanut_License_Manager::get_product_tier
     */
    public function get_product_tier_falls_back_to_default_for_unknown_product(): void {
        $tier = Peanut_License_Manager::get_product_tier('unknown-product', 'pro');

        $this->assertEquals(Peanut_License_Manager::TIERS['pro'], $tier);
    }

    /**
     * @test
     * @covers Peanut_License_Manager::get_product_tiers
     */
    public function get_product_tiers_returns_all_tiers_for_product(): void {
        $tiers = Peanut_License_Manager::get_product_tiers('peanut-suite');

        $this->assertArrayHasKey('free', $tiers);
        $this->assertArrayHasKey('pro', $tiers);
        $this->assertArrayHasKey('agency', $tiers);
    }

    // =========================================================================
    // License Validity Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_License_Manager::is_valid
     */
    public function is_valid_returns_true_for_active_non_expired_license(): void {
        $license = PeanutTestHelper::createMockLicense([
            'status' => 'active',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 year')),
        ]);

        $this->assertTrue(Peanut_License_Manager::is_valid($license));
    }

    /**
     * @test
     * @covers Peanut_License_Manager::is_valid
     */
    public function is_valid_returns_true_for_active_license_without_expiration(): void {
        $license = PeanutTestHelper::createMockLicense([
            'status' => 'active',
            'expires_at' => null,
        ]);

        $this->assertTrue(Peanut_License_Manager::is_valid($license));
    }

    /**
     * @test
     * @covers Peanut_License_Manager::is_valid
     */
    public function is_valid_returns_false_for_expired_license(): void {
        $license = PeanutTestHelper::createMockLicense([
            'status' => 'active',
            'expires_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ]);

        $this->assertFalse(Peanut_License_Manager::is_valid($license));
    }

    /**
     * @test
     * @covers Peanut_License_Manager::is_valid
     */
    public function is_valid_returns_false_for_suspended_license(): void {
        $license = PeanutTestHelper::createMockLicense([
            'status' => 'suspended',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 year')),
        ]);

        $this->assertFalse(Peanut_License_Manager::is_valid($license));
    }

    /**
     * @test
     * @covers Peanut_License_Manager::is_valid
     */
    public function is_valid_returns_false_for_revoked_license(): void {
        $license = PeanutTestHelper::createMockLicense([
            'status' => 'revoked',
        ]);

        $this->assertFalse(Peanut_License_Manager::is_valid($license));
    }

    // =========================================================================
    // Activation Capacity Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_License_Manager::can_activate
     */
    public function can_activate_returns_true_when_under_limit(): void {
        $activation = PeanutTestHelper::createMockActivation(['is_active' => true]);
        $license = PeanutTestHelper::createMockLicense([
            'max_activations' => 3,
            'activations' => [$activation],
        ]);

        $this->assertTrue(Peanut_License_Manager::can_activate($license));
    }

    /**
     * @test
     * @covers Peanut_License_Manager::can_activate
     */
    public function can_activate_returns_false_when_at_limit(): void {
        $activations = [
            PeanutTestHelper::createMockActivation(['is_active' => true]),
            PeanutTestHelper::createMockActivation(['is_active' => true]),
            PeanutTestHelper::createMockActivation(['is_active' => true]),
        ];
        $license = PeanutTestHelper::createMockLicense([
            'max_activations' => 3,
            'activations' => $activations,
        ]);

        $this->assertFalse(Peanut_License_Manager::can_activate($license));
    }

    /**
     * @test
     * @covers Peanut_License_Manager::can_activate
     */
    public function can_activate_ignores_inactive_activations(): void {
        $activations = [
            PeanutTestHelper::createMockActivation(['is_active' => true]),
            PeanutTestHelper::createMockActivation(['is_active' => false]),
            PeanutTestHelper::createMockActivation(['is_active' => false]),
        ];
        $license = PeanutTestHelper::createMockLicense([
            'max_activations' => 2,
            'activations' => $activations,
        ]);

        $this->assertTrue(Peanut_License_Manager::can_activate($license));
    }

    /**
     * @test
     * @covers Peanut_License_Manager::can_activate
     */
    public function can_activate_handles_empty_activations(): void {
        $license = PeanutTestHelper::createMockLicense([
            'max_activations' => 3,
            'activations' => [],
        ]);

        $this->assertTrue(Peanut_License_Manager::can_activate($license));
    }

    /**
     * @test
     * @covers Peanut_License_Manager::can_activate
     */
    public function can_activate_handles_null_activations(): void {
        $license = PeanutTestHelper::createMockLicense([
            'max_activations' => 3,
        ]);
        $license->activations = null;

        $this->assertTrue(Peanut_License_Manager::can_activate($license));
    }

    // =========================================================================
    // Product Tier Tests
    // =========================================================================

    /**
     * @test
     */
    public function product_tiers_constant_contains_peanut_suite(): void {
        $this->assertArrayHasKey('peanut-suite', Peanut_License_Manager::PRODUCT_TIERS);

        $suite = Peanut_License_Manager::PRODUCT_TIERS['peanut-suite'];
        $this->assertArrayHasKey('free', $suite);
        $this->assertArrayHasKey('pro', $suite);
        $this->assertArrayHasKey('agency', $suite);
    }

    /**
     * @test
     */
    public function product_tiers_constant_contains_formflow(): void {
        $this->assertArrayHasKey('formflow', Peanut_License_Manager::PRODUCT_TIERS);

        $formflow = Peanut_License_Manager::PRODUCT_TIERS['formflow'];
        $this->assertContains('basic_forms', $formflow['free']['features']);
        $this->assertContains('conditional_logic', $formflow['pro']['features']);
    }

    /**
     * @test
     */
    public function product_tiers_constant_contains_peanut_booker(): void {
        $this->assertArrayHasKey('peanut-booker', Peanut_License_Manager::PRODUCT_TIERS);

        $booker = Peanut_License_Manager::PRODUCT_TIERS['peanut-booker'];
        $this->assertContains('basic_booking', $booker['free']['features']);
        $this->assertContains('payments', $booker['pro']['features']);
        $this->assertContains('multi_staff', $booker['agency']['features']);
    }
}
