<?php

namespace WPShop\WPCommunity\Layout\Profile;

class UserComments {

    /**
     * @param $user_id
     * @param $page
     *
     * @return int|\WP_Comment[]
     */
    public function get_comments( $user_id, $page = 1 ) {
        $per_page    = $this->get_per_page();
        $total_count = get_comments( [
            'user_id' => $user_id,
            'count'   => true,
        ] );

        $args = [
            'user_id' => $user_id,
            'orderby' => 'comment_date_gmt',
        ];
        if ( $total_count > $per_page ) {
            $args['number'] = $per_page;
            if ( $page > 1 ) {
                $args['offset'] = ( $page - 1 ) * $per_page;
            }


        }

        return get_comments( $args );
    }

    /**
     * @return int
     */
    protected function get_per_page() {
        return min( max( absint( apply_filters( 'wpcommunity/user_comments/per_page', 100 ) ), 1 ), 1000 );
    }
}
