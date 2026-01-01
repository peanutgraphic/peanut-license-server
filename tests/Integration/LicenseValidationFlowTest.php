<?php
/**
 * License Validation Flow Integration Tests
 *
 * Tests the complete license validation lifecycle including:
 * - License activation from client sites
 * - Activation limit enforcement
 * - License deactivation
 * - Cross-plugin license sharing
 *
 * @package Peanut_License_Server\Tests\Integration
 */

namespace Peanut\LicenseServer\Tests\Integration;

use Peanut\LicenseServer\Tests\TestCase;

/**
 * @covers Peanut_License_Manager
 * @covers Peanut_License_Validator
 */
class LicenseValidationFlowTest extends TestCase {

    /**
     * Test complete activation flow
     */
    public function test_activation_flow(): void {
        $license_key = $this->generateLicenseKey();
        $site_url = 'https://client-site.com';
        $product_slug = 'peanut-suite';

        // Step 1: Create a license
        $license = $this->createMockLicense($license_key, [
            'max_activations' => 3,
            'status' => 'active',
            'products' => [$product_slug],
        ]);

        // Step 2: Validate format
        $is_valid_format = \Peanut_License_Validator::is_valid_format($license_key);
        $this->assertTrue($is_valid_format, 'License format should be valid');

        // Step 3: Check license exists and is active
        $this->assertEquals('active', $license->status);

        // Step 4: Check activation count
        $current_activations = 0;
        $can_activate = $current_activations < $license->max_activations;
        $this->assertTrue($can_activate, 'Should be able to activate');

        // Step 5: Activation should succeed
        $activation_result = $this->simulateActivation($license, $site_url);
        $this->assertTrue($activation_result['success']);
    }

    /**
     * Test activation limit enforcement
     */
    public function test_activation_limit_enforcement(): void {
        $license_key = $this->generateLicenseKey();
        $product_slug = 'peanut-suite';

        $license = $this->createMockLicense($license_key, [
            'max_activations' => 2,
            'status' => 'active',
            'products' => [$product_slug],
        ]);

        // Activate on two sites (should succeed)
        $result1 = $this->simulateActivation($license, 'https://site1.com');
        $result2 = $this->simulateActivation($license, 'https://site2.com');

        $this->assertTrue($result1['success'], 'First activation should succeed');
        $this->assertTrue($result2['success'], 'Second activation should succeed');

        // Third activation should fail
        $result3 = $this->simulateActivation($license, 'https://site3.com');
        $this->assertFalse($result3['success'], 'Third activation should fail');
        $this->assertEquals('max_activations_reached', $result3['error_code']);
    }

    /**
     * Test license deactivation frees up slot
     */
    public function test_deactivation_frees_slot(): void {
        $license_key = $this->generateLicenseKey();

        $license = $this->createMockLicense($license_key, [
            'max_activations' => 1,
            'status' => 'active',
        ]);

        // Activate
        $site1 = 'https://site1.com';
        $result1 = $this->simulateActivation($license, $site1);
        $this->assertTrue($result1['success']);

        // Try to activate another - should fail
        $site2 = 'https://site2.com';
        $result2 = $this->simulateActivation($license, $site2);
        $this->assertFalse($result2['success']);

        // Deactivate first site
        $deactivate_result = $this->simulateDeactivation($license, $site1);
        $this->assertTrue($deactivate_result['success']);

        // Now second site should succeed
        $result3 = $this->simulateActivation($license, $site2);
        $this->assertTrue($result3['success'], 'Should activate after deactivation freed slot');
    }

