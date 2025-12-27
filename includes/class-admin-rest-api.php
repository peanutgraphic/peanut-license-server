<?php
/**
 * Admin REST API Class
 *
 * Provides authenticated REST API endpoints for admin operations.
 *
 * @package Peanut_License_Server
 */

defined('ABSPATH') || exit;

class Peanut_Admin_REST_API {

    /**
     * API namespace
     */
    private const NAMESPACE = 'peanut-admin/v1';

    /**
     * Initialize the admin API
     */
    public static function init(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    /**
     * Register REST API routes
     */
    public static function register_routes(): void {
        // Licenses endpoints
        register_rest_route(self::NAMESPACE, '/licenses', [
            [
                'methods' => 'GET',
                'callback' => [self::class, 'get_licenses'],
                'permission_callback' => [self::class, 'admin_permission_check'],
                'args' => self::get_collection_params(),
            ],
            [
                'methods' => 'POST',
                'callback' => [self::class, 'create_license'],
                'permission_callback' => [self::class, 'admin_permission_check'],
                'args' => self::get_license_create_params(),
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/licenses/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [self::class, 'get_license'],
                'permission_callback' => [self::class, 'admin_permission_check'],
            ],
            [
                'methods' => 'PUT,PATCH',
                'callback' => [self::class, 'update_license'],
                'permission_callback' => [self::class, 'admin_permission_check'],
                'args' => self::get_license_update_params(),
            ],
            [
                'methods' => 'DELETE',
                'callback' => [self::class, 'delete_license'],
                'permission_callback' => [self::class, 'admin_permission_check'],
            ],
        ]);

        // License actions
        register_rest_route(self::NAMESPACE, '/licenses/(?P<id>\d+)/suspend', [
            'methods' => 'POST',
            'callback' => [self::class, 'suspend_license'],
            'permission_callback' => [self::class, 'admin_permission_check'],
        ]);

        register_rest_route(self::NAMESPACE, '/licenses/(?P<id>\d+)/reactivate', [
            'methods' => 'POST',
            'callback' => [self::class, 'reactivate_license'],
            'permission_callback' => [self::class, 'admin_permission_check'],
        ]);

        register_rest_route(self::NAMESPACE, '/licenses/(?P<id>\d+)/regenerate', [
            'methods' => 'POST',
            'callback' => [self::class, 'regenerate_license_key'],
            'permission_callback' => [self::class, 'admin_permission_check'],
        ]);

        register_rest_route(self::NAMESPACE, '/licenses/(?P<id>\d+)/transfer', [
            'methods' => 'POST',
            'callback' => [self::class, 'transfer_license'],
            'permission_callback' => [self::class, 'admin_permission_check'],
            'args' => [
                'email' => [
                    'required' => true,
                    'type' => 'string',
                    'format' => 'email',
                ],
                'name' => [
                    'type' => 'string',
                ],
                'deactivate_sites' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ]);

        // Activations
        register_rest_route(self::NAMESPACE, '/licenses/(?P<id>\d+)/activations', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_license_activations'],
            'permission_callback' => [self::class, 'admin_permission_check'],
        ]);

        register_rest_route(self::NAMESPACE, '/activations/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [self::class, 'deactivate_site'],
            'permission_callback' => [self::class, 'admin_permission_check'],
        ]);

        // Analytics
        register_rest_route(self::NAMESPACE, '/analytics/stats', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_analytics_stats'],
            'permission_callback' => [self::class, 'admin_permission_check'],
        ]);

