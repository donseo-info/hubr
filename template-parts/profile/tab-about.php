<?php

/**
 * @version 1.0
 */

?>
<template id="avatar-cropper-template">
    <div class="cropper-preview js-avatar-cropper">
        <div class="cropper-preview__img js-avatar-copper-review"></div>
        <div class="cropper-preview__controls">
            <button class="btn js-avatar-copper-apply"><?php echo __( 'Apply', 'wpcommunity' ) ?></button>
            <button class="btn js-avatar-cropper-zoom-minus">-</button>
            <button class="btn js-avatar-cropper-zoom-plus">+</button>
            <button class="btn js-avatar-cropper-reset"><?php echo __( 'Reset', 'wpcommunity' ) ?></button>
            <a class="js-avatar-cropper-cancel" href="#"><?php echo __( 'Cancel', 'wpcommunity' ) ?></a>
        </div>
    </div>
</template>

<div class="profile-form__row cropper js-avatar-container">
    <div class="avatar-preview">
        <?php echo get_avatar( get_current_user_id(), 150 ) ?>
        <div class="avatar-file">
            <div class="avatar-file__box"></div>
            <input type="file" class="avatar-file__input js-avatar-uploader" accept="image/jpeg,image/png" name="avatar">
            <div class="avatar-file__label"><?php echo __( 'Upload Avatar', 'wpcommunity' ) ?></div>
        </div>
        <button class="btn js-avatar-remove"><?php echo __( 'Remove Avatar', 'wpcommunity' ) ?></button>
    </div>
</div>

