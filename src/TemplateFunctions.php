<?php

namespace WPShop\WPCommunity;

class TemplateFunctions {

    /**
     * @return void
     */
    public function init() {
    }

    /**
     * @param string $excerpt
     * @param string $length
     *
     * @return string
     */
    public function get_the_excerpt( $excerpt, $length = null ) {
        if ( null === $length ) {
            $length = 200;
        }

        /**
         * Allows you to set to use the full content of the post instead of an excerpt
         *
         * [ru] Позволяет установить использование полного контента записи вместо отрывка
         *
         * @hooked \WPShop\WPCommunity\Customizer\Customizer\DefaultHooks::_set_post_card_user_full_text()
         *
         * @since 1.1
         */
        $length = (int) apply_filters( 'wpcommunity/template_functions/excerpt_length', $length );

        $excerpt = strip_tags( $excerpt );
        $excerpt = $this->substring_by_word( $excerpt, $length );

        return $excerpt;
    }


    /**
     * Substring string by length
     *
     * @param string $string
     * @param int    $length
     * @param string $delimiter
     *
     * @return string
     */
    public function substring_by_word( $string, $length = 200, $delimiter = ' ' ) {

        if ( $length < 100 ) {
            $offset = ceil( $length * 0.3 );
        } else {
            $offset = ceil( $length * 0.2 );
        }

        if ( mb_strlen( $string ) > $length ) {
            $search = mb_strpos( $string, $delimiter, $length );
            if ( $search ) {
                $substr = mb_substr( $string, 0, $search );

                // ищем конец предложения
                $substr_with_offset = mb_substr( $string, 0, $search + $offset );

                $symbols = [ '.', '!', '?', ';' ];
                foreach ( $symbols as $symbol ) {
                    $search_end = mb_strpos( $substr_with_offset, $symbol, $length - $offset );
                    if ( $search_end ) {
                        $substr = mb_substr( $string, 0, $search_end + 1 );
                    }
                }

                $substr = rtrim( $substr, ',:' );

                return $substr;
            }
        }

        return $string;
    }
}
