# MP Directory

A production-ready WordPress plugin for importing and displaying Members of Parliament from an external API.

## Features

- **Custom Post Type**: MPs stored as a custom post type with full WordPress integration
- **ACF Integration**: Rich, editable data fields using Advanced Custom Fields
- **Configurable API**: Flexible API configuration for any MP data source
- **Data Import**: Manual and scheduled imports with batch processing
- **Preview System**: Preview API data before importing with transient caching
- **Frontend Display**: Beautiful, responsive archive and single pages for MPs
- **Filtering**: Search and filter MPs by party, constituency, and more
- **SEO-Friendly**: Each MP has their own single page for optimal SEO
- **Cron Scheduling**: Automatic scheduled imports (hourly, twice daily, daily)
- **Error Handling**: Robust API error handling with exponential backoff

## Requirements

- WordPress 6.5 or higher
- PHP 8.1 or higher
- Advanced Custom Fields (recommended, but plugin works without it)

## Installation

1. Download the plugin zip file or clone this repository
2. Upload to `wp-content/plugins/mp-directory/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Install and activate Advanced Custom Fields (recommended)
5. Go to MP Directory → Settings to configure your API

## Configuration

### API Settings

1. Navigate to **MP Directory → Settings** in WordPress admin
2. Configure the following settings:

   - **API Base URL**: The endpoint URL for fetching MP data (required)
   - **API Key**: Optional API key for authentication
   - **Preview Cache TTL**: How long to cache preview data (5-120 minutes)
   - **Import Batch Size**: Number of MPs to import per batch (10-500)

### Scheduled Import

Enable automatic imports to keep MP data up-to-date:

1. Check **Enable Scheduled Import**
2. Select **Import Frequency** (hourly, twice daily, or daily)
3. Save settings

The plugin will automatically fetch and update MP data at the specified interval.

## Usage

### Importing MPs

#### Manual Import

1. Go to **MP Directory → Import**
2. Click **Load Preview** to see a sample of API data
3. Review the preview to ensure your API is configured correctly
4. Click **Start Import** to begin the import process
5. Wait for the import to complete (progress bar shows status)

#### Scheduled Import

Once enabled in settings, imports run automatically in the background.

### Viewing MPs

**Archive Page**: `/mp/` - Lists all Members of Parliament with search and filtering

**Single Page**: `/mp/[mp-name]/` - Individual MP profile with full details

### Frontend Features

- **Search**: Search MPs by name
- **Filters**: Filter by party and constituency
- **Responsive**: Mobile-first design that works on all devices
- **Photo Display**: Featured images for each MP
- **Contact Info**: Display email, phone, and other contact details
- **Social Media**: Links to social media profiles

## API Data Mapping

The plugin expects JSON data with the following structure:

```json
{
  "id": 241,
  "firstName": "John",
  "lastName": "Smith",
  "firstLastName": "John Smith",
  "club": "Party Name",
  "districtName": "Constituency Name",
  "birthDate": "1980-01-15",
  "email": "john.smith@parliament.gov",
  "educationLevel": "Higher Education",
  "profession": "Lawyer",
  "photo": "https://example.com/photo.jpg"
}
```

### Field Mapping

| API Field        | WordPress Field         | Description                        |
| ---------------- | ----------------------- | ---------------------------------- |
| `id`             | `_mp_api_id` (meta)     | Unique identifier for matching     |
| `firstName`      | `mp_first_name` (ACF)   | First name                         |
| `lastName`       | `mp_last_name` (ACF)    | Last name                          |
| `firstLastName`  | Post Title              | Full name (used as post title)     |
| `club`           | `mp_party` (ACF)        | Political party                    |
| `districtName`   | `mp_constituency` (ACF) | Electoral constituency             |
| `birthDate`      | `mp_birthdate` (ACF)    | Date of birth                      |
| `email`          | `mp_contacts` (ACF)     | Contact information                |
| `educationLevel` | `mp_education` (ACF)    | Education details                  |
| `photo`          | Featured Image          | MP photo (downloaded and attached) |

Any unmapped fields are stored in `mp_extra_json` for reference.

## Customization

### Templates

You can override plugin templates by copying them to your theme:

```
your-theme/
  mp-directory-card.php        (overrides templates/parts/mp-card.php)
  mp-directory-meta-table.php  (overrides templates/parts/mp-meta-table.php)
