<?php
if (!defined('ABSPATH')) exit;

class UGC_REST {

    private static function table_profiles() { global $wpdb; return $wpdb->prefix . 'ugc_profiles'; }
    private static function table_likes() { global $wpdb; return $wpdb->prefix . 'ugc_likes'; }
    private static function table_reports() { global $wpdb; return $wpdb->prefix . 'ugc_reports'; }

    public static function register_routes() {

        register_rest_route('ugc/v1', '/profile/upsert', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'profile_upsert'],
            'permission_callback' => [__CLASS__, 'perm_public_nonce'],
        ]);

        register_rest_route('ugc/v1', '/profile/me', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'profile_me'],
            'permission_callback' => [__CLASS__, 'perm_public_nonce'],
        ]);

        register_rest_route('ugc/v1', '/posts', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'create_post'],
            'permission_callback' => [__CLASS__, 'perm_public_nonce'],
        ]);

        register_rest_route('ugc/v1', '/posts/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_post'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('ugc/v1', '/posts/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [__CLASS__, 'update_post'],
            'permission_callback' => [__CLASS__, 'perm_public_nonce'],
        ]);

        register_rest_route('ugc/v1', '/posts/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [__CLASS__, 'delete_post'],
            'permission_callback' => [__CLASS__, 'perm_public_nonce'],
        ]);

        register_rest_route('ugc/v1', '/feed', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'feed'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('ugc/v1', '/like/toggle', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'like_toggle'],
            'permission_callback' => [__CLASS__, 'perm_public_nonce'],
        ]);

        register_rest_route('ugc/v1', '/comments', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'comments_list'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('ugc/v1', '/comment', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'comment_add'],
            'permission_callback' => [__CLASS__, 'perm_public_nonce'],
        ]);

        register_rest_route('ugc/v1', '/report', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'report_add'],
            'permission_callback' => [__CLASS__, 'perm_public_nonce'],
        ]);

        register_rest_route('ugc/v1', '/upload', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'upload_media'],
            'permission_callback' => [__CLASS__, 'perm_public_nonce'],
        ]);
    }

    public static function perm_public_nonce($request) {
        // Allow public usage but require REST nonce when available.
        // If nonce missing, we still allow (for caching / public feed), BUT for write operations we enforce.
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce) {
            return new WP_Error('ugc_nonce_missing', 'Nonce manquant.', ['status' => 403]);
        }
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('ugc_nonce_invalid', 'Nonce invalide.', ['status' => 403]);
        }
        return true;
    }

    private static function get_visitor_uuid($request) {
        $uuid = $request->get_header('X-UGC-Visitor');
        if (!$uuid) $uuid = $request->get_param('visitor_uuid');
        $uuid = is_string($uuid) ? preg_replace('/[^a-zA-Z0-9\-_]/', '', $uuid) : '';
        return $uuid;
    }

    private static function default_avatar_svg_data_uri($display_name) {
        $initial = strtoupper(mb_substr(trim($display_name), 0, 1));
        if (!$initial) $initial = 'U';
        // Simple deterministic color from hash
        $h = substr(md5($display_name), 0, 6);
        $bg = '#' . $h;
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><rect width="64" height="64" rx="16" fill="'.$bg.'"/><text x="50%" y="54%" text-anchor="middle" font-family="Arial, sans-serif" font-size="28" fill="#fff">'.$initial.'</text></svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private static function profile_row_to_public($row) {
        $avatar_url = null;
        if (!empty($row->avatar_attachment_id)) {
            $avatar_url = wp_get_attachment_image_url((int)$row->avatar_attachment_id, 'thumbnail');
        }
        if (!$avatar_url) {
            $avatar_url = self::default_avatar_svg_data_uri($row->display_name);
        }

        return [
            'id' => (int) $row->id,
            'visitor_uuid' => (string) $row->visitor_uuid,
            'display_name' => (string) $row->display_name,
            'avatar_url' => $avatar_url,
            'status' => (string) $row->status,
        ];
    }

    public static function profile_me($request) {
        global $wpdb;
        $uuid = self::get_visitor_uuid($request);
        if (!$uuid) return new WP_Error('ugc_uuid_missing', 'visitor_uuid manquant.', ['status' => 400]);

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . self::table_profiles() . " WHERE visitor_uuid=%s LIMIT 1", $uuid));
        if (!$row) return new WP_Error('ugc_profile_not_found', 'Profil introuvable.', ['status' => 404]);
        if ($row->status !== 'active') return new WP_Error('ugc_profile_banned', 'Profil banni.', ['status' => 403]);

        return rest_ensure_response(self::profile_row_to_public($row));
    }

    public static function profile_upsert($request) {
        global $wpdb;

        $uuid = self::get_visitor_uuid($request);
        if (!$uuid) return new WP_Error('ugc_uuid_missing', 'visitor_uuid manquant.', ['status' => 400]);

        $display_name = sanitize_text_field($request->get_param('display_name'));
        $pin = (string) $request->get_param('pin');
        $avatar_attachment_id = $request->get_param('avatar_attachment_id');

        if (!$display_name) return new WP_Error('ugc_display_name_missing', 'Pseudonyme requis.', ['status' => 400]);
        if (!preg_match('/^[0-9]{4,6}$/', $pin)) return new WP_Error('ugc_pin_invalid', 'PIN invalide (4–6 chiffres).', ['status' => 400]);

        $profiles = self::table_profiles();

        // Existing by visitor_uuid?
        $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $profiles WHERE visitor_uuid=%s LIMIT 1", $uuid));

        if ($existing) {
            if ($existing->status !== 'active') return new WP_Error('ugc_profile_banned', 'Profil banni.', ['status' => 403]);

            // Verify pin
            if (!password_verify($pin, $existing->pin_hash)) {
                return new WP_Error('ugc_pin_wrong', 'PIN incorrect.', ['status' => 403]);
            }

            // If changing display_name, enforce uniqueness
            if ($display_name !== $existing->display_name) {
                $taken = $wpdb->get_var($wpdb->prepare("SELECT id FROM $profiles WHERE display_name=%s AND id<>%d LIMIT 1", $display_name, $existing->id));
                if ($taken) return new WP_Error('ugc_pseudo_taken', 'Ce pseudonyme est déjà pris.', ['status' => 409]);
            }

            $data = ['display_name' => $display_name];
            if (is_numeric($avatar_attachment_id)) $data['avatar_attachment_id'] = (int)$avatar_attachment_id;

            $wpdb->update($profiles, $data, ['id' => (int)$existing->id], null, ['%d']);
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $profiles WHERE id=%d", (int)$existing->id));
            return rest_ensure_response(self::profile_row_to_public($row));
        }

        // If no existing visitor_uuid: check if display_name already exists
        $by_name = $wpdb->get_row($wpdb->prepare("SELECT * FROM $profiles WHERE display_name=%s LIMIT 1", $display_name));
        if ($by_name) {
            // Reclaim profile using PIN: if correct, assign this uuid to the profile (single-device active)
            if (!password_verify($pin, $by_name->pin_hash)) {
                return new WP_Error('ugc_pseudo_taken', 'Pseudonyme déjà pris. PIN incorrect.', ['status' => 409]);
            }
            if ($by_name->status !== 'active') return new WP_Error('ugc_profile_banned', 'Profil banni.', ['status' => 403]);

            // Clear any other record with this uuid (shouldn't exist)
            $wpdb->update($profiles, ['visitor_uuid' => $uuid], ['id' => (int)$by_name->id], ['%s'], ['%d']);
            if (is_numeric($avatar_attachment_id)) {
                $wpdb->update($profiles, ['avatar_attachment_id' => (int)$avatar_attachment_id], ['id' => (int)$by_name->id], ['%d'], ['%d']);
            }
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $profiles WHERE id=%d", (int)$by_name->id));
            return rest_ensure_response(self::profile_row_to_public($row));
        }

        $pin_hash = password_hash($pin, PASSWORD_DEFAULT);

        $insert = [
            'visitor_uuid' => $uuid,
            'display_name' => $display_name,
            'avatar_attachment_id' => (is_numeric($avatar_attachment_id) ? (int)$avatar_attachment_id : null),
            'pin_hash' => $pin_hash,
            'status' => 'active',
        ];
        $formats = ['%s','%s','%d','%s','%s'];

        // For null avatar, adjust insert
        if ($insert['avatar_attachment_id'] === null) {
            unset($insert['avatar_attachment_id']);
            $formats = ['%s','%s','%s','%s']; // visitor, display, pin_hash, status
        }

        $ok = $wpdb->insert($profiles, $insert, $formats);
        if (!$ok) return new WP_Error('ugc_profile_create_failed', 'Création profil impossible.', ['status' => 500]);

        $id = (int)$wpdb->insert_id;
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $profiles WHERE id=%d", $id));
        return rest_ensure_response(self::profile_row_to_public($row));
    }

    private static function require_profile($request) {
        global $wpdb;
        $uuid = self::get_visitor_uuid($request);
        if (!$uuid) return new WP_Error('ugc_uuid_missing', 'visitor_uuid manquant.', ['status' => 400]);

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . self::table_profiles() . " WHERE visitor_uuid=%s LIMIT 1", $uuid));
        if (!$row) return new WP_Error('ugc_profile_required', 'Profil requis.', ['status' => 403]);
        if ($row->status !== 'active') return new WP_Error('ugc_profile_banned', 'Profil banni.', ['status' => 403]);

        return $row;
    }

    public static function create_post($request) {
        $profile = self::require_profile($request);
        if (is_wp_error($profile)) return $profile;

        $caption = wp_kses_post((string) $request->get_param('caption'));
        $media_type = sanitize_text_field((string) $request->get_param('media_type'));
        $media_ids = $request->get_param('media_ids');

        if (!$caption && empty($media_ids)) {
            return new WP_Error('ugc_empty_post', 'Texte ou média requis.', ['status' => 400]);
        }

        if (!$media_type) $media_type = empty($media_ids) ? 'none' : 'image';

        if (!in_array($media_type, ['none','image','video','gallery'], true)) {
            return new WP_Error('ugc_media_type_invalid', 'media_type invalide.', ['status' => 400]);
        }

        if (!is_array($media_ids)) $media_ids = [];
        $media_ids = array_values(array_filter(array_map('intval', $media_ids)));

        $post_id = wp_insert_post([
            'post_type' => 'ugc_post',
            'post_status' => 'publish',
            'post_title' => wp_trim_words(wp_strip_all_tags($caption ?: 'Publication'), 8, '…'),
            'post_content' => $caption ?: '',
        ], true);

        if (is_wp_error($post_id)) return $post_id;

        update_post_meta($post_id, 'ugc_profile_id', (int)$profile->id);
        update_post_meta($post_id, 'ugc_media_type', $media_type);
        update_post_meta($post_id, 'ugc_media_ids', wp_json_encode($media_ids));
        update_post_meta($post_id, 'ugc_like_count', 0);
        update_post_meta($post_id, 'ugc_comment_count', 0);
        update_post_meta($post_id, 'ugc_trend_score', 0);

        if (!empty($media_ids)) {
            // Use first image as thumbnail when possible
            $first = (int)$media_ids[0];
            if ($first) {
                $mime = get_post_mime_type($first);
                if ($mime && strpos($mime, 'image/') === 0) {
                    set_post_thumbnail($post_id, $first);
                }
            }
        }

        UGC_Trending::recalc_for_post($post_id);

        return rest_ensure_response(self::post_to_public($post_id, (int)$profile->id));
    }

    public static function get_post($request) {
        $post_id = (int) $request->get_param('id');

        if (!$post_id || get_post_type($post_id) !== 'ugc_post') {
            return new WP_Error('ugc_post_invalid', 'Post invalide.', ['status' => 404]);
        }

        $uuid = self::get_visitor_uuid($request);
        $my_profile_id = 0;
        if ($uuid) {
            global $wpdb;
            $my_profile_id = (int)$wpdb->get_var($wpdb->prepare("SELECT id FROM " . self::table_profiles() . " WHERE visitor_uuid=%s LIMIT 1", $uuid));
        }

        $post_data = self::post_to_public($post_id, $my_profile_id);
        if (!$post_data) {
            return new WP_Error('ugc_post_not_found', 'Post introuvable.', ['status' => 404]);
        }

        return rest_ensure_response($post_data);
    }

    public static function update_post($request) {
        global $wpdb;
        $profile = self::require_profile($request);
        if (is_wp_error($profile)) return $profile;

        $post_id = (int) $request->get_param('id');

        if (!$post_id || get_post_type($post_id) !== 'ugc_post') {
            return new WP_Error('ugc_post_invalid', 'Post invalide.', ['status' => 404]);
        }

        // Check ownership
        $post_profile_id = (int) get_post_meta($post_id, 'ugc_profile_id', true);
        if ($post_profile_id !== (int)$profile->id) {
            return new WP_Error('ugc_unauthorized', 'Vous n\'êtes pas autorisé à modifier ce post.', ['status' => 403]);
        }

        $caption = wp_kses_post((string) $request->get_param('caption'));
        $media_type = sanitize_text_field((string) $request->get_param('media_type'));
        $media_ids = $request->get_param('media_ids');

        // Update post content
        wp_update_post([
            'ID' => $post_id,
            'post_content' => $caption ?: '',
            'post_title' => wp_trim_words(wp_strip_all_tags($caption ?: 'Publication'), 8, '…'),
        ]);

        // Update media if provided
        if ($media_ids !== null) {
            if (!is_array($media_ids)) $media_ids = [];
            $media_ids = array_values(array_filter(array_map('intval', $media_ids)));

            update_post_meta($post_id, 'ugc_media_type', $media_type ?: 'none');
            update_post_meta($post_id, 'ugc_media_ids', wp_json_encode($media_ids));

            if (!empty($media_ids)) {
                $first = (int)$media_ids[0];
                if ($first) {
                    $mime = get_post_mime_type($first);
                    if ($mime && strpos($mime, 'image/') === 0) {
                        set_post_thumbnail($post_id, $first);
                    }
                }
            } else {
                delete_post_thumbnail($post_id);
            }
        }

        UGC_Trending::recalc_for_post($post_id);

        return rest_ensure_response(self::post_to_public($post_id, (int)$profile->id));
    }

    public static function delete_post($request) {
        global $wpdb;
        $profile = self::require_profile($request);
        if (is_wp_error($profile)) return $profile;

        $post_id = (int) $request->get_param('id');

        if (!$post_id || get_post_type($post_id) !== 'ugc_post') {
            return new WP_Error('ugc_post_invalid', 'Post invalide.', ['status' => 404]);
        }

        // Check ownership
        $post_profile_id = (int) get_post_meta($post_id, 'ugc_profile_id', true);
        if ($post_profile_id !== (int)$profile->id) {
            return new WP_Error('ugc_unauthorized', 'Vous n\'êtes pas autorisé à supprimer ce post.', ['status' => 403]);
        }

        // Delete the post (WordPress will handle post meta cleanup)
        $result = wp_delete_post($post_id, true);

        if (!$result) {
            return new WP_Error('ugc_delete_failed', 'Impossible de supprimer le post.', ['status' => 500]);
        }

        // Clean up orphaned likes and comments will be handled by WordPress
        $wpdb->delete(self::table_likes(), ['post_id' => $post_id], ['%d']);

        return rest_ensure_response(['success' => true, 'message' => 'Post supprimé avec succès.']);
    }

    private static function post_to_public($post_id, $my_profile_id = 0) {
        global $wpdb;
        $post = get_post($post_id);
        if (!$post) return null;

        $profile_id = (int) get_post_meta($post_id, 'ugc_profile_id', true);
        $profiles = self::table_profiles();
        $profile = $wpdb->get_row($wpdb->prepare("SELECT id, display_name, avatar_attachment_id, status, visitor_uuid FROM $profiles WHERE id=%d LIMIT 1", $profile_id));

        $profile_public = $profile ? self::profile_row_to_public($profile) : [
            'id' => 0,
            'display_name' => 'Utilisateur',
            'avatar_url' => self::default_avatar_svg_data_uri('Utilisateur')
        ];

        $media_type = (string) get_post_meta($post_id, 'ugc_media_type', true);
        $media_ids = json_decode((string)get_post_meta($post_id, 'ugc_media_ids', true), true);
        if (!is_array($media_ids)) $media_ids = [];

        $media = [];
        foreach ($media_ids as $aid) {
            $aid = (int)$aid;
            $url = wp_get_attachment_url($aid);
            if (!$url) continue;
            $mime = get_post_mime_type($aid);
            $media[] = [
                'id' => $aid,
                'url' => $url,
                'mime' => $mime,
                'is_video' => ($mime && strpos($mime, 'video/') === 0),
            ];
        }

        $like_count = (int) get_post_meta($post_id, 'ugc_like_count', true);
        $comment_count = (int) get_post_meta($post_id, 'ugc_comment_count', true);
        $trend_score = (float) get_post_meta($post_id, 'ugc_trend_score', true);

        $liked_by_me = false;
        if ($my_profile_id) {
            $liked = $wpdb->get_var($wpdb->prepare("SELECT id FROM " . self::table_likes() . " WHERE post_id=%d AND profile_id=%d LIMIT 1", $post_id, $my_profile_id));
            $liked_by_me = !!$liked;
        }

        return [
            'id' => (int)$post_id,
            'caption' => $post->post_content,
            'date' => mysql2date('c', $post->post_date_gmt, false),
            'author' => $profile_public,
            'media_type' => $media_type ?: 'none',
            'media' => $media,
            'like_count' => $like_count,
            'comment_count' => $comment_count,
            'trend_score' => $trend_score,
            'liked_by_me' => $liked_by_me,
        ];
    }

    public static function feed($request) {
        $mode = sanitize_text_field((string)$request->get_param('mode'));
        $page = max(1, (int)$request->get_param('page'));
        $per_page = min(20, max(5, (int)$request->get_param('per_page')));

        $order_by = 'date';
        $meta_key = '';
        if ($mode === 'trending') {
            $order_by = 'meta_value_num';
            $meta_key = 'ugc_trend_score';
        }

        // Identify my profile if visitor_uuid passed (for liked_by_me)
        $my_profile_id = 0;
        $uuid = self::get_visitor_uuid($request);
        if ($uuid) {
            global $wpdb;
            $my_profile_id = (int)$wpdb->get_var($wpdb->prepare("SELECT id FROM " . self::table_profiles() . " WHERE visitor_uuid=%s LIMIT 1", $uuid));
        }

        $args = [
            'post_type' => 'ugc_post',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
        ];

        if ($mode === 'trending') {
            $args['meta_key'] = $meta_key;
            $args['orderby'] = $order_by;
            $args['order'] = 'DESC';
            $args['date_query'] = [
                ['after' => '7 days ago']
            ];
        } else {
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
        }

        $q = new WP_Query($args);

        $items = [];
        foreach ($q->posts as $p) {
            $items[] = self::post_to_public($p->ID, $my_profile_id);
        }

        return rest_ensure_response([
            'mode' => $mode ?: 'latest',
            'page' => $page,
            'per_page' => $per_page,
            'found' => (int)$q->found_posts,
            'items' => $items,
        ]);
    }

    public static function like_toggle($request) {
        global $wpdb;
        $profile = self::require_profile($request);
        if (is_wp_error($profile)) return $profile;

        $post_id = (int)$request->get_param('post_id');
        if (!$post_id || get_post_type($post_id) !== 'ugc_post') {
            return new WP_Error('ugc_post_invalid', 'Post invalide.', ['status' => 400]);
        }

        $likes = self::table_likes();

        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $likes WHERE post_id=%d AND profile_id=%d LIMIT 1", $post_id, (int)$profile->id));
        if ($existing) {
            $wpdb->delete($likes, ['id' => (int)$existing], ['%d']);
            $liked = false;
        } else {
            $wpdb->insert($likes, ['post_id' => $post_id, 'profile_id' => (int)$profile->id], ['%d','%d']);
            $liked = true;
        }

        // Update cached like_count
        $count = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $likes WHERE post_id=%d", $post_id));
        update_post_meta($post_id, 'ugc_like_count', $count);
        UGC_Trending::recalc_for_post($post_id);

        return rest_ensure_response(['post_id' => $post_id, 'liked' => $liked, 'like_count' => $count]);
    }

    public static function comments_list($request) {
        $post_id = (int)$request->get_param('post_id');
        if (!$post_id || get_post_type($post_id) !== 'ugc_post') {
            return new WP_Error('ugc_post_invalid', 'Post invalide.', ['status' => 400]);
        }

        $page = max(1, (int)$request->get_param('page'));
        $per_page = min(50, max(10, (int)$request->get_param('per_page')));

        $args = [
            'post_id' => $post_id,
            'status' => 'approve',
            'number' => $per_page,
            'offset' => ($page - 1) * $per_page,
            'orderby' => 'comment_date_gmt',
            'order' => 'ASC',
        ];
        $comments = get_comments($args);

        global $wpdb;
        $profiles = self::table_profiles();

        $items = [];
        foreach ($comments as $c) {
            $pid = (int) get_comment_meta($c->comment_ID, 'ugc_profile_id', true);
            $p = null;
            if ($pid) {
                $p = $wpdb->get_row($wpdb->prepare("SELECT id, visitor_uuid, display_name, avatar_attachment_id, status FROM $profiles WHERE id=%d LIMIT 1", $pid));
            }
            $items[] = [
                'id' => (int)$c->comment_ID,
                'content' => $c->comment_content,
                'date' => mysql2date('c', $c->comment_date_gmt, false),
                'author' => $p ? self::profile_row_to_public($p) : [
                    'id' => 0,
                    'display_name' => 'Utilisateur',
                    'avatar_url' => self::default_avatar_svg_data_uri('Utilisateur')
                ]
            ];
        }

        return rest_ensure_response(['post_id' => $post_id, 'page' => $page, 'items' => $items]);
    }

    public static function comment_add($request) {
        global $wpdb;
        $profile = self::require_profile($request);
        if (is_wp_error($profile)) return $profile;

        $post_id = (int)$request->get_param('post_id');
        $content = wp_kses_post((string)$request->get_param('content'));

        if (!$post_id || get_post_type($post_id) !== 'ugc_post') {
            return new WP_Error('ugc_post_invalid', 'Post invalide.', ['status' => 400]);
        }
        if (!trim(wp_strip_all_tags($content))) {
            return new WP_Error('ugc_comment_empty', 'Commentaire vide.', ['status' => 400]);
        }

        $comment_id = wp_insert_comment([
            'comment_post_ID' => $post_id,
            'comment_content' => $content,
            'comment_approved' => 1,
            'comment_author' => $profile->display_name,
            'comment_author_email' => '',
            'comment_author_url' => '',
        ]);

        if (!$comment_id) return new WP_Error('ugc_comment_failed', 'Impossible de commenter.', ['status' => 500]);

        update_comment_meta($comment_id, 'ugc_profile_id', (int)$profile->id);

        // Update cached comment_count
        $count = (int) get_comments_number($post_id);
        update_post_meta($post_id, 'ugc_comment_count', $count);
        UGC_Trending::recalc_for_post($post_id);

        return rest_ensure_response([
            'id' => (int)$comment_id,
            'post_id' => $post_id,
            'content' => $content,
            'author' => self::profile_row_to_public($profile),
        ]);
    }

    public static function report_add($request) {
        global $wpdb;
        $post_id = (int)$request->get_param('post_id');
        $reason = sanitize_text_field((string)$request->get_param('reason'));
        $details = sanitize_textarea_field((string)$request->get_param('details'));

        if (!$post_id || get_post_type($post_id) !== 'ugc_post') {
            return new WP_Error('ugc_post_invalid', 'Post invalide.', ['status' => 400]);
        }
        if (!$reason) $reason = 'Signalement';

        // reporter is optional (can be anonymous)
        $reporter_id = null;
        $uuid = self::get_visitor_uuid($request);
        if ($uuid) {
            $reporter_id = (int)$wpdb->get_var($wpdb->prepare("SELECT id FROM " . self::table_profiles() . " WHERE visitor_uuid=%s LIMIT 1", $uuid));
            if (!$reporter_id) $reporter_id = null;
        }

        $ok = $wpdb->insert(self::table_reports(), [
            'post_id' => $post_id,
            'reporter_profile_id' => $reporter_id,
            'reason' => $reason,
            'details' => $details,
            'status' => 'open',
        ], ['%d','%d','%s','%s','%s']);

        if (!$ok) return new WP_Error('ugc_report_failed', 'Signalement impossible.', ['status' => 500]);

        return rest_ensure_response(['ok' => true]);
    }

    public static function upload_media($request) {
        // Custom upload endpoint that bypasses WordPress image validation
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $files = $request->get_file_params();

        if (empty($files['file'])) {
            return new WP_Error('ugc_no_file', 'Aucun fichier fourni.', ['status' => 400]);
        }

        $file = $files['file'];

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
                         'image/avif', 'image/heic', 'image/heif', 'image/bmp',
                         'video/mp4', 'video/webm', 'video/quicktime'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types, true) && !in_array($file['type'], $allowed_types, true)) {
            return new WP_Error('ugc_invalid_type', 'Type de fichier non autorisé.', ['status' => 400]);
        }

        // Temporarily disable all image processing to prevent errors
        add_filter('intermediate_image_sizes_advanced', '__return_empty_array', 9999);
        add_filter('big_image_size_threshold', '__return_false', 9999);

        // Handle the upload using WordPress functions but suppress errors
        $upload_overrides = [
            'test_form' => false,
            'mimes' => [
                'jpg|jpeg|jpe' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'avif' => 'image/avif',
                'heic' => 'image/heic',
                'heif' => 'image/heif',
                'bmp' => 'image/bmp',
                'mp4' => 'video/mp4',
                'webm' => 'video/webm',
                'mov' => 'video/quicktime',
            ]
        ];

        $uploaded = wp_handle_upload($file, $upload_overrides);

        // Re-enable filters
        remove_filter('intermediate_image_sizes_advanced', '__return_empty_array', 9999);
        remove_filter('big_image_size_threshold', '__return_false', 9999);

        if (isset($uploaded['error'])) {
            return new WP_Error('ugc_upload_error', $uploaded['error'], ['status' => 500]);
        }

        // Create attachment post
        $attachment_data = [
            'post_mime_type' => $uploaded['type'],
            'post_title' => sanitize_file_name(pathinfo($uploaded['file'], PATHINFO_FILENAME)),
            'post_content' => '',
            'post_status' => 'inherit'
        ];

        $attachment_id = wp_insert_attachment($attachment_data, $uploaded['file']);

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        // Generate minimal metadata without sub-sizes for images
        $metadata = [];

        if (strpos($uploaded['type'], 'image/') === 0) {
            $imagesize = @getimagesize($uploaded['file']);
            if ($imagesize) {
                $metadata = [
                    'width' => $imagesize[0],
                    'height' => $imagesize[1],
                    'file' => _wp_relative_upload_path($uploaded['file']),
                    'sizes' => [], // No sub-sizes
                    'image_meta' => [
                        'aperture' => '0',
                        'credit' => '',
                        'camera' => '',
                        'caption' => '',
                        'created_timestamp' => '0',
                        'copyright' => '',
                        'focal_length' => '0',
                        'iso' => '0',
                        'shutter_speed' => '0',
                        'title' => '',
                        'orientation' => '0',
                        'keywords' => [],
                    ],
                ];
            }
        } elseif (strpos($uploaded['type'], 'video/') === 0) {
            $metadata = [
                'file' => _wp_relative_upload_path($uploaded['file']),
            ];
        }

        if (!empty($metadata)) {
            wp_update_attachment_metadata($attachment_id, $metadata);
        }

        // Return response similar to wp/v2/media
        return rest_ensure_response([
            'id' => $attachment_id,
            'source_url' => wp_get_attachment_url($attachment_id),
            'mime_type' => $uploaded['type'],
            'media_details' => $metadata,
        ]);
    }
}
