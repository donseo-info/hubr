<?php

namespace WPShop\WPCommunity\Metaboxes;

use Wpshop\Core\MetaBox;
use WPShop\WPCommunity\Membership;

class MetaboxMembership extends MetaBox {

    /**
     * Constructor
     */
    public function __construct() {
        $this->set_settings( [
            'prefix'         => 'membership_',
            'post_type'      => [ 'post', 'page' ],
            'meta_box_title' => __( 'Membership', 'wpcommunity' ),
            'context'        => 'side',
        ] );

        parent::__construct();
    }

    /**
     * @return void
     */
    public function render_fields() {
        if ( in_array( $this->pid, Membership::get_always_accessible_pages() ) ) {
            $this->field_select_disabled(
                'access',
                __( 'Access', 'wpcommunity' ),
                [
                    Membership::ACCESS_DEFAULT => __( 'Default', 'wpcommunity' ),
                    Membership::ACCESS_PUBLIC  => __( 'Public', 'wpcommunity' ),
                    Membership::ACCESS_PRIVATE => __( 'Private', 'wpcommunity' ),
                ],
                '',
                Membership::ACCESS_PUBLIC
            );
        } else {

            $this->field_select(
                'access',
                __( 'Access', 'wpcommunity' ),
                [
                    Membership::ACCESS_DEFAULT => __( 'Default', 'wpcommunity' ),
                    Membership::ACCESS_PUBLIC  => __( 'Public', 'wpcommunity' ),
                    Membership::ACCESS_PRIVATE => __( 'Private', 'wpcommunity' ),
                ]
            );
        }
    }

    /**
     * Select
     *
     * @param string $name
     * @param string $label
     * @param array  $options
     * @param string $description
     */
    public function field_select_disabled( $name = '', $label = '', $options = [], $description = '', $value = '' ) {
        $this->add_to_save( $name, 'select' );

        echo '	<tr>';
        echo '		<th><label for="' . $name . '">' . $label . '</label></th>';
        echo '		<td>';
        echo '			<select id="' . $name . '" name="' . $name . '" class="' . $name . '_field" disabled>';

        foreach ( $options as $key => $option ) {
            echo '			<option value="' . $key . '" ' . selected( $value, $key, false ) . '> ' . $option . '</option>';
        }

        echo '			</select>';
        echo '          <p class="description">' . $description . '</p>';
        echo '		</td>';
        echo '	</tr>';
    }
}
