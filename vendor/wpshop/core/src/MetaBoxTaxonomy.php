<?php

namespace Wpshop\Core;

/**
 * Class Social
 *
 * @version 1.0.0
 *
 * Changelog
 *
 * 1.0.0    init
 */

class MetaBoxTaxonomy {

    public $fields = array();
    public $term_id = 0;

    public function __construct() {

        if ( is_admin() ) {

            add_action( 'region_add_form_fields',  array( $this, 'create_screen_fields'), 10, 1 );
            add_action( 'region_edit_form_fields', array( $this, 'edit_screen_fields' ),  10, 2 );

            add_action( 'created_region', array( $this, 'save_data' ), 10, 1 );
            add_action( 'edited_region',  array( $this, 'save_data' ), 10, 1 );

            //add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts_styles' ) );
            //add_action( 'admin_footer',          array( $this, 'add_admin_js' )        );

        }

    }


    public function add( $args = array() ) {

        if ( ! empty( $args['taxonomy'] ) && is_array( $args['taxonomy'] ) ) {
            foreach ( $args['taxonomy'] as $taxonomy ) {

                add_action( $taxonomy . '_add_form_fields',  array( $this, 'create_screen_fields'), 10, 1 );
                add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_screen_fields' ),  10, 2 );

                add_action( 'created_' . $taxonomy, array( $this, 'save_data' ), 10, 1 );
                add_action( 'edited_' . $taxonomy,  array( $this, 'save_data' ), 10, 1 );

            }
        }

        if ( ! empty( $args['fields'] ) ) {
            $this->fields = $args['fields'];
        }

    }


    public function create_screen_fields( $taxonomy ) {

        // if fields empty
        if ( empty( $this->fields ) ) return;

        foreach ( $this->fields as $field ) {

            $this->field( $field );

        }

    }


    public function edit_screen_fields( $term, $taxonomy ) {

        if ( empty( $this->fields ) ) return;

        // set term id for fields
        $this->term_id = $term->term_id;

        foreach ( $this->fields as $field ) {

            $this->field( $field );

        }

    }


    public function save_data( $term_id ) {

        if ( ! empty( $this->fields ) ) {
            foreach ( $this->fields as $field ) {
                $type = ( ! empty( $field['type'] ) ) ? $field['type'] : 'text';

                $value = isset( $_POST[ $field['name'] ] ) ? $_POST[ $field['name'] ] : '';

                if ( $type == 'text' ) $value = sanitize_text_field( $value );
                if ( $type == 'color' ) $value = sanitize_hex_color( $value );
                if ( $type == 'url' ) $value = esc_url( $value );

                update_term_meta( $term_id, $field['name'], $value );
            }
        }

    }


    protected function field( $field = array() ) {
        if ( ! empty( $field['type'] ) ) {

            if ( $field['type'] == 'text' ) $this->field_text( $field );

        } else {
            $this->field_text( $field );
        }
    }


    protected function field_text( $field = array() ) {

        $label          = ( ! empty( $field['label'] ) ) ? $field['label'] : 'Label' ;
        $description    = ( ! empty( $field['description'] ) ) ? $field['description'] : '' ;

        if ( $this->term_id ) :

            $term_value = get_term_meta( $this->term_id, $field['name'], true );
            if ( empty( $term_value ) ) $term_value = '';

            echo '<tr class="form-field term-' . $field['name'] . '-wrap">';
            echo '<th scope="row">';
            echo '    <label for="' . $field['name'] . '">' . $label . '</label>';
            echo '</th>';
            echo '<td>';
            echo '    <input type="text" id="' . $field['name'] . '" name="' . $field['name'] . '" value="' . esc_attr( $term_value ) . '">';
            if ( ! empty( $description )) echo '    <p class="description">' . $description . '</p>';
            echo '</td>';
            echo '</tr>';

        else :

            echo '<div class="form-field term-' . $field['name'] . '-wrap">';
            echo '	<label for="' . $field['name'] . '">' . $label . '</label>';
            echo '	<input type="text" id="' . $field['name'] . '" name="' . $field['name'] . '" value="">';
            if ( ! empty( $description )) echo '    <p class="description">' . $description . '</p>';
            echo '</div>';

        endif;

    }


    public function load_scripts_styles() {

        // Color picker
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_style( 'wp-color-picker' );

    }


    public function add_admin_js() {

        // Print js only once per page
        if ( did_action( 'Region_Term_Meta_js' ) >= 1 ) {
            return;
        }

        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                $('.region_color_picker').wpColorPicker();
            });
        </script>
        <?php

        do_action( 'Region_Term_Meta_js', $this );

    }

}