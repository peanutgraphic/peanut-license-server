<?php
/**
 * Rate Limiter Unit Tests
 *
 * Tests rate limiting functionality, window management, and header generation.
 *
 * @package Peanut_License_Server
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers Peanut_Rate_Limiter
 */
class RateLimiterTest extends TestCase {

    /**
     * Set up test fixtures
     */
    protected function setUp(): void {
        parent::setUp();
        PeanutTestHelper::clearTransients();
    }

    /**
     * Tear down test fixtures
     */
    protected function tearDown(): void {
        parent::tearDown();
        PeanutTestHelper::clearTransients();
    }

    // =========================================================================
    // Rate Limit Check Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_Rate_Limiter::is_rate_limited
     */
    public function is_rate_limited_returns_false_when_no_requests(): void {
        $this->assertFalse(Peanut_Rate_Limiter::is_rate_limited('license_validate'));
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::is_rate_limited
     */
    public function is_rate_limited_returns_false_when_under_limit(): void {
        // Make 5 requests (limit is 10 for license_validate)
        for ($i = 0; $i < 5; $i++) {
            Peanut_Rate_Limiter::record_request('license_validate');
        }

        $this->assertFalse(Peanut_Rate_Limiter::is_rate_limited('license_validate'));
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::is_rate_limited
     */
    public function is_rate_limited_returns_true_when_at_limit(): void {
        // Make 10 requests (limit for license_validate)
        for ($i = 0; $i < 10; $i++) {
            Peanut_Rate_Limiter::record_request('license_validate');
        }

        $this->assertTrue(Peanut_Rate_Limiter::is_rate_limited('license_validate'));
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::is_rate_limited
     */
    public function is_rate_limited_uses_default_for_unknown_endpoint(): void {
        // Default limit is 60 requests
        for ($i = 0; $i < 60; $i++) {
            Peanut_Rate_Limiter::record_request('unknown_endpoint');
        }

        $this->assertTrue(Peanut_Rate_Limiter::is_rate_limited('unknown_endpoint'));
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::is_rate_limited
     */
    public function is_rate_limited_tracks_per_identifier(): void {
        // Fill limit for identifier 'user1'
        for ($i = 0; $i < 10; $i++) {
            Peanut_Rate_Limiter::record_request('license_validate', 'user1');
        }

        // user1 should be limited
        $this->assertTrue(Peanut_Rate_Limiter::is_rate_limited('license_validate', 'user1'));

        // user2 should not be limited
        $this->assertFalse(Peanut_Rate_Limiter::is_rate_limited('license_validate', 'user2'));
    }

    // =========================================================================
    // Request Recording Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_Rate_Limiter::record_request
     */
    public function record_request_increments_counter(): void {
        Peanut_Rate_Limiter::record_request('license_validate');
        $info = Peanut_Rate_Limiter::get_rate_info('license_validate');

        $this->assertEquals(9, $info['remaining']); // 10 - 1 = 9
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::record_request
     */
    public function record_request_tracks_multiple_requests(): void {
        Peanut_Rate_Limiter::record_request('license_validate');
        Peanut_Rate_Limiter::record_request('license_validate');
        Peanut_Rate_Limiter::record_request('license_validate');

        $info = Peanut_Rate_Limiter::get_rate_info('license_validate');

        $this->assertEquals(7, $info['remaining']); // 10 - 3 = 7
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::record_request
     */
    public function record_request_tracks_different_endpoints_separately(): void {
        Peanut_Rate_Limiter::record_request('license_validate');
        Peanut_Rate_Limiter::record_request('license_status');

        $validateInfo = Peanut_Rate_Limiter::get_rate_info('license_validate');
        $statusInfo = Peanut_Rate_Limiter::get_rate_info('license_status');

        $this->assertEquals(9, $validateInfo['remaining']); // 10 - 1
        $this->assertEquals(29, $statusInfo['remaining']); // 30 - 1
    }

    // =========================================================================
    // Rate Info Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_Rate_Limiter::get_rate_info
     */
    public function get_rate_info_returns_correct_limit_for_license_validate(): void {
        $info = Peanut_Rate_Limiter::get_rate_info('license_validate');

        $this->assertEquals(10, $info['limit']);
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::get_rate_info
     */
    public function get_rate_info_returns_correct_limit_for_license_status(): void {
        $info = Peanut_Rate_Limiter::get_rate_info('license_status');

        $this->assertEquals(30, $info['limit']);
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::get_rate_info
     */
    public function get_rate_info_returns_correct_limit_for_update_check(): void {
        $info = Peanut_Rate_Limiter::get_rate_info('update_check');

        $this->assertEquals(60, $info['limit']);
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::get_rate_info
     */
    public function get_rate_info_returns_correct_limit_for_download(): void {
        $info = Peanut_Rate_Limiter::get_rate_info('download');

        $this->assertEquals(5, $info['limit']);
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::get_rate_info
     */
    public function get_rate_info_returns_default_limit_for_unknown(): void {
        $info = Peanut_Rate_Limiter::get_rate_info('unknown_endpoint');

        $this->assertEquals(60, $info['limit']); // default
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::get_rate_info
     */
    public function get_rate_info_returns_full_remaining_when_no_requests(): void {
        $info = Peanut_Rate_Limiter::get_rate_info('license_validate');

        $this->assertEquals(10, $info['remaining']);
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::get_rate_info
     */
    public function get_rate_info_returns_zero_remaining_when_exhausted(): void {
        for ($i = 0; $i < 10; $i++) {
            Peanut_Rate_Limiter::record_request('license_validate');
        }

        $info = Peanut_Rate_Limiter::get_rate_info('license_validate');

        $this->assertEquals(0, $info['remaining']);
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::get_rate_info
     */
    public function get_rate_info_returns_reset_timestamp(): void {
        $info = Peanut_Rate_Limiter::get_rate_info('license_validate');

        $this->assertArrayHasKey('reset', $info);
        $this->assertIsInt($info['reset']);
        $this->assertGreaterThan(time(), $info['reset']);
    }

    // =========================================================================
    // Clear Rate Limit Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_Rate_Limiter::clear
     */
    public function clear_removes_rate_limit(): void {
        // Create some requests
        for ($i = 0; $i < 10; $i++) {
            Peanut_Rate_Limiter::record_request('license_validate');
        }

        // Verify limit is reached
        $this->assertTrue(Peanut_Rate_Limiter::is_rate_limited('license_validate'));

        // Clear the limit
        Peanut_Rate_Limiter::clear('license_validate');

        // Verify limit is reset
        $this->assertFalse(Peanut_Rate_Limiter::is_rate_limited('license_validate'));
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::clear
     */
    public function clear_only_affects_specified_identifier(): void {
        // Create requests for two identifiers
        for ($i = 0; $i < 10; $i++) {
            Peanut_Rate_Limiter::record_request('license_validate', 'user1');
            Peanut_Rate_Limiter::record_request('license_validate', 'user2');
        }

        // Clear only user1
        Peanut_Rate_Limiter::clear('license_validate', 'user1');

        // user1 should be cleared
        $this->assertFalse(Peanut_Rate_Limiter::is_rate_limited('license_validate', 'user1'));

        // user2 should still be limited
        $this->assertTrue(Peanut_Rate_Limiter::is_rate_limited('license_validate', 'user2'));
    }

    // =========================================================================
    // Check Method Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_Rate_Limiter::check
     */
    public function check_returns_null_when_not_limited(): void {
        $result = Peanut_Rate_Limiter::check('license_validate');

        $this->assertNull($result);
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::check
     */
    public function check_returns_response_when_limited(): void {
        for ($i = 0; $i < 10; $i++) {
            Peanut_Rate_Limiter::record_request('license_validate');
        }

        $result = Peanut_Rate_Limiter::check('license_validate');

        $this->assertInstanceOf(WP_REST_Response::class, $result);
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::check
     */
    public function check_returns_429_status_when_limited(): void {
        for ($i = 0; $i < 10; $i++) {
            Peanut_Rate_Limiter::record_request('license_validate');
        }

        $result = Peanut_Rate_Limiter::check('license_validate');

        $this->assertEquals(429, $result->get_status());
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::check
     */
    public function check_returns_error_details_when_limited(): void {
        for ($i = 0; $i < 10; $i++) {
            Peanut_Rate_Limiter::record_request('license_validate');
        }

        $result = Peanut_Rate_Limiter::check('license_validate');
        $data = $result->get_data();

        $this->assertFalse($data['success']);
        $this->assertEquals('rate_limit_exceeded', $data['error']);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('retry_after', $data);
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::check
     */
    public function check_includes_retry_after_header_when_limited(): void {
        for ($i = 0; $i < 10; $i++) {
            Peanut_Rate_Limiter::record_request('license_validate');
        }

        $result = Peanut_Rate_Limiter::check('license_validate');
        $headers = $result->get_headers();

        $this->assertArrayHasKey('Retry-After', $headers);
        $this->assertGreaterThan(0, $headers['Retry-After']);
    }

    // =========================================================================
    // Add Headers Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_Rate_Limiter::add_headers
     */
    public function add_headers_adds_rate_limit_headers(): void {
        $response = new WP_REST_Response(['test' => 'data'], 200);

        $result = Peanut_Rate_Limiter::add_headers($response, 'license_validate');
        $headers = $result->get_headers();

        $this->assertArrayHasKey('X-RateLimit-Limit', $headers);
        $this->assertArrayHasKey('X-RateLimit-Remaining', $headers);
        $this->assertArrayHasKey('X-RateLimit-Reset', $headers);
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::add_headers
     */
    public function add_headers_preserves_original_response(): void {
        $response = new WP_REST_Response(['test' => 'data'], 200);

        $result = Peanut_Rate_Limiter::add_headers($response, 'license_validate');

        $this->assertEquals(['test' => 'data'], $result->get_data());
        $this->assertEquals(200, $result->get_status());
    }

    /**
     * @test
     * @covers Peanut_Rate_Limiter::add_headers
     */
    public function add_headers_shows_correct_remaining(): void {
        Peanut_Rate_Limiter::record_request('license_validate');
        Peanut_Rate_Limiter::record_request('license_validate');

        $response = new WP_REST_Response(['test' => 'data'], 200);
        $result = Peanut_Rate_Limiter::add_headers($response, 'license_validate');
        $headers = $result->get_headers();

        $this->assertEquals(8, $headers['X-RateLimit-Remaining']); // 10 - 2
    }

    // =========================================================================
    // Different Rate Limit Configurations
    // =========================================================================

    /**
     * @test
     */
    public function download_endpoint_has_stricter_limit(): void {
        // Download limit is 5 per 5 minutes
        for ($i = 0; $i < 5; $i++) {
            Peanut_Rate_Limiter::record_request('download');
        }

        $this->assertTrue(Peanut_Rate_Limiter::is_rate_limited('download'));
    }

    /**
     * @test
     */
    public function update_check_has_higher_limit(): void {
        // Update check limit is 60 per minute
        for ($i = 0; $i < 30; $i++) {
            Peanut_Rate_Limiter::record_request('update_check');
        }

        // Should not be limited yet
        $this->assertFalse(Peanut_Rate_Limiter::is_rate_limited('update_check'));

        // Add 30 more
        for ($i = 0; $i < 30; $i++) {
            Peanut_Rate_Limiter::record_request('update_check');
        }

        // Now should be limited
        $this->assertTrue(Peanut_Rate_Limiter::is_rate_limited('update_check'));
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    /**
     * @test
     */
    public function rate_limiter_handles_empty_endpoint_type(): void {
        $this->assertFalse(Peanut_Rate_Limiter::is_rate_limited(''));
    }

    /**
     * @test
     */
    public function rate_limiter_handles_null_identifier(): void {
        Peanut_Rate_Limiter::record_request('license_validate', null);

        $this->assertFalse(Peanut_Rate_Limiter::is_rate_limited('license_validate', null));
    }
}
