# WP Boat Sync - Complete Sync Process Overview

## Architecture

The sync system uses an **asynchronous queue-based architecture** with 3 main components:

| Component | File | Purpose |
|-----------|------|---------|
| API Layer | `class-wpbs-api.php` | Communicates with Boats.com API |
| Sync Engine | `class-wpbs-sync.php` | Queue processing & data transformation |
| Database Layer | `class-wpbs-db.php` | 3 custom tables: `wpbs_boats`, `wpbs_images`, `wpbs_queue` |

---

## Sync Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                        SYNC TRIGGER                                 │
│   (Manual Sync / Auto Cron: Hourly or Daily)                        │
└────────────────────────────┬────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    1. START FULL SYNC                               │
│   enqueue_full_sync() → Creates run_id, enqueues first fetch_page   │
└────────────────────────────┬────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    2. FETCH PAGE JOB                                │
│   job_fetch_page() → API call to boats.com/inventory/search         │
│   • Fetches boats in batches (configurable rows_per_page)           │
│   • For each boat: enqueues sync_boat job                           │
│   • If more pages: enqueues next fetch_page job                     │
│   • If last page: enqueues reconcile job                            │
└────────────────────────────┬────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    3. SYNC BOAT JOB                                 │
│   job_sync_boat() → Syncs individual boat data                      │
│   • Smart sync: Skip if data unchanged (hash comparison)            │
│   • upsert_boat_post() → Creates/updates WordPress CPT post         │
│   • update_fields() → Maps 80+ fields to post meta                  │
│   • sync_images() → Downloads & attaches images (if changed)        │
│   • apply_soldout_logic() → Handles sold/inactive boats             │
└────────────────────────────┬────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    4. RECONCILE JOB                                 │
│   job_reconcile() → Runs after all boats synced                     │
│   • Finds boats NOT seen in current run (missing from API)          │
│   • If treat_missing_as_sold enabled: marks them as sold            │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Queue System Details

### Job Types

| Type | Description | Payload |
|------|-------------|---------|
| `fetch_page` | Fetches a page of boats from API | `run_id`, `offset`, `rows` |
| `sync_boat` | Syncs single boat to WordPress | `document_id`, `run_id`, `partial`, `force` |
| `reconcile` | Marks missing boats as sold | `run_id` |

### Job States

| Status | Description |
|--------|-------------|
| `pending` | Waiting to be processed |
| `processing` | Currently being worked on (locked) |
| `done` | Successfully completed |
| `failed` | Failed after 3 retry attempts |

### Queue Processing

- **Batch Size:** Configurable (default processes multiple jobs per cron tick)
- **Stale Recovery:** Jobs stuck in `processing` > 10 minutes are auto-recovered
- **Retry Logic:** Failed jobs retry up to 3 times with exponential backoff (30s, 60s, 90s)
- **Cleanup:** Jobs with `done`/`failed` status older than 2 days are auto-deleted daily

---

## Smart Sync (Skip Unchanged Data)

The sync intelligently skips updates when data hasn't changed:

1. Compare `LastModificationDate` from API with stored `_wpbs_last_modification_date`
2. Generate MD5 hash of boat data and compare with stored `_wpbs_data_hash`
3. **If both match** → Skip post/image updates (only update tracking data)
4. **If either changed** → Full update of post, meta, and images
5. `force=true` parameter bypasses skip logic

### Sync Status Values

| Status | Description |
|--------|-------------|
| `updated` | Boat data was changed and synced |
| `skipped` | Boat data unchanged, sync skipped |

### Benefits

- ✅ Reduces database writes significantly
- ✅ Faster sync times
- ✅ Lower server load
- ✅ Images only re-downloaded when boat data changes

---

## Data Mapping (80+ Fields)

The sync maps extensive boat data to WordPress post meta:

| Category | Fields |
|----------|--------|
| **Pricing** | `wpbs_price`, `wpbs_price_amount`, `wpbs_original_price`, `wpbs_norm_price` |
| **Identity** | `wpbs_document_id`, `wpbs_make`, `wpbs_model`, `wpbs_model_year` |
| **Dimensions** | `wpbs_length_overall`, `wpbs_beam`, `wpbs_dry_weight`, `wpbs_displacement` |
| **Engine** | `wpbs_engine_summary`, `wpbs_total_engine_power`, `wpbs_fuel_type`, `wpbs_engines_json` |
| **Location** | `wpbs_city`, `wpbs_state`, `wpbs_boat_country`, `wpbs_location` |
| **Dealer** | `wpbs_dealer_name`, `wpbs_office_phone`, `wpbs_office_email`, `wpbs_sales_rep_name` |
| **Media** | `wpbs_gallery_attachment_ids`, `wpbs_embedded_video_urls` |

---

## Image Sync Process

1. **Extract URLs** from API response (handles multiple formats)
2. **Check for existing attachment** by `_wpbs_source_url` meta
3. **Download if new** via `download_url()` + `media_handle_sideload()`
4. **Set featured image** (first image)
5. **Store gallery** in `wpbs_gallery_attachment_ids` meta
6. **Track in `wpbs_images` table** for deduplication

---

## Sold/Inactive Boat Handling

When `SalesStatus` ≠ "Active":

1. ✅ Boat stays **Published** (not drafted)
2. ✅ Add `_wpbs_is_sold` meta flag = "1"
3. ✅ Assign **"Sold"** term to `boat_status` taxonomy
4. ✅ Display red **"SOLD" badge** on grid cards
5. ✅ If boat becomes active again → Remove sold flag and taxonomy term

> **Note:** Sold boats remain visible to users but are clearly marked as sold.

### boat_status Taxonomy

| Term | Description |
|------|-------------|
| `sold` | Boat is sold/inactive |
| `available` | Boat is active (default) |

---

## Deduplication

The sync handles duplicate boat posts:

1. Finds duplicates by `_wpbs_document_id` meta
2. **Keeps oldest post**, deletes others
3. **Reparents attachments** to kept post
4. Runs automatically during sync or manually via admin

---

## Performance Metrics

The system tracks:

| Metric | Description |
|--------|-------------|
| `avg_jobs_per_sec` | Processing speed |
| `avg_job_seconds` | Average job duration |
| `last_run_at` | Timestamp of last run |
| `last_run_jobs` | Jobs processed in last run |
| `last_run_seconds` | Duration of last run |

Stored in `wpbs_queue_metrics` option for dashboard display.

---

## Cron Schedules

| Hook | Frequency | Purpose |
|------|-----------|---------|
| `wpbs_cron_auto_sync` | Hourly/Daily/Off | Trigger full sync |
| `wpbs_process_queue` | On-demand (single event) | Process queue batch |
| `wpbs_cleanup_queue` | Daily | Delete old done/failed jobs |

---

## Database Tables

| Table | Purpose |
|-------|---------|
| `wp_wpbs_boats` | Boat tracking (document_id, post_id, sync status, raw_json) |
| `wp_wpbs_images` | Image tracking (document_id, url, attachment_id) |
| `wp_wpbs_queue` | Job queue (type, payload, status, attempts) |
