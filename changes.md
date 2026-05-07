# WP Boat Sync - Changelog

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