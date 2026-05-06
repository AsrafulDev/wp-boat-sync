Let me examine the changes and recent commits.                                                                                                                                                                           
                                                                                                                                                                                                                         
● Bash(git log --oneline -10)                                                                                                                                                                                              
  ⎿  Waiting…
                                                                                                                                                                                                                           
     1dd0596  * Version: 1.1.1                    
     e90a31f  Version: 1.1.0                                                                                                                                                                                               
     … +7 lines (ctrl+o to expand)                                                                                                                                                                                         
                                                  
● Bash(git diff HEAD --stat)                                                                                                                                                                                               
  ⎿  warning: in the working copy of 'assets/vendor/chartjs/chart.umd.min.js', LF will be replaced by CRLF the next time Git touches it                                                                                    
     warning: in the working copy of 'includes/class-wpbs-admin.php', LF will be replaced by CRLF the next time Git touches it
     warning: in the working copy of 'includes/class-wpbs-elementor-widget.php', LF will be replaced by CRLF the next time Git touches it                                                                                  
     … +12 lines (ctrl+o to expand)                                                                                                                                                                                        
   
● Bash(git diff HEAD)                                                                                                                                                                                                      
  ⎿  warning: in the working copy of 'assets/vendor/chartjs/chart.umd.min.js', LF will be replaced by CRLF the next time Git touches it
     warning: in the working copy of 'includes/class-wpbs-admin.php', LF will be replaced by CRLF the next time Git touches it                                                                                             
     warning: in the working copy of 'includes/class-wpbs-elementor-widget.php', LF will be replaced by CRLF the next time Git touches it
     … +92 lines (ctrl+o to expand)                                                                                                                                                                                        
                                                                                                                                                                                                                           
● Now let me also check the admin file changes (it shows as modified but diff was empty, likely just line endings).
                                                                                                                                                                                                                           
● Bash(git diff HEAD -- includes/class-wpbs-admin.php)                                                                                                                                                                     
  ⎿  warning: in the working copy of 'includes/class-wpbs-admin.php', LF will be replaced by CRLF the next time Git touches it
                                                                                                                                                                                                                           
● The includes/class-wpbs-admin.php has no content changes (just line-ending normalization). Now I have the full picture. Here's the summary document:
                                                                                                                                                                                                                           
  ---                                                                                                                                                                                                                      
  Uncommitted Changes Summary (8 files, +266 / −76)                                                                                                                                                                        
                                                                                                                                                                                                                           
  1. Sold Boat Filtering (core feature)                                                                                                                                                                                    
                  
  Files: class-wpbs-plugin.php, class-wpbs-shortcodes.php, class-wpbs-sync.php, class-wpbs-utils.php

  What changed:
  - Replaced the old treat_missing_as_sold approach (which flagged missing-sync boats as sold) with a proper WP meta query filter on _wpbs_is_sold
  - Added a correctly-structured OR meta clause to exclude sold boats: boats with _wpbs_is_sold = 1 are excluded, boats with no _wpbs_is_sold meta key at all are included
  - The Builder filter dropdown now uses get_active_builders() — a direct SQL query that excludes sold boats at the DB level, instead of fetching all builders then filtering in PHP
  - Skipped sold boats in the grid loop with continue (belt-and-suspenders)
  - Changed treat_missing_as_sold default from 1 to 0 — boats that disappear from the API feed are no longer auto-marked sold
  - Commented-out sync-side sold guards (not active yet — sync still pulls everything)

  Why:
  The previous treat_missing_as_sold = 1 meant any boat not found in the API sync was automatically marked "sold". This caused boats to disappear from listings incorrectly. The new approach relies on the API explicitly
  setting _wpbs_is_sold, and the query layer actively excludes those — more reliable and gives the dealer control.

  ---
  2. Pagination Plumbing (fix)

  Files: class-wpbs-elementor-widget.php, class-wpbs-shortcodes.php

  What changed:
  - In the Elementor widget: detects paged from $_GET or $_POST and passes it through to the shortcode
  - In the shortcode: unified $paged resolution reads from get_query_var('paged') and get_query_var('page') (covers both archive and single-page contexts), plus $_GET['paged']
  - A merged $request = array_merge($_GET, $_POST) ensures filter form POST values are read alongside URL query params

  Why:
  Before, pagination was broken under Elementor — the paged query var wasn't reaching the shortcode's WP_Query. AJAX pagination worked, but server-side pagination on page load always showed page 1.

  ---
  3. Builders Dropdown — Active Only

  Files: class-wpbs-shortcodes.php

  What changed:
  - Replaced the old builder dropdown (which looped over $filter_options['builders'] — a list of ALL builders ever synced) with a new get_active_builders() method
  - get_active_builders() runs a single SELECT DISTINCT on wp_postmeta joined against the sold meta key, excluding sold boats in SQL
  - This means the Builder filter only shows brands that have non-sold boats currently available

  Why:
  When a dealer sells their last Bayliner, "Bayliner" still appeared in the filter dropdown, leading users to an empty results page. Now only builders with active inventory are shown.

  ---
  4. Color Scheme — Blue to Red

  Files: assets/css/wpbs-frontend.css

  What changed:
  - --wpbs-primary: #0066cc → #E30613 (BoatTrader-style red)
  - --wpbs-primary-dark: #0052a3 → #91020a

  Why:
  Branding match — the site's primary accent color is red, not blue.

  ---
  5. Removed "X boats" Count from UI

  Files: class-wpbs-shortcodes.php, class-wpbs-elementor-widget.php

  What changed:
  - The .wpbs-archive-count element (showing e.g. "124 boats") was removed from both the server-rendered HTML and the AJAX placeholder
  - The count-related Elementor controls (color picker and typography for .wpbs-archive-count) were commented out
  - Pagination info simplified from Page 1 of 10 (245 boats) to just Page 1 of 10

  Why:
  The count was unreliable during AJAX filter transitions (showed stale numbers before the response arrived). Also removed design clutter since filter results are self-evident.

  ---
  6. JS Nonce Debugging

  Files: assets/js/wpbs-gallery.js

  What changed:
  - Added optional chaining (wpbsFilter?.nonce || '') as a safety fallback
  - Added console.log for the nonce value being sent

  Why:
  Temporary debugging aid — the nonce was occasionally undefined during AJAX filter requests, and this helps trace whether it's a localization timing issue or a form state bug.

  ---
  7. Meta Query Always Attached

  Files: class-wpbs-plugin.php

  What changed:

  ---
  7. Meta Query Always Attached

  Files: class-wpbs-plugin.php

  What changed:
  - Previously: meta_query was only attached to WP_Query args when there was more than 1 clause (if (count($meta_query) > 1))
  - Now: it's always assigned ($args['meta_query'] = $meta_query), because the sold-exclusion clause is always present

  Why:
  With the permanent sold-exclusion clause, the meta_query is never empty, so the old guard was incorrect. Even when zero user filters are active, the sold exclusion must apply.