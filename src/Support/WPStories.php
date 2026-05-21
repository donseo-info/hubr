<?php

namespace WPShop\WPCommunity\Support;

use WPShop\WPCommunity\Customizer\CssBuilder;

class WPStories {

    /**
     * @return void
     */
    public function init() {
        add_action( 'wpcommunity/layout/css', [ $this, '_add_styles' ] );
    }

    /**
     * @param CssBuilder $css
     *
     * @return void
     */
    public function _add_styles( $css ) {
        if ( ! class_exists( \Wpshop\PluginWpstories\Wpstories::class ) ) {
            return;
        }

        // Устанавливаем по умолчанию значение цвета текущей темы
        $css->new_rule( '.wpstories-preview__text' )
            ->add_property( 'color', 'var(--wpsc-text-color)' )
        ;
    }
}
