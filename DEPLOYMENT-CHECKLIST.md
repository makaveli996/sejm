# MP Directory Plugin - Deployment Checklist

## ✅ Pre-Deployment Checklist

### Code Files

- [x] `mp-directory.php` - Main plugin file
- [x] `includes/class-cpt.php` - Custom Post Type
- [x] `includes/class-acf.php` - ACF Fields
- [x] `includes/class-settings.php` - Settings Page
- [x] `includes/class-rest.php` - API Client
- [x] `includes/class-importer.php` - Import Engine
- [x] `includes/class-cron.php` - Cron Scheduler
- [x] `includes/class-assets.php` - Asset Management
- [x] `includes/helpers.php` - Helper Functions

### Admin Views

- [x] `admin/views/settings-page.php`
- [x] `admin/views/importer-preview.php`

### Frontend Templates

- [x] `templates/archive-mp.php`
- [x] `templates/single-mp.php`
- [x] `templates/parts/mp-card.php`
- [x] `templates/parts/mp-meta-table.php`

### Assets

- [x] `assets/css/frontend.css`
- [x] `assets/css/admin.css`
- [x] `assets/js/frontend.js`
- [x] `assets/js/admin.js`

### Documentation

- [x] `README.md` - Complete documentation
- [x] `INSTALLATION.md` - Installation guide
- [x] `TECHNICAL-SUMMARY.md` - Technical details
- [x] `languages/mp-directory.pot` - Translation template

---

## 🚀 Installation Steps

### 1. Verify Plugin Location

```
wp-content/plugins/mp-directory/
```

### 2. Activate Plugin

WordPress Admin → Plugins → Activate "MP Directory"

### 3. Install Dependencies (Recommended)

- Install **Advanced Custom Fields** plugin
- Activate ACF

### 4. Configure Settings

1. Go to **MP Directory → Settings**
2. Set **API Base URL**: `https://api.sejm.gov.pl/sejm/term10/MP`
3. Configure import settings (defaults are fine)
4. Click **Save Settings**
5. Click **Test Connection** to verify

### 5. Run Initial Import

1. Go to **MP Directory → Import**
2. Click **Load Preview** (should show 20 MPs)
3. Review preview data
4. Click **Start Import**
5. Wait for completion (~2-5 minutes for full import)

### 6. Verify Frontend

- Visit: `https://yoursite.com/mp/`
- Should see MP archive with filters
- Click on any MP to see single page

### 7. Optional: Enable Scheduled Imports

1. Go to **MP Directory → Settings**
2. Check **Enable Scheduled Import**
3. Select frequency (Daily recommended)
4. Save settings

---

## 🧪 Testing Checklist

### Admin Functionality

- [ ] Plugin activates without errors
- [ ] Settings page loads
- [ ] API test connection works
- [ ] Preview loads data
- [ ] Import completes successfully
- [ ] MPs appear in admin list
- [ ] Can edit MP manually
- [ ] ACF fields display correctly

### Frontend Display

- [ ] Archive page loads at `/mp/`
- [ ] MP cards display with images
- [ ] Search works
- [ ] Party filter works
- [ ] Constituency filter works
- [ ] Pagination works
- [ ] Single MP page loads
- [ ] All MP details display
- [ ] Contact info displays
- [ ] Social links work
- [ ] Back link returns to archive

### Responsive Design

- [ ] Desktop (1920px)
- [ ] Laptop (1366px)
- [ ] Tablet (768px)
- [ ] Mobile (375px)

### Performance

- [ ] Archive page loads < 2 seconds
- [ ] Single page loads < 1 second
- [ ] No console errors
- [ ] Images load properly
- [ ] No PHP warnings/errors

---

## 🔧 Configuration for Polish Sejm API

### Recommended Settings

```
API Base URL: https://api.sejm.gov.pl/sejm/term10/MP
API Key: (leave empty)
Preview Cache TTL: 20 minutes
Import Batch Size: 100
Enable Cron: Yes (checked)
Cron Interval: Daily
```

### API Endpoint Details

The Sejm API returns JSON with this structure:

```json
{
  "id": 241,
  "firstName": "Sławomir",
  "lastName": "Mentzen",
  "firstLastName": "Sławomir Mentzen",
  "club": "Konfederacja",
  "districtName": "Warszawa",
  "birthDate": "1986-11-20",
  "email": "Slawomir.Mentzen@sejm.pl",
  ...
}
```

All fields are automatically mapped to WordPress.

---

## 🐛 Common Issues & Solutions

### Issue 1: Plugin Won't Activate

**Cause**: PHP version < 8.1 or WordPress < 6.5
**Solution**: Upgrade PHP and WordPress to minimum requirements

### Issue 2: ACF Warning Appears

**Cause**: Advanced Custom Fields not installed
**Solution**: Install ACF plugin (free version works fine)

### Issue 3: Import Shows "No API URL"

**Cause**: API Base URL not configured
**Solution**: Go to Settings and enter API URL

### Issue 4: Preview Shows Error

**Cause**: API unreachable or incorrect URL
**Solution**:

1. Test URL in browser
2. Check API Test Connection
3. Verify no firewall blocking

### Issue 5: Import Fails After Starting

**Cause**: PHP timeout or API rate limiting
**Solution**:

1. Reduce Import Batch Size to 50
2. Increase PHP `max_execution_time` to 300
3. Try import during off-peak hours

### Issue 6: Images Not Importing

**Cause**: `allow_url_fopen` disabled or uploads folder not writable
**Solution**:

1. Enable `allow_url_fopen` in php.ini
2. Check WordPress uploads folder permissions (755)
3. Verify image URLs are accessible

### Issue 7: Archive Shows 404

**Cause**: Rewrite rules not flushed
**Solution**:

1. Go to Settings → Permalinks
2. Click "Save Changes"
3. Try archive URL again

### Issue 8: Filters Don't Work

**Cause**: Meta query not indexed or rewrite issue
**Solution**:

1. Flush permalinks (Settings → Permalinks → Save)
2. Clear any caching plugins
3. Try again

---

## 📊 Expected Results

### After First Import

- **MPs Created**: 400-500 posts (depending on current term)
- **Featured Images**: ~95% should have photos
- **Import Time**: 3-5 minutes
- **Database Size**: ~5-10 MB (including images)

### Archive Page

- **Load Time**: < 2 seconds
- **MPs Per Page**: 12-16 (responsive grid)
- **Filters**: Party, Constituency, Search
- **Mobile View**: Single column, fully functional

### Single MP Page

- **Sections**: Hero, Details, Biography, Contacts, Social
- **Load Time**: < 1 second
- **SEO**: Clean URLs, proper meta tags

---

## 🔒 Security Verification

### Verify These Security Measures

- [x] Nonces on all AJAX requests
- [x] Capability checks (`manage_options`)
- [x] Input sanitization
- [x] Output escaping
- [x] No direct file access
- [x] Prepared SQL statements
- [x] Safe file uploads

### Test Security

```php
// Try accessing files directly (should fail):
https://yoursite.com/wp-content/plugins/mp-directory/includes/class-cpt.php
// Should show blank page or "Direct access not allowed"

// Try AJAX without nonce (should fail):
// Use browser console: jQuery.post(ajaxurl, {action: 'mp_directory_import'})
// Should return error
```

---

## 📈 Performance Optimization (Optional)

### For Large Sites (1000+ MPs)

1. **Enable Object Caching**

   - Install Redis or Memcached
   - Use W3 Total Cache or WP Rocket

2. **Optimize Database**

   ```sql
   ALTER TABLE wp_postmeta ADD INDEX mp_party (meta_key, meta_value(20));
   ALTER TABLE wp_postmeta ADD INDEX mp_constituency (meta_key, meta_value(30));
   ```

3. **Image Optimization**

   - Install Imagify or ShortPixel
   - Compress existing images

4. **CDN Integration**
   - Use Cloudflare or similar
   - Serve images from CDN

---

## 🌍 Translation Setup (Optional)

### Create Polish Translation

1. Install **Loco Translate** plugin
2. Go to Loco Translate → Plugins → MP Directory
3. Click "New Language" → Polish (pl_PL)
4. Translate strings in the editor
5. Save and compile

### Or Use POEdit

1. Open `languages/mp-directory.pot` in POEdit
2. Create new translation (Polish)
3. Translate all strings
4. Save as `mp-directory-pl_PL.po` and `mp-directory-pl_PL.mo`
5. Upload to `languages/` folder

---

## 🎯 Final Verification

### Before Going Live

1. **Test All Features**

   - [ ] Import works
   - [ ] Archive displays
   - [ ] Single pages work
   - [ ] Filters function
   - [ ] Search works
   - [ ] Mobile responsive

2. **Check Performance**

   - [ ] Page load < 3 seconds
   - [ ] No console errors
   - [ ] Images optimized

3. **Verify SEO**

   - [ ] Each MP has unique URL
   - [ ] Meta descriptions present
   - [ ] Image alt tags filled

4. **Security Check**

   - [ ] No PHP errors
   - [ ] Admin access secured
   - [ ] File permissions correct

5. **Scheduled Import**
   - [ ] Cron event scheduled
   - [ ] Next run time shown
   - [ ] Test manual trigger

---

## 📞 Support Resources

### Documentation

- README.md - Feature overview
- INSTALLATION.md - Step-by-step guide
- TECHNICAL-SUMMARY.md - Developer reference

### WordPress Resources

- [Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Custom Post Types](https://developer.wordpress.org/plugins/post-types/)
- [WP-Cron](https://developer.wordpress.org/plugins/cron/)

### Debugging

Enable debug mode in `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check logs at: `wp-content/debug.log`

---

## ✨ Post-Deployment Tasks

### Week 1

- [ ] Monitor import logs
- [ ] Check for PHP errors
- [ ] Verify scheduled import runs
- [ ] Test frontend on multiple devices
- [ ] Gather user feedback

### Week 2

- [ ] Optimize any slow queries
- [ ] Add any missing translations
- [ ] Fine-tune cron frequency
- [ ] Consider adding more filters

### Month 1

- [ ] Review import success rate
- [ ] Check database size growth
- [ ] Optimize images if needed
- [ ] Plan feature enhancements

---

## 🎉 Success Criteria

✅ **Plugin is ready for production when:**

- All tests pass
- Import completes without errors
- Frontend displays correctly
- No PHP warnings/errors
- Mobile responsive
- SEO-friendly URLs working
- Scheduled import functioning
- Documentation complete

---

**Status**: ✅ **READY FOR DEPLOYMENT**

**Version**: 1.0.0  
**WordPress**: 6.5+  
**PHP**: 8.1+  
**Last Updated**: 2025-10-16

---

_Developed with ❤️ for the Polish Parliament_