        register_rest_route(self::NAMESPACE, '/analytics/timeline', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_analytics_timeline'],
            'permission_callback' => [self::class, 'admin_permission_check'],
            'args' => [
                'days' => [
                    'type' => 'integer',
                    'default' => 30,
                    'minimum' => 1,
                    'maximum' => 365,
                ],
                'metric' => [
                    'type' => 'string',
                    'default' => 'licenses',
                    'enum' => ['licenses', 'activations', 'validations'],
                ],
            ],
        ]);

        // Audit trail
        register_rest_route(self::NAMESPACE, '/audit', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_audit_logs'],
            'permission_callback' => [self::class, 'admin_permission_check'],
            'args' => array_merge(self::get_collection_params(), [
                'event' => [
                    'type' => 'string',
                ],
                'license_id' => [
                    'type' => 'integer',
                ],
                'user_id' => [
                    'type' => 'integer',
                ],
            ]),
        ]);

        register_rest_route(self::NAMESPACE, '/audit/license/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_license_audit'],
            'permission_callback' => [self::class, 'admin_permission_check'],
        ]);

        // GDPR
        register_rest_route(self::NAMESPACE, '/gdpr/export', [
            'methods' => 'POST',
            'callback' => [self::class, 'gdpr_export'],
            'permission_callback' => [self::class, 'admin_permission_check'],
            'args' => [
                'email' => [
                    'required' => true,
                    'type' => 'string',
                    'format' => 'email',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/gdpr/anonymize', [
            'methods' => 'POST',
            'callback' => [self::class, 'gdpr_anonymize'],
            'permission_callback' => [self::class, 'admin_permission_check'],
            'args' => [
                'email' => [
                    'required' => true,
                    'type' => 'string',
                    'format' => 'email',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/gdpr/delete', [
            'methods' => 'POST',
            'callback' => [self::class, 'gdpr_delete'],
            'permission_callback' => [self::class, 'admin_permission_check'],
            'args' => [
                'email' => [
                    'required' => true,
                    'type' => 'string',
                    'format' => 'email',
                ],
            ],
        ]);

        // Webhooks
        register_rest_route(self::NAMESPACE, '/webhooks', [
            [
                'methods' => 'GET',
                'callback' => [self::class, 'get_webhooks'],
                'permission_callback' => [self::class, 'admin_permission_check'],
            ],
            [
                'methods' => 'POST',
                'callback' => [self::class, 'create_webhook'],
                'permission_callback' => [self::class, 'admin_permission_check'],
                'args' => [
                    'url' => [
                        'required' => true,
                        'type' => 'string',
                        'format' => 'uri',
                    ],
                    'events' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                    ],
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/webhooks/(?P<id>[a-zA-Z0-9]+)', [
            'methods' => 'DELETE',
            'callback' => [self::class, 'delete_webhook'],
            'permission_callback' => [self::class, 'admin_permission_check'],
        ]);

        register_rest_route(self::NAMESPACE, '/webhooks/test', [
            'methods' => 'POST',
            'callback' => [self::class, 'test_webhook'],
            'permission_callback' => [self::class, 'admin_permission_check'],
            'args' => [
                'url' => [
                    'required' => true,
                    'type' => 'string',
                    'format' => 'uri',
                ],
                'secret' => [
                    'type' => 'string',
                ],
            ],
        ]);

        // Batch operations
        register_rest_route(self::NAMESPACE, '/batch/generate', [
            'methods' => 'POST',
            'callback' => [self::class, 'batch_generate'],
            'permission_callback' => [self::class, 'admin_permission_check'],
            'args' => [
                'count' => [
                    'required' => true,
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 100,
                ],
                'customer_email' => [
                    'required' => true,
                    'type' => 'string',
                    'format' => 'email',
                ],
                'tier' => [
                    'type' => 'string',
                    'enum' => ['free', 'pro', 'agency'],
                    'default' => 'pro',
                ],
                'expires_at' => [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/batch/export', [
            'methods' => 'POST',
            'callback' => [self::class, 'batch_export'],
            'permission_callback' => [self::class, 'admin_permission_check'],
            'args' => [
                'format' => [
                    'type' => 'string',
                    'enum' => ['csv', 'json'],
                    'default' => 'json',
                ],
                'status' => [
                    'type' => 'string',
                ],
                'tier' => [
                    'type' => 'string',
                ],
            ],
        ]);

        // Security restrictions
        register_rest_route(self::NAMESPACE, '/licenses/(?P<id>\d+)/restrictions', [
            [
                'methods' => 'GET',
                'callback' => [self::class, 'get_license_restrictions'],
                'permission_callback' => [self::class, 'admin_permission_check'],
            ],
            [
                'methods' => 'PUT,PATCH',
                'callback' => [self::class, 'update_license_restrictions'],
                'permission_callback' => [self::class, 'admin_permission_check'],
            ],
        ]);
    }

    /**
     * Check admin permissions
     */
    public static function admin_permission_check(WP_REST_Request $request): bool {
        // Check for application password or cookie auth
        if (!is_user_logged_in()) {
            return false;
        }

        return current_user_can('manage_options');
    }

    /**
     * Get collection parameters
     */
    private static function get_collection_params(): array {
        return [
            'page' => [
                'type' => 'integer',
                'default' => 1,
                'minimum' => 1,
            ],
            'per_page' => [
                'type' => 'integer',
                'default' => 20,
                'minimum' => 1,
                'maximum' => 100,
            ],
            'search' => [
                'type' => 'string',
            ],
            'status' => [
                'type' => 'string',
                'enum' => ['active', 'expired', 'suspended', 'revoked'],
            ],
            'tier' => [
                'type' => 'string',
                'enum' => ['free', 'pro', 'agency'],
            ],
        ];
    }

    /**
     * Get license create parameters
     */
    private static function get_license_create_params(): array {
        return [
            'customer_email' => [
                'required' => true,
                'type' => 'string',
                'format' => 'email',
            ],
            'customer_name' => [
                'type' => 'string',
            ],
            'tier' => [
                'type' => 'string',
                'enum' => ['free', 'pro', 'agency'],
                'default' => 'pro',
            ],
            'max_activations' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            'expires_at' => [
                'type' => 'string',
                'format' => 'date-time',
            ],
            'product_id' => [
                'type' => 'integer',
            ],
        ];
    }

    /**
     * Get license update parameters
     */
    private static function get_license_update_params(): array {
        return [
            'customer_email' => [
                'type' => 'string',
                'format' => 'email',
            ],
            'customer_name' => [
                'type' => 'string',
            ],
            'tier' => [
                'type' => 'string',
                'enum' => ['free', 'pro', 'agency'],
            ],
            'status' => [
                'type' => 'string',
                'enum' => ['active', 'expired', 'suspended', 'revoked'],
            ],
            'max_activations' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            'expires_at' => [
                'type' => 'string',
                'format' => 'date-time',
            ],
        ];
    }

    /**
     * Get licenses
     */
    public static function get_licenses(WP_REST_Request $request): WP_REST_Response {
        $result = Peanut_License_Manager::get_all([
            'page' => $request->get_param('page'),
            'per_page' => $request->get_param('per_page'),
            'search' => $request->get_param('search'),
            'status' => $request->get_param('status'),
            'tier' => $request->get_param('tier'),
        ]);

        $response = new WP_REST_Response($result['data']);
        $response->header('X-WP-Total', $result['total']);
        $response->header('X-WP-TotalPages', $result['total_pages']);

        return $response;
    }

    /**
     * Get single license
     */
    public static function get_license(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $license = Peanut_License_Manager::get_by_id($request->get_param('id'));

        if (!$license) {
            return new WP_Error('not_found', __('License not found.', 'peanut-license-server'), ['status' => 404]);
        }

        return new WP_REST_Response($license);
    }

    /**
     * Create license
     */
    public static function create_license(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $license = Peanut_License_Manager::create([
            'customer_email' => $request->get_param('customer_email'),
            'customer_name' => $request->get_param('customer_name'),
            'tier' => $request->get_param('tier'),
            'max_activations' => $request->get_param('max_activations'),
            'expires_at' => $request->get_param('expires_at'),
            'product_id' => $request->get_param('product_id') ?: 0,
        ]);

        if (!$license) {
            return new WP_Error('create_failed', __('Failed to create license.', 'peanut-license-server'), ['status' => 500]);
        }

        return new WP_REST_Response($license, 201);
    }

    /**
     * Update license
     */
    public static function update_license(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $license_id = $request->get_param('id');
        $license = Peanut_License_Manager::get_by_id($license_id);

        if (!$license) {
            return new WP_Error('not_found', __('License not found.', 'peanut-license-server'), ['status' => 404]);
        }

        $data = [];
        $params = ['customer_email', 'customer_name', 'tier', 'status', 'max_activations', 'expires_at'];

        foreach ($params as $param) {
            if ($request->has_param($param)) {
                $data[$param] = $request->get_param($param);
            }
        }

        $updated = Peanut_License_Manager::update($license_id, $data);

        if (!$updated) {
            return new WP_Error('update_failed', __('Failed to update license.', 'peanut-license-server'), ['status' => 500]);
        }

        return new WP_REST_Response(Peanut_License_Manager::get_by_id($license_id));
    }

    /**
     * Delete license
     */
    public static function delete_license(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $license_id = $request->get_param('id');
        $result = Peanut_License_Manager::delete($license_id);

        if (!$result) {
            return new WP_Error('delete_failed', __('Failed to delete license.', 'peanut-license-server'), ['status' => 500]);
        }

        return new WP_REST_Response(['deleted' => true, 'id' => $license_id]);
    }

    /**
     * Suspend license
     */
    public static function suspend_license(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $license_id = $request->get_param('id');
        $result = Peanut_License_Manager::suspend($license_id);

        if (!$result) {
            return new WP_Error('suspend_failed', __('Failed to suspend license.', 'peanut-license-server'), ['status' => 500]);
        }

        return new WP_REST_Response(Peanut_License_Manager::get_by_id($license_id));
    }

    /**
     * Reactivate license
     */
    public static function reactivate_license(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $license_id = $request->get_param('id');
        $result = Peanut_License_Manager::reactivate($license_id);

        if (!$result) {
            return new WP_Error('reactivate_failed', __('Failed to reactivate license.', 'peanut-license-server'), ['status' => 500]);
        }

        return new WP_REST_Response(Peanut_License_Manager::get_by_id($license_id));
    }

    /**
     * Regenerate license key
     */
    public static function regenerate_license_key(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $license_id = $request->get_param('id');
        $result = Peanut_License_Manager::regenerate_key($license_id);

        if (!$result) {
            return new WP_Error('regenerate_failed', __('Failed to regenerate license key.', 'peanut-license-server'), ['status' => 500]);
        }

        return new WP_REST_Response($result);
    }

    /**
     * Transfer license
     */
    public static function transfer_license(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $license_id = $request->get_param('id');
        $result = Peanut_License_Manager::transfer_license($license_id, [
            'email' => $request->get_param('email'),
            'name' => $request->get_param('name'),
            'deactivate_sites' => $request->get_param('deactivate_sites'),
        ]);

        if (!$result) {
            return new WP_Error('transfer_failed', __('Failed to transfer license.', 'peanut-license-server'), ['status' => 500]);
        }

        return new WP_REST_Response($result);
    }

    /**
     * Get license activations
     */
    public static function get_license_activations(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $license_id = $request->get_param('id');
        $activations = Peanut_License_Manager::get_activations($license_id);

        return new WP_REST_Response($activations);
    }

    /**
     * Deactivate site
     */
    public static function deactivate_site(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $activation_id = $request->get_param('id');
        $result = Peanut_License_Manager::deactivate_site($activation_id);

        if (!$result) {
            return new WP_Error('deactivate_failed', __('Failed to deactivate site.', 'peanut-license-server'), ['status' => 500]);
        }

        return new WP_REST_Response(['deactivated' => true, 'id' => $activation_id]);
    }

    /**
     * Get analytics stats
     */
    public static function get_analytics_stats(WP_REST_Request $request): WP_REST_Response {
        $stats = Peanut_Analytics::get_dashboard_stats();
        return new WP_REST_Response($stats);
    }

    /**
     * Get analytics timeline
     */
    public static function get_analytics_timeline(WP_REST_Request $request): WP_REST_Response {
        $days = $request->get_param('days');
        $metric = $request->get_param('metric');

        $data = Peanut_Analytics::get_timeline_data($days, $metric);
        return new WP_REST_Response($data);
    }

    /**
     * Get audit logs
     */
    public static function get_audit_logs(WP_REST_Request $request): WP_REST_Response {
        $result = Peanut_Audit_Trail::get_logs([
            'page' => $request->get_param('page'),
            'per_page' => $request->get_param('per_page'),
            'event' => $request->get_param('event'),
            'license_id' => $request->get_param('license_id'),
            'user_id' => $request->get_param('user_id'),
        ]);

        $response = new WP_REST_Response($result['data']);
        $response->header('X-WP-Total', $result['total']);
        $response->header('X-WP-TotalPages', $result['total_pages']);

        return $response;
    }

    /**
     * Get license audit history
     */
    public static function get_license_audit(WP_REST_Request $request): WP_REST_Response {
        $license_id = $request->get_param('id');
        $history = Peanut_Audit_Trail::get_license_history($license_id);

        return new WP_REST_Response($history);
    }

    /**
     * GDPR export
     */
    public static function gdpr_export(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $email = $request->get_param('email');
        $data = Peanut_GDPR_Compliance::export_customer_data($email);

        if (!$data) {
            return new WP_Error('not_found', __('No data found for this email.', 'peanut-license-server'), ['status' => 404]);
        }

        return new WP_REST_Response($data);
    }

    /**
     * GDPR anonymize
     */
    public static function gdpr_anonymize(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $email = $request->get_param('email');
        $result = Peanut_GDPR_Compliance::anonymize_customer_data($email);

        if (!$result['success']) {
            return new WP_Error('anonymize_failed', $result['error'], ['status' => 500]);
        }

        return new WP_REST_Response($result);
    }

    /**
     * GDPR delete
     */
    public static function gdpr_delete(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $email = $request->get_param('email');
        $result = Peanut_GDPR_Compliance::delete_customer_data($email);

        if (!$result['success']) {
            return new WP_Error('delete_failed', $result['error'], ['status' => 500]);
        }

        return new WP_REST_Response($result);
    }

    /**
     * Get webhooks
     */
    public static function get_webhooks(WP_REST_Request $request): WP_REST_Response {
        $webhooks = Peanut_Webhook_Notifications::get_webhook_endpoints();
        return new WP_REST_Response($webhooks);
    }

    /**
     * Create webhook
     */
    public static function create_webhook(WP_REST_Request $request): WP_REST_Response {
        $url = $request->get_param('url');
        $events = $request->get_param('events') ?: [];

        $webhook = Peanut_Webhook_Notifications::add_endpoint($url, ['events' => $events]);
        return new WP_REST_Response($webhook, 201);
    }

    /**
     * Delete webhook
     */
    public static function delete_webhook(WP_REST_Request $request): WP_REST_Response {
        $id = $request->get_param('id');
        Peanut_Webhook_Notifications::remove_endpoint($id);

        return new WP_REST_Response(['deleted' => true, 'id' => $id]);
    }

    /**
     * Test webhook
     */
    public static function test_webhook(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $url = $request->get_param('url');
        $secret = $request->get_param('secret') ?: '';

        $result = Peanut_Webhook_Notifications::send_test_webhook($url, $secret);

        if (!$result['success']) {
            return new WP_Error('test_failed', $result['error'], ['status' => 500]);
        }

        return new WP_REST_Response($result);
    }

    /**
     * Batch generate licenses
     */
    public static function batch_generate(WP_REST_Request $request): WP_REST_Response {
        $count = $request->get_param('count');
        $result = Peanut_Batch_Operations::generate_bulk($count, [
            'customer_email' => $request->get_param('customer_email'),
            'tier' => $request->get_param('tier'),
            'expires_at' => $request->get_param('expires_at'),
            'product_id' => 0,
        ]);

        return new WP_REST_Response($result);
    }

    /**
     * Batch export licenses
     */
    public static function batch_export(WP_REST_Request $request): WP_REST_Response {
        $format = $request->get_param('format');
        $filters = [
            'status' => $request->get_param('status'),
            'tier' => $request->get_param('tier'),
        ];

        if ($format === 'csv') {
            $data = Peanut_Batch_Operations::export_csv($filters);
        } else {
            $data = Peanut_Batch_Operations::export_json($filters);
        }

        return new WP_REST_Response([
            'format' => $format,
            'data' => $data,
        ]);
    }

    /**
     * Get license restrictions
     */
    public static function get_license_restrictions(WP_REST_Request $request): WP_REST_Response {
        $license_id = $request->get_param('id');
        $restrictions = Peanut_Security_Features::get_license_restrictions($license_id);

        return new WP_REST_Response($restrictions ?: []);
    }

    /**
     * Update license restrictions
     */
    public static function update_license_restrictions(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $license_id = $request->get_param('id');
        $restrictions = $request->get_json_params();

        $result = Peanut_Security_Features::set_license_restrictions($license_id, $restrictions);

        if (!$result) {
            return new WP_Error('update_failed', __('Failed to update restrictions.', 'peanut-license-server'), ['status' => 500]);
        }

        return new WP_REST_Response(Peanut_Security_Features::get_license_restrictions($license_id));
    }
}

// Initialize the admin API
Peanut_Admin_REST_API::init();
