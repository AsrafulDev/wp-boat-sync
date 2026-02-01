# WP Boat Sync

Sync Boats.com inventory into WordPress with automated syncing, image downloads, and beautiful frontend displays.

## Features

- **Automated Sync** - Hourly or daily sync from Boats.com API
- **Queue System** - Async processing with retry logic and stale job recovery
- **Smart Sync** - Skips unchanged boats based on modification date and data hash
- **Image Downloads** - Automatically downloads and attaches boat images to WordPress
- **Sold Boat Handling** - Sold boats stay published with "Sold" badge (not drafted)
- **Deduplication** - Automatically removes duplicate boat posts
- **Shortcodes** - Flexible shortcodes for grids, singles, galleries, and more
- **Filter System** - AJAX-powered filtering with range sliders for price, year, length
- **Responsive Design** - Mobile-friendly BoatTrader-style UI

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Boats.com API key and Party ID

## Installation

1. Upload the `wp-boat-sync` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Boat Sync > Settings** and enter your API credentials
4. Run a manual sync or wait for scheduled sync

## Settings

| Setting | Description |
|---------|-------------|
| API Key | Your Boats.com API key |
| Party ID | Your Boats.com Party ID |
| Auto Sync | Off / Hourly / Daily |
| Rows per Page | Number of boats to fetch per API call (1-100) |
| Download Images | Enable/disable image downloading |
| Max Images per Boat | Maximum images to download per boat (1-200) |
| Treat Missing as Sold | Mark boats not in API response as sold |

## Shortcodes

### Boat Grid
Display multiple boats in a responsive grid layout.

```
[wpbs_boat_grid posts_per_page="12" columns="3" filter="true" filter_position="top" orderby="date"]
```

**Attributes:**
| Attribute | Default | Description |
|-----------|---------|-------------|
| `posts_per_page` | 12 | Number of boats per page |
| `columns` | 3 | Grid columns (1-6) |
| `filter` | false | Show AJAX filter bar |
| `filter_position` | top | Filter position: top, left, right |
| `orderby` | date | Sort: date, price_low, price_high, year |

### Single Boat
Full single boat display with all sections.

```
[wpbs_boat_single id="9963690"]
[wpbs_boat_single post_id="123"]
```

### Gallery
Full image gallery with lightbox.

```
[wpbs_gallery id="9963690"]
```

### Image Slider
Compact image slider with navigation.

```
[wpbs_slider id="9963690" max="4"]
```

### Accordion Details
Collapsible boat specifications.

```
[wpbs_accordion id="9963690"]
```

### Quick Specs
Key specifications in grid format.

```
[wpbs_quick_specs id="9963690"]
```

### Price Display
```
[wpbs_price id="9963690"]
[wpbs_price_card id="9963690"]
```

### Dealer Info
```
[wpbs_dealer_card id="9963690"]
[wpbs_more_boats id="9963690" limit="4"]
```

### Individual Sections
```
[wpbs_tab_description id="9963690"]
[wpbs_tab_measurements id="9963690"]
[wpbs_tab_propulsion id="9963690"]
[wpbs_tab_features id="9963690"]
[wpbs_location id="9963690"]
[wpbs_overview id="9963690"]
```

## Filter Bar

The filter bar provides AJAX-powered filtering with:

- **Category** dropdown (boat types)
- **Price Range** slider ($0 - $5,000,000)
- **Year Range** slider (1900 - current year)
- **Length Range** slider (0 - 200 ft)
- **Sort** dropdown
- **Auto-load** after 1 second debounce on any filter change

### Filter Positions

| Position | Description |
|----------|-------------|
| `top` | Filter bar above grid (default) |
| `left` | Filter sidebar on left |
| `right` | Filter sidebar on right |

## Sync Process

### Job Types

| Type | Description |
|------|-------------|
| `fetch_page` | Fetches a page of boats from API |
| `sync_boat` | Syncs individual boat to WordPress |
| `reconcile` | Marks missing boats as sold |

### Smart Sync

The sync intelligently skips updates when data hasn't changed:

1. Compares `LastModificationDate` with stored value
2. Compares MD5 hash of boat data with stored hash
3. If unchanged → Skip update (faster sync)
4. If changed → Full update of post, meta, images

### Queue States

| Status | Description |
|--------|-------------|
| `pending` | Waiting to be processed |
| `processing` | Currently being worked on |
| `done` | Successfully completed |
| `failed` | Failed after 3 retry attempts |

### Automatic Cleanup

- Completed jobs (`done`/`failed`) older than 2 days are auto-deleted
- Stale processing jobs (stuck > 10 minutes) are auto-recovered

## Sold Boat Handling

When a boat's `SalesStatus` is not "Active":

1. Boat stays **Published** (visible to users)
2. `_wpbs_is_sold` meta flag set to "1"
3. "Sold" term assigned to `boat_status` taxonomy
4. Red "SOLD" badge displayed on grid cards
5. If boat becomes active again → Flags removed

## Data Fields

The plugin syncs 80+ fields from the Boats.com API:

| Category | Fields |
|----------|--------|
| **Pricing** | price, price_amount, original_price, norm_price |
| **Identity** | document_id, make, model, model_year |
| **Dimensions** | length_overall, beam, dry_weight, displacement |
| **Engine** | engine_summary, total_engine_power, fuel_type |
| **Location** | city, state, country, location |
| **Dealer** | dealer_name, office_phone, office_email |
| **Media** | gallery_attachment_ids, embedded_video_urls |

All fields are prefixed with `wpbs_` in post meta.

## Taxonomies

### boat_status
Used for sold/available status filtering.

- **Sold** - Boats marked as sold/inactive
- **Available** - Active boats (default)

## Templates

The plugin includes default templates for:

- `archive-boats.php` - Boat listing page
- `single-boats.php` - Individual boat page

Templates can be overridden by copying to your theme folder.

## Admin Pages

| Page | Description |
|------|-------------|
| **Dashboard** | Sync overview, statistics, charts |
| **Boats** | List of all synced boats |
| **Queue** | Pending/processing/failed jobs |
| **Settings** | API credentials, sync options |
| **Shortcode Builder** | Visual shortcode generator |

## Hooks & Filters

### Actions
```php
// After a boat is synced
do_action('wpbs_after_boat_sync', $post_id, $document_id, $data);

// Before sync starts
do_action('wpbs_before_full_sync', $run_id);
```

### Filters
```php
// Modify boat data before saving
add_filter('wpbs_boat_data', function($data, $document_id) {
    return $data;
}, 10, 2);

// Customize price display
add_filter('wpbs_price_display', function($display, $price_data) {
    return $display;
}, 10, 2);
```

## Database Tables

| Table | Purpose |
|-------|---------|
| `wp_wpbs_boats` | Boat tracking (document_id, post_id, sync status) |
| `wp_wpbs_images` | Image tracking (document_id, url, attachment_id) |
| `wp_wpbs_queue` | Job queue (type, payload, status, attempts) |

## Cron Schedules

| Hook | Frequency | Purpose |
|------|-----------|---------|
| `wpbs_cron_auto_sync` | Hourly/Daily | Trigger full sync |
| `wpbs_process_queue` | On-demand | Process queue batch |
| `wpbs_cleanup_queue` | Daily | Delete old completed jobs |

## Changelog

### 0.1.0
- Initial release
- Boats.com API integration
- Async queue-based sync
- Smart sync (skip unchanged)
- Sold boat handling with tags
- AJAX filter system with range sliders
- Shortcode builder
- Responsive grid layouts

## Support

For issues or feature requests, please contact the plugin author.

## License

GPL v2 or later
