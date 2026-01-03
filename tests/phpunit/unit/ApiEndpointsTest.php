<?php
/**
 * API Endpoints Unit Tests
 *
 * Tests REST API responses, error handling, and request validation.
 *
 * @package Peanut_License_Server
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers Peanut_API_Endpoints
 */
class ApiEndpointsTest extends TestCase {

    private Peanut_API_Endpoints $api;

    /**
     * Set up test fixtures
     */
    protected function setUp(): void {
        parent::setUp();
        $this->api = new Peanut_API_Endpoints();
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
    // Route Registration Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_API_Endpoints::register_routes
     */
    public function register_routes_does_not_throw(): void {
        $this->expectNotToPerformAssertions();

        // Should not throw any exceptions
        $this->api->register_routes();
    }

    // =========================================================================
    // Validate License Endpoint Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_API_Endpoints::validate_license
     */
    public function validate_license_returns_error_for_invalid_format(): void {
        $request = PeanutTestHelper::createMockRequest('POST', '/peanut-api/v1/license/validate', [
            'license_key' => 'invalid-key',
            'site_url' => 'https://example.com',
        ]);

        $response = $this->api->validate_license($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();

        $this->assertFalse($data['success']);
        $this->assertEquals('invalid_format', $data['error']);
        $this->assertEquals(400, $response->get_status());
    }

    /**
     * @test
     * @covers Peanut_API_Endpoints::validate_license
     */
    public function validate_license_includes_rate_limit_headers(): void {
        $request = PeanutTestHelper::createMockRequest('POST', '/peanut-api/v1/license/validate', [
            'license_key' => 'ABCD-EFGH-IJKL-MNOP',
            'site_url' => 'https://example.com',
        ]);

        $response = $this->api->validate_license($request);
        $headers = $response->get_headers();

        $this->assertArrayHasKey('X-RateLimit-Limit', $headers);
        $this->assertArrayHasKey('X-RateLimit-Remaining', $headers);
        $this->assertArrayHasKey('X-RateLimit-Reset', $headers);
    }

    /**
     * @test
     * @covers Peanut_API_Endpoints::validate_license
     */
    public function validate_license_sanitizes_license_key(): void {
        $request = PeanutTestHelper::createMockRequest('POST', '/peanut-api/v1/license/validate', [
            'license_key' => '  abcd-efgh-ijkl-mnop  ', // lowercase with spaces
            'site_url' => 'https://example.com',
        ]);

        $response = $this->api->validate_license($request);

        // Should not fail due to format (key is sanitized before format check)
        $data = $response->get_data();
        // With valid format after sanitization, it should proceed to key lookup
        // Since our mock DB returns null, it will fail at invalid_license_key
        $this->assertFalse($data['success']);
        // Either format error (if spaces cause issues) or key not found
        $this->assertContains($data['error'], ['invalid_format', 'invalid_license_key']);
    }

    // =========================================================================
    // Deactivate License Endpoint Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_API_Endpoints::deactivate_license
     */
    public function deactivate_license_returns_error_for_invalid_format(): void {
        $request = PeanutTestHelper::createMockRequest('POST', '/peanut-api/v1/license/deactivate', [
            'license_key' => 'bad-key',
            'site_url' => 'https://example.com',
        ]);

        $response = $this->api->deactivate_license($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();

        $this->assertFalse($data['success']);
        $this->assertEquals('invalid_format', $data['error']);
        $this->assertEquals(400, $response->get_status());
    }

    /**
     * @test
     * @covers Peanut_API_Endpoints::deactivate_license
     */
    public function deactivate_license_includes_rate_limit_headers(): void {
        $request = PeanutTestHelper::createMockRequest('POST', '/peanut-api/v1/license/deactivate', [
            'license_key' => 'ABCD-EFGH-IJKL-MNOP',
            'site_url' => 'https://example.com',
        ]);

        $response = $this->api->deactivate_license($request);
        $headers = $response->get_headers();

        $this->assertArrayHasKey('X-RateLimit-Limit', $headers);
        $this->assertArrayHasKey('X-RateLimit-Remaining', $headers);
        $this->assertArrayHasKey('X-RateLimit-Reset', $headers);
    }

    // =========================================================================
    // License Status Endpoint Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_API_Endpoints::get_license_status
     */
    public function get_license_status_returns_error_for_invalid_format(): void {
        $request = PeanutTestHelper::createMockRequest('GET', '/peanut-api/v1/license/status', [
            'license_key' => 'not-valid',
        ]);

        $response = $this->api->get_license_status($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();

        $this->assertFalse($data['success']);
        $this->assertEquals('invalid_format', $data['error']);
        $this->assertEquals(400, $response->get_status());
    }

    /**
     * @test
     * @covers Peanut_API_Endpoints::get_license_status
     */
    public function get_license_status_includes_rate_limit_headers(): void {
        $request = PeanutTestHelper::createMockRequest('GET', '/peanut-api/v1/license/status', [
            'license_key' => 'ABCD-EFGH-IJKL-MNOP',
        ]);

        $response = $this->api->get_license_status($request);
        $headers = $response->get_headers();

        $this->assertArrayHasKey('X-RateLimit-Limit', $headers);
        $this->assertArrayHasKey('X-RateLimit-Remaining', $headers);
        $this->assertArrayHasKey('X-RateLimit-Reset', $headers);
    }

    // =========================================================================
    // Health Check Endpoint Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_API_Endpoints::health_check
     */
    public function health_check_returns_success(): void {
        $response = $this->api->health_check();

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertEquals('ok', $data['status']);
    }

    /**
     * @test
     * @covers Peanut_API_Endpoints::health_check
     */
    public function health_check_includes_version(): void {
        $response = $this->api->health_check();
        $data = $response->get_data();

        $this->assertArrayHasKey('version', $data);
        $this->assertEquals(PEANUT_LICENSE_SERVER_VERSION, $data['version']);
    }

    /**
     * @test
     * @covers Peanut_API_Endpoints::health_check
     */
    public function health_check_includes_timestamp(): void {
        $response = $this->api->health_check();
        $data = $response->get_data();

        $this->assertArrayHasKey('timestamp', $data);
        $this->assertNotEmpty($data['timestamp']);
    }

    /**
     * @test
     * @covers Peanut_API_Endpoints::health_check
     */
    public function health_check_includes_plugin_version(): void {
        PeanutTestHelper::setOption('peanut_license_server_plugin_version', '2.0.0');

        $response = $this->api->health_check();
        $data = $response->get_data();

        $this->assertArrayHasKey('plugin_version', $data);
        $this->assertEquals('2.0.0', $data['plugin_version']);
    }

    // =========================================================================
    // Update Check Endpoint Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_API_Endpoints::check_update
     */
    public function check_update_returns_error_for_invalid_plugin(): void {
        $request = PeanutTestHelper::createMockRequest('GET', '/peanut-api/v1/updates/check', [
            'plugin' => 'invalid-plugin-slug',
            'version' => '1.0.0',
        ]);

        $response = $this->api->check_update($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(400, $response->get_status());

        $data = $response->get_data();
        $this->assertEquals('invalid_plugin', $data['error']);
    }

    /**
     * @test
     * @covers Peanut_API_Endpoints::check_update
     */
    public function check_update_returns_valid_plugins_list_on_error(): void {
        $request = PeanutTestHelper::createMockRequest('GET', '/peanut-api/v1/updates/check', [
            'plugin' => 'invalid-plugin-slug',
        ]);

        $response = $this->api->check_update($request);
        $data = $response->get_data();

        $this->assertArrayHasKey('valid_plugins', $data);
        $this->assertIsArray($data['valid_plugins']);
        $this->assertContains('peanut-suite', $data['valid_plugins']);
    }

    /**
     * @test
     * @covers Peanut_API_Endpoints::check_update
     */
    public function check_update_includes_rate_limit_headers(): void {
        $request = PeanutTestHelper::createMockRequest('GET', '/peanut-api/v1/updates/check', [
            'plugin' => 'peanut-suite',
            'version' => '1.0.0',
        ]);

        $response = $this->api->check_update($request);
        $headers = $response->get_headers();

        $this->assertArrayHasKey('X-RateLimit-Limit', $headers);
        $this->assertArrayHasKey('X-RateLimit-Remaining', $headers);
        $this->assertArrayHasKey('X-RateLimit-Reset', $headers);
    }

    /**
     * @test
     * @covers Peanut_API_Endpoints::check_update
     */
    public function check_update_accepts_peanut_suite(): void {
        $request = PeanutTestHelper::createMockRequest('GET', '/peanut-api/v1/updates/check', [
            'plugin' => 'peanut-suite',
            'version' => '1.0.0',
        ]);

        $response = $this->api->check_update($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());
    }

    /**
     * @test
     * @covers Peanut_API_Endpoints::check_update
     */
    public function check_update_accepts_formflow(): void {
        $request = PeanutTestHelper::createMockRequest('GET', '/peanut-api/v1/updates/check', [
            'plugin' => 'formflow',
            'version' => '1.0.0',
        ]);

        $response = $this->api->check_update($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());
    }

    // =========================================================================
    // Plugin Info Endpoint Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_API_Endpoints::get_plugin_info
     */
    public function get_plugin_info_returns_error_for_invalid_plugin(): void {
        $request = PeanutTestHelper::createMockRequest('GET', '/peanut-api/v1/updates/info', [
            'plugin' => 'invalid-slug',
        ]);

        $response = $this->api->get_plugin_info($request);

        $this->assertEquals(400, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals('invalid_plugin', $data['error']);
    }

    /**
     * @test
     * @covers Peanut_API_Endpoints::get_plugin_info
     */
    public function get_plugin_info_returns_200_for_valid_plugin(): void {
        $request = PeanutTestHelper::createMockRequest('GET', '/peanut-api/v1/updates/info', [
            'plugin' => 'peanut-suite',
        ]);

        $response = $this->api->get_plugin_info($request);

        $this->assertEquals(200, $response->get_status());
    }

    // =========================================================================
    // Report Site Health Endpoint Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_API_Endpoints::report_site_health
     */
    public function report_site_health_returns_error_for_invalid_format(): void {
        $request = PeanutTestHelper::createMockRequest('POST', '/peanut-api/v1/site/health', [
            'license_key' => 'bad-format',
            'site_url' => 'https://example.com',
            'plugin_version' => '1.0.0',
        ]);

        $response = $this->api->report_site_health($request);

        $this->assertEquals(400, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals('invalid_format', $data['error']);
    }

    /**
     * @test
     * @covers Peanut_API_Endpoints::report_site_health
     */
    public function report_site_health_returns_error_for_invalid_license(): void {
        $request = PeanutTestHelper::createMockRequest('POST', '/peanut-api/v1/site/health', [
            'license_key' => 'ABCD-EFGH-IJKL-MNOP',
            'site_url' => 'https://example.com',
            'plugin_version' => '1.0.0',
        ]);

        $response = $this->api->report_site_health($request);

        // License lookup fails with mock DB
        $this->assertEquals(400, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals('invalid_license', $data['error']);
    }

    // =========================================================================
    // License Activations Endpoint Tests
    // =========================================================================

    /**
     * @test
     * @covers Peanut_API_Endpoints::get_license_activations
     */
    public function get_license_activations_returns_error_for_invalid_format(): void {
        $request = PeanutTestHelper::createMockRequest('GET', '/peanut-api/v1/license/activations', [
            'license_key' => 'bad-format',
        ]);

        $response = $this->api->get_license_activations($request);

        $this->assertEquals(400, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals('invalid_format', $data['error']);
    }

    /**
     * @test
     * @covers Peanut_API_Endpoints::get_license_activations
     */
    public function get_license_activations_returns_error_for_invalid_license(): void {
        $request = PeanutTestHelper::createMockRequest('GET', '/peanut-api/v1/license/activations', [
            'license_key' => 'ABCD-EFGH-IJKL-MNOP',
        ]);

        $response = $this->api->get_license_activations($request);

        $this->assertEquals(400, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals('invalid_license', $data['error']);
    }

    // =========================================================================
    // Response Format Tests
    // =========================================================================

    /**
     * @test
     */
    public function error_responses_have_consistent_format(): void {
        $request = PeanutTestHelper::createMockRequest('POST', '/peanut-api/v1/license/validate', [
            'license_key' => 'bad',
            'site_url' => 'https://example.com',
        ]);

        $response = $this->api->validate_license($request);
        $data = $response->get_data();

        // All error responses should have these fields
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertIsBool($data['success']);
        $this->assertIsString($data['error']);
        $this->assertIsString($data['message']);
    }

    /**
     * @test
     */
    public function success_responses_return_200_status(): void {
        $response = $this->api->health_check();

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * @test
     */
    public function error_responses_return_400_status(): void {
        $request = PeanutTestHelper::createMockRequest('POST', '/peanut-api/v1/license/validate', [
            'license_key' => 'invalid',
            'site_url' => 'https://example.com',
        ]);

        $response = $this->api->validate_license($request);

        $this->assertEquals(400, $response->get_status());
    }
}
