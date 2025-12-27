# Peanut License Server

License management, validation, and update server for Peanut Suite WordPress plugins.

## Description

Peanut License Server provides a complete license management solution for WordPress plugins. It handles license validation, site activations, automatic updates, and integrates with WooCommerce Subscriptions for automated license provisioning.

## Features

### License Management
- Generate and manage license keys with Free, Pro, and Agency tiers
- Track activations per license with configurable limits
- Suspend, expire, or revoke licenses
- Transfer licenses between customers
- Batch operations for bulk license management

### API & Validation
- RESTful API for license validation
- Site activation/deactivation endpoints
- Rate limiting for API protection
- Validation logging and analytics

### Update Server
- Serve plugin updates to licensed sites
- Version-based update delivery
- Changelog and requirements management
- Download tracking

### WooCommerce Integration
- Automatic license creation on subscription purchase
- License tier mapping to product variations
- Webhook notifications for subscription events
- Subscription status sync

### Security
- Audit trail for all license operations
- Security event logging
- GDPR compliance tools (export, anonymize, delete)
- Rate limiting and IP monitoring

### Analytics
- License growth tracking
- Activation trends
- API usage statistics
- Site health monitoring

## Requirements

- WordPress 6.0+
- PHP 8.0+
- WooCommerce (optional, for e-commerce integration)
- WooCommerce Subscriptions (optional, for subscription management)

## Installation

1. Upload the `peanut-license-server` folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin
3. Navigate to **Licenses** in the admin menu to configure

## Configuration

### API Settings
Configure API and update server settings under **Licenses > Settings**:
- Enable/disable license validation API
- Enable/disable plugin update server
- Set cache duration for client validation results

### Product Settings
For each product, configure:
- Current version number
- WordPress and PHP requirements
- Changelog entries
- Update ZIP file location

## API Endpoints

### License Validation
```
POST /wp-json/peanut-api/v1/license/validate
```

### Site Activation
```
POST /wp-json/peanut-api/v1/license/activate
```

### Site Deactivation
```
POST /wp-json/peanut-api/v1/license/deactivate
```

### Update Check
```
GET /wp-json/peanut-api/v1/updates/check?plugin=PLUGIN_SLUG
```

## Client SDK

Include the SDK in your plugin to integrate with the license server:

```php
require_once 'path/to/class-peanut-license-client.php';

$client = new Peanut_License_Client([
    'server_url' => 'https://your-site.com',
    'plugin_slug' => 'your-plugin',
]);

$result = $client->validate_license($license_key);
```

## License Tiers

| Tier | Max Activations | Typical Use |
|------|-----------------|-------------|
| Free | 1 | Personal/testing |
| Pro | 3 | Small business |
| Agency | 25 | Agencies/developers |

## Frontend Development

The admin interface uses a modern React SPA built with Vite.

### Directory Structure
```
frontend/
├── src/
│   ├── components/     # Reusable UI components
│   │   ├── common/     # Buttons, cards, modals, etc.
│   │   └── layout/     # Sidebar, header, layout
│   ├── pages/          # Route pages
│   ├── api/            # API client and endpoints
│   ├── contexts/       # React contexts (theme, etc.)
│   ├── types/          # TypeScript type definitions
│   └── utils/          # Utility functions
├── package.json
└── vite.config.ts
```

### Tech Stack
- React 19 + TypeScript
- Vite 6 for build tooling
- Tailwind CSS 4.0 for styling
- React Query for data fetching
- React Router for navigation
- Recharts for analytics charts
- Lucide for icons

### Development
```bash
cd frontend
npm install
npm run dev
```

### Building for Production
```bash
npm run build
```
Build output goes to `assets/dist/` for WordPress integration.

### Pages
- **Dashboard** - License statistics, quick actions, recent activity
- **Licenses** - License management with CRUD operations
- **Analytics** - Validation charts, tier distribution, error analysis
- **Audit Trail** - Complete action log with filters
- **Webhooks** - Configure webhook integrations
- **Products** - Plugin update management
- **GDPR Tools** - Data export, anonymize, delete
- **Security** - IP blocking, rate limits, security events
- **Settings** - API and server configuration

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## Support

For support, please visit [Peanut Graphic](https://peanutgraphic.com).

## License

GPL-2.0-or-later