```

Or use standard WordPress template hierarchy:

```
your-theme/
  archive-mp.php  (overrides plugin's archive template)
  single-mp.php   (overrides plugin's single template)
```

### Styling

The plugin includes minimal, clean CSS. You can:

1. Disable plugin styles and write your own
2. Override specific CSS rules in your theme
3. Use the plugin's CSS classes as hooks for custom styling

### Hooks & Filters

**Actions:**

- `mp_directory_before_import` - Runs before import starts
- `mp_directory_after_import` - Runs after import completes
- `mp_directory_cron_settings_changed` - Triggered when cron settings change

**Filters:**

- `mp_directory_api_request_args` - Modify API request arguments
- `mp_directory_import_post_data` - Modify post data before saving

## Development

### File Structure

```
mp-directory/
├── mp-directory.php          # Main plugin file
├── includes/
│   ├── class-cpt.php         # Custom post type registration
│   ├── class-acf.php         # ACF field groups
│   ├── class-settings.php    # Settings page
│   ├── class-rest.php        # API client
│   ├── class-importer.php    # Import logic
│   ├── class-cron.php        # Cron scheduling
│   ├── class-assets.php      # Asset management
│   └── helpers.php           # Helper functions
├── admin/
│   └── views/
│       ├── settings-page.php
│       └── importer-preview.php
├── templates/
│   ├── archive-mp.php
│   ├── single-mp.php
│   └── parts/
│       ├── mp-card.php
│       └── mp-meta-table.php
├── assets/
│   ├── css/
│   │   ├── frontend.css
│   │   └── admin.css
│   └── js/
│       ├── frontend.js
│       └── admin.js
└── languages/
    └── mp-directory.pot
```

### Coding Standards

- Follows WordPress Coding Standards
- PHP 8.1+ compatible
- Namespaced classes (`MP_Directory\*`)
- Full internationalization support
- Proper escaping and sanitization

## Troubleshooting

### Import Fails

1. Check API URL in settings
2. Test API connection using the "Test Connection" button
3. Check WordPress error log for detailed messages
4. Ensure API is returning valid JSON

### No MPs Displayed

1. Verify import completed successfully
2. Check that MP post type exists (MP Directory menu in admin)
3. Visit Settings → Permalinks and click "Save" to flush rewrite rules

### ACF Fields Not Showing

1. Ensure Advanced Custom Fields plugin is installed and activated
2. The plugin will show an admin notice if ACF is not available
3. Basic functionality works without ACF, but with limited fields

### Scheduled Import Not Running

1. Ensure WP-Cron is enabled on your server
2. Check "Enable Scheduled Import" is checked in settings
3. Use a plugin like WP Crontrol to verify the scheduled event exists

## Performance

- **Batch Processing**: Large imports are split into manageable batches
- **Caching**: Preview data is cached to reduce API calls
- **Timeouts**: 15-second timeout with automatic retry for failed requests
- **Exponential Backoff**: Rate limiting and error handling to prevent API abuse

## Security

- Nonce verification on all AJAX requests
- Capability checks (`manage_options`) for admin functions
- Sanitization of all user input
- Escaped output in all templates
- Prepared database queries

## Support

For issues, feature requests, or contributions:

- Check the documentation above
- Review WordPress debug log for errors
- Ensure all requirements are met

## Changelog

### 1.0.0 (2025-10-16)

- Initial release
- Custom post type for MPs
- ACF field integration
- Configurable API client
- Manual and scheduled imports
- Preview system with caching
- Responsive frontend templates
- Search and filter functionality
- Comprehensive error handling

## License

GPL v2 or later

## Credits

Developed for displaying Members of Parliament data with WordPress.
