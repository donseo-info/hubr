<?php

namespace Wpshop\Core\Customizer\Control;
use Wpshop\Core\ThemeOptions;

/**
 * Class PageConstructor
 * 1.0.6    2021-10-06      Add filter `wpshop_home_constructor_show_post_types_field` to show post type field
 * 1.0.5    2021-06-29      Add setting for title tag
 * 1.0.4    2021-01-21      Add setting for output header category
 * 1.0.3    2020-07-24      Add sorting posts and output header link
 * 1.0.2    2020-04-01      Add field for css classes
 * 1.0.1    2019-05-13      Hide input field
 * 1.0.0    2019-04-10      Init
 */
class PageConstructor extends \WP_Customize_Control {

    /**
     * Control's Type.
     *
     * @var   string
     */
    public $type = 'page-constructor';

    public $presets = [];
    public $title_tags = [];
    public $posts_order = [];
    public $post_card_types = [];
    public $post_card_elements = [];


    /**
     * @var
     */
    protected $options;


    /**
     * @param ThemeOptions $options
     *
     * @return $this
     */
    public function set_options( ThemeOptions $options ) {
        $this->options = $options;
        return $this;
    }


    /**
     * Render the control's content.
     *
     * @return void
     */
    public function render_content() {
        if ( empty( $this->choices ) ) {
            return;
        }

        if ( ! empty( $this->choices['presets'] ) ) {
            $this->presets[] = 'none';
            $this->presets   = array_merge( $this->presets, $this->choices['presets'] );
        }

        if ( ! empty( $this->choices['title_tags'] ) ) {
            $this->title_tags = $this->choices['title_tags'];
        }

        if ( ! empty( $this->choices['posts_order'] ) ) {
            $this->posts_order = $this->choices['posts_order'];
        }

        if ( ! empty( $this->choices['post_card_types'] ) ) {
            $this->post_card_types = $this->choices['post_card_types'];
        }

        if ( ! empty( $this->choices['post_card_elements'] ) ) {
            $this->post_card_elements = $this->choices['post_card_elements'];
        }

        $value      = $this->value();
        $value_json = json_decode( $value, true );

        if ( ! empty( $this->label ) ) {
            echo '<span class="customize-control-title">' . esc_html( $this->label ) . '</span>';
        }

        if ( ! empty( $this->description ) ) {
            echo '<span class="description customize-control-description">' . $this->description . '</span>';
        }

        echo '<div class="wpshop-customize-pc-items">';

        if ( ! empty( $value_json ) && is_array( $value_json ) ) {
            foreach ( $value_json as $section ) {
                $this->render_pc_item( $section );
            }
        }

        echo '</div><!--.wpshop-customize-pc-items-->';


        echo '<div>';
        echo '<span class="button js-wpshop-customize-pc-add" data-type="posts">+ ' . __( 'Posts output', $this->options->text_domain ) . '</span> ';
        echo '<span class="button js-wpshop-customize-pc-add" data-type="html">+ ' . __( 'HTML', $this->options->text_domain ) . '</span> ';
        echo '</div>';

        echo '<div class="wpshop-customize-pc-placeholders">';
        $this->render_pc_item( [ 'section_type' => 'posts' ] );
        $this->render_pc_item( [ 'section_type' => 'html' ] );
        echo '</div>';

        echo '<input type="hidden" class="wpshop-customize-pc-field" ' . $this->get_link() . ' value="' . esc_attr( $value ) . '">';

    }


    public function render_pc_item( $section = [] ) {

        $section_type = ( ! empty( $section['section_type'] ) ) ? $section['section_type'] : 'posts';
        $body         = '';
        $item_header  = '';
        if ( $section_type == 'posts' ) {
            $body        = $this->render_pc_item_posts( $section );
            $item_header = __( 'Posts output', $this->options->text_domain );
        } elseif ( $section_type == 'html' ) {
            $body        = $this->render_pc_item_html( $section );
            $item_header = __( 'HTML', $this->options->text_domain );
        }

        $section_header_text = $this->get_item_value( 'section_header_text', $section );

        echo '<div class="wpshop-customize-pc-item wpshop-customize-pc-item--' . $section_type . '" data-type="' . $section_type . '">';
        echo '  <div class="wpshop-customize-pc-item-header">';
        echo $item_header;
        echo ' <em>' . $section_header_text . '</em>';
        echo '</div>';

        echo '  <div class="wpshop-customize-pc-item-body">';

        echo $body;

        echo '    <input type="hidden" name="section_type" value="' . $section_type . '">';
        echo '    <span class="button js-wpshop-customize-pc-delete">' . __( 'Remove', $this->options->text_domain ) . '</span> ';
        echo '  </div>';

        echo '</div><!--.wpshop-customize-pc-item-->';
    }


    public function render_pc_presets( $section = [] ) {
        $out          = '';
        $preset_value = $this->get_item_value( 'preset', $section );

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'Presets', $this->options->text_domain ) . ':</label>';
        $out .= '    <div class="wpshop-customize-pc-field">';
        $out .= '    <div class="wpshop-customize-pc-presets">';
        foreach ( $this->presets as $preset ) {
            $active = ( $preset_value == $preset ) ? ' active' : '';
            $out    .= '        <span class="wpshop-customize-pc-preset preset-' . $preset . $active . '" data-preset-name="' . $preset . '"></span>';
        }
        $out .= '        <input type="hidden" name="preset" value="' . esc_attr( $preset_value ) . '">';
        $out .= '    </div>';
        $out .= '    </div>';

        return $out;
    }


    public function render_pc_item_posts( $section = [] ) {

        $out = '';

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'Title', $this->options->text_domain ) . ':</label>';
        $out .= '    <div class="wpshop-customize-pc-field"><input name="section_header_text" type="text" value="' . esc_attr( $this->get_item_value( 'section_header_text', $section ) ) . '" autocomplete="off"></div>';

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'Title tag', $this->options->text_domain ) . ':</label>';
        $out .= '    <div class="wpshop-customize-pc-field">';
        $out .= '      <select name="title_tag">';
        foreach ( $this->title_tags as $title_tag ) {
            $out .= '<option value="' . $title_tag . '"' . selected( $title_tag, $this->get_item_value( 'title_tag', $section ), false ) . '>' . $title_tag . '</option>';
        }
        $out .= '      </select>';
        $out .= '    </div>';

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'Category ID', $this->options->text_domain ) . ':</label> <a href="https://support.wpshop.ru/faq/get-post-id/" target="_blank" rel="noopener" class="wpshop-ico-help" title="' . __( 'How find out ID category', $this->options->text_domain ) . '">?</a>';
        $out .= '    <div class="wpshop-customize-pc-field"><input name="cat" type="text" value="' . esc_attr( $this->get_item_value( 'cat', $section ) ) . '"></div>';

        $out .= '    <div class="wpshop-customize-pc-field">';
        $out .= '      <label><input name="output_header_category" type="checkbox"' . checked( true, $this->get_item_value( 'output_header_category', $section, true ), false ) . '> ' . __( 'display category name as heading', $this->options->text_domain ) . '</label>';
        $out .= '    </div>';

        $out .= '    <div class="wpshop-customize-pc-field">';
        $out .= '      <label><input name="output_header_link" type="checkbox"' . checked( true, $this->get_item_value( 'output_header_link', $section ), false ) . '> ' . __( 'display heading with category link', $this->options->text_domain ) . '</label>';
        $out .= '    </div>';

        if ( apply_filters( 'wpshop_home_constructor_show_post_types_field', false ) ) {
            $out .= '    <label class="wpshop-customize-pc-label">' . __( 'Post type', $this->options->text_domain ) . ':</label>';
            $out .= '    <div class="wpshop-customize-pc-field"><input name="post_type" type="text" value="' . esc_attr( $this->get_item_value( 'post_type', $section ) ) . '"></div>';
        }

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'Show only posts', $this->options->text_domain ) . ':</label> <a href="https://support.wpshop.ru/faq/get-post-id/" target="_blank" rel="noopener" class="wpshop-ico-help" title="' . __( 'How find out ID post', $this->options->text_domain ) . '">?</a>';
        $out .= '    <div class="wpshop-customize-pc-field"><input name="post__in" type="text" value="' . esc_attr( $this->get_item_value( 'post__in', $section ) ) . '"></div>';

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'Exclude posts', $this->options->text_domain ) . ':</label> <a href="https://support.wpshop.ru/faq/get-post-id/" target="_blank" rel="noopener" class="wpshop-ico-help" title="' . __( 'How find out ID post', $this->options->text_domain ) . '">?</a>';
        $out .= '    <div class="wpshop-customize-pc-field"><input name="post__not_in" type="text" value="' . esc_attr( $this->get_item_value( 'post__not_in', $section ) ) . '"></div>';

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'Indentation', $this->options->text_domain ) . ':</label>';
        $out .= '    <div class="wpshop-customize-pc-field"><input name="offset" type="number" value="' . esc_attr( $this->get_item_value( 'offset', $section ) ) . '"></div>';

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'Number of posts', $this->options->text_domain ) . ':</label>';
        $out .= '    <div class="wpshop-customize-pc-field"><input name="posts_per_page" type="number" value="' . esc_attr( $this->get_item_value( 'posts_per_page', $section ) ) . '"></div>';

        $out .= '    <div class="wpshop-customize-pc-field">';
        $out .= '      <label><input name="show_subcategories" type="checkbox"' . checked( true, $this->get_item_value( 'show_subcategories', $section ), false ) . '> ' . __( 'show subcategories', $this->options->text_domain ) . '</label>';
        $out .= '    </div>';

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'Order posts', $this->options->text_domain ) . ':</label>';
        $out .= '    <div class="wpshop-customize-pc-field">';
        $out .= '      <select name="post_order">';
        foreach ( $this->posts_order as $post_order ) {
            $out .= '<option value="' . $post_order . '"' . selected( $post_order, $this->get_item_value( 'post_order', $section ), false ) . '>' . $post_order . '</option>';
        }
        $out .= '      </select>';
        $out .= '    </div>';

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'Type of post cards', $this->options->text_domain ) . ':</label>';
        $out .= '    <div class="wpshop-customize-pc-field">';
        $out .= '      <select name="post_card_type">';
        foreach ( $this->post_card_types as $post_card_type ) {
            $out .= '<option value="' . $post_card_type . '"' . selected( $post_card_type, $this->get_item_value( 'post_card_type', $section ), false ) . '>' . $post_card_type . '</option>';
        }
        $out .= '      </select>';
        $out .= '    </div>';

        $out .= '<div class="wpshop-customize-pc-field">';
        $out .= '    <span class="button wpshop-customize-pc-post-card-settings-open">' . __( 'Settings post cards', $this->options->text_domain ) . '</span>';
        $out .= '</div>';

        $out .= '<div class="wpshop-customize-pc-post-card-settings">';

        foreach ( $this->post_card_elements as $post_card_element_key => $post_card_element_label ) {
            $out .= $this->post_card_settings_options( $post_card_element_label, 'show_' . $post_card_element_key, $this->get_item_value( 'show_' . $post_card_element_key, $section ) );
        }

        $out .= '</div>';

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'CSS classes', $this->options->text_domain ) . ':</label>';
        $out .= '    <div class="wpshop-customize-pc-field"><input name="section_css_classes" type="text" value="' . esc_attr( $this->get_item_value( 'section_css_classes', $section ) ) . '" autocomplete="off"></div>';

        $out .= $this->render_pc_presets( $section );

        return $out;
    }

    public function render_pc_item_html( $section = [] ) {

        $out = '';

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'Title', $this->options->text_domain ) . ':</label>';
        $out .= '    <div class="wpshop-customize-pc-field"><input name="section_header_text" type="text" value="' . esc_attr( $this->get_item_value( 'section_header_text', $section ) ) . '"></div>';

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'Title tag', $this->options->text_domain ) . ':</label>';
        $out .= '    <div class="wpshop-customize-pc-field">';
        $out .= '      <select name="title_tag">';
        foreach ( $this->title_tags as $title_tag ) {
            $out .= '<option value="' . $title_tag . '"' . selected( $title_tag, $this->get_item_value( 'title_tag', $section ), false ) . '>' . $title_tag . '</option>';
        }
        $out .= '      </select>';
        $out .= '    </div>';

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'HTML', $this->options->text_domain ) . '</label>';
        $out .= '    <div class="wpshop-customize-pc-field"><textarea rows="5" name="html_code">' . esc_textarea( $this->get_item_value( 'html_code', $section ) ) . '</textarea></div>';

        $out .= '    <label class="wpshop-customize-pc-label">' . __( 'CSS classes', $this->options->text_domain ) . ':</label>';
        $out .= '    <div class="wpshop-customize-pc-field"><input name="section_css_classes" type="text" value="' . esc_attr( $this->get_item_value( 'section_css_classes', $section ) ) . '" autocomplete="off"></div>';

        $out .= $this->render_pc_presets( $section );

        return $out;

    }


    public function get_item_value( $name, $section = [], $default = '' ) {
        if ( isset( $section[ $name ] ) ) {
            return $section[ $name ];
        }

        return $default;
    }


    public function post_card_settings_options( $label = '', $name = '', $value = '' ) {
        $out = '';
        $out .= '    <div class="wpshop-customize-pc-field">';
        $out .= '        <label>' . $label . '</label>';
        $out .= '        <select name="' . $name . '">';

        $out .= '            <option ' . selected( 'default', $value, false ) . ' value="default">' . __( 'default', $this->options->text_domain ) . '</option>';
        $out .= '            <option ' . selected( 'show', $value, false ) . ' value="show">' . __( 'show', $this->options->text_domain ) . '</option>';
        $out .= '            <option ' . selected( 'hide', $value, false ) . ' value="hide">' . __( 'hide', $this->options->text_domain ) . '</option>';

        $out .= '        </select>';
        $out .= '    </div>';
        return $out;
    }


    /**
     * Enqueue control related scripts/styles.
     *
     * @return void
     */
    public function enqueue() {
        wp_enqueue_style( 'wpshop-customize-control-' . $this->type, trailingslashit( get_template_directory_uri() ) . 'vendor/wpshop/core/resources/css/customizer/' . $this->type . '.css' );
        wp_enqueue_script( 'wpshop-customize-control-' . $this->type, trailingslashit( get_template_directory_uri() ) . 'vendor/wpshop/core/resources/js/customizer/' . $this->type . '.js', [ 'jquery' ], null, true );
    }

}