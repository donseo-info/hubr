<?php

namespace WPShop\WPCommunity\Actions\Admin;

use JetBrains\PhpStorm\NoReturn;
use WPShop\WPCommunity\Features\Advertisement;

class SaveAdOptionsAction {

    /**
     * @var Advertisement
     */
    protected $advertisement;

    /**
     * @param Advertisement $advertisement
     */
    public function __construct( Advertisement $advertisement ) {
        $this->advertisement = $advertisement;
    }

    /**
     * @return void
     */
    public function init() {
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_save_ad_options';
            add_action( "wp_ajax_{$action}", [ $this, '_save_ad_options' ] );
        }
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _save_ad_options() {
        // todo add validate nonce

        $this->advertisement->save_items( wp_unslash( $_POST['blocks'] ?? [] ) );

        wp_send_json_success();
    }
}
