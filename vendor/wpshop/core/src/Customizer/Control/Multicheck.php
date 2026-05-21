<?php

namespace Wpshop\Core\Customizer\Control;

/**
 * Class Multicheck
 *
 * @version 1.0.1
 *
 * Changelog
 *
 * 1.0.1    2019-04-18      fix checked function
 * 1.0.0    2018-10-24      init
 */
class Multicheck extends \WP_Customize_Control {

    /**
     * Control's Type.
     *
     * @var   string
     */
    public $type = 'multicheck';


    /**
     * Render the control's content.
     *
     * @return void
     */
    public function render_content() {
        if ( empty( $this->choices ) ) {
            return;
        }

        if ( ! empty( $this->label ) ) {
            echo '<span class="customize-control-title">' . esc_html( $this->label ) . '</span>';
        }

        if ( ! empty( $this->description ) ) {
            echo '<span class="description customize-control-description">' . $this->description . '</span>';
        }

        $multi_values = ! is_array( $this->value() ) ? explode( ',', $this->value() ) : $this->value();

        echo '<ul>';
        foreach ( $this->choices as $value => $label ) {
            $val = esc_attr( $value );
            $checked = checked( in_array( $value, $multi_values ), true, false );

            echo '<li><label>';
            echo '<input type="checkbox" value="' . $val . '" ' . $checked . '> ';
            echo esc_html( $label );
            echo '</label></li>';

        }
        echo '</ul>';
        echo '<input type="hidden" ' . $this->get_link() . ' value="' . esc_attr( implode( ',', $multi_values ) ) . '">';
    }


    /**
     * Enqueue control related scripts/styles.
     *
     * @return void
     */
    public function enqueue() {
        wp_enqueue_script( 'wpshop-customize-control-' . $this->type, trailingslashit( get_template_directory_uri() ) . 'vendor/wpshop/core/resources/js/customizer/' . $this->type . '.js', [ 'jquery' ], null, true );
    }

}