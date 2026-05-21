<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

?>
<template id="tmpl-wpcommunity-menu-item-image-popup">
    <div class="wpcommunity-modal " role="dialog" aria-hidden="true">
        <div class="wpcommunity-modal-dialog" role="document">
            <div class="wpcommunity-modal-content">
                <div class="wpcommunity-modal-content__header">
                    <div class="wpcommunity-modal-content-header">
                        <div class="wpcommunity-modal-content-header__title"><?php echo __( 'Set Menu Icon', 'wpcommunity' ) ?></div>
                        <button class="wpcommunity-modal-content-header__close js-wpcommunity-menu-icon--close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="wpcommunity-modal-content__body wpcommunity-menu-icon">
                    <textarea name="" id="" rows="10"></textarea>
                </div>
                <div class="wpcommunity-modal-content__footer">
                    <a href="#" class="button button-primary js-wpcommunity-menu-icon--apply"><?php echo __( 'Ok' ) ?></a>
                    <a href="#" class="js-wpcommunity-menu-icon--cancel"><?php echo __( 'Cancel' ) ?></a>
                </div>
            </div>
        </div>
    </div>
</template>
