<?php

namespace WPShop\WPCommunity;

use DateTime;
use DateTimeImmutable;

class Helper {

    public function init() {

    }


    function days_between_dates( $first_date, $second_date ) {
        $first_date  = new DateTime( $first_date );
        $second_date = new DateTime( $second_date );

        $pos_diff = $first_date->diff( $second_date )->format( "%r%a" ); //3
//		$neg_diff = $second_date->diff($first_date)->format("%r%a"); //-3

        return $pos_diff;
    }


    /**
     * Returns the form of a word depending on the $number
     *
     * @param int   $number
     * @param array $forms
     *
     * @return mixed
     */
    public function get_word_forms( $number, $forms ) {
        $cases = [ 2, 0, 1, 1, 1, 2 ];

        return $forms[ ( $number % 100 > 4 && $number % 100 < 20 ) ? 2 : $cases[ min( $number % 10, 5 ) ] ];
    }


    public function beauty_date( $date ) {

        $time = strtotime( $date );

        $current = current_time( 'timestamp' );
        $diff    = $current - $time;

        if ( $diff < 30 ) {
            return __( 'now', 'wpcommunity' );
        } elseif ( $diff < HOUR_IN_SECONDS ) {
            $value = round( $diff / MINUTE_IN_SECONDS );

            return $value . ' ' . _n( 'minute', 'minutes', $value, 'wpcommunity' );
        } elseif ( $diff < DAY_IN_SECONDS ) {
            $value = round( $diff / HOUR_IN_SECONDS );

            return $value . ' ' . _n( 'hour', 'hours', $value, 'wpcommunity' );
        } elseif ( $diff < MONTH_IN_SECONDS ) {
            $value = round( $diff / DAY_IN_SECONDS );

            return $value . ' ' . _n( 'day', 'days', $value, 'wpcommunity' );
        } elseif ( $diff < YEAR_IN_SECONDS ) {
            $value = round( $diff / MONTH_IN_SECONDS );

            return $value . ' ' . _n( 'month', 'months', $value, 'wpcommunity' );
        } else {
            $value = round( $diff / YEAR_IN_SECONDS );

            return $value . ' ' . _n( 'year', 'years', $value, 'wpcommunity' );
        }

    }

}
