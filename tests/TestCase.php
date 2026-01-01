<?php
/**
 * Base Test Case for Peanut License Server
 *
 * @package Peanut_License_Server\Tests
 */

namespace Peanut\LicenseServer\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Brain\Monkey;

abstract class TestCase extends PHPUnitTestCase {

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();

        // Reset global test state
        global $test_options, $test_transients, $test_user_capabilities, $test_user_logged_in;
        $test_options = [];
        $test_transients = [];
        $test_user_capabilities = ['manage_options' => true];
        $test_user_logged_in = true;
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Set a test option
     */
    protected function setOption(string $key, mixed $value): void {
        global $test_options;
        $test_options[$key] = $value;
    }

    /**
     * Set a test transient
     */
    protected function setTransient(string $key, mixed $value, int $expiration = 0): void {
        global $test_transients;
        $test_transients[$key] = [
            'value' => $value,
            'expires' => $expiration > 0 ? time() + $expiration : 0,
        ];
    }

    /**
     * Get a test transient
     */
    protected function getTransient(string $key): mixed {
        global $test_transients;
        return $test_transients[$key]['value'] ?? false;
    }

    /**
     * Set user capabilities
     */
    protected function setUserCapability(string $capability, bool $can): void {
        global $test_user_capabilities;
        $test_user_capabilities[$capability] = $can;
    }

    /**
     * Set user logged in state
     */
    protected function setUserLoggedIn(bool $logged_in): void {
        global $test_user_logged_in;
        $test_user_logged_in = $logged_in;
    }

    /**
     * Generate a valid license key format
     */
    protected function generateLicenseKey(): string {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $key = '';
        for ($i = 0; $i < 4; $i++) {
            if ($i > 0) {
                $key .= '-';
            }
            for ($j = 0; $j < 4; $j++) {
                $key .= $chars[random_int(0, strlen($chars) - 1)];
            }
        }
        return $key;
    }
}
