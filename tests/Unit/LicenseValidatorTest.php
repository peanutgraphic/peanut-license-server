<?php
/**
 * License Validator Unit Tests
 *
 * Tests for the Peanut_License_Validator class.
 *
 * @package Peanut_License_Server\Tests\Unit
 */

namespace Peanut\LicenseServer\Tests\Unit;

use Peanut\LicenseServer\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers Peanut_License_Validator
 */
class LicenseValidatorTest extends TestCase {

    /**
     * Test that valid license key format is accepted
     *
     * @dataProvider validLicenseKeyProvider
     */
    public function test_valid_license_key_format_is_accepted(string $key): void {
        // We need to include the class for static method testing
        require_once dirname(__DIR__, 2) . '/includes/class-license-validator.php';

        $this->assertTrue(
            \Peanut_License_Validator::is_valid_format($key),
            "License key '{$key}' should be valid"
        );
    }

    /**
     * Test that invalid license key format is rejected
     *
     * @dataProvider invalidLicenseKeyProvider
     */
    public function test_invalid_license_key_format_is_rejected(string $key): void {
        require_once dirname(__DIR__, 2) . '/includes/class-license-validator.php';

        $this->assertFalse(
            \Peanut_License_Validator::is_valid_format($key),
            "License key '{$key}' should be invalid"
        );
    }

    /**
     * Test license key sanitization removes invalid characters
     */
    public function test_sanitize_key_removes_invalid_characters(): void {
        require_once dirname(__DIR__, 2) . '/includes/class-license-validator.php';

        $dirty_key = '  abcd-1234-efgh-5678  ';
        $clean_key = \Peanut_License_Validator::sanitize_key($dirty_key);

        $this->assertEquals('ABCD-1234-EFGH-5678', $clean_key);
    }

    /**
     * Test license key sanitization handles special characters
     */
    public function test_sanitize_key_handles_special_characters(): void {
        require_once dirname(__DIR__, 2) . '/includes/class-license-validator.php';

        $dirty_key = 'ABCD!@#$1234%^&*EFGH()5678';
        $clean_key = \Peanut_License_Validator::sanitize_key($dirty_key);

        $this->assertEquals('ABCD1234EFGH5678', $clean_key);
    }

    /**
     * Test license key sanitization preserves hyphens
     */
    public function test_sanitize_key_preserves_hyphens(): void {
        require_once dirname(__DIR__, 2) . '/includes/class-license-validator.php';

        $key = 'ABCD-1234-EFGH-5678';
        $clean_key = \Peanut_License_Validator::sanitize_key($key);

        $this->assertEquals('ABCD-1234-EFGH-5678', $clean_key);
    }

    /**
     * Test license key is converted to uppercase
     */
    public function test_sanitize_key_converts_to_uppercase(): void {
        require_once dirname(__DIR__, 2) . '/includes/class-license-validator.php';

        $lowercase_key = 'abcd-1234-efgh-5678';
        $clean_key = \Peanut_License_Validator::sanitize_key($lowercase_key);

        $this->assertEquals('ABCD-1234-EFGH-5678', $clean_key);
    }

    /**
     * Test error constants are defined
     */
    public function test_error_constants_are_defined(): void {
        require_once dirname(__DIR__, 2) . '/includes/class-license-validator.php';

        $this->assertEquals('invalid_license_key', \Peanut_License_Validator::ERROR_INVALID_KEY);
        $this->assertEquals('license_expired', \Peanut_License_Validator::ERROR_EXPIRED);
        $this->assertEquals('license_suspended', \Peanut_License_Validator::ERROR_SUSPENDED);
        $this->assertEquals('license_revoked', \Peanut_License_Validator::ERROR_REVOKED);
        $this->assertEquals('activation_limit_reached', \Peanut_License_Validator::ERROR_ACTIVATION_LIMIT);
        $this->assertEquals('invalid_site_url', \Peanut_License_Validator::ERROR_INVALID_SITE);
        $this->assertEquals('server_error', \Peanut_License_Validator::ERROR_SERVER);
    }

    /**
     * Test last error tracking
     */
    public function test_error_tracking(): void {
        require_once dirname(__DIR__, 2) . '/includes/class-license-validator.php';

        $validator = new \Peanut_License_Validator();

        // Initially no error
        $this->assertEquals('', $validator->get_last_error());
        $this->assertEquals('', $validator->get_last_error_message());
    }

    /**
     * Valid license key provider
     */
    public static function validLicenseKeyProvider(): array {
        return [
            'standard format' => ['ABCD-1234-EFGH-5678'],
            'all uppercase letters' => ['AAAA-BBBB-CCCC-DDDD'],
            'all numbers' => ['1234-5678-9012-3456'],
            'mixed alphanumeric' => ['A1B2-C3D4-E5F6-G7H8'],
            'lowercase (should validate)' => ['abcd-1234-efgh-5678'],
        ];
    }

    /**
     * Invalid license key provider
     */
    public static function invalidLicenseKeyProvider(): array {
        return [
            'too short' => ['ABCD-1234-EFGH'],
            'too long' => ['ABCD-1234-EFGH-5678-9999'],
            'wrong separator' => ['ABCD_1234_EFGH_5678'],
            'no separators' => ['ABCD1234EFGH5678'],
            'special characters' => ['AB@D-12#4-EF$H-56%8'],
            'empty string' => [''],
            'wrong group length' => ['ABC-1234-EFGH-5678'],
            'spaces in key' => ['ABCD 1234 EFGH 5678'],
        ];
    }
}
