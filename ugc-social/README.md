# UGC Social Feed (Pseudo + PIN)

**Shortcode:** `[ugc_app]`

## What it does
- Public feed: Latest + Trending (7 days window)
- Frontend posting: text-only allowed, plus image/video optional
- Unique pseudo enforced with PIN
- Likes (custom table) + comments (WP comments) + reporting

## Install
1. Upload the zip to WordPress: Plugins → Add New → Upload Plugin
2. Activate
3. Add `[ugc_app]` in a page

## Notes (VPS)
- To support larger videos, increase `upload_max_filesize` and `post_max_size` in PHP settings.
