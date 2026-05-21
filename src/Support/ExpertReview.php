<?php

namespace WPShop\WPCommunity\Support;

use WPShop\WPCommunity\Customizer\CssBuilder;

class ExpertReview {

    use ThemeVariablesTrait;

    /**
     * @return void
     */
    public function init() {
        add_action( 'wpcommunity/layout/css', [ $this, '_add_expert_review_styles' ] );
    }

    /**
     * @param CssBuilder $css
     *
     * @return void
     */
    public function _add_expert_review_styles( $css ) {
        if ( ! defined( 'EXPERT_REVIEW_VERSION' ) ) {
            return;
        }

        // Ищем и устанавливаем по умолчанию значение цвета для светлой темы,
        // т.к. фон у блоков Expert Review светлый
        if ( $color = $this->get_theme_color( 'wpsc-text-color' ) ) {
            $css->new_rule( '.expert-review, .expert-review-popup__content' )
                ->add_property( 'color', sanitize_hex_color( $color ) )
            ;
        }
    }
}
