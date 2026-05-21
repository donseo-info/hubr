<?php

namespace WPShop\WPCommunity\Metaboxes;

class CategorySeo {

    const META_TITLE = '_hubr_cat_seo_title';
    const META_DESC  = '_hubr_cat_seo_description';

    public function init(): void {
        // Admin: fields on add/edit category page
        add_action( 'category_add_form_fields',  [ $this, 'render_add_form' ] );
        add_action( 'category_edit_form_fields', [ $this, 'render_edit_form' ] );

        // Save
        add_action( 'created_category', [ $this, 'save' ] );
        add_action( 'edited_category',  [ $this, 'save' ] );

        // Frontend: output in <head>
        add_action( 'wp_head', [ $this, 'output_meta_tags' ], 1 );
    }

    public function render_add_form(): void {
        ?>
        <div class="form-field">
            <label for="hubr_cat_seo_title">SEO Title</label>
            <input type="text" id="hubr_cat_seo_title" name="hubr_cat_seo_title" value="" maxlength="80">
            <p>Заголовок для поисковиков (до 60 символов). Если пусто — используется название категории.</p>
        </div>
        <div class="form-field">
            <label for="hubr_cat_seo_description">SEO Description</label>
            <textarea id="hubr_cat_seo_description" name="hubr_cat_seo_description" rows="3" maxlength="300"></textarea>
            <p>Описание для поисковиков (до 160 символов).</p>
        </div>
        <?php $this->render_counter_script(); ?>
        <?php
    }

    public function render_edit_form( \WP_Term $term ): void {
        $title = get_term_meta( $term->term_id, self::META_TITLE, true );
        $desc  = get_term_meta( $term->term_id, self::META_DESC,  true );
        wp_nonce_field( 'hubr_cat_seo_' . $term->term_id, '_hubr_cat_seo_nonce' );
        ?>
        <tr class="form-field">
            <th scope="row"><label for="hubr_cat_seo_title">SEO Title</label></th>
            <td>
                <input type="text" id="hubr_cat_seo_title" name="hubr_cat_seo_title"
                       value="<?php echo esc_attr( $title ) ?>" maxlength="80">
                <span id="hubr_cat_title_count" style="font-size:11px;color:#888"></span>
                <p class="description">Заголовок для поисковиков (до 60 символов). Если пусто — используется название категории.</p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="hubr_cat_seo_description">SEO Description</label></th>
            <td>
                <textarea id="hubr_cat_seo_description" name="hubr_cat_seo_description"
                          rows="3" maxlength="300"><?php echo esc_textarea( $desc ) ?></textarea>
                <span id="hubr_cat_desc_count" style="font-size:11px;color:#888"></span>
                <p class="description">Описание для поисковиков (до 160 символов).</p>
            </td>
        </tr>
        <?php $this->render_counter_script(); ?>
        <?php
    }

    private function render_counter_script(): void {
        ?>
        <script>
        (function() {
            function counter(inputId, counterId, max) {
                var el = document.getElementById(inputId);
                var ct = document.getElementById(counterId);
                if (!el) return;
                // for add-form, create counter span after input
                if (!ct) {
                    ct = document.createElement('span');
                    ct.id = counterId;
                    ct.style.cssText = 'font-size:11px;color:#888;margin-left:8px';
                    el.insertAdjacentElement('afterend', ct);
                }
                function update() {
                    var len = el.value.length;
                    ct.textContent = len + ' / ' + max;
                    ct.style.color = len > max ? '#d63638' : '#888';
                }
                el.addEventListener('input', update);
                update();
            }
            counter('hubr_cat_seo_title',       'hubr_cat_title_count', 60);
            counter('hubr_cat_seo_description',  'hubr_cat_desc_count',  160);
        })();
        </script>
        <?php
    }

    public function save( int $term_id ): void {
        // Nonce check for edit form; add form has no nonce — just sanitize
        if ( isset( $_POST['_hubr_cat_seo_nonce'] ) ) {
            if ( ! wp_verify_nonce( $_POST['_hubr_cat_seo_nonce'], 'hubr_cat_seo_' . $term_id ) ) {
                return;
            }
        }

        $title = sanitize_text_field( $_POST['hubr_cat_seo_title'] ?? '' );
        $desc  = sanitize_textarea_field( $_POST['hubr_cat_seo_description'] ?? '' );

        if ( $title !== '' ) {
            update_term_meta( $term_id, self::META_TITLE, $title );
        } else {
            delete_term_meta( $term_id, self::META_TITLE );
        }

        if ( $desc !== '' ) {
            update_term_meta( $term_id, self::META_DESC, $desc );
        } else {
            delete_term_meta( $term_id, self::META_DESC );
        }
    }

    public function output_meta_tags(): void {
        if ( ! is_category() ) {
            return;
        }

        $term_id = get_queried_object_id();
        $title   = get_term_meta( $term_id, self::META_TITLE, true );
        $desc    = get_term_meta( $term_id, self::META_DESC,  true );

        // Fallback to category name / description
        $term = get_queried_object();
        if ( ! $title && $term ) {
            $title = $term->name;
        }
        if ( ! $desc && $term && $term->description ) {
            $desc = $term->description;
        }

        if ( $title ) {
            echo '<title>' . esc_html( $title ) . ' — ' . esc_html( get_bloginfo( 'name' ) ) . '</title>' . "\n";
            echo '<meta name="title" content="' . esc_attr( $title ) . '">' . "\n";
            echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
        }

        if ( $desc ) {
            echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
            echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
        }
    }
}
