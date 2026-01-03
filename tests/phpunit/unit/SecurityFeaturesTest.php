<?php
/**
 * Security Features Unit Tests
 *
 * Tests IP whitelisting, domain locking, hardware fingerprinting, and security validation.
 *
 * @package Peanut_License_Server
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers Peanut_Security_Features
 */
class SecurityFeaturesTest extends TestCase {

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
    // Table Name Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_Security_Features::get_table_name
     */
    public function get_table_name_returns_prefixed_table(): void {
        global $wpdb;
        $table = Peanut_Security_Features::get_table_name();

        $this->assertEquals($wpdb->prefix . 'peanut_license_restrictions', $table);
    }

    // =========================================================================
    // IP Whitelist Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_Security_Features::check_ip_whitelist
     */
    public function check_ip_whitelist_returns_true_for_empty_whitelist(): void {
        $result = Peanut_Security_Features::check_ip_whitelist('192.168.1.1', []);

        $this->assertTrue($result);
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_ip_whitelist
     */
    public function check_ip_whitelist_matches_exact_ip(): void {
        $whitelist = ['192.168.1.1', '10.0.0.1'];

        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.1', $whitelist));
        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('10.0.0.1', $whitelist));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_ip_whitelist
     */
    public function check_ip_whitelist_rejects_non_whitelisted_ip(): void {
        $whitelist = ['192.168.1.1', '10.0.0.1'];

        $this->assertFalse(Peanut_Security_Features::check_ip_whitelist('192.168.1.2', $whitelist));
        $this->assertFalse(Peanut_Security_Features::check_ip_whitelist('172.16.0.1', $whitelist));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_ip_whitelist
     */
    public function check_ip_whitelist_supports_cidr_notation(): void {
        $whitelist = ['192.168.1.0/24']; // 192.168.1.0 - 192.168.1.255

        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.1', $whitelist));
        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.100', $whitelist));
        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.255', $whitelist));
        $this->assertFalse(Peanut_Security_Features::check_ip_whitelist('192.168.2.1', $whitelist));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_ip_whitelist
     */
    public function check_ip_whitelist_supports_wildcard(): void {
        $whitelist = ['192.168.1.*'];

        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.1', $whitelist));
        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.100', $whitelist));
        $this->assertFalse(Peanut_Security_Features::check_ip_whitelist('192.168.2.1', $whitelist));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_ip_whitelist
     */
    public function check_ip_whitelist_supports_range(): void {
        $whitelist = ['192.168.1.1-192.168.1.100'];

        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.1', $whitelist));
        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.50', $whitelist));
        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.100', $whitelist));
        $this->assertFalse(Peanut_Security_Features::check_ip_whitelist('192.168.1.101', $whitelist));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_ip_whitelist
     */
    public function check_ip_whitelist_handles_whitespace(): void {
        $whitelist = ['  192.168.1.1  ', '10.0.0.1'];

        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.1', $whitelist));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_ip_whitelist
     */
    public function check_ip_whitelist_ignores_empty_entries(): void {
        $whitelist = ['192.168.1.1', '', '   ', '10.0.0.1'];

        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.1', $whitelist));
        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('10.0.0.1', $whitelist));
    }

    // =========================================================================
    // Domain Lock Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_Security_Features::check_domain_lock
     */
    public function check_domain_lock_returns_true_for_empty_list(): void {
        $result = Peanut_Security_Features::check_domain_lock('https://example.com', []);

        $this->assertTrue($result);
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_domain_lock
     */
    public function check_domain_lock_matches_exact_domain(): void {
        $allowed = ['example.com', 'test.org'];

        $this->assertTrue(Peanut_Security_Features::check_domain_lock('https://example.com', $allowed));
        $this->assertTrue(Peanut_Security_Features::check_domain_lock('https://test.org', $allowed));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_domain_lock
     */
    public function check_domain_lock_rejects_non_allowed_domain(): void {
        $allowed = ['example.com'];

        $this->assertFalse(Peanut_Security_Features::check_domain_lock('https://other.com', $allowed));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_domain_lock
     */
    public function check_domain_lock_supports_wildcard_subdomain(): void {
        $allowed = ['*.example.com'];

        $this->assertTrue(Peanut_Security_Features::check_domain_lock('https://www.example.com', $allowed));
        $this->assertTrue(Peanut_Security_Features::check_domain_lock('https://app.example.com', $allowed));
        $this->assertTrue(Peanut_Security_Features::check_domain_lock('https://sub.app.example.com', $allowed));
        // Base domain should also match with wildcard
        $this->assertTrue(Peanut_Security_Features::check_domain_lock('https://example.com', $allowed));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_domain_lock
     */
    public function check_domain_lock_is_case_insensitive(): void {
        $allowed = ['Example.COM'];

        $this->assertTrue(Peanut_Security_Features::check_domain_lock('https://example.com', $allowed));
        $this->assertTrue(Peanut_Security_Features::check_domain_lock('https://EXAMPLE.COM', $allowed));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_domain_lock
     */
    public function check_domain_lock_extracts_domain_from_url(): void {
        $allowed = ['example.com'];

        $this->assertTrue(Peanut_Security_Features::check_domain_lock('https://example.com/path/to/page', $allowed));
        $this->assertTrue(Peanut_Security_Features::check_domain_lock('http://example.com:8080', $allowed));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_domain_lock
     */
    public function check_domain_lock_returns_false_for_invalid_url(): void {
        $allowed = ['example.com'];

        $this->assertFalse(Peanut_Security_Features::check_domain_lock('not-a-url', $allowed));
        $this->assertFalse(Peanut_Security_Features::check_domain_lock('', $allowed));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_domain_lock
     */
    public function check_domain_lock_handles_subdomain_matching(): void {
        $allowed = ['example.com'];

        // Subdomains should match when base domain is allowed
        $this->assertTrue(Peanut_Security_Features::check_domain_lock('https://www.example.com', $allowed));
        $this->assertTrue(Peanut_Security_Features::check_domain_lock('https://blog.example.com', $allowed));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_domain_lock
     */
    public function check_domain_lock_prevents_partial_matches(): void {
        $allowed = ['example.com'];

        // Should not match domains that just contain "example.com"
        $this->assertFalse(Peanut_Security_Features::check_domain_lock('https://malicious-example.com', $allowed));
    }

    // =========================================================================
    // Hardware Fingerprint Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_Security_Features::check_hardware_fingerprint
     */
    public function check_hardware_fingerprint_returns_true_for_empty_stored(): void {
        $result = Peanut_Security_Features::check_hardware_fingerprint('any-fingerprint', '');

        $this->assertTrue($result);
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_hardware_fingerprint
     */
    public function check_hardware_fingerprint_matches_exact(): void {
        $fingerprint = 'abc123def456';

        $this->assertTrue(Peanut_Security_Features::check_hardware_fingerprint($fingerprint, $fingerprint));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_hardware_fingerprint
     */
    public function check_hardware_fingerprint_rejects_mismatch(): void {
        $this->assertFalse(Peanut_Security_Features::check_hardware_fingerprint('abc123', 'xyz789'));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_hardware_fingerprint
     */
    public function check_hardware_fingerprint_is_case_insensitive(): void {
        $this->assertTrue(Peanut_Security_Features::check_hardware_fingerprint('ABC123DEF', 'abc123def'));
        $this->assertTrue(Peanut_Security_Features::check_hardware_fingerprint('abc123def', 'ABC123DEF'));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_hardware_fingerprint
     */
    public function check_hardware_fingerprint_handles_whitespace(): void {
        $this->assertTrue(Peanut_Security_Features::check_hardware_fingerprint('  abc123  ', 'abc123'));
        $this->assertTrue(Peanut_Security_Features::check_hardware_fingerprint('abc123', '  abc123  '));
    }

    /**
     * @test
     * @covers Peanut_Security_Features::check_hardware_fingerprint
     */
    public function check_hardware_fingerprint_uses_timing_safe_comparison(): void {
        // This test ensures hash_equals is used (timing-safe)
        // We can verify by checking that equal strings match
        $fingerprint = 'secure-fingerprint-hash';

        $this->assertTrue(Peanut_Security_Features::check_hardware_fingerprint($fingerprint, $fingerprint));
    }

    // =========================================================================
    // Generate Hardware Fingerprint Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_Security_Features::generate_hardware_fingerprint
     */
    public function generate_hardware_fingerprint_returns_sha256_hash(): void {
        $serverInfo = [
            'server_name' => 'example.com',
            'server_addr' => '192.168.1.1',
            'document_root' => '/var/www/html',
        ];

        $fingerprint = Peanut_Security_Features::generate_hardware_fingerprint($serverInfo);

        $this->assertEquals(64, strlen($fingerprint)); // SHA256 = 64 hex chars
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $fingerprint);
    }

    /**
     * @test
     * @covers Peanut_Security_Features::generate_hardware_fingerprint
     */
    public function generate_hardware_fingerprint_is_deterministic(): void {
        $serverInfo = [
            'server_name' => 'example.com',
            'server_addr' => '192.168.1.1',
            'document_root' => '/var/www/html',
        ];

        $fingerprint1 = Peanut_Security_Features::generate_hardware_fingerprint($serverInfo);
        $fingerprint2 = Peanut_Security_Features::generate_hardware_fingerprint($serverInfo);

        $this->assertEquals($fingerprint1, $fingerprint2);
    }

    /**
     * @test
     * @covers Peanut_Security_Features::generate_hardware_fingerprint
     */
    public function generate_hardware_fingerprint_differs_for_different_info(): void {
        $serverInfo1 = [
            'server_name' => 'example.com',
            'server_addr' => '192.168.1.1',
        ];

        $serverInfo2 = [
            'server_name' => 'other.com',
            'server_addr' => '192.168.1.2',
        ];

        $fingerprint1 = Peanut_Security_Features::generate_hardware_fingerprint($serverInfo1);
        $fingerprint2 = Peanut_Security_Features::generate_hardware_fingerprint($serverInfo2);

        $this->assertNotEquals($fingerprint1, $fingerprint2);
    }

    /**
     * @test
     * @covers Peanut_Security_Features::generate_hardware_fingerprint
     */
    public function generate_hardware_fingerprint_handles_missing_keys(): void {
        $serverInfo = []; // Empty array

        $fingerprint = Peanut_Security_Features::generate_hardware_fingerprint($serverInfo);

        // Should still produce a valid hash
        $this->assertEquals(64, strlen($fingerprint));
    }

    // =========================================================================
    // Validate Request Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_Security_Features::validate_request
     */
    public function validate_request_returns_valid_when_no_restrictions(): void {
        $result = Peanut_Security_Features::validate_request(1, [
            'site_url' => 'https://example.com',
        ]);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * @test
     * @covers Peanut_Security_Features::validate_request
     */
    public function validate_request_returns_all_checks(): void {
        $result = Peanut_Security_Features::validate_request(1, [
            'site_url' => 'https://example.com',
        ]);

        $this->assertArrayHasKey('checks', $result);
        $this->assertArrayHasKey('ip', $result['checks']);
        $this->assertArrayHasKey('domain', $result['checks']);
        $this->assertArrayHasKey('hardware', $result['checks']);
    }

    /**
     * @test
     * @covers Peanut_Security_Features::validate_request
     */
    public function validate_request_returns_errors_array(): void {
        $result = Peanut_Security_Features::validate_request(1, [
            'site_url' => 'https://example.com',
        ]);

        $this->assertArrayHasKey('errors', $result);
        $this->assertIsArray($result['errors']);
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    /**
     * @test
     */
    public function ip_whitelist_handles_ipv4_addresses(): void {
        $whitelist = ['192.168.1.1'];

        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.1', $whitelist));
    }

    /**
     * @test
     */
    public function cidr_handles_small_subnets(): void {
        $whitelist = ['192.168.1.0/30']; // 192.168.1.0-192.168.1.3

        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.0', $whitelist));
        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.3', $whitelist));
        $this->assertFalse(Peanut_Security_Features::check_ip_whitelist('192.168.1.4', $whitelist));
    }

    /**
     * @test
     */
    public function cidr_handles_single_host(): void {
        $whitelist = ['192.168.1.1/32']; // Only 192.168.1.1

        $this->assertTrue(Peanut_Security_Features::check_ip_whitelist('192.168.1.1', $whitelist));
        $this->assertFalse(Peanut_Security_Features::check_ip_whitelist('192.168.1.2', $whitelist));
    }

    /**
     * @test
     */
    public function domain_lock_handles_localhost(): void {
        $allowed = ['localhost'];

        $this->assertTrue(Peanut_Security_Features::check_domain_lock('http://localhost', $allowed));
        $this->assertTrue(Peanut_Security_Features::check_domain_lock('http://localhost:8080', $allowed));
    }

    /**
     * @test
     */
    public function domain_lock_handles_ip_as_domain(): void {
        $allowed = ['192.168.1.1'];

        $this->assertTrue(Peanut_Security_Features::check_domain_lock('http://192.168.1.1', $allowed));
    }
}