<form action="" method="post" class="js-profile-form">

    <div class="profile-form__row">
        <div class="profile-form__label">
            <?php echo esc_html__( 'First Name', 'wpcommunity' ) ?>
        </div>
        <div class="profile-form__body">
            <input name="first_name" type="text" class="input"
                   value="<?php esc_attr_e( get_user_meta( get_current_user_id(), 'first_name', true ) ) ?>">
        </div>
    </div>

    <div class="profile-form__row">
        <div class="profile-form__label">
            <?php echo esc_html__( 'Last Name', 'wpcommunity' ) ?>
        </div>
        <div class="profile-form__body">
            <input name="last_name" type="text" class="input"
                   value="<?php esc_attr_e( get_user_meta( get_current_user_id(), 'last_name', true ) ); ?>">
        </div>
    </div>

    <?php

    /**
     * Allows to hide login input
     *
     * [ru] Позволяет скрыть ввод логина
     *
     * @since 1.0
     */
    $show_login = apply_filters( 'wpcommunity/profile/show_login_input', true );

    if ( $show_login ):
        ?>
        <div class="profile-form__row">
            <div class="profile-form__label">
                <?php echo esc_html__( 'Login', 'wpcommunity' ) ?>
            </div>
            <div class="profile-form__body">
                <input name="login" type="text" class="input"
                       value="<?php esc_attr_e( wp_get_current_user()->user_login ); ?>">
            </div>
        </div>
    <?php endif ?>


    <div class="profile-form__row">
        <div class="profile-form__label">
            <?php echo esc_html__( 'New Password', 'wpcommunity' ) ?>
        </div>
        <div class="profile-form__body">
            <button class="button js-wpcommunity-set-new-password">
                <?php echo esc_html__( 'Set New Password', 'wpcommunity' ); ?>
            </button>
            <div class="new-password js-wpcommunity-new-password-inputs" style="display: none;">
                <div class="new-password__row">
                    <?php echo esc_html__( 'Old Password', 'wpcommunity' ) ?>
                    <input type="password" name="old_password" autocomplete="current-password">
                    <span class="new-password__toggle-password js-wpcommunity-toggle-password" title="<?php echo esc_attr( __( 'show password', 'wpcommunity' ) ) ?>">
                        <svg width="20" height="20">
                            <use xlink:href="#ico-eye"></use>
                        </svg>
                    </span>
                </div>
                <label class="new-password__row">
                    <?php echo esc_html__( 'New Password', 'wpcommunity' ) ?>
                    <input type="password" name="new_password" autocomplete="new-password">
                    <span class="new-password__toggle-password js-wpcommunity-toggle-password" title="<?php echo esc_attr( __( 'show password', 'wpcommunity' ) ) ?>">
                        <svg width="20" height="20">
                            <use xlink:href="#ico-eye"></use>
                        </svg>
                    </span>
                </label>
                <label class="new-password__row">
                    <?php echo esc_html__( 'Confirm New Password', 'wpcommunity' ) ?>
                    <input type="password" name="new_password_confirm" autocomplete="new-password">
                    <span class="new-password__toggle-password js-wpcommunity-toggle-password" title="<?php echo esc_attr( __( 'show password', 'wpcommunity' ) ) ?>">
                        <svg width="20" height="20">
                            <use xlink:href="#ico-eye"></use>
                        </svg>
                    </span>
                </label>
                <a href="#" class="js-wpcommunity-cancel-new-password"><?php echo esc_html__( 'Cancel', 'wpcommunity' ) ?></a>
            </div>
        </div>
    </div>

    <div class="profile-form__row">
        <div class="profile-form__label">
            <?php echo __( 'Display name publicly as', 'wpcommunity' ) ?>
        </div>
        <div class="profile-form__body">
            <?php
            $options = [
                'nickname'        => __( 'Nickname', 'wpcommunity' ),
                'first_last_name' => __( 'First Name and Last Name', 'wpcommunity' ),
            ]
            ?>
            <select name="wpcommunity_display_name" class="select">
                <?php foreach ( $options as $value => $label ): ?>
                    <option value="<?php echo $value ?>"<?php selected( $value, get_user_meta( get_current_user_id(), 'wpcommunity_display_name', true ) ) ?>><?php echo $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="profile-form__row">
        <div class="profile-form__label">
            <?php echo esc_html__( 'About Me', 'wpcommunity' ) ?>
        </div>
        <div class="profile-form__body">
            <textarea name="description" rows="5"
                      class="input input-block"><?php echo get_user_meta( get_current_user_id(), 'description', true ) ?></textarea>
        </div>
    </div>

    <div class="profile-form__row">
        <div class="profile-form__label">
            <label for="<?php echo $_id = uniqid( 'social_profile.' ) ?>"><?php echo __( 'Site', 'wpcommunity' ) ?></label>
        </div>
        <div class="profile-form__body">
            <input name="social_profile[url]" id="<?php echo $_id ?>" type="text" class="input"
                   value="<?php echo get_user_meta( get_current_user_id(), 'url', true ) ?>">
        </div>
    </div>
    <?php
    $n = 0;
    foreach ( wp_get_user_contact_methods() as $inp_name => $label ): $n ++ ?>
        <div class="profile-form__row social-profile<?php echo $n > 9 ? ' social-profile--hide' : '' ?> js-social-profile">
            <div class="profile-form__label">
                <label for="<?php echo $_id = uniqid( 'social_profile.' ) ?>"><?php echo $label ?></label>
            </div>
            <div class="profile-form__body">
                <input name="social_profile[<?php echo $inp_name ?>]" id="<?php echo $_id ?>" type="text" class="input"
                       value="<?php echo get_user_meta( get_current_user_id(), $inp_name, true ) ?>">
            </div>
        </div>
    <?php endforeach ?>
    <div class="profile-form__row js-social-profile-more">
        <a href="#"><?php echo __( 'show more', 'wpcommunity' ) ?></a>
    </div>
    <div class="profile-form__row">
        <div class="profile-form__label">

        </div>
        <div class="profile-form__body">
            <button class="btn" type="submit"><?php echo esc_html__( 'Save', 'wpcommunity' ) ?></button>
        </div>
    </div>
</form>
