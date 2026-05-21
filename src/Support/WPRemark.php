<?php

namespace WPShop\WPCommunity\Support;

class WPRemark {

    use ThemeVariablesTrait;

    /**
     * @return void
     */
    public function init() {
        add_filter( 'wpremark_styles', [ $this, '_append_styles' ] );
    }

    /**
     * @param $styles
     *
     * @return array
     */
    public function _append_styles( $styles ) {
        // Ищем и устанавливаем по умолчанию значение цвета для светлой темы,
        // т.к. фон у блоков wpremark светлый
        if ( $color = $this->get_theme_color( 'wpsc-text-color' ) ) {
            $styles['.wpremark-body'][] = 'color:' . sanitize_hex_color( $color ) . '';
        }

        return $styles;
    }
}
