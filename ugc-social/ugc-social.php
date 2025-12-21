<?php
/**
 * Plugin Name: UGC Social Feed (Pseudo + PIN)
 * Description: Mini social feed (text/photo/video) with pseudo unique + PIN, likes, comments, trending feed, and frontend posting.
 * Version: 0.1.1
 * Author: PERSO
 * Text Domain: ugc-social
 */

if (!defined('ABSPATH')) exit;

define('UGC_SOCIAL_VERSION', '0.2.3');
define('UGC_SOCIAL_PATH', plugin_dir_path(__FILE__));
define('UGC_SOCIAL_URL', plugin_dir_url(__FILE__));

require_once UGC_SOCIAL_PATH . 'includes/class-ugc-activator.php';
require_once UGC_SOCIAL_PATH . 'includes/class-ugc-cpt.php';
require_once UGC_SOCIAL_PATH . 'includes/class-ugc-rest.php';
require_once UGC_SOCIAL_PATH . 'includes/class-ugc-trending.php';
require_once UGC_SOCIAL_PATH . 'includes/class-ugc-admin.php';


/**
 * Allow more image formats for uploads (WebP/AVIF/HEIC/etc.)
 * Note: SVG is intentionally NOT enabled by default (security).
 */
add_filter('upload_mimes', function($mimes){
    $mimes['webp'] = 'image/webp';
    $mimes['avif'] = 'image/avif';
    $mimes['heic'] = 'image/heic';
    $mimes['heif'] = 'image/heif';
    $mimes['tif']  = 'image/tiff';
    $mimes['tiff'] = 'image/tiff';
    $mimes['bmp']  = 'image/bmp';
    $mimes['ico']  = 'image/x-icon';
    // keep jpg/jpeg/png/gif as-is
    return $mimes;
});

/**
 * Prevent WordPress from failing uploads when it can't generate responsive sub-sizes
 * for some formats (depends on server Imagick/GD support).
 * We keep the original file and skip intermediate sizes for these formats.
 */
add_filter('intermediate_image_sizes_advanced', function($sizes, $metadata, $attachment_id){
    $mime = get_post_mime_type($attachment_id);
    $skip = ['image/webp','image/avif','image/heic','image/heif','image/tiff','image/bmp','image/x-icon'];
    if ($mime && in_array($mime, $skip, true)) {
        return []; // no sub-sizes
    }
    return $sizes;
}, 10, 3);


register_activation_hook(__FILE__, ['UGC_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['UGC_Activator', 'deactivate']);

add_action('init', function() {
    UGC_CPT::register();
});

add_action('rest_api_init', function() {
    UGC_REST::register_routes();
});

add_action('init', function() {
    UGC_Trending::register_cron();
});

add_action('ugc_social_recalc_trending', function() {
    UGC_Trending::recalc_recent();
});

add_shortcode('ugc_app', function($atts = []) {
    wp_enqueue_style('ugc-social', UGC_SOCIAL_URL . 'assets/app.css', [], filemtime(UGC_SOCIAL_PATH . 'assets/app.css'));
    wp_enqueue_script('ugc-social', UGC_SOCIAL_URL . 'assets/app.js', ['wp-api-fetch'], UGC_SOCIAL_VERSION, true);

    wp_localize_script('ugc-social', 'UGC_SOCIAL', [
        'restUrl' => esc_url_raw(rest_url('ugc/v1')),
        'nonce'   => wp_create_nonce('wp_rest'),
        'siteUrl' => esc_url_raw(site_url('/')),
        'strings' => [
            'latest' => __('Derniers', 'ugc-social'),
            'trending' => __('Tendance', 'ugc-social'),
            'publish' => __('Publier', 'ugc-social'),
            'comment' => __('Commenter', 'ugc-social'),
            'like' => __('J\'aime', 'ugc-social'),
        ],
    ]);

    ob_start();
    ?>
    <div class="ugc-app" data-ugc-app="1">

      <div class="ugc-header ugc-header--brand">
        <div class="ugc-tabs ugc-tabs--pill">
          <button class="ugc-tab is-active" data-mode="latest">Derniers</button>
          <button class="ugc-tab" data-mode="trending">Tendance</button>
        </div>

        <button class="ugc-start" type="button" data-action="open-composer">
          <span class="ugc-start__text" data-me-prompt>Quoi de neuf, Pseudo ?</span>
        </button>
      </div>

      <div class="ugc-feed" data-ugc-feed></div>

      <div class="ugc-loadmore">
        <button class="ugc-btn" data-action="load-more">Charger plus</button>
      </div>

      <!-- Composer Modal -->
      <div class="ugc-modal" data-modal="composer" aria-hidden="true">
        <div class="ugc-modal__backdrop" data-action="close-modal"></div>
        <div class="ugc-modal__panel">
          <div class="ugc-sheet__handle" aria-hidden="true"></div>
          <div class="ugc-modal__title">Nouvelle publication</div>

          <div class="ugc-modal__body">

            <!-- Step 1: Identity -->
            <div class="ugc-step" data-step="auth">
              <div class="ugc-grid ugc-grid--auth" data-auth-block>
                <div class="ugc-field">
                  <label>Pseudonyme <span class="ugc-req">*</span></label>
                  <input type="text" maxlength="60" data-input="display_name" placeholder="ex: Mouha" />
                </div>
                <div class="ugc-field">
                  <label>PIN (4–6 chiffres) <span class="ugc-req">*</span></label>
                  <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="6" data-input="pin" placeholder="ex: 1234" />
                </div>
                <div class="ugc-field">
                  <label>Avatar (optionnel)</label>
                  <input type="file" accept="image/*" data-input="avatar_file" />
                  <div class="ugc-hint">Si vide, on génère un avatar automatique.</div>
                </div>
              </div>

              <div class="ugc-auth-info" data-auth-info style="display:none;">
                <div class="ugc-auth-info__row">
                  <div class="ugc-auth-info__left">
                    <img class="ugc-avatar" data-auth-me-avatar alt="" />
                    <div>
                      <div class="ugc-auth-info__name" data-auth-me-name></div>
                      <div class="ugc-auth-info__hint">Vous êtes déjà connecté sur ce téléphone.</div>
                    </div>
                  </div>
                  <button class="ugc-btn" type="button" data-action="switch-user">Changer</button>
                </div>
              </div>

              <div class="ugc-step__actions">
                <button class="ugc-btn ugc-btn-primary" type="button" data-action="auth-continue">Valider</button>
              </div>
            </div>

            <!-- Step 2: Post -->
            <div class="ugc-step" data-step="post" style="display:none;">
              <div class="ugc-field">
                <label>Texte</label>
                <textarea rows="5" maxlength="2000" data-input="caption" placeholder="Écrivez quelque chose..."></textarea>
              </div>

              <div class="ugc-field">
                <label>Média (optionnel)</label>
                <input type="file" accept="image/*,video/*" data-input="media_file" />
                <div class="ugc-hint">Photo ou vidéo (MP4/WebM). Le texte seul est autorisé.</div>
              </div>
            </div>

            <div class="ugc-error" data-error></div>
          </div>

          <div class="ugc-modal__actions" data-post-actions style="display:none;">
            <button class="ugc-btn" type="button" data-action="close-modal">Annuler</button>
            <button class="ugc-btn ugc-btn-primary" type="button" data-action="submit-post">Publier</button>
          </div>
        </div>
      </div>

      <!-- Comments Modal -->
      <div class="ugc-modal" data-modal="comments" aria-hidden="true">
        <div class="ugc-modal__backdrop" data-action="close-modal"></div>
        <div class="ugc-modal__panel">
          <div class="ugc-sheet__handle" aria-hidden="true"></div>
          <div class="ugc-modal__title">Commentaires</div>

          <div class="ugc-modal__body">
            <div class="ugc-comments" data-comments></div>

            <div class="ugc-field">
              <textarea rows="3" maxlength="1200" data-input="comment_text" placeholder="Votre commentaire..."></textarea>
            </div>

            <div class="ugc-error" data-comments-error></div>
          </div>

          <div class="ugc-modal__actions">
            <button class="ugc-btn" type="button" data-action="close-modal">Fermer</button>
            <button class="ugc-btn ugc-btn-primary" type="button" data-action="submit-comment">Envoyer</button>
          </div>
        </div>
      </div>

    </div>
    <?php
    return ob_get_clean();
});