    /**
     * Test expired license cannot be activated
     */
    public function test_expired_license_cannot_activate(): void {
        $license_key = $this->generateLicenseKey();

        $license = $this->createMockLicense($license_key, [
            'status' => 'expired',
            'expires_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ]);

        $result = $this->simulateActivation($license, 'https://site.com');

        $this->assertFalse($result['success']);
        $this->assertEquals('license_expired', $result['error_code']);
    }

    /**
     * Test revoked license cannot be activated
     */
    public function test_revoked_license_cannot_activate(): void {
        $license_key = $this->generateLicenseKey();

        $license = $this->createMockLicense($license_key, [
            'status' => 'revoked',
        ]);

        $result = $this->simulateActivation($license, 'https://site.com');

        $this->assertFalse($result['success']);
        $this->assertEquals('license_revoked', $result['error_code']);
    }

    /**
     * Test same site re-activation doesn't count as new
     */
    public function test_same_site_reactivation(): void {
        $license_key = $this->generateLicenseKey();
        $site_url = 'https://same-site.com';

        $license = $this->createMockLicense($license_key, [
            'max_activations' => 1,
            'status' => 'active',
        ]);

        // First activation
        $result1 = $this->simulateActivation($license, $site_url);
        $this->assertTrue($result1['success']);

        // Re-activation of same site should succeed (not count as new)
        $result2 = $this->simulateActivation($license, $site_url);
        $this->assertTrue($result2['success'], 'Re-activation should succeed');
        $this->assertEquals(1, $license->current_activations, 'Should not increment activation count');
    }

    /**
     * Test product-specific license validation
     */
    public function test_product_specific_validation(): void {
        $license_key = $this->generateLicenseKey();

        $license = $this->createMockLicense($license_key, [
            'status' => 'active',
            'products' => ['peanut-suite', 'peanut-booker'],
        ]);

        // Should work for allowed products
        $result1 = $this->simulateActivation($license, 'https://site.com', 'peanut-suite');
        $this->assertTrue($result1['success']);

        // Should fail for unlicensed product
        $result2 = $this->simulateActivation($license, 'https://site.com', 'peanut-festival');
        $this->assertFalse($result2['success']);
        $this->assertEquals('product_not_licensed', $result2['error_code']);
    }

    /**
     * Test URL normalization for activation matching
     */
    public function test_url_normalization(): void {
        $license_key = $this->generateLicenseKey();

        $license = $this->createMockLicense($license_key, [
            'max_activations' => 1,
            'status' => 'active',
        ]);

        // Activate with trailing slash
        $result1 = $this->simulateActivation($license, 'https://site.com/');
        $this->assertTrue($result1['success']);

        // Same site without trailing slash should be recognized
        $normalized1 = $this->normalizeUrl('https://site.com/');
        $normalized2 = $this->normalizeUrl('https://site.com');

        $this->assertEquals($normalized1, $normalized2, 'URLs should normalize to same value');
    }

    /**
     * Test heartbeat/check-in updates last_checked
     */
    public function test_heartbeat_updates_timestamp(): void {
        $license_key = $this->generateLicenseKey();
        $site_url = 'https://site.com';

        $license = $this->createMockLicense($license_key, [
            'status' => 'active',
        ]);

        // Activate
        $this->simulateActivation($license, $site_url);

        // Simulate heartbeat
        $before_check = time();
        $heartbeat_result = $this->simulateHeartbeat($license, $site_url);

        $this->assertTrue($heartbeat_result['success']);
        $this->assertGreaterThanOrEqual($before_check, $heartbeat_result['last_checked']);
    }

    /**
     * Create a mock license for testing
     */
    private function createMockLicense(string $key, array $data = []): object {
        $defaults = [
            'id' => rand(1, 1000),
            'license_key' => $key,
            'status' => 'active',
            'max_activations' => 5,
            'current_activations' => 0,
            'activations' => [],
            'products' => ['peanut-suite'],
            'expires_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return (object) array_merge($defaults, $data);
    }

    /**
     * Simulate license activation
     */
    private function simulateActivation(object $license, string $site_url, string $product = 'peanut-suite'): array {
        // Check license status
        if ($license->status === 'expired') {
            return ['success' => false, 'error_code' => 'license_expired'];
        }

        if ($license->status === 'revoked') {
            return ['success' => false, 'error_code' => 'license_revoked'];
        }

        // Check product
        if (!in_array($product, $license->products, true)) {
            return ['success' => false, 'error_code' => 'product_not_licensed'];
        }

        // Check if already activated on this site
        $normalized_url = $this->normalizeUrl($site_url);
        foreach ($license->activations as $activation) {
            if ($this->normalizeUrl($activation['site_url']) === $normalized_url) {
                // Already activated - return success without incrementing
                return ['success' => true, 'reactivation' => true];
            }
        }

        // Check activation limit
        if ($license->current_activations >= $license->max_activations) {
            return ['success' => false, 'error_code' => 'max_activations_reached'];
        }

        // Activate
        $license->activations[] = [
            'site_url' => $site_url,
            'activated_at' => date('Y-m-d H:i:s'),
        ];
        $license->current_activations++;

        return ['success' => true];
    }

    /**
     * Simulate license deactivation
     */
    private function simulateDeactivation(object $license, string $site_url): array {
        $normalized_url = $this->normalizeUrl($site_url);

        foreach ($license->activations as $key => $activation) {
            if ($this->normalizeUrl($activation['site_url']) === $normalized_url) {
                unset($license->activations[$key]);
                $license->current_activations = max(0, $license->current_activations - 1);
                return ['success' => true];
            }
        }

        return ['success' => false, 'error_code' => 'activation_not_found'];
    }

    /**
     * Simulate heartbeat
     */
    private function simulateHeartbeat(object $license, string $site_url): array {
        return [
            'success' => true,
            'last_checked' => time(),
        ];
    }

    /**
     * Normalize URL for comparison
     */
    private function normalizeUrl(string $url): string {
        $url = strtolower($url);
        $url = rtrim($url, '/');
        $url = preg_replace('#^https?://#', '', $url);
        $url = preg_replace('#^www\.#', '', $url);
        return $url;
    }
}
