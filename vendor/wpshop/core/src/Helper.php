<?php

namespace Wpshop\Core;

/**
 * Class Helper
 *
 * 1.0.1    2019-04-19      improve substring_by_word
 */
class Helper {

    protected $options;

    public function __construct( ThemeOptions $options = null ) {
        if ( $options ) {
            $this->options = $options;
        }
    }


    /**
     * Symbols count
     *
     * @since       1.0
     * @updated     2018-04-04
     *
     * @param string $text
     *
     * @return int
     */
    public function symbols_count( $text = '' ) {

        // удаляем все известные шорткоды
        $text = strip_shortcodes($text);
        // удаляем все остальные шорткоды
        $text = preg_replace( '~\[[^\]]+\]~', '', $text );
        // удаляем все теги
        $text = strip_tags($text);
        // переводит спецсимволы в символы
        $text = htmlspecialchars_decode($text);
        // два и больше пробела\перевода строки меняем на один
        $text = preg_replace('/([\s]{2,})/',' ', $text);
        // удаляем пробелы до и после текста
        $text = trim($text);
        // считаем и выводим
        $count = mb_strlen( utf8_decode( $text ) );

        return $count;
    }


    /**
     * Get IP
     *
     * @return mixed
     */
    public static function get_ip() {
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }


    /**
     * Substring string by length
     *
     * @param $string
     * @param int $length
     * @param string $del
     *
     * @return string
     */
    public function substring_by_word( $string, $length = 200, $del = ' ' ) {

        if ( $length < 100 ) {
            $offset = ceil( $length * 0.2 );
        } else {
            $offset = ceil( $length * 0.1 );
        }

        if ( mb_strlen( $string ) > $length ) {
            $search = mb_strpos( $string, $del, $length );
            if ( $search ) {
                $substr = mb_substr( $string, 0, $search );

                // ищем конец предложения
                $substr_with_offset = mb_substr( $string, 0, $search + $offset );

                $symbols = ['.', '!', '?', ';'];
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


    /**
     * Round number, ex. for views
     *
     * @param int $num
     *
     * @return int|string
     */
    public function rounded_number( $num = 0 ) {
        if ( $num > 1000000 ) {
            return round( ( $num / 1000000 ), 1 ) . __( 'm.', $this->options->text_domain );
        }
        if ( $num > 100000 ) {
            return round( ( $num / 1000 ) ) . __( 'k.', $this->options->text_domain );
        }
        if ( $num > 1000 ) {
            return round( ( $num / 1000 ), 1 ) . __( 'k.', $this->options->text_domain );
        }
        return $num;
    }

    /**
     * Returns the form of a word depending on the $number
     *
     * @param int $number
     * @param array $forms
     * @return mixed
     */
    public function get_word_forms( $number, $forms ) {
        $cases = array ( 2, 0, 1, 1, 1, 2 );
        return $forms[ ( $number % 100 > 4 && $number % 100 < 20 ) ? 2 : $cases[ min( $number % 10, 5 ) ] ];
    }


    /**
     * Parse string with ids, without duplicate for default
     *
     * @param string|int $ids
     * @param boolean $duplicate
     *
     * @return array
     */
    public static function parse_ids_from_string( $ids = '', $duplicate = false ) {

        $result = [];
        if ( is_string( $ids ) || is_numeric( $ids ) ) {
            $exp = explode( ',', $ids );
            foreach ( $exp as $el ) {
                $el = trim( $el );
                if ( is_numeric( $el ) ) {
                    if ( ! $duplicate ) {
                        if ( ! in_array($el, $result) ) $result[] = $el;
                    } else {
                        $result[] = $el;
                    }
                }
            }
        }

        return $result;
    }

}