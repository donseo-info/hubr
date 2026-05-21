<?php

namespace Wpshop\Core\Customizer\Control;

/**
 * Class SortableCheckboxes
 *
 * 1.0.2    2019-05-10      Fix order save
 * 1.0.1    2019-01-27      Change names, add CSS enqueue
 * 1.0.0    2018-10-24      Init
 */
class SortableCheckboxes extends \WP_Customize_Control {

    /**
     * Control's Type.
     *
     * @var   string
     */
    public $type = 'sortable-checkboxes';


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


        $values = explode( ',', $this->value() );
        $choices = $this->choices;

        $options = array();

        foreach ($values as $val) {
            $options[ $val ] = 1;
        }

        foreach( $choices as $key => $val ) {
            if ( isset( $options[ $key ] ) ) continue;

            if ( in_array( $key, $values ) ) {
                $options[ $key ] = '1';
            } else {
                $options[ $key ] = '0';
            }
        }

        echo '<ul class="wpshop-customize-sortable-checkboxes-list">';

        foreach ( $options as $key => $value ) {
            if ( ! empty( $key ) ) {
                echo '<li>';
                echo '<label>';
                echo '<input name="' . esc_attr( $key ) . '" class="wpshop-customize-sortable-checkboxes-item" type="checkbox" value="' . esc_attr( $value ) . '" ' . checked( $value, true, false ) . '>';
                echo esc_html( $choices[$key] );
                echo '</label>';
                echo '<i class="dashicons dashicons-menu wpshop-customize-sortable-checkboxes-handle"></i>';
                echo '</li>';
            }
        }

        echo '<li style="display: none;">';
        echo '<input type="hidden" class="wpshop-customize-sortable-checkboxes-field" ' . $this->get_link() . ' value="' . esc_attr( $this->value() ) . '">';
        echo '</li>';

        echo '</ul>';

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