<?php

namespace WPShop\WPCommunity\Customizer\Control;

use WP_Customize_Control;
use WPShop\WPCommunity\Customizer\ThemeColors;

class ColorSchemeControl extends WP_Customize_Control {

    /**
     * @var string
     */
    public $type = 'wpcommunity_color_scheme';

    /**
     * @var string
     */
    public $theme = '';

    /**
     * @inheridoc
     * @return void
     */
    public function enqueue() {
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_style( 'wp-color-picker' );

        $scripts_suffix = wp_scripts_get_suffix();

        wp_enqueue_style(
            'wpcommunity-customize-color-scheme',
            get_template_directory_uri() . "/assets/admin/css/customizer/color-scheme-control{$scripts_suffix}.css",
            [],
            '1.0'
        );

//        wp_register_script(
//            'wpcommunity-color-picker-alpha',
//            get_stylesheet_directory_uri() . "/assets/admin/js/wp-color-picker-alpha{$scripts_suffix}.js",
//            [ 'jquery', 'wp-color-picker' ],
//            false,
//            true
//        );

        // see https://www.npmjs.com/package/vanilla-picker-mini
        wp_register_script(
            'wpcommunity-vanilla-picker',
            get_template_directory_uri() . "/assets/admin/js/lib/vanilla-picker{$scripts_suffix}.js",
            [],
            false,
            true
        );

        // see https://gka.github.io/chroma.js/
        // see https://gka.github.io/palettes/#/9|d|00429d,96ffea,ffffe0|ffffe0,ff005e,93003a|1|1
        wp_register_script(
            'wpcommunity-chroma',
            get_template_directory_uri() . "/assets/admin/js/lib/chroma{$scripts_suffix}.js",
            [],
            false,
            true
        );

        wp_enqueue_script(
            'wpcommunity-customize-color-scheme',
            get_template_directory_uri() . "/assets/admin/js/customizer/color-scheme-control{$scripts_suffix}.js",
            [
                'customize-controls',
                'jquery',
                'wpcommunity-customizer-utils',
                'wpcommunity-vanilla-picker',
                'wpcommunity-chroma',
            ],
            '1.0',
            true
        );

//        wp_localize_script( 'wpcommunity-customize-color-scheme', 'wpcommunity_customize_color_scheme_globals', [
//            'color_data' => Customizer::get_color_variables_data(),
//        ] );
    }

    public function to_json() {
        $this->json['theme'] = $this->theme;
        parent::to_json();
    }

//    public function render_content() {
//    }

    public function content_template() {
        $colors  = ThemeColors::get_variables_for_control();
        $presets = ThemeColors::get_presets();
        ?>
        <div class="wpcommunity-color-scheme wpcommunity-color-scheme-tabs">
            <div class="wpcommunity-color-scheme-tab-labels">
                <div class="wpcommunity-color-scheme-tab-labels__item active" data-tab="dark">
                    <span><?php echo __( 'Dark Theme', 'wpcommunity' ) ?></span>
                </div>
                <div class="wpcommunity-color-scheme-tab-labels__item" data-tab="light">
                    <span><?php echo __( 'Light Theme', 'wpcommunity' ) ?></span>
                </div>
            </div>
            <div class="wpcommunity-color-scheme-tabs-contents">
                <?php $is_active = true ?>
                <?php foreach ( $colors as $theme => $items ): ?>

                    <div class="wpcommunity-color-scheme-tabs-contents__item<?php echo ! $is_active ? ' active' : '' ?>" data-tab="<?php echo esc_attr( $theme ) ?>"<?php echo ! $is_active ? '' : ' style="display:none;"' ?>>
                        <div class="wpcommunity-color-scheme-presets">
                            <?php foreach ( $presets as $preset_item ): ?>
                                <?php
                                $palette = [];
                                foreach ( $preset_item['colors'] as $variable => $colors ) {
                                    $palette[ $variable ] = $colors[ $theme ];
                                }
                                ?>
                                <div class="wpcommunity-color-scheme-presets__item wpcommunity-color-scheme-preset js-wpcommunity-color-scheme--preset"
                                     title="<?php echo esc_html( $preset_item['name'] ) ?>"
                                     data-palette="<?php echo esc_attr( json_encode( $palette ) ) ?>">
                                    <?php foreach ( $palette as $variable => $color ): ?>
                                        <div class="wpcommunity-color-scheme-preset__color"
                                             data-variable="<?php echo esc_attr( $variable ) ?>"
                                             style="background: <?php echo esc_attr( $color ) ?>"></div>
                                    <?php endforeach ?>
                                </div>
                            <?php endforeach ?>
                        </div>

                        <div class="wpcommunity-color-scheme-palette-wrap">
                            <div class="wpcommunity-color-scheme-palette">
                                <?php foreach ( $items as $item ): ?>
                                    <div class="wpcommunity-color-scheme-palette__item wpcommunity-color-scheme-palette-item">
                                        <div class="wpcommunity-color-scheme-palette-item__swatch js-wpcommunity-color-scheme--swatch"
                                             data-variable="<?php echo esc_attr( $item['variable'] ) ?>"
                                             data-color="<?php echo esc_attr( $item['value'] ) ?>"
                                             data-contrast_level="<?php echo esc_attr( $item['contrast_level'] ) ?>"
                                             title="<?php echo esc_attr( $item['label'] ) ?>">
                                            <div class="wpcommunity-color-scheme-palette-item__swatch-inner" style="background: <?php echo esc_attr( $item['value'] ) ?>"></div>
                                        </div>
                                        <span class="wpcommunity-color-scheme-palette-item__title" style="display: none">
                                            <?php echo esc_html( $item['label'] ) ?>
                                        </span>
                                    </div>
                                    <span class="wpcommunity-color-scheme-palette-item__description" style="display: none">
                                        <?php echo esc_html( $item['description'] ) ?>
                                    </span>
                                <?php endforeach ?>
                            </div>
                            <div class="wpcommunity-color-scheme-palette__actions">
                                <span class="dashicons dashicons-image-rotate js-wpcommunity-color-scheme-palette--reset"
                                      title="<?php echo __( 'Reset', 'wpcommunity' ) ?>" style="display: none"></span>
                                <span class="dashicons dashicons-arrow-down-alt2 js-wpcommunity-color-scheme-palette--expand"
                                      data-up="dashicons-arrow-up-alt2"
                                      data-down="dashicons-arrow-down-alt2"></span>
                            </div>
                        </div>
                    </div>
                    <?php $is_active = false ?>
                <?php endforeach ?>
            </div>
            <div class="js-wpcommunity-color-scheme--color-picker"></div>
            <a href="#" class="js-wpcommunity-color-scheme--generate-link"><?php echo __( 'Generate Scheme', 'wpcommunity' ) ?></a>
            <div class="wpcommunity-color-scheme-palette-generation js-wpcommunity-color-scheme--generation-container" style="display: none">
                <div class="wpcommunity-color-scheme-preview_gradient js-wpcommunity-color-scheme--preview-gradient"></div>
                <label>
                    <input type="checkbox" data-name="bezier" checked>
                    <span><?php echo __( 'Bezier interpolation', 'wpcommunity' ) ?></span>
                </label>
                <label>
                    <input type="checkbox" data-name="lightness" checked>
                    <span><?php echo __( 'Correct Lightness', 'wpcommunity' ) ?></span>
                </label>
                <span><?php echo __( 'Choose base colors for generation', 'wpcommunity' ) ?></span>
                <div class="wpcommunity-color-scheme-palette-generation-base">
                    <div class="wpcommunity-color-scheme-palette-generation-base__color js-wpcommunity-color-scheme--generation-base" data-type="color-1">
                        <div class="wpcommunity-color-scheme-palette-generation-base__color-inner" style="background: #2111b0"></div>
                    </div>
                    <span class="button js-wpcommunity-color-scheme--generate"><?php echo __( 'Generate Palette', 'wpcommunity' ) ?></span>
                </div>
                <div class="js-wpcommunity-color-scheme--generation-color-picker"></div>
                <!--                <a href="#" class="">--><?php //echo __( 'Hide', 'wpcommunity' ) ?><!--</a>-->
            </div>
        </div>
        <?php
    }
}
