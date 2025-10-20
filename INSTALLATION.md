# MP Directory Plugin - Installation & Usage Guide

## 🚀 Quick Start

### 1. Installation

```bash
# Navigate to your WordPress plugins directory
cd wp-content/plugins/

# The plugin is already in: wp-content/plugins/mp-directory/
```

### 2. Activation

1. Go to WordPress Admin → **Plugins**
2. Find **MP Directory** in the plugin list
3. Click **Activate**

### 3. Install ACF (Recommended)

1. Go to **Plugins → Add New**
2. Search for "Advanced Custom Fields"
3. Install and activate **Advanced Custom Fields** (free version)

> **Note**: The plugin works without ACF, but you'll have limited field functionality.

### 4. Configure API Settings

1. Navigate to **MP Directory → Settings**
2. Enter your **API Base URL**:

   ```
   https://api.sejm.gov.pl/sejm/term10/MP
   ```

   (or your custom API endpoint)

3. Add **API Key** if required (optional)
4. Configure import settings:

   - **Preview Cache TTL**: 20 minutes (default)
   - **Import Batch Size**: 100 (default)

5. Click **Save Settings**

6. Test your API connection:
   - Click **Test Connection** button
   - Wait for success confirmation

### 5. Import MPs

1. Go to **MP Directory → Import**
2. Click **Load Preview**
   - This fetches the first 20 MPs to verify your API works
   - Review the preview table
3. Click **Start Import**
   - Wait for the import to complete
   - Progress bar shows real-time status
4. Once complete, you'll see a success message

### 6. View Your MPs

**Frontend:**

- Archive: `https://yoursite.com/mp/`
- Single MP: `https://yoursite.com/mp/[mp-name]/`

**Admin:**

- Go to **MP Directory → All MPs** to edit MPs manually

---

## 📊 API Configuration Examples

### Example 1: Polish Sejm API (10th Term)

```
API Base URL: https://api.sejm.gov.pl/sejm/term10/MP
API Key: (leave empty)
```

### Example 2: Custom API with Pagination

If your API uses different pagination parameters, the plugin automatically handles:

- `?page=1&per_page=100`

### Example 3: API with Authentication

```
API Base URL: https://api.example.com/parliament/members
API Key: your-secret-api-key-here
```

The plugin will send the key as a Bearer token:

```
Authorization: Bearer your-secret-api-key-here
```

---

## 🔄 Scheduled Imports (Cron)

### Enable Automatic Updates

1. Go to **MP Directory → Settings**
2. Scroll to **Scheduled Import** section
3. Check **Enable Scheduled Import**
4. Select **Import Frequency**:
   - **Hourly**: Updates every hour
   - **Twice Daily**: Morning and evening
   - **Daily**: Once per day
5. Click **Save Settings**

### Monitor Scheduled Imports

The settings page shows:

```
Next automatic import: In 4 hours
```

### Disable Scheduled Imports

Simply uncheck **Enable Scheduled Import** and save.

---

## 🎨 Frontend Customization

### Default URLs

- **Archive**: `/mp/`
- **Single MP**: `/mp/john-smith/`

### Changing the Slug

To change `/mp/` to something else (e.g., `/members/`):

1. Edit `includes/class-cpt.php`
2. Find line: `'rewrite' => array( 'slug' => 'mp' )`
3. Change to: `'rewrite' => array( 'slug' => 'members' )`
4. Go to **Settings → Permalinks** and click **Save Changes**

### Override Templates in Your Theme

Copy plugin templates to your theme to customize:

```
your-theme/
├── archive-mp.php              # Override archive
├── single-mp.php               # Override single page
├── mp-directory-card.php       # Override MP card
└── mp-directory-meta-table.php # Override meta table
```

### Custom CSS

Add custom styles in your theme's `style.css`:

```css
/* Change party badge color */
.mp-party-badge {
  background: #dc3545 !important;
}

/* Customize MP card hover effect */
.mp-card:hover {
  transform: scale(1.05);
}

/* Modify archive grid */
.mp-grid {
  grid-template-columns: repeat(4, 1fr);
}
```

---

## 🔍 Filtering & Search

### Archive Page Filters

Users can filter MPs by:

- **Search**: Search by name
- **Party**: Dropdown of all parties
- **Constituency**: Dropdown of all constituencies

### URL Parameters

Direct link to filtered results:

```
/mp/?mp_party=Konfederacja
/mp/?mp_constituency=Warszawa
/mp/?s=John
```

### Programmatic Filtering

```php
// Get all MPs from a specific party
$args = array(
    'post_type' => 'mp',
    'meta_query' => array(
        array(
            'key' => 'mp_party',
            'value' => 'Konfederacja'
        )
    )
);
$mps = new WP_Query($args);
```

---

## 🛠️ Advanced Usage

### Manual Import via Code

```php
$importer = new MP_Directory\Importer();
$result = $importer->run_import(0, 0);

if (!is_wp_error($result)) {
    echo "Imported: " . $result['imported'];
    echo "Updated: " . $result['updated'];
}
```

### Get MP Data

```php
// Get ACF field value
$party = MP_Directory\mp_directory_get_field('mp_party', $post_id);

// Get all contacts
$contacts = MP_Directory\mp_directory_get_field('mp_contacts', $post_id);
```

### Custom Queries

```php
// Get all parties
$parties = MP_Directory\mp_directory_get_parties();

// Get all constituencies
$constituencies = MP_Directory\mp_directory_get_constituencies();
```

---

## 🐛 Troubleshooting

### Issue: "No MPs found in API response"

**Solution:**

1. Test API URL in browser to verify it returns JSON
2. Check API format matches expected structure
3. Review WordPress debug log for errors

### Issue: Import Fails Halfway

**Solution:**

1. Increase PHP `max_execution_time` in `php.ini`:
   ```
   max_execution_time = 300
   ```
2. Reduce **Import Batch Size** to 50 or lower
3. Check server error logs

### Issue: Featured Images Not Importing

**Solution:**

1. Ensure `allow_url_fopen` is enabled in PHP
2. Check image URLs are accessible (try opening in browser)
3. Verify WordPress uploads directory is writable

### Issue: Scheduled Import Not Running

**Solution:**

1. Verify WP-Cron is enabled (not disabled in `wp-config.php`)
2. Install "WP Crontrol" plugin to check scheduled events
3. Manually trigger: **Tools → Cron Events → Run Now**

### Issue: Filters Don't Work

**Solution:**

1. Go to **Settings → Permalinks**
2. Click **Save Changes** (flushes rewrite rules)
3. Try filtering again

### Issue: ACF Fields Not Showing

**Solution:**

1. Ensure ACF plugin is activated
2. Deactivate and reactivate MP Directory plugin
3. Check ACF field group is registered: **Custom Fields → Field Groups**

---

## 📈 Performance Tips

### Large Datasets (1000+ MPs)

1. **Increase Import Batch Size**: Set to 200-300 for faster imports
2. **Enable Object Caching**: Use Redis or Memcached
3. **Optimize Images**: Use image optimization plugin

### Reduce Server Load

1. **Increase Preview Cache TTL**: Set to 60-120 minutes
2. **Schedule Imports During Off-Peak**: Use "Daily" at 3 AM
3. **Disable Revisions** (optional):
   ```php
   add_filter('wp_revisions_to_keep', function($num, $post) {
       if ('mp' === $post->post_type) {
           return 5; // Keep only 5 revisions
       }
       return $num;
   }, 10, 2);
   ```

---

## 🔒 Security Notes

- ✅ All AJAX requests are nonce-verified
- ✅ Capability checks ensure only admins can import
- ✅ Input sanitization and output escaping throughout
- ✅ API key stored securely in WordPress options
- ✅ No direct file access allowed

---

## 📞 Support Checklist

Before seeking help:

- [ ] WordPress version 6.5+?
- [ ] PHP version 8.1+?
- [ ] ACF plugin installed?
- [ ] API URL configured and tested?
- [ ] Permalinks flushed (Settings → Permalinks → Save)?
- [ ] WP_DEBUG enabled to see errors?
- [ ] Checked WordPress error log?

---

## 🎯 Next Steps

1. **Customize Templates**: Override default templates in your theme
2. **Add Custom Fields**: Extend ACF field group with additional fields
3. **Create Shortcodes**: Build custom shortcodes for embedding MPs
4. **Integrate with Page Builders**: Use with Elementor, Beaver Builder, etc.
5. **Add REST API Endpoints**: Expose MP data via WordPress REST API

---

## 📝 Example API Response Format

The plugin expects this JSON structure:

```json
[
  {
    "id": 241,
    "firstName": "Sławomir",
    "lastName": "Mentzen",
    "firstLastName": "Sławomir Mentzen",
    "club": "Konfederacja",
    "districtName": "Warszawa",
    "districtNum": 19,
    "birthDate": "1986-11-20",
    "birthLocation": "Toruń",
    "educationLevel": "wyższe",
    "email": "Slawomir.Mentzen@sejm.pl",
    "profession": "doradca podatkowy",
    "numberOfVotes": 101269,
    "voivodeship": "mazowieckie",
    "active": true
  }
]
```

Any fields not explicitly mapped are stored in `mp_extra_json` for reference.

---

**Enjoy your MP Directory! 🎉**
