<?php
if (!defined('ABSPATH')) exit;

class UGC_Trending {

    public static function register_cron() {
        // already scheduled on activation
    }

    public static function compute_score($likes, $comments, $age_hours) {
        $fresh = max(0, 100 - $age_hours); // decays linearly
        return ($likes * 3.0) + ($comments * 2.0) + $fresh;
    }

    public static function recalc_for_post($post_id) {
        $like_count = (int) get_post_meta($post_id, 'ugc_like_count', true);
        $comment_count = (int) get_post_meta($post_id, 'ugc_comment_count', true);

        $post = get_post($post_id);
        if (!$post) return;

        $age_hours = (time() - strtotime($post->post_date_gmt . ' GMT')) / 3600.0;
        $score = self::compute_score($like_count, $comment_count, $age_hours);

        update_post_meta($post_id, 'ugc_trend_score', $score);
    }

    public static function recalc_recent() {
        $args = [
            'post_type' => 'ugc_post',
            'post_status' => 'publish',
            'posts_per_page' => 200,
            'date_query' => [
                ['after' => '14 days ago']
            ],
            'fields' => 'ids',
        ];
        $ids = get_posts($args);
        foreach ($ids as $id) {
            self::recalc_for_post($id);
        }
    }
}
