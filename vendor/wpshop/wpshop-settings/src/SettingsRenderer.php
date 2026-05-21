<?php

namespace Wpshop\Settings;

use const ABSPATH;

class SettingsRenderer {

    const VERSION = '1.3';

    /**
     * @var AbstractSettings
     */
    protected $settings;

    /**
     * @var string
     */
    protected $textdomain;

    /**
     * @param AbstractSettings $settings
     * @param string           $textdomain
     */
    public function __construct( AbstractSettings $settings, $textdomain ) {
        $this->settings   = $settings;
        $this->textdomain = $textdomain;
    }

    /**
     * @return void
     */
    public function render_reg_input() {
        $this->settings->register_reg_settings();;
        ?>
        <input name="<?php echo $this->settings->get_reg_input_name( 'license' ) ?>" type="text" value="" placeholder="XX0000-000000-000000000000000000-0000" class="wpshop-settings-text">
        <button type="submit" class="wpshop-settings-button"><?php echo __( 'Activate', $this->textdomain ) ?></button>
        <?php
    }

    /**
     * @param string $title
     * @param string $description
     * @param string $doc_link
     *
     * @return void
     */
    public function render_header( $title, $description = '', $doc_link = '' ) {
        ?>
        <div class="wpshop-settings-header__title">
            <span><?php echo $title ?></span>
            <?php if ( $doc_link ): ?>
                <a href="<?php echo esc_attr( $doc_link ) ?>" target="_blank" rel="noopener" class="wpshop-settings-help-ico">?</a>
            <?php endif ?>
        </div>
        <?php if ( $description ): ?>
            <div class="wpshop-settings-header__description">
                <?php echo $description ?>
            </div>
        <?php endif;
    }

    /**
     * @param string $title
     * @param array  $args
     *
     * @return void
     */
    public function render_label( $title, $args ) {
        ?>
        <div class="wpshop-settings-form-row__label">
            <label for="<?php echo esc_attr( $args['id'] ?? '' ) ?>"><?php echo $title ?></label>
        </div>
        <?php
    }

    /**
     * @param string $description
     * @param string $additional_classes
     *
     * @return void
     */
    public function render_input_description( $description = '', $additional_classes = '' ) {
        if ( ! $description ) {
            return;
        }
        ?>
        <div class="wpshop-settings-form-description<?php echo $additional_classes ? ' ' . $additional_classes : '' ?>">
            <?php echo $description ?>
        </div>
        <?php
    }

    /**
     * @param string $title
     * @param string $description
     * @param string $doc_link
     *
     * @return void
     */
    public function render_subheader( $title, $description = '', $doc_link = '' ) {
        ?>
        <div class="wpshop-settings-subheader">
            <div class="wpshop-settings-subheader__title">
                <span><?php echo $title ?></span>
                <?php if ( $doc_link ): ?>
                    <a href="<?php echo esc_attr( $doc_link ) ?>" target="_blank" rel="noopener" class="wpshop-settings-help-ico">?</a>
                <?php endif ?>
            </div>
            <?php if ( $description ): ?>
                <div class="wpshop-settings-subheader__description">
                    <?php echo $description ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * @param string $name input name
     * @param string $title
     * @param array  $args
     * @param string $description
     *
     * @return void
     */
    public function render_input( $name, $title, array $args = [], $description = '' ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );

        $this->render_label( $title, $args );
        ?>
        <div class="wpshop-settings-form-row__body">
            <?php $this->render_input_field( $name, $args ); ?>
            <?php $this->render_input_description( $description ); ?>
        </div>
        <?php
    }

