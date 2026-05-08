# WP Boat Sync - Changelog

## v1.1.3 - 2026-05-08

### Optimized
- **Database Query Performance**: Major optimization to reduce database queries
  - Replaced multiple `get_post_meta()` calls with single `get_post_custom()` in:
    - `includes/class-wpbs-shortcodes.php` - `get_boat_meta()` function (25 queries → 1 query)
    - `templates/single-boats.php` - Single boat page (25 queries → 1 query)
    - `templates/archive-boats.php` - Archive loop (8+ queries per post → 1 query)
    - `includes/widgets/gallery.php` - Gallery widget (3 queries → 1 query)
  - **Performance gain**: ~87.5% fewer DB queries on archive pages (96 → 12 for 12 boats)
  - **Speed improvement**: Estimated 0.084 to 0.42 seconds faster page load

### Fixed
- **Elementor Boat Grid Widget**: Added "Sold" condition to pre-filter dropdown
  - Previously only "New" and "Used" were available as pre-filter options
  - Now supports filtering by "New", "Used", or "Sold" status
  - Updated `includes/class-wpbs-elementor-widget.php` to include 'sold' option

---

## v1.1.2 - 2026-05-08

### Fixed
- **Gallery Status Labels**: Fixed the condition badge (New/Used/Sold) not displaying on the single boat page gallery and Elementor Gallery Widget
  - Added condition badge logic to `templates/single-boats.php` to display status labels on gallery images
  - Fixed Elementor Gallery Widget (`includes/widgets/gallery.php`) to properly render the condition badge based on the `show_condition_badge` setting
  - Badge displays "New", "Used", or "Sold" based on `wpbs_condition` and `wpbs_sales_status` meta values
  - CSS styles were already defined in `assets/css/wpbs-frontend.css`

---

## v1.1.1

### Added
- Initial plugin structure and sync functionality

### Features
- Boat inventory sync from Boats.com API
- Custom post type for boats
- Gallery and media handling
- Elementor widget support
- Admin dashboard and queue management                                                                                                                                                                           