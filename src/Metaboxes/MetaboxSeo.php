<?php

namespace WPShop\WPCommunity\Metaboxes;

use WP_Post;

class MetaboxSeo {

    const META_TITLE = '_hubr_seo_title';
    const META_DESC  = '_hubr_seo_description';

    protected array $post_types = [ 'post', 'page' ];

    public function init(): void {
        add_action( 'add_meta_boxes', [ $this, '_add_meta_boxes' ] );
        add_action( 'save_post',      [ $this, '_save_meta_box' ], 10, 2 );
        add_action( 'wp_head',        [ $this, '_output_meta_tags' ], 1 );
    }

    public function _add_meta_boxes(): void {
        add_meta_box(
            'hubr_seo',
            'SEO',
            [ $this, '_render_meta_box' ],
            $this->post_types,
            'normal',
            'high'
        );
    }

    public function _save_meta_box( int $post_id, WP_Post $post ): void {
        if ( ! isset( $_POST['_hubr_seo_nonce'] ) || ! wp_verify_nonce( $_POST['_hubr_seo_nonce'], 'hubr_seo_' . $post_id ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( get_post_type_object( $post->post_type )->cap->edit_post, $post_id ) ) {
            return;
        }

        if ( ! in_array( $post->post_type, $this->post_types ) ) {
            return;
        }

        $title = sanitize_text_field( $_POST['hubr_seo_title'] ?? '' );
        $desc  = sanitize_textarea_field( $_POST['hubr_seo_description'] ?? '' );

        if ( $title !== '' ) {
            update_post_meta( $post_id, self::META_TITLE, $title );
        } else {
            delete_post_meta( $post_id, self::META_TITLE );
        }

        if ( $desc !== '' ) {
            update_post_meta( $post_id, self::META_DESC, $desc );
        } else {
            delete_post_meta( $post_id, self::META_DESC );
        }
    }

    public function _render_meta_box( WP_Post $post ): void {
        $title = get_post_meta( $post->ID, self::META_TITLE, true );
        $desc  = get_post_meta( $post->ID, self::META_DESC, true );
        wp_nonce_field( 'hubr_seo_' . $post->ID, '_hubr_seo_nonce' );
        ?>
        <style>
            #hubr_seo .hubr-seo-field { margin-bottom: 12px; }
            #hubr_seo .hubr-seo-field label { display: block; font-weight: 600; margin-bottom: 4px; }
            #hubr_seo .hubr-seo-field input,
            #hubr_seo .hubr-seo-field textarea { width: 100%; box-sizing: border-box; }
            #hubr_seo .hubr-seo-field textarea { height: 80px; resize: vertical; }
            #hubr_seo .hubr-seo-counter { font-size: 11px; color: #888; float: right; }
            #hubr_seo .hubr-seo-counter.over { color: #d63638; }
        </style>
        <div class="hubr-seo-field">
            <label for="hubr_seo_title">
                Title
                <span class="hubr-seo-counter" id="hubr_seo_title_count"></span>
            </label>
            <input type="text" id="hubr_seo_title" name="hubr_seo_title"
                   value="<?php echo esc_attr( $title ) ?>"
                   placeholder="<?php echo esc_attr( get_the_title( $post->ID ) ) ?>">
        </div>
        <div class="hubr-seo-field">
            <label for="hubr_seo_description">
                Description
                <span class="hubr-seo-counter" id="hubr_seo_desc_count"></span>
            </label>
            <textarea id="hubr_seo_description" name="hubr_seo_description"
                      placeholder="Описание для поисковиков..."><?php echo esc_textarea( $desc ) ?></textarea>
        </div>
        <script>
        (function() {
            function counter(inputId, counterId, max) {
                var el = document.getElementById(inputId);
                var ct = document.getElementById(counterId);
                if (!el || !ct) return;
                function update() {
                    var len = el.value.length;
                    ct.textContent = len + ' / ' + max;
                    ct.className = 'hubr-seo-counter' + (len > max ? ' over' : '');
                }
                el.addEventListener('input', update);
                update();
            }
            counter('hubr_seo_title', 'hubr_seo_title_count', 60);
            counter('hubr_seo_description', 'hubr_seo_desc_count', 160);
        })();
        </script>
        <?php
    }

    public function _output_meta_tags(): void {
        if ( ! is_singular() ) {
            return;
        }

        $post_id = get_the_ID();
        $title   = get_post_meta( $post_id, self::META_TITLE, true ) ?: get_the_title( $post_id );
        $desc    = get_post_meta( $post_id, self::META_DESC, true )  ?: get_the_excerpt( get_post( $post_id ) );
        $url     = get_permalink( $post_id );
        $site    = get_bloginfo( 'name' );

        // Featured image
        $image_url = '';
        if ( has_post_thumbnail( $post_id ) ) {
            $img = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'large' );
            if ( $img ) $image_url = $img[0];
        }
        // Fallback: first gallery image
        if ( ! $image_url ) {
            $gallery = get_post_meta( $post_id, '_hubr_gallery', true );
            if ( is_array( $gallery ) ) {
                foreach ( $gallery as $gid ) {
                    $f = get_attached_file( $gid );
                    if ( $f && str_starts_with( basename( $f ), 'generic_' ) ) continue;
                    $img = wp_get_attachment_image_src( $gid, 'large' );
                    if ( $img ) { $image_url = $img[0]; break; }
                }
            }
        }

        // Basic meta
        if ( $title ) echo '<meta name="title" content="'       . esc_attr( $title ) . '">' . "\n";
        if ( $desc )  echo '<meta name="description" content="' . esc_attr( $desc )  . '">' . "\n";

        // Open Graph
        echo '<meta property="og:type"        content="article">' . "\n";
        echo '<meta property="og:site_name"   content="' . esc_attr( $site )  . '">' . "\n";
        echo '<meta property="og:url"         content="' . esc_url( $url )    . '">' . "\n";
        echo '<meta property="og:title"       content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $desc )  . '">' . "\n";
        if ( $image_url ) {
            echo '<meta property="og:image"       content="' . esc_url( $image_url ) . '">' . "\n";
            echo '<meta property="og:image:width"  content="1200">' . "\n";
            echo '<meta property="og:image:height" content="630">' . "\n";
        }

        // Twitter Cards
        echo '<meta name="twitter:card"        content="' . ( $image_url ? 'summary_large_image' : 'summary' ) . '">' . "\n";
        echo '<meta name="twitter:title"       content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $desc )  . '">' . "\n";
        if ( $image_url ) {
            echo '<meta name="twitter:image" content="' . esc_url( $image_url ) . '">' . "\n";
        }

        // Article meta
        echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c', $post_id ) ) . '">' . "\n";
        echo '<meta property="article:modified_time"  content="' . esc_attr( get_the_modified_date( 'c', $post_id ) ) . '">' . "\n";
    }
}
