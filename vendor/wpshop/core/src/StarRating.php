<?php

namespace Wpshop\Core;

/**
 * Class StarRating
 *
 * 1.0.7    2020-05-27      Renamed rating class to wp-star-rating
 * 1.0.6    2020-04-03      Add filter for count text
 * 1.0.5    2020-01-09      Add filter for turning on microdata
 * 1.0.4    2019-12-18      Remove schema.org
 * 1.0.3    2019-05-22      Add argument $show_text to the_rating function
 * 1.0.2    2019-03-26      Fix warning $rating_count_text
 * 1.0.1    2018-09-07      Fix warning HTTP_X_FORWARDED_FOR
 */
class StarRating {

    protected $options;

    private $best = 5;
    private $worst = 1;
    private $expired = DAY_IN_SECONDS;

    public function __construct( ThemeOptions $options ) {
        $this->options = $options;
    }

    public function ajax_actions() {
        add_action( 'wp_ajax_wpshop_star_rating_submit', array( $this, 'submit' ) );
        add_action( 'wp_ajax_nopriv_wpshop_star_rating_submit', array( $this, 'submit' ) );
    }

    public function the_rating( $post_id = 0, $show_text = true ) {
        if ( empty( $post_id ) ) return;

        $rating_value = (float) get_post_meta( $post_id, 'wpshop_rating_value', true );
        $rating_sum   = (int) get_post_meta( $post_id, 'wpshop_rating_sum', true );
        $rating_count = (int) get_post_meta( $post_id, 'wpshop_rating_count', true );
        $rating_ips   = get_post_meta( $post_id, 'wpshop_rating_ips', true );

        $star_rating_class = '';

        if ( empty( $rating_ips ) ) $rating_ips = array();

        // remove expired ips
        $rating_ips = $this->remove_expired_ips( $rating_ips, $post_id );
        if ( $this->check_ip( $rating_ips ) ) {
            $star_rating_class .= ' disabled';
        }

        $stars = round( $rating_value );
        $star_rating_class .= ' star-rating--score-'. $stars;

        echo '<div class="wp-star-rating js-star-rating' . $star_rating_class . '" data-post-id="'. $post_id .'" data-rating-count="'. $rating_count .'" data-rating-sum="'. $rating_sum .'" data-rating-value="'. $rating_value .'">';

        for ( $i=1; $i<=5; $i++ ) {
            echo '<span class="star-rating-item js-star-rating-item" data-score="'. $i .'"><svg aria-hidden="true" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="i-ico"><path fill="currentColor" d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z" class="ico-star"></path></svg></span>';
        }

        echo '</div>';


        if ( $show_text ) {
            if ( $rating_count > 0 ) {

                $rating_count_text = apply_filters( 'wpshop_rating_count_text', 'assessment' );

                if ( get_bloginfo( 'language' ) == 'ru-RU' ) {
                    global $wpshop_helper;
                    $rating_count_text = $wpshop_helper->get_word_forms( $rating_count, array(
                        'оценка',
                        'оценки',
                        'оценок'
                    ) );
                }

                if ( get_bloginfo( 'language' ) == 'uk' ) {
                    global $wpshop_helper;
                    $rating_count_text = $wpshop_helper->get_word_forms( $rating_count, array(
                        'оцінка',
                        'оцінки',
                        'оцінок'
                    ) );
                }

                echo '<div class="star-rating-text"><em>( <strong>' . $rating_count . '</strong> ' . $rating_count_text . ', ' . __( 'average', $this->options->text_domain ) . ' <strong>' . $rating_value . '</strong> ' . __( 'from', $this->options->text_domain ) . ' <strong>' . $this->best . '</strong> )</em></div>';
            } else {
                echo '<div class="star-rating-text"><em>( ' . __( 'No ratings yet', $this->options->text_domain ) . ' )</em></div>';
            }
        }

        $markup = apply_filters( 'wpshop_rating_markup', 'no' );

        if ( $markup == 'schema' && $rating_count > 0 ) {
            echo '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
            echo '<meta itemprop="bestRating" content="' . $this->best . '">';
            echo '<meta itemprop="worstRating" content="' . $this->worst . '">';
            echo '<meta itemprop="ratingValue" content="' . $rating_value . '">';
            echo '<meta itemprop="ratingCount" content="' . $rating_count . '">';
            echo '</div>';
        }
    }

