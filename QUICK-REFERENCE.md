# MP Directory - Quick Reference Card

## 🎯 Quick Start (5 Minutes)

### 1️⃣ Activate Plugin

WordPress Admin → **Plugins** → Find "MP Directory" → **Activate**

### 2️⃣ Configure API

**MP Directory** → **Settings** → Enter API URL:

```
https://api.sejm.gov.pl/sejm/term10/MP
```

→ **Save Settings** → **Test Connection** ✓

### 3️⃣ Import Data

**MP Directory** → **Import** → **Load Preview** → **Start Import** → Wait 3-5 min

### 4️⃣ View Results

Visit: **yoursite.com/mp/** 🎉

---

## 📍 Key Pages

| Page      | URL                                            | Purpose                |
| --------- | ---------------------------------------------- | ---------------------- |
| Settings  | `/wp-admin/admin.php?page=mp-directory`        | Configure API & import |
| Import    | `/wp-admin/admin.php?page=mp-directory-import` | Run manual import      |
| All MPs   | `/wp-admin/edit.php?post_type=mp`              | Manage MPs             |
| Archive   | `/mp/`                                         | Frontend MP list       |
| Single MP | `/mp/[name]/`                                  | Individual MP page     |

---

## ⚙️ Settings Explained

### API Configuration

- **API Base URL**: Where to fetch MP data _(required)_
- **API Key**: Authentication token _(optional)_

### Import Settings

- **Preview Cache TTL**: How long to cache preview (default: 20 min)
- **Import Batch Size**: MPs per batch (default: 100)

### Scheduled Import

- **Enable**: Turn on automatic imports
- **Frequency**: hourly / twice daily / daily

---

## 🔄 Import Workflow

```
Load Preview → Review Data → Start Import → Complete ✓
   (10 sec)      (1 min)       (3-5 min)
```

**Preview**: Shows first 20 MPs (cached for TTL minutes)
**Import**: Processes all MPs in batches, shows progress bar

---

## 🎨 Frontend Features

### Archive Page (/mp/)

- ✅ Search by name
- ✅ Filter by party
- ✅ Filter by constituency
- ✅ Responsive grid layout
- ✅ Pagination

### Single MP Page

- ✅ Photo & name
- ✅ Party & constituency
- ✅ Birth date & education
- ✅ Biography
- ✅ Contact information
- ✅ Social media links

---

## 🛠️ Common Tasks

### Manually Update One MP

1. **MP Directory** → **All MPs**
2. Click on MP name
3. Edit fields
4. **Update**

### Re-import All MPs

1. **MP Directory** → **Import**
2. **Start Import**
3. Existing MPs will be updated, new ones added

### Schedule Daily Import

1. **MP Directory** → **Settings**
2. Check **Enable Scheduled Import**
3. Select **Daily**
4. **Save Settings**

### Clear Cache

1. **MP Directory** → **Import**
2. **Refresh Preview** (forces fresh API call)

---

## 🐛 Troubleshooting

### "No API URL configured"

→ Go to Settings, enter API Base URL, Save

### "Preview failed"

→ Click **Test Connection** in Settings
→ Check API URL is correct

### "Import stuck"

→ Reduce **Import Batch Size** to 50
→ Try again

### "404 on /mp/ page"

→ Go to **Settings → Permalinks**
→ Click **Save Changes**

### "No images showing"

→ Check image URLs in API response
→ Verify uploads folder is writable

---

## 📊 Expected Behavior

### First Import (Polish Sejm API)

- **MPs imported**: ~460
- **Time**: 3-5 minutes
- **Images**: ~95% with photos
- **Result**: Ready to view on frontend

### Scheduled Import

- **Frequency**: Once per day (if enabled)
- **Duration**: 3-5 minutes
- **Updates**: Existing MPs refreshed
- **New**: Any new MPs added

---

## 🎯 Best Practices

✅ **DO**:

- Test API connection before importing
- Use preview to verify data format
- Enable scheduled import for auto-updates
- Flush permalinks after plugin activation

❌ **DON'T**:

- Import multiple times simultaneously
- Change API URL during active import
- Disable ACF plugin (if using advanced fields)
- Edit `_mp_api_id` meta field manually

---

## 📱 Mobile Compatibility

Plugin is fully responsive:

- ✅ Mobile-first CSS
- ✅ Touch-friendly filters
- ✅ Readable single pages
- ✅ Fast loading

Test on:

- iPhone Safari
- Android Chrome
- Tablet (iPad)

---

## 🔐 Security Notes

Plugin is secure by default:

- ✅ Only admins can import
- ✅ All data sanitized
- ✅ No direct file access
- ✅ Nonce protection on AJAX

No additional security plugins needed.

---

## 📈 Performance

### Archive Page

- Load time: < 2 seconds
- MPs per page: 12-16
- Filters: Instant (no reload)

### Import Speed

- 100 MPs: ~1 minute
- 500 MPs: ~5 minutes
- 1000 MPs: ~10 minutes

### Caching

- Preview cache: 20 minutes (configurable)
- No frontend caching (use WP caching plugin)

---

## 🌍 Internationalization

Plugin supports translation:

1. Install **Loco Translate** plugin
2. Translate MP Directory to your language
3. Save and activate

All text is translatable including:

- Admin interface
- Frontend labels
- Error messages
- Form placeholders

---

## 📞 Need Help?

### Check These First

1. **README.md** - Full documentation
2. **INSTALLATION.md** - Detailed setup guide
3. **WordPress debug.log** - Error messages

### Enable Debug Mode

Edit `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check logs at: `wp-content/debug.log`

---

## 🎓 Learn More

### Customization

- Override templates in your theme
- Add custom CSS for styling
- Use hooks/filters for modifications

### Documentation Files

- `README.md` - Feature overview
- `INSTALLATION.md` - Setup guide
- `TECHNICAL-SUMMARY.md` - Developer docs
- `DEPLOYMENT-CHECKLIST.md` - Pre-launch checklist

---

## ✅ Quick Checklist

**After Installation:**

- [ ] Plugin activated
- [ ] ACF plugin installed
- [ ] API URL configured
- [ ] Connection tested
- [ ] First import completed
- [ ] Archive page viewed
- [ ] Single MP page viewed
- [ ] Scheduled import enabled

**You're all set! 🎉**

---

## 💡 Pro Tips

1. **Preview First**: Always load preview before importing
2. **Schedule Wisely**: Daily import at 3 AM is ideal
3. **Monitor Logs**: Check `debug.log` after first import
4. **Backup First**: Backup database before major imports
5. **Test Locally**: Test on staging before production

---

## 🆘 Emergency Fixes

### Plugin Won't Activate

```php
// Check PHP version in phpinfo()
php -v  // Must be 8.1+
```

### Import Failed Completely

```php
// Delete all MPs and re-import
// MP Directory → All MPs → Bulk Actions → Move to Trash
```

### Frontend Not Working

```php
// Flush rewrite rules
// Settings → Permalinks → Save Changes
```

### Scheduled Import Not Running

```php
// Install "WP Crontrol" plugin
// Check if event "mp_directory_cron_import" exists
```

---

**Version**: 1.0.0  
**Last Updated**: 2025-10-16  
**Support**: See README.md

---

_Happy MP managing! 🏛️_
