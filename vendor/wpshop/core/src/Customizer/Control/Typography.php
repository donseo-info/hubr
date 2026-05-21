<?php

namespace Wpshop\Core\Customizer\Control;
use Wpshop\Core\ThemeOptions;
use Wpshop\Core\Core;
use Wpshop\Core\Fonts;

/**
 * Class Typography
 *
 * 1.0.3    2021-06-29      add step for font size
 * 1.0.2    2021-04-29      fix font_style func PHP8
 * 1.0.1    2019-05-27      ability to disable colors
 * 1.0.0    2018-10-24      init
 */
class Typography extends \WP_Customize_Control {

    /**
     * Control's Type.
     *
     * @var   string
     */
    public $type = 'typography';


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
        global $wpshop_core;

        if ( empty( $this->choices ) ) {
            return;
        }

        $wpshop_fonts = new Fonts();
        $font_list = $wpshop_fonts->get_fonts_key_value();

        if ( ! empty( $this->value() ) ) {
            $data = json_decode( $this->value(), true );
        }
        $font_family = ( ! empty( $data['font-family'] ) ) ? $data['font-family'] : '';
        $font_size = ( ! empty( $data['font-size'] ) ) ? $data['font-size'] : '';
        $line_height = ( ! empty( $data['line-height'] ) ) ? $data['line-height'] : '';
        $color = ( ! empty( $data['color'] ) ) ? $data['color'] : '';
        $font_style = ( ! empty( $data['font-style'] ) ) ? $data['font-style'] : array();
        $unit = ( ! empty( $data['unit'] ) ) ? $data['unit'] : '';
        if ( ! empty( $font_style ) ) $font_style = explode( ',', $font_style );
        $font_size_step = ( $unit == 'em' ) ? '0.1' : '1';

        $default = '';
        if ( is_object( $this->setting ) ) {
            if ( $this->setting->default ) {
                $default = $this->setting->default;
            }
        }

        echo '<span class="customize-control-title">' ;
        if ( ! empty( $this->label ) ) echo esc_html( $this->label );

        echo '<span class="wpshop-customize-label-tools">';

        //echo '<span class="wpshop-customize-label-btn active dashicons dashicons-desktop" data-option="desktop" title="{{ data.desktop_label }}"></span>';
        //echo '<span class="wpshop-customize-label-btn dashicons dashicons-smartphone" data-option="mobile" title="{{ data.mobile_label }}"></span>';
        echo '<span title="' . __( 'Reset', $this->options->text_domain ) . '" class="wpshop-customize-label-btn wpshop-customize-reset dashicons dashicons-image-rotate" data-default="' . esc_attr( $default ) .'"></span>';


        echo '</span><!--.wpshop-customize-label-tools-->';

        echo '</span><!--.customize-control-title-->';

        if ( ! empty( $this->description ) ) {
            echo '<span class="description customize-control-description">' . $this->description . '</span>';
        }

        echo '<div class="wpshop-customize-typography-wrap">';
        echo '<div class="wpshop-customize-typography-font-family">';
        echo '<select>';
        foreach ( $font_list as $font_id => $font_name ) {
            echo '<option value="' . $font_id . '" ' . selected( $font_id, $font_family, false ) . '>' . $font_name . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '<div class="wpshop-customize-typography-styles">';
        echo '<span data-type="bold" title="' . __( 'Bold', $this->options->text_domain ) . '" class="' . $this->font_style( $font_style, 'bold' ) . ' wpshop-customize-typography-style-btn dashicons dashicons-editor-bold"></span>';
        echo '<span data-type="italic" title="' . __( 'Italic', $this->options->text_domain ) . '" class="' . $this->font_style( $font_style, 'italic' ) . ' wpshop-customize-typography-style-btn dashicons dashicons-editor-italic"></span>';
        echo '<span data-type="underline" title="' . __( 'Underline', $this->options->text_domain ) . '" class="' . $this->font_style( $font_style, 'underline' ) . ' wpshop-customize-typography-style-btn dashicons dashicons-editor-underline"></span>';
        echo '<span data-type="uppercase" title="' . __( 'Uppercase', $this->options->text_domain ) . '" class="' . $this->font_style( $font_style, 'uppercase' ) . ' wpshop-customize-typography-style-btn dashicons dashicons-editor-textcolor"></span>';
        echo '</div>';
        echo '</div>';

        echo '<div class="wpshop-customize-typography-wrap">';
        echo '<div class="wpshop-customize-typography-font-size">';
        echo '<span class="wpshop-customize-typography-label">' . __( 'Font-size', $this->options->text_domain ) . ':</span>';
        echo '<input type="number"  step="' . $font_size_step . '" value="' . $font_size . '">';
        echo '<span class="wpshop-customize-typography-unit">' . $unit . '<input type="hidden" value="' . $unit . '"></span>';
        echo '</div>';
        echo '<div class="wpshop-customize-typography-line-height">';
        echo '<span class="wpshop-customize-typography-label">' . __( 'Line-height', $this->options->text_domain ) . ':</span>';
        echo '<input type="number" step="0.1" value="' . $line_height . '">';
        echo '</div>';
        echo '</div>';

        if ( in_array( 'color', $this->choices ) ) {
            echo '<div class="wpshop-customize-typography-wrap wpshop-customize-typography-color">';
            echo '<span class="wpshop-customize-typography-label">' . __( 'Color', $this->options->text_domain ) . ':</span>';
            echo '<input class="" type="text" maxlength="7" value="' . $color . '">';
            echo '</div>';
        }

        echo '<input type="hidden" class="wpshop-customize-typography-field" ' . $this->get_link() . ' value="' . esc_attr( $this->value() ) . '">';

    }

    protected function font_style( $styles, $style ) {
        if ( in_array( $style, $styles ) ) {
            return 'active';
        }
        return '';
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