<?php

namespace WPShop\WPCommunity\Features;

use function WPShop\WPCommunity\get_setting;

class GoogleReCaptcha {

    const FORM_SIGN_UP = 'sign_up';


    /**
     * @return void
     */
    public function init() {
        add_filter( 'wpcommunity/assets/script_deps', [ $this, '_add_script_deps' ] );
        add_filter( 'wpcommunity/assets/script_globals', [ $this, '_add_options' ] );
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function inclide_script( $type ) {
        switch ( $type ) {
            case self::FORM_SIGN_UP:
                return get_setting( 'page.profile' ) && is_page( get_setting( 'page.profile' ) );
            default:
                return false;
        }
    }


    /**
     * @param array $scripts_deps
     *
     * @return array
     */
    public function _add_script_deps( $scripts_deps ) {
        foreach ( [ GoogleReCaptcha::FORM_SIGN_UP ] as $type ) {
            if ( get_setting( "grecaptcha.{$type}.enabled" ) && $this->inclide_script( $type ) ) {
                $scripts_deps[] = 'wpcommunity-g-recaptcha';
                break;
            }
        }

        return $scripts_deps;
    }

    /**
     * @param array $script_globals
     *
     * @return array
     */
    public function _add_options( $script_globals ) {
        $enabled = false;
        foreach ( [ self::FORM_SIGN_UP ] as $form ) {
            if ( $this->enabled( $form ) ) {
                $script_globals['grecaptcha']['forms'][] = $form;

                $enabled = true;
            }
        }

        if ( $enabled ) {
            $script_globals['grecaptcha']['site_key'] = get_setting( 'grecaptcha.site_key' );
        }

        return $script_globals;
    }


    /**
     * @param string $form
     *
     * @return bool
     */
    public function enabled( $form ) {
        return get_setting( "grecaptcha.{$form}.enabled" );
    }

    /**
     * @param string $response
     *
     * @return bool
     */
    public function verify( $response ) {
        $resp = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', [
            'body'      => [
                'secret'   => get_setting( 'grecaptcha.secret_key' ),
                'response' => $response,
            ],
            'sslverify' => false,
        ] );

        $result = json_decode( wp_remote_retrieve_body( $resp ), true );

        return ! empty( $result['success'] );
    }
}
