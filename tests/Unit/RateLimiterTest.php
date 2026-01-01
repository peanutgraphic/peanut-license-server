<?php
/**
 * Rate Limiter Unit Tests
 *
 * Tests for the Peanut_Rate_Limiter class.
 *
 * @package Peanut_License_Server\Tests\Unit
 */

namespace Peanut\LicenseServer\Tests\Unit;

use Peanut\LicenseServer\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers Peanut_Rate_Limiter
 */
class RateLimiterTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();

        // Include the rate limiter class
        require_once dirname(__DIR__, 2) . '/includes/class-rate-limiter.php';

        // Clear any existing transients
        global $test_transients;
        $test_transients = [];
    }

    /**
     * Test that first request is not rate limited
     */
    public function test_first_request_is_not_rate_limited(): void {
        $this->assertFalse(
            \Peanut_Rate_Limiter::is_rate_limited('license_validate', '192.168.1.1')
        );
    }

    /**
     * Test that requests within limit are not blocked
     */
    public function test_requests_within_limit_are_not_blocked(): void {
        $identifier = '192.168.1.2';

        // Make 5 requests (limit is 10 for license_validate)
        for ($i = 0; $i < 5; $i++) {
            \Peanut_Rate_Limiter::record_request('license_validate', $identifier);
        }

        $this->assertFalse(
            \Peanut_Rate_Limiter::is_rate_limited('license_validate', $identifier)
        );
    }

    /**
     * Test that requests exceeding limit are blocked
     */
    public function test_requests_exceeding_limit_are_blocked(): void {
        $identifier = '192.168.1.3';

        // Make 10 requests (the limit for license_validate)
        for ($i = 0; $i < 10; $i++) {
            \Peanut_Rate_Limiter::record_request('license_validate', $identifier);
        }

        $this->assertTrue(
            \Peanut_Rate_Limiter::is_rate_limited('license_validate', $identifier)
        );
    }

    /**
     * Test rate info returns correct structure
     */
    public function test_get_rate_info_returns_correct_structure(): void {
        $identifier = '192.168.1.4';

        $info = \Peanut_Rate_Limiter::get_rate_info('license_validate', $identifier);

        $this->assertArrayHasKey('limit', $info);
        $this->assertArrayHasKey('remaining', $info);
        $this->assertArrayHasKey('reset', $info);
    }

    /**
     * Test rate info shows correct remaining count
     */
    public function test_rate_info_shows_correct_remaining(): void {
        $identifier = '192.168.1.5';

        // Initial state
        $info = \Peanut_Rate_Limiter::get_rate_info('license_validate', $identifier);
        $this->assertEquals(10, $info['remaining']);

        // After 3 requests
        for ($i = 0; $i < 3; $i++) {
            \Peanut_Rate_Limiter::record_request('license_validate', $identifier);
        }

        $info = \Peanut_Rate_Limiter::get_rate_info('license_validate', $identifier);
        $this->assertEquals(7, $info['remaining']);
    }

    /**
     * Test clear removes rate limit for identifier
     */
    public function test_clear_removes_rate_limit(): void {
        $identifier = '192.168.1.6';

        // Hit the rate limit
        for ($i = 0; $i < 10; $i++) {
            \Peanut_Rate_Limiter::record_request('license_validate', $identifier);
        }

        $this->assertTrue(\Peanut_Rate_Limiter::is_rate_limited('license_validate', $identifier));

        // Clear the limit
        \Peanut_Rate_Limiter::clear('license_validate', $identifier);

        $this->assertFalse(\Peanut_Rate_Limiter::is_rate_limited('license_validate', $identifier));
    }

    /**
     * Test different endpoint types have different limits
     *
     * @dataProvider endpointLimitProvider
     */
    public function test_endpoint_types_have_different_limits(string $endpoint, int $expectedLimit): void {
        $identifier = '192.168.1.7';

        $info = \Peanut_Rate_Limiter::get_rate_info($endpoint, $identifier);

        $this->assertEquals($expectedLimit, $info['limit']);
    }

    /**
     * Test rate limiting is per-identifier
     */
    public function test_rate_limiting_is_per_identifier(): void {
        $identifier1 = '192.168.1.8';
        $identifier2 = '192.168.1.9';

        // Rate limit identifier1
        for ($i = 0; $i < 10; $i++) {
            \Peanut_Rate_Limiter::record_request('license_validate', $identifier1);
        }

        // identifier1 should be limited, identifier2 should not
        $this->assertTrue(\Peanut_Rate_Limiter::is_rate_limited('license_validate', $identifier1));
        $this->assertFalse(\Peanut_Rate_Limiter::is_rate_limited('license_validate', $identifier2));
    }

    /**
     * Test rate limiting is per-endpoint-type
     */
    public function test_rate_limiting_is_per_endpoint_type(): void {
        $identifier = '192.168.1.10';

        // Rate limit license_validate
        for ($i = 0; $i < 10; $i++) {
            \Peanut_Rate_Limiter::record_request('license_validate', $identifier);
        }

        // license_validate should be limited, license_status should not
        $this->assertTrue(\Peanut_Rate_Limiter::is_rate_limited('license_validate', $identifier));
        $this->assertFalse(\Peanut_Rate_Limiter::is_rate_limited('license_status', $identifier));
    }

    /**
     * Test unknown endpoint uses default limits
     */
    public function test_unknown_endpoint_uses_default_limits(): void {
        $identifier = '192.168.1.11';

        $info = \Peanut_Rate_Limiter::get_rate_info('unknown_endpoint', $identifier);

        // Default is 60 requests per minute
        $this->assertEquals(60, $info['limit']);
    }

    /**
     * Test reset time is in the future
     */
    public function test_reset_time_is_in_future(): void {
        $identifier = '192.168.1.12';

        \Peanut_Rate_Limiter::record_request('license_validate', $identifier);
        $info = \Peanut_Rate_Limiter::get_rate_info('license_validate', $identifier);

        $this->assertGreaterThan(time(), $info['reset']);
    }

    /**
     * Endpoint limit provider
     */
    public static function endpointLimitProvider(): array {
        return [
            'license_validate' => ['license_validate', 10],
            'license_status' => ['license_status', 30],
            'update_check' => ['update_check', 60],
            'download' => ['download', 5],
            'default' => ['default', 60],
        ];
    }
}
