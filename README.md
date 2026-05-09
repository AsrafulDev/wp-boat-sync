# WP Boat Sync

Sync Boats.com inventory into WordPress with automated syncing, image downloads, and beautiful frontend displays via shortcodes and Elementor widgets.

## Features

### Sync & Import
- **Automated Sync** — Hourly or daily sync from Boats.com API
- **Queue System** — Async processing with retry logic and stale job recovery (processes 5 jobs per batch)
- **Smart Sync** — Skips unchanged boats based on modification date and data hash comparison
- **Image Downloads** — Automatically downloads and attaches boat images to WordPress media library
- **Sold Boat Handling** — Sold boats stay published with "Sold" badge instead of being drafted
- **Deduplication** — Automatically removes duplicate boat posts
- **WP-CLI Support** — Full CLI command suite for manual sync operations
- **Detailed Logging** — Comprehensive sync logs with run history

### Display & Frontend
- **12 Elementor Widgets** — Full suite of drag-and-drop boat display widgets
- **20+ Shortcodes** — Flexible shortcodes for grids, singles, galleries, tabs, and more
- **AJAX Filter System** — Real-time filtering with range sliders for price, year, and length
- **Responsive Design** — Mobile-friendly, BoatTrader-style card-based UI
- **Complete Style Controls** — Every widget has full Elementor style controls (colors, typography, spacing, borders, shadows)

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Boats.com API key and Party ID
- Elementor (free or pro) for Elementor widgets

## Installation

1. Upload the `wp-boat-sync` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Boat Sync > Settings** and enter your API credentials
4. Run a manual sync or wait for the scheduled cron sync

## Elementor Widgets

All widgets appear under the **Boat Elements** category in the Elementor panel. Each widget has full style controls for colors, typography, spacing, borders, shadows, and responsive settings.

### Boat Grid
Display multiple boats in a responsive card grid with optional AJAX filtering.

**Controls:** Posts per page, responsive columns (1–6), sort order, filter toggle with position (top/left/right), pagination toggle, condition badge toggle.

**Pre-Filters:** Category, Builder/Make, Location, Condition (new/used/sold), Featured only.

**Style Sections:** Grid, Card, Image, Condition Badge, Title, Price, Meta, Button, Filter (with labels, inputs, search button sub-sections), Sort Bar (with count text, label, dropdown sub-sections).

### Boat Gallery
Full image gallery with thumbnail strip, lightbox, previous/next navigation, video support, and condition badge overlay.

**Controls:** Boat ID, Post ID, lightbox toggle, condition badge toggle.

**Style Sections:** Main Image, Condition Badge, Navigation Buttons, Thumbnails, View Button.

### Quick Specs
Key boat specifications displayed in a grid of icon-labeled cards (Engine, Total Power, Engine Hours, Class, Length, Year, Model, Capacity).

**Style Sections:** Container, Heading, Spec Items, Labels, Values, Icons.

### Price Card
Complete pricing display with title, year/make/model, location, brand taxonomy links, formatted price, estimated monthly payment calculation, sold badge, and contact buttons (email + phone).

**Style Sections:** Header, Container, Title, Year/Make/Model, Location, Brand Links (normal/hover), Price, Label, Status Badge, Monthly Payment, Body, Sold Badge, Contact Button (normal/hover).

### Dealer Card
Dealer/dealership contact information with name, phone, email, location, and "View All Boats" link.

**Style Sections:** Container, Dealer Name (color, font size, font weight), Content/Link colors, Button (normal/hover states).

### Boat Tabs
Tabbed interface with Description, Measurements, Propulsion, and Features tabs. Uses bulk meta fetching for performance.

**Controls:** Boat ID, Post ID.

**Style Sections:** Tab Navigation, Tab Buttons (normal/hover/active), Tab Content.

### Boat Accordion
Collapsible details/specifications in an accordion layout using native `<details>` elements.

**Controls:** Boat ID, Post ID, Show/Hide toggles for all 6 sections (Description, Measurements, Propulsion, Features, More Details, Location), Default Open Section selector.

**Style Sections:** Card Title, Header (normal/hover/active), Icon, Content.

### More Boats
Random related boat listings grid excluding the current boat.

**Controls:** Boat ID, Post ID, number of boats (1–20), responsive columns.

**Style Sections:** Container, Heading, Card, Image, Card Title, Price, Meta Info, Button.

### Brand List
Alphabetical list of all boat brands (from the `brand` taxonomy) with optional post count.

**Controls:** Show count toggle, responsive columns (1–6).

**Style Sections:** Container, Heading, Brand Item, Brand Name (normal/hover), Count, Logo.

### Loan Calculator
Interactive boat loan payment calculator with real-time JavaScript calculation.

**Controls:** Boat ID, Post ID (auto-populates purchase price from boat).

**Style Sections:** Container, Title, Labels, Inputs, Calculate Button (normal/hover), Result Display.

### Boat Overview
Full boat description/content with title and optional "Sold" badge.

**Style Sections:** Container, Title, Content, Content Headings, Lists.

### Boat Breadcrumb
SEO-friendly breadcrumb navigation for boat archives, taxonomy pages, and single boat pages.

**Controls:** Separator character, Show Home toggle, Home text.

**Style Sections:** Container, Links (normal/hover), Current Page, Separator.

## Shortcodes

### Boat Grid
```
[wpbs_boat_grid posts_per_page="12" columns="3" filter="true" filter_position="top" orderby="date" pagination="yes"]
```

| Attribute | Default | Description |
|-----------|---------|-------------|
| `posts_per_page` | 12 | Number of boats per page |
| `columns` | 3 | Grid columns: single number or `desktop,tablet,mobile` (e.g. `4,2,1`) |
| `filter` | false | Show AJAX filter bar (`true`/`false`) |
| `filter_position` | top | Filter position: `top`, `left`, `right` |
| `orderby` | date | Sort order: `date`, `price_low`, `price_high`, `year` |
| `pagination` | yes | Show pagination (`yes`/`no`) |
| `category` | — | Pre-filter by boat category |
| `builder` | — | Pre-filter by builder/make |
| `location` | — | Pre-filter by location |
| `condition` | — | Pre-filter by condition: `new`, `used`, `sold` |
| `featured` | — | Show featured boats only (`true`/`false`) |
| `paged` | 1 | Current page number |

### Single Boat
```
[wpbs_boat_single id="9963690"]
[wpbs_boat_single post_id="123"]
```

### Gallery & Media
```
[wpbs_gallery id="9963690"]
[wpbs_slider id="9963690" max="4"]
```

| Attribute | Default | Description |
|-----------|---------|-------------|
| `id` | — | Boat document ID |
| `post_id` | — | WordPress post ID |
| `max` | — | Max images for slider |

### Price & Dealer
```
[wpbs_price id="9963690"]
[wpbs_price_card id="9963690"]
[wpbs_dealer_card id="9963690"]
[wpbs_more_boats id="9963690" limit="4"]
```

### Specifications & Details
```
[wpbs_accordion id="9963690"]
[wpbs_quick_specs id="9963690"]
[wpbs_tabs id="9963690"]
[wpbs_overview id="9963690"]
[wpbs_location id="9963690"]
```

### Individual Tab Sections
```
[wpbs_tab_description id="9963690"]
[wpbs_tab_measurements id="9963690"]
[wpbs_tab_propulsion id="9963690"]
[wpbs_tab_features id="9963690"]
```

### Brand List & Calculator
```
[wpbs_brand_list show_count="yes"]
[wpbs_loan_calculator id="9963690"]
```

### Breadcrumb
```
[wpbs_breadcrumb separator="›" show_home="yes" home_text="Home"]
```

## Filter System

The AJAX-powered filter bar supports:

| Filter | Type | Description |
|--------|------|-------------|
| Category | Dropdown | Filter by boat category |
| Builder/Make | Dropdown | Filter by manufacturer |
| Location | Dropdown | Filter by location |
| Price Range | Range Slider | $0 – $5,000,000 |
| Year Range | Range Slider | 1900 – current year |
| Length Range | Range Slider | 0 – 200 ft |
| Condition | Dropdown | New, Used, Sold |
| Sort | Dropdown | Date, Price Low-High, Price High-Low, Year |

**Filter positions:** `top` (bar above grid), `left` (sidebar), `right` (sidebar)

## Settings

| Setting | Description |
|---------|-------------|
| API Key | Your Boats.com API key |
| Party ID | Your Boats.com Party ID |
| Auto Sync | Off / Hourly / Daily |
| Rows per Page | Number of boats per API call (1–100) |
| Download Images | Enable/disable image downloading |
| Max Images per Boat | Max images to download per boat (1–200) |
| Treat Missing as Sold | Mark boats not in API response as sold |
| Loan Down Payment % | Default down payment for loan calculator (default: 20%) |
| Loan Interest Rate % | Default APR for loan calculator (default: 7.5%) |
| Loan Term Years | Default loan term for calculator (default: 15) |

## Sync Process

### Job Types

| Type | Description |
|------|-------------|
| `fetch_page` | Fetches a page of boats from the API |
| `sync_boat` | Syncs an individual boat to WordPress |
| `reconcile` | Marks missing boats as sold |

### Smart Sync

1. Compares `LastModificationDate` with stored value
2. Compares MD5 hash of boat data with stored hash
3. If unchanged → skips update (faster sync, fewer API calls)
4. If changed → full update of post, meta, and images

### Queue States

| Status | Description |
|--------|-------------|
| `pending` | Waiting to be processed |
| `processing` | Currently being worked on |
| `done` | Successfully completed |
| `failed` | Failed after 3 retry attempts |

### Automatic Cleanup

- Completed jobs (`done`/`failed`) older than 2 days are auto-deleted
- Stale processing jobs (stuck > 10 minutes) are auto-recovered to `pending`

## Sold Boat Handling

When a boat's `SalesStatus` is not "Active":

1. Boat stays **Published** (visible to visitors)
2. `_wpbs_is_sold` meta flag set to `"1"`
3. "Sold" term assigned to `boat_status` taxonomy
4. Red "SOLD" badge displayed on grid cards and gallery
5. If boat becomes active again → flags are removed

## Data Fields

The plugin syncs 80+ fields from the Boats.com API:

| Category | Fields |
|----------|--------|
| **Pricing** | price, price_amount, original_price, norm_price |
| **Identity** | document_id, make, model, model_year, boat_id |
| **Dimensions** | length_overall, beam, draft, dry_weight, displacement, nominal_length, min_draft, bridge_clearance, deadrise, cabin_headroom |
| **Engine** | engine_summary, num_engines, total_engine_power, engine_hours, engine_type, engine_make, engine_model, fuel_type, drive_type, propeller, engines_json |
| **Performance** | cruising_speed, max_speed, fuel_capacity, range |
| **Hull & Build** | hull_material, hull_id, keel_type, trim_tabs, windlass_type, electrical_circuit, builder_name, designer_name |
| **Features** | boat_category, boat_class, condition, cabins, heads, water_capacity, passenger_capacity |
| **Location** | location, city, state, country, boat_city, boat_state, boat_country |
| **Dealer** | dealer_name, office_phone, office_email |
| **Media** | gallery_attachment_ids, embedded_video_urls |
| **Status** | status, sales_status, is_sold, additional_detail_html |

All fields are prefixed with `wpbs_` in post meta.

## Taxonomies

### brand
Builder/manufacturer taxonomy. Used for brand list display and brand links in price cards.

### boat_status
Status taxonomy for filtering:

- **Sold** — Boats marked as sold/inactive
- **Available** — Active boats (default)

## Templates

The plugin includes default templates that can be overridden by copying to your theme:

- `archive-boats.php` — Boat listing/archive page
- `single-boats.php` — Individual boat detail page
- `elementor-archive.php` — Elementor-powered taxonomy archive template

## Admin Pages

| Page | Description |
|------|-------------|
| **Dashboard** | Sync overview, statistics, charts |
| **Boats** | List of all synced boats with meta data |
| **Queue** | Pending/processing/failed jobs monitor |
| **Settings** | API credentials, sync options, loan defaults |
| **Shortcode Builder** | Visual shortcode generator |

## WP-CLI Commands

```
wp boat-sync full        # Run a full sync
wp boat-sync process     # Process queue batch
wp boat-sync reset       # Reset sync state
wp boat-sync status      # Show sync status
```

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
| `wp_wpbs_boats` | Boat tracking (document_id, post_id, sync status, last modified) |
| `wp_wpbs_images` | Image tracking (document_id, url, attachment_id) |
| `wp_wpbs_queue` | Job queue (type, payload, status, attempts, last_error) |

## Cron Schedules

| Hook | Frequency | Purpose |
|------|-----------|---------|
| `wpbs_cron_auto_sync` | Hourly/Daily | Trigger full sync |
| `wpbs_process_queue` | On-demand (per batch) | Process 5 queue jobs |
| `wpbs_delete_boat` | On-demand | Handle boat deletion |
| `wpbs_cleanup_queue` | Daily | Delete old completed jobs |

## Changelog

### 1.1.4
- Major performance optimization for sold boat filtering
- Improved database query efficiency

### 1.1.3
- Optimized database queries across all widgets
- Fixed gallery status labels
- Bulk meta fetching for tabs and accordion widgets

### 1.1.2
- Client-specific changes and improvements

### 1.1.0
- Added 12 Elementor widgets with full style controls
- Boat Grid, Gallery, Quick Specs, Price Card, Dealer Card
- Boat Tabs, Accordion, More Boats, Brand List
- Loan Calculator, Overview, Breadcrumb
- Custom Elementor category "Boat Elements"
- Elementor archive template support
- Responsive controls for all widgets
- Complete typography, color, spacing, border, and shadow controls

### 1.0.0
- Initial stable release
- Boats.com API integration
- Async queue-based sync
- Smart sync (skip unchanged boats)
- Sold boat handling with badges
- AJAX filter system with range sliders
- Shortcode builder
- Responsive grid layouts
- WP-CLI command support

## Support

For issues or feature requests, please contact the plugin author.

## License

GPL v2 or later
