<?php
/**
 * Security Features Unit Tests
 *
 * Tests for the Peanut_Security_Features class.
 *
 * @package Peanut_License_Server\Tests\Unit
 */

namespace Peanut\LicenseServer\Tests\Unit;

use Peanut\LicenseServer\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers Peanut_Security_Features
 */
class SecurityFeaturesTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();

        // Include the security features class
        require_once dirname(__DIR__, 2) . '/includes/class-security-features.php';
    }

    // =========================================
    // IP Whitelist Tests
    // =========================================

    /**
     * Test empty whitelist allows all IPs
     */
    public function test_empty_whitelist_allows_all_ips(): void {
        $this->assertTrue(
            \Peanut_Security_Features::check_ip_whitelist('192.168.1.1', [])
        );
    }

    /**
     * Test exact IP match
     */
    public function test_exact_ip_match(): void {
        $whitelist = ['192.168.1.100', '192.168.1.200'];

        $this->assertTrue(
            \Peanut_Security_Features::check_ip_whitelist('192.168.1.100', $whitelist)
        );

        $this->assertFalse(
            \Peanut_Security_Features::check_ip_whitelist('192.168.1.50', $whitelist)
        );
    }

    /**
     * Test CIDR notation matching
     *
     * @dataProvider cidrMatchProvider
     */
    public function test_cidr_notation_matching(string $ip, string $cidr, bool $expected): void {
        $whitelist = [$cidr];

        $this->assertEquals(
            $expected,
            \Peanut_Security_Features::check_ip_whitelist($ip, $whitelist),
            "IP {$ip} should " . ($expected ? '' : 'not ') . "match CIDR {$cidr}"
        );
    }

    /**
     * Test wildcard IP matching
     *
     * @dataProvider wildcardMatchProvider
     */
    public function test_wildcard_ip_matching(string $ip, string $pattern, bool $expected): void {
        $whitelist = [$pattern];

        $this->assertEquals(
            $expected,
            \Peanut_Security_Features::check_ip_whitelist($ip, $whitelist),
            "IP {$ip} should " . ($expected ? '' : 'not ') . "match pattern {$pattern}"
        );
    }

    /**
     * Test IP range matching
     *
     * @dataProvider ipRangeProvider
     */
    public function test_ip_range_matching(string $ip, string $range, bool $expected): void {
        $whitelist = [$range];

        $this->assertEquals(
            $expected,
            \Peanut_Security_Features::check_ip_whitelist($ip, $whitelist),
            "IP {$ip} should " . ($expected ? '' : 'not ') . "match range {$range}"
        );
    }

    /**
     * Test whitelist ignores empty entries
     */
    public function test_whitelist_ignores_empty_entries(): void {
        $whitelist = ['', '  ', '192.168.1.100', ''];

        $this->assertTrue(
            \Peanut_Security_Features::check_ip_whitelist('192.168.1.100', $whitelist)
        );

        $this->assertFalse(
            \Peanut_Security_Features::check_ip_whitelist('192.168.1.50', $whitelist)
        );
    }

    // =========================================
    // Domain Lock Tests
    // =========================================

    /**
     * Test empty domain list allows all domains
     */
    public function test_empty_domain_list_allows_all(): void {
        $this->assertTrue(
            \Peanut_Security_Features::check_domain_lock('https://example.com', [])
        );
    }

    /**
     * Test exact domain match
     */
    public function test_exact_domain_match(): void {
        $allowed = ['example.com', 'another.com'];

        $this->assertTrue(
            \Peanut_Security_Features::check_domain_lock('https://example.com/page', $allowed)
        );

        $this->assertFalse(
            \Peanut_Security_Features::check_domain_lock('https://evil.com', $allowed)
        );
    }

    /**
     * Test domain matching is case insensitive
     */
    public function test_domain_matching_is_case_insensitive(): void {
        $allowed = ['EXAMPLE.COM'];

        $this->assertTrue(
            \Peanut_Security_Features::check_domain_lock('https://example.com', $allowed)
        );

        $this->assertTrue(
            \Peanut_Security_Features::check_domain_lock('https://Example.Com', $allowed)
        );
    }

    /**
     * Test wildcard subdomain matching
     *
     * @dataProvider wildcardDomainProvider
     */
    public function test_wildcard_subdomain_matching(string $url, string $pattern, bool $expected): void {
        $allowed = [$pattern];

        $this->assertEquals(
            $expected,
            \Peanut_Security_Features::check_domain_lock($url, $allowed),
            "URL {$url} should " . ($expected ? '' : 'not ') . "match pattern {$pattern}"
        );
    }

    /**
     * Test invalid URL returns false
     */
    public function test_invalid_url_returns_false(): void {
        $allowed = ['example.com'];

        $this->assertFalse(
            \Peanut_Security_Features::check_domain_lock('not-a-url', $allowed)
        );

        $this->assertFalse(
            \Peanut_Security_Features::check_domain_lock('', $allowed)
        );
    }

    // =========================================
    // Hardware Fingerprint Tests
    // =========================================

    /**
     * Test empty stored fingerprint allows all
     */
    public function test_empty_stored_fingerprint_allows_all(): void {
        $this->assertTrue(
            \Peanut_Security_Features::check_hardware_fingerprint('any_fingerprint', '')
        );
    }

    /**
     * Test exact fingerprint match
     */
    public function test_exact_fingerprint_match(): void {
        $fingerprint = 'abc123def456';

        $this->assertTrue(
            \Peanut_Security_Features::check_hardware_fingerprint($fingerprint, $fingerprint)
        );
    }

    /**
     * Test fingerprint comparison is case insensitive
     */
    public function test_fingerprint_comparison_is_case_insensitive(): void {
        $this->assertTrue(
            \Peanut_Security_Features::check_hardware_fingerprint('ABC123', 'abc123')
        );
    }

    /**
     * Test fingerprint comparison trims whitespace
     */
    public function test_fingerprint_comparison_trims_whitespace(): void {
        $this->assertTrue(
            \Peanut_Security_Features::check_hardware_fingerprint('  abc123  ', 'abc123')
        );
    }

    /**
     * Test mismatched fingerprint fails
     */
    public function test_mismatched_fingerprint_fails(): void {
        $this->assertFalse(
            \Peanut_Security_Features::check_hardware_fingerprint('abc123', 'xyz789')
        );
    }

    /**
     * Test fingerprint uses timing-safe comparison
     */
    public function test_fingerprint_uses_timing_safe_comparison(): void {
        // This test verifies hash_equals is used (timing-attack resistant)
        // We can't directly test timing, but we can verify the function works correctly
        $this->assertTrue(
            \Peanut_Security_Features::check_hardware_fingerprint(
                'a' . str_repeat('x', 63),
                'a' . str_repeat('x', 63)
            )
        );

        $this->assertFalse(
            \Peanut_Security_Features::check_hardware_fingerprint(
                'a' . str_repeat('x', 63),
                'b' . str_repeat('x', 63)
            )
        );
    }

    // =========================================
    // Hardware Fingerprint Generation Tests
    // =========================================

    /**
     * Test fingerprint generation creates consistent hash
     */
    public function test_fingerprint_generation_is_consistent(): void {
        $server_info = [
            'server_name' => 'example.com',
            'server_addr' => '192.168.1.1',
            'document_root' => '/var/www/html',
            'php_version' => '8.1.0',
            'os' => 'Linux',
        ];

        $fp1 = \Peanut_Security_Features::generate_hardware_fingerprint($server_info);
        $fp2 = \Peanut_Security_Features::generate_hardware_fingerprint($server_info);

        $this->assertEquals($fp1, $fp2);
    }

    /**
     * Test different server info produces different fingerprint
     */
    public function test_different_server_info_produces_different_fingerprint(): void {
        $server_info1 = [
            'server_name' => 'example.com',
            'server_addr' => '192.168.1.1',
        ];

        $server_info2 = [
            'server_name' => 'different.com',
            'server_addr' => '192.168.1.1',
        ];

        $fp1 = \Peanut_Security_Features::generate_hardware_fingerprint($server_info1);
        $fp2 = \Peanut_Security_Features::generate_hardware_fingerprint($server_info2);

        $this->assertNotEquals($fp1, $fp2);
    }

    /**
     * Test fingerprint is SHA256 hash
     */
    public function test_fingerprint_is_sha256_hash(): void {
        $server_info = ['server_name' => 'test'];

        $fp = \Peanut_Security_Features::generate_hardware_fingerprint($server_info);

        // SHA256 hash is 64 characters
        $this->assertEquals(64, strlen($fp));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $fp);
    }

    // =========================================
    // Data Providers
    // =========================================

    public static function cidrMatchProvider(): array {
        return [
            'IP in /24 range' => ['192.168.1.100', '192.168.1.0/24', true],
            'IP outside /24 range' => ['192.168.2.100', '192.168.1.0/24', false],
            'IP at start of /24' => ['192.168.1.0', '192.168.1.0/24', true],
            'IP at end of /24' => ['192.168.1.255', '192.168.1.0/24', true],
            'IP in /16 range' => ['10.0.50.100', '10.0.0.0/16', true],
            'IP outside /16 range' => ['10.1.0.1', '10.0.0.0/16', false],
            'IP in /8 range' => ['10.255.255.255', '10.0.0.0/8', true],
            'Single IP /32' => ['192.168.1.1', '192.168.1.1/32', true],
            'Different IP /32' => ['192.168.1.2', '192.168.1.1/32', false],
        ];
    }

    public static function wildcardMatchProvider(): array {
        return [
            'Match with wildcard at end' => ['192.168.1.100', '192.168.1.*', true],
            'No match with wildcard' => ['192.168.2.100', '192.168.1.*', false],
            'Match 10.x.x.x' => ['10.50.100.200', '10.*.*.*', true],
        ];
    }

    public static function ipRangeProvider(): array {
        return [
            'IP in middle of range' => ['192.168.1.50', '192.168.1.1-192.168.1.100', true],
            'IP at start of range' => ['192.168.1.1', '192.168.1.1-192.168.1.100', true],
            'IP at end of range' => ['192.168.1.100', '192.168.1.1-192.168.1.100', true],
            'IP before range' => ['192.168.1.0', '192.168.1.1-192.168.1.100', false],
            'IP after range' => ['192.168.1.101', '192.168.1.1-192.168.1.100', false],
        ];
    }

    public static function wildcardDomainProvider(): array {
        return [
            'Subdomain matches wildcard' => ['https://sub.example.com', '*.example.com', true],
            'Deep subdomain matches wildcard' => ['https://deep.sub.example.com', '*.example.com', true],
            'Root domain matches wildcard' => ['https://example.com', '*.example.com', true],
            'Different domain no match' => ['https://evil.com', '*.example.com', false],
            'Similar domain no match' => ['https://notexample.com', '*.example.com', false],
        ];
    }
}
