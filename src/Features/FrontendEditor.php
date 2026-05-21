<?php

namespace WPShop\WPCommunity\Features;

class FrontendEditor {

    /**
     * @return void
     */
    public function init() {
        add_filter( 'ajax_query_attachments_args', [ $this, '_limit_access_to_attachment_to_authors_only' ] );
    }

    /**
     * @param array $query
     *
     * @return array
     */
    public function _limit_access_to_attachment_to_authors_only( $query ) {
        if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {
            $user_id         = get_current_user_id();
            $query['author'] = $user_id;
        }

        return $query;
    }

    /**
     * @return $this
     */
    public function register() {
        add_action( 'wp_enqueue_scripts', function () {
            wp_enqueue_style( 'wpcommunity-editor' );
        } );

        return $this;
    }

    /**
     * @return $this
     */
    public function grant_upload_cap() {

        /**
         * @since 1.0
         */
        $roles_to_upgrade = (array) apply_filters( 'wpcommunity/frontend_editor/upload_files_roles', [
            'contributor',
            'subscriber',
        ] );

        foreach ( $roles_to_upgrade as $role ) {
            $role = get_role( $role );
            if ( $role && ! $role->has_cap( 'upload_files' ) ) {
                $role->add_cap( 'upload_files' );
            }
        }

        return $this;
    }

    /**
     * @param string $content
     * @param string $editor_id
     *
     * @return void
     */
    public function wp_editor( $content, $editor_id ) {
        $settings = [
            'media_buttons'    => true,
            'textarea_rows'    => 10,
            'drag_drop_upload' => 1,
            'teeny'            => false,
            'quicktags'        => true,

            //'teeny'         => true,
            //		    'tinymce'       => [
            //			    'toolbar1' => 'bold,italic,underline,blockquote,bullist,numlist,alignleft,aligncenter,alignright,undo,redo,link,unlink,removeformat,source',
            //			    'toolbar2' => '',
            //		    ],
            //'quicktags' => [
            //    'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,pre,more,close',
            //],

        ];

        /**
         * @since 1.0
         */
        $settings = apply_filters( 'wpcommunity/editor/settings', $settings );


        wp_editor( $content, $editor_id, $settings );
    }
}