    /**
     * @param string $name
     * @param array  $args
     *
     * @return void
     */
    public function render_input_field( $name, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'type' => 'text',
            'id'   => uniqid( "{$name}." ),
        ] );

        $input_name = $this->settings->get_input_name( $name );
        $attributes = [];
        foreach ( [ 'type', 'min', 'max', 'step', 'placeholder' ] as $attr ) {
            if ( array_key_exists( $attr, $args ) ) {
                $attributes[] = "$attr=\"{$args[$attr]}\"";
            }
        }
        $attributes = implode( ' ', $attributes );
        $attributes = $attributes ? " $attributes" : '';

        $classes = array_key_exists( 'classes', $args ) ? $args['classes'] : [];
        $classes = is_array( $classes ) ? $classes : [ $classes ];
        array_unshift( $classes, 'wpshop-settings-text' );
        ?>
        <input class="<?php echo implode( ' ', $classes ) ?>"
               id="<?php echo esc_attr( $args['id'] ) ?>"
               name="<?php echo esc_attr( $input_name ) ?>"
               value="<?php echo esc_attr( $this->settings->get_value( $name ) ) ?>"<?php echo $attributes ?>>
        <?php
    }

    /**
     * @param string $name
     * @param string $title
     * @param array  $args
     * @param string $description
     *
     * @return void
     */
    public function render_password_input( $name, $title, array $args = [], $description = '' ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );

        $this->render_label( $title, $args );
        ?>
        <div class="wpshop-settings-form-row__body">
            <?php $this->render_password_input_field( $name, $args ); ?>
            <?php $this->render_input_description( $description ); ?>
        </div>
        <?php
    }

    /**
     * @param string $name
     * @param array  $args
     *
     * @return void
     */
    public function render_password_input_field( $name, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'type'              => 'text',
            'id'                => uniqid( "{$name}." ),
            'placeholder'       => '',
            'placeholder_value' => '*****',
        ] );

        $input_name = $this->settings->get_input_name( $name );

        $classes = array_key_exists( 'classes', $args ) ? $args['classes'] : [];
        $classes = is_array( $classes ) ? $classes : [ $classes ];
        array_unshift( $classes, 'wpshop-settings-text' );

        ?>
        <input class="<?php echo implode( ' ', $classes ) ?>"
               id="<?php echo esc_attr( $args['id'] ) ?>"
               name="<?php echo esc_attr( $input_name ) ?>"
               value=""
               placeholder="<?php echo ! $this->settings->get_value( $name ) ? $args['placeholder'] : $args['placeholder_value'] ?>">
        <?php
    }


    /**
     * @param string $name input name
     * @param string $title
     * @param array  $options
     * @param array  $args
     * @param string $description
     *
     * @return void
     */
    public function render_select( $name, $title, array $options, array $args = [], $description = '' ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );
        $this->render_label( $title, $args );
        ?>
        <div class="wpshop-settings-form-row__body">
            <?php $this->render_select_field( $name, $options, $args ); ?>
            <?php $this->render_input_description( $description ); ?>
        </div>
        <?php
    }

    /**
     * @param string $name
     * @param array  $options
     * @param array  $args
     *
     * @return void
     */
    public function render_select_field( $name, array $options, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );

        $input_name = $this->settings->get_input_name( $name );

        $classes = array_key_exists( 'classes', $args ) ? $args['classes'] : [];
        $classes = is_array( $classes ) ? $classes : [ $classes ];
        array_unshift( $classes, 'wpshop-settings-select' );

        ?>
        <select id="<?php echo esc_attr( $args['id'] ) ?>"
                name="<?php echo esc_attr( $input_name ) ?>"
                class="<?php echo implode( ' ', $classes ) ?>">
            <?php foreach ( $options as $value => $label ): ?>
                <option value="<?php echo esc_attr( $value ) ?>"<?php selected( $this->settings->get_value( $name ), $value ) ?>><?php echo $label ?></option>
            <?php endforeach ?>
        </select>
        <?php
    }

    /**
     * @param string $name input name
     * @param string $label
     * @param array  $args
     * @param string $description
     *
     * @return void
     */
    public function render_checkbox( $name, $label = '', array $args = [], $description = '' ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );
        ?>
        <label for="<?php echo esc_attr( $args['id'] ) ?>" class="wpshop-settings-form-label">
            <?php $this->render_checkbox_field( $name, $label, $args ); ?>
        </label>
        <?php
        $this->render_input_description( $description, 'wpshop-settings-form-description--switch-box' );
    }

    /**
     * @param string $name
     * @param string $label
     * @param array  $args
     *
     * @return void
     */
    public function render_checkbox_field( $name, $label = '', array $args = [] ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );

        $input_name = $this->settings->get_input_name( $name );
        $classes    = implode( ' ', (array) ( $args['classes'] ?? [] ) );
        $classes    = $classes ? " $classes" : '';

        $data_attributes = [];
        foreach ( $args as $key => $value ) {
            if ( substr( $key, 0, 5 ) === 'data-' ) {
                $data_attributes[] = "$key=\"$value\"";
            }
        }
        $data_attributes = implode( ' ', $data_attributes );
        $data_attributes = $data_attributes ? " $data_attributes" : '';
        ?>
        <input type="hidden" name="<?php echo $input_name ?>" value="0">
        <input type="checkbox"
               class="wpshop-settings-switch-box<?php echo $classes ?>"
               name="<?php echo esc_attr( $input_name ) ?>"
               id="<?php echo esc_attr( $args['id'] ) ?>"
            <?php echo $data_attributes ?>
               value="1"<?php
        checked( $this->settings->get_value( $name ) );
        disabled( $args['disabled'] ?? false ) ?>>
        <?php echo esc_html( $label ) ?>
        <?php
    }

    /**
     * @param string $name
     * @param string $title
     * @param array  $args
     * @param string $description
     *
     * @return void
     */
    public function render_textarea( $name, $title, $args = [], $description = '' ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );

        $this->render_label( $title, $args );
        ?>
        <div class="wpshop-settings-form-row__body">
            <?php $this->render_textarea_field( $name, $args ) ?>
            <?php $this->render_input_description( $description ); ?>
        </div>
        <?php
    }

    /**
     * @param string $name
     * @param array  $args
     *
     * @return void
     */
    public function render_textarea_field( $name, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'cols' => '',
            'rows' => 5,
            'id'   => uniqid( "{$name}." ),
        ] );

        $input_name = $this->settings->get_input_name( $name );


        $classes = array_key_exists( 'classes', $args ) ? $args['classes'] : [];
        $classes = is_array( $classes ) ? $classes : [ $classes ];
        array_unshift( $classes, 'wpshop-settings-text' );
        ?>
        <textarea class="<?php echo implode( ' ', $classes ) ?>"
                  name="<?php echo esc_attr( $input_name ) ?>"
                  id="<?php echo esc_attr( $args['id'] ) ?>"
                  cols="<?php echo esc_attr( $args['cols'] ) ?>"
                  rows="<?php echo esc_attr( $args['rows'] ) ?>"><?php echo esc_textarea( (string) $this->settings->get_value( $name ) ) ?></textarea>
        <?php
    }

    /**
     * @param string $name input name
     * @param string $label
     * @param array  $args
     * @param string $description
     *
     * @return void
     */
    public function render_color_picker( $name, $label, array $args = [], $description = '' ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );

        $this->render_label( $label, $args );
        ?>
        <div class="wpshop-settings-form-row__body">
            <?php $this->render_color_picker_field( $name, $args ); ?>
            <?php $this->render_input_description( $description ); ?>
        </div>
        <?php
    }

    /**
     * @param string $name
     * @param array  $args
     *
     * @return void
     */
    public function render_color_picker_field( $name, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );

        $input_name = $this->settings->get_input_name( $name );
        ?>
        <input type="text"
               id="<?php echo esc_attr( $args['id'] ) ?>"
               name="<?php echo $input_name ?>"
               value="<?php echo esc_attr( $this->settings->get_value( $name ) ) ?>"
               data-default-color="<?php echo esc_attr( $args['default'] ?? '' ) ?>"
               class="js-wpshop-settings-color-picker">
        <?php
    }

    /**
     * @param callable $cb
     *
     * @return void
     */
    public function wrap_form( $cb ) {
        $has_cap = current_user_can( 'manage_options' );

        if ( $has_cap && $this->settings->verify() ) {
            ?>
            <form action="<?php echo add_query_arg( 'locale', $this->settings->get_locale(), 'options.php' ) ?>" method="post">
            <?php
        }

        $cb( $this->settings, $this );

        if ( $has_cap && $this->settings->verify() ) {
            ?>
            <div class="wpshop-settings-container__footer js-wpshop-settings-container-save">
                <?php $this->settings->register_settings(); ?>
                <button type="submit" class="wpshop-settings-button"><?php echo __( 'Save', $this->textdomain ) ?></button>
            </div>
            </form>
            <?php
        }
    }

    /**
     * @return string
     *
     * @see wp_dropdown_languages()
     */
    public function wp_dropdown_languages( array $args = [] ) {
        $parsed_args = wp_parse_args(
            $args,
            [
                'id'                          => 'locale',
                'name'                        => 'locale',
                'languages'                   => [],
                'translations'                => [],
                'selected'                    => '',
                'echo'                        => 1,
                'show_available_translations' => true,
                'show_option_site_default'    => false,
                'show_option_en_us'           => true,
                'explicit_option_en_us'       => false,
            ]
        );

        // Bail if no ID or no name.
        if ( ! $parsed_args['id'] || ! $parsed_args['name'] ) {
            return;
        }

        // English (United States) uses an empty string for the value attribute.
        if ( 'en_US' === $parsed_args['selected'] && ! $parsed_args['explicit_option_en_us'] ) {
            $parsed_args['selected'] = '';
        }

        $translations = $parsed_args['translations'];
        if ( empty( $translations ) ) {
            require_once ABSPATH . 'wp-admin/includes/translation-install.php';
            $translations = wp_get_available_translations();
        }

        /*
         * $parsed_args['languages'] should only contain the locales. Find the locale in
         * $translations to get the native name. Fall back to locale.
         */
        $languages = [];
        foreach ( $parsed_args['languages'] as $locale ) {
            if ( isset( $translations[ $locale ] ) ) {
                $translation = $translations[ $locale ];
                $languages[] = [
                    'language'    => $translation['language'],
                    'native_name' => $translation['native_name'],
                    'lang'        => current( $translation['iso'] ),
                ];

                // Remove installed language from available translations.
                unset( $translations[ $locale ] );
            } else {
                $languages[] = [
                    'language'    => $locale,
                    'native_name' => $locale,
                    'lang'        => '',
                ];
            }
        }

        $translations_available = ( ! empty( $translations ) && $parsed_args['show_available_translations'] );

        // Holds the HTML markup.
        $structure = [];

        // List installed languages.
        if ( $translations_available ) {
            $structure[] = '<optgroup label="' . esc_attr_x( 'Installed', 'translations' ) . '">';
        }

        // Site default.
        if ( $parsed_args['show_option_site_default'] ) {
            $structure[] = sprintf(
                '<option value="site-default" data-installed="1"%s>%s</option>',
                selected( 'site-default', $parsed_args['selected'], false ),
                __( 'Default' )
            );
        }

        if ( $parsed_args['show_option_en_us'] ) {
            $value       = ( $parsed_args['explicit_option_en_us'] ) ? 'en_US' : '';
            $structure[] = sprintf(
                '<option value="%s" lang="en" data-installed="1"%s>English (United States)</option>',
                esc_attr( $value ),
                selected( $value, $parsed_args['selected'], false )
            );
        }

        // List installed languages.
        foreach ( $languages as $language ) {
            $structure[] = sprintf(
                '<option value="%s" lang="%s"%s data-installed="1">%s</option>',
                esc_attr( $language['language'] ),
                esc_attr( $language['lang'] ),
                selected( $language['language'], $parsed_args['selected'], false ),
                esc_html( $language['native_name'] )
            );
        }
        if ( $translations_available ) {
            $structure[] = '</optgroup>';
        }

        // List available translations.
        if ( $translations_available ) {
            $structure[] = '<optgroup label="' . esc_attr_x( 'Available', 'translations' ) . '">';
            foreach ( $translations as $translation ) {
                $structure[] = sprintf(
                    '<option value="%s" lang="%s"%s>%s</option>',
                    esc_attr( $translation['language'] ),
                    esc_attr( current( $translation['iso'] ) ),
                    selected( $translation['language'], $parsed_args['selected'], false ),
                    esc_html( $translation['native_name'] )
                );
            }
            $structure[] = '</optgroup>';
        }

        // Combine the output string.
        $output = sprintf( '<select name="%s" id="%s">', esc_attr( $parsed_args['name'] ), esc_attr( $parsed_args['id'] ) );
        $output .= implode( "\n", $structure );
        $output .= '</select>';

        if ( $parsed_args['echo'] ) {
            echo $output;
        }

        return $output;
    }
}