    public function get_the_rating( $post_id = 0 ) {
        if ( empty( $post_id ) ) return;

        $rating_value = (float) get_post_meta( $post_id, 'wpshop_rating_value', true );
        $rating_sum   = (int) get_post_meta( $post_id, 'wpshop_rating_sum', true );
        $rating_count = (int) get_post_meta( $post_id, 'wpshop_rating_count', true );

        $star_rating_class = '';

        echo '<div class="star-rating-text">';
        echo '<em><strong>' . $rating_value . '</strong></em>';
        echo '</div>';

        $stars = round( $rating_value );
        $star_rating_class .= ' star-rating--score-'. $stars;

        echo '<div class="wp-star-rating' . $star_rating_class . '" data-post-id="'. $post_id .'" data-rating-count="'. $rating_count .'" data-rating-sum="'. $rating_sum .'" data-rating-value="'. $rating_value .'">';

        for ( $i=1; $i<=5; $i++ ) {
            echo '<span class="star-rating-item" data-score="'. $i .'"><svg aria-hidden="true" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="i-ico"><path fill="currentColor" d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z" class="ico-star"></path></svg></span>';
        }

        echo '</div>';
    }

    public function submit() {

        $nonce = $_POST['nonce'];

        // проверяем nonce код, если проверка не пройдена прерываем обработку
        if ( ! wp_verify_nonce( $nonce, 'wpshop-nonce' ) ) {
            wp_send_json_error( 'Error nonce validation' );
            die();
        }

        if ( empty( $_POST['post_id'] ) || empty( $_POST['score'] ) )
            wp_send_json_error( 'Not enough data' );

        $post_id = (int) $_POST['post_id'];
        $score = (int) $_POST['score'];

        $post = get_post( $post_id );
        if ( ! $post ) wp_send_json_error( 'Post not found' );

        $rating_sum = get_post_meta( $post->ID, 'wpshop_rating_sum', true );
        $rating_count = get_post_meta( $post->ID, 'wpshop_rating_count', true );
        $rating_ips = get_post_meta( $post->ID, 'wpshop_rating_ips', true );
        if ( empty( $rating_ips ) ) $rating_ips = array();

        //print_r($rating_ips);

        // remove expired ips
        $ip = $this->get_ip();
        $rating_ips = $this->remove_expired_ips( $rating_ips, $post->ID );
        if ( $this->check_ip( $rating_ips ) ) wp_send_json_error( 'already' );

        // add new ip
        $new_ip = array(
            'ip'        => $ip,
            'expired'   => time() + $this->expired,
        );
        $rating_ips[] = $new_ip;

        // calculate
        if ( empty( $rating_sum ) ) $rating_sum = 0;
        if ( empty( $rating_count ) ) $rating_count = 0;

        $rating_sum = $rating_sum + $score;
        $rating_count++;

        $rating_value = round( $rating_sum / $rating_count, 2 );

        update_post_meta( $post->ID, 'wpshop_rating_sum', $rating_sum );
        update_post_meta( $post->ID, 'wpshop_rating_value', $rating_value );
        update_post_meta( $post->ID, 'wpshop_rating_count', $rating_count );
        update_post_meta( $post->ID, 'wpshop_rating_ips', $rating_ips );

        wp_send_json_success();

        die();

    }

    private function remove_expired_ips( $rating_ips = array(), $post_id = 0 ) {
        // remove expired ips
        $changed = false;
        if ( ! empty( $rating_ips ) ) {
            foreach ( $rating_ips as $key => $rating_ip ) {
                if ( $rating_ip['expired'] < time() ) {
                    unset( $rating_ips[ $key ] );
                    $changed = true;
                }
            }
        }

        if ( ! empty( $post_id ) && $changed ) {
            update_post_meta( $post_id, 'wpshop_rating_ips', $rating_ips );
        }

        return $rating_ips;
    }

    private function get_ip() {
        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    private function check_ip( $rating_ips = array() ) {

        $ip = $this->get_ip();

        if ( ! empty( $rating_ips ) ) {
            foreach ( $rating_ips as $key => $rating_ip ) {
                if ( $rating_ip['ip'] == $ip ) {
                    return true;
                }
            }
        }
        return false;

    }

}