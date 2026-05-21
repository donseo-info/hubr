<?php

/**
 * @version 1.0
 */

use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\Features\Advertisement;
use function WPShop\WPCommunity\_ob_get_content;
use function WPShop\WPCommunity\theme_container;

/**
 * @var array{'advertisement':Advertisement} $args
 */

$settings = theme_container()->get( Settings::class );
?>
<div class="wrap wpshop-settings-wrap">
    <div class="wpshop-settings-head">
        <div class="wpshop-settings-head__title">
            <?php echo THEME_NAME ?>
        </div>
    </div>

    <div class="wpshop-settings-notices">
        <h1 class="screen-reader-text"><?php echo __( 'Advertisement Settings', 'wpcommunity' ) ?></h1>
        <?php settings_errors( 'wpcommunity_messages' ); ?>
    </div>

    <div class="wpshop-settings-container">

        <div class="wpshop-settings-container__body">
            <div class="wpshop-settings-box wpshop-settings-box--large-padding">

                <div class="wpshop-settings-header">
                    <div class="wpshop-settings-header__title">
                        <span><?php echo __( 'Advertisement Settings', 'wpcommunity' ) ?></span>
                        <a href="<?php echo $settings->doc_link( 'doc' ) . '/advertisement#settings' ?>" target="_blank" rel="noopener" class="wpshop-settings-help-ico">?</a>
                    </div>
                </div>

                <div class="wpsc-ad-list js-wpsc-ad-container"></div>

                <input type="hidden" name="ad_data" value="<?php echo esc_attr( json_encode( $args['advertisement']->get_items() ) ) ?>">

                <div class="wpshop-settings-form-row">
                    <div>
                        <button class="wpshop-settings-button js-wpsc-ad-add-block"><?php echo esc_html__( 'Add Block', 'wpcommunity' ) ?></button>
                    </div>
                </div>

                <div class="wpshop-settings-container__footer js-wpsc-ad-save-container">
                    <button class="wpshop-settings-button js-wpsc-ad-save"><?php echo esc_html__( 'Save', 'wpcommunity' ) ?></button>
                </div>
            </div>
        </div>

    </div>
</div>

<script type="text/html" id="tmpl-wpsc-ad-item">
    <div class="wpsc-ad-list__item js-wpsc-ad-item" data-index="{{data._idx}}">
        <div class="wpshop-settings-form-row wpshop-settings-form-row-codemiror">
            <div class="wpshop-settings-form-row__label">
                <label for="content_pc.{{data._idx}}"><?php echo esc_html__( 'Content on PC', 'wpcommunity' ) ?></label>
            </div>
            <div class="wpshop-settings-form-row__body">
                <textarea class="wpshop-settings-text" name="content_pc" id="content_pc.{{data._idx}}" cols="30" rows="5">{{data.content_pc}}</textarea>
            </div>
        </div>
        <div class="wpshop-settings-form-row wpshop-settings-form-row-codemiror">
            <div class="wpshop-settings-form-row__label">
                <label for="content_mobile.{{data._idx}}"><?php echo esc_html__( 'Content on Mobile Device', 'wpcommunity' ) ?></label>
            </div>
            <div class="wpshop-settings-form-row__body">
                <textarea class="wpshop-settings-text" name="content_mobile" id="content_mobile.{{data._idx}}" cols="30" rows="5">{{data.content_mobile}}</textarea>
            </div>
        </div>
        <div class="wpshop-settings-form-row">
            <div class="wpshop-settings-form-row__label">
                <label for="place.{{data._idx}}"><?php echo esc_html__( 'Where to insert', 'wpcommunity' ) ?></label>
            </div>
            <div class="wpshop-settings-form-row__body">
                <select class="wpshop-settings-select js-wpsc-ad-item-type-select" name="place" id="place.{{data._idx}}">
                    <option value=""><?php echo esc_html__( '(select place type)', 'wpcommunity' ) ?></option>
                    <?php foreach ( Advertisement::insert_places() as $value => $label ): ?>
                        <option
                                value="<?php echo esc_attr( $value ) ?>" {{data.place== "<?php echo esc_js( $value ) ?>" ? "selected" : ""}}>
                        <?php echo esc_html( $label ) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>

        <div class="wpshop-settings-form-row wpshop-settings-form-row--with-sub-items">
            <div class="wpshop-settings-form-row__label"></div>
            <div class="wpshop-settings-form-row__body js-wpsc-ad-item-options-placeholder">
            </div>
        </div>

        <a href="#" class="js-wpsc-remove-ad-block"><?php echo esc_html__( 'Remove', 'wpcommunity' ) ?></a>
    </div>
</script>

<script type="text/html" id="tmpl-wpsc-ad-block--archive">
    <# console.log(data) #>
    <fieldset class="wpshop-settings-form-row__body wpsc-ad-options">
        <legend class="wpsc-ad-options__legend"><?php echo esc_html__( 'Output Options in Feed', 'wpcommunity' ) ?></legend>
        <label for="options.home.{{data._idx}}" class="wpshop-settings-form-label">
            <input type="checkbox" name="options.home" id="options.home.{{data._idx}}" class="wpshop-settings-switch-box"
                   value="1" {{data.home== 1 ? 'checked' : ''}}>
            <?php echo esc_html__( 'Latest', 'wpcommunity' ) ?>
        </label>
        <label for="options.popular.{{data._idx}}" class="wpshop-settings-form-label">
            <input type="checkbox" name="options.popular" id="options.popular.{{data._idx}}" class="wpshop-settings-switch-box"
                   value="1" {{data.popular== 1 ? 'checked' : ''}}>
            <?php echo esc_html__( 'Popular', 'wpcommunity' ) ?>
        </label>
        <label for="options.subs.{{data._idx}}" class="wpshop-settings-form-label">
            <input type="checkbox" name="options.subs" id="options.subs.{{data._idx}}" class="wpshop-settings-switch-box"
                   value="1" {{data.subs== 1 ? 'checked' : ''}}>
            <?php echo esc_html__( 'Subscriptions', 'wpcommunity' ) ?>
        </label>
        <label for="options.bookmarks.{{data._idx}}" class="wpshop-settings-form-label">
            <input type="checkbox" name="options.bookmarks" id="options.bookmarks.{{data._idx}}" class="wpshop-settings-switch-box"
                   value="1" {{data.bookmarks== 1 ? 'checked' : ''}}>
            <?php echo esc_html__( 'Bookmarks', 'wpcommunity' ) ?>
        </label>
        <label class="wpsc-inline-label">
            <?php echo sprintf(
                esc_html__( 'After every %s post', 'wpcommunity' ),
                _ob_get_content( function () {
                    ?>
                    <input type="number" name="options.after_n" value="{{data.after_n}}" class="wpshop-settings-text">
                    <?php
                } )
            ) ?>
        </label>
    </fieldset>
</script>

<script type="text/html" id="tmpl-wpsc-ad-block--post">
    <fieldset class="wpshop-settings-form-row__body wpsc-ad-options">
        <legend class="wpsc-ad-options__legend"><?php echo esc_html__( 'Output Options in a Post', 'wpcommunity' ) ?></legend>
        <label for="options.before_content.{{data._idx}}" class="wpshop-settings-form-label">
            <input type="checkbox" name="options.before_content" id="options.before_content.{{data._idx}}" class="wpshop-settings-switch-box"
                   value="1" {{data.before_content== 1 ? 'checked' : ''}}>
            <?php echo esc_html__( 'Before content', 'wpcommunity' ) ?>
        </label>
        <label for="options.after_content.{{data._idx}}" class="wpshop-settings-form-label">
            <input type="checkbox" name="options.after_content" id="options.after_content.{{data._idx}}" class="wpshop-settings-switch-box"
                   value="1" {{data.after_content== 1 ? 'checked' : ''}}>
            <?php echo esc_html__( 'After content', 'wpcommunity' ) ?>
        </label>
        <label for="options.before_comments.{{data._idx}}" class="wpshop-settings-form-label">
            <input type="checkbox" name="options.before_comments" id="options.before_comments.{{data._idx}}" class="wpshop-settings-switch-box"
                   value="1" {{data.before_comments== 1 ? 'checked' : ''}}>
            <?php echo esc_html__( 'Before comments', 'wpcommunity' ) ?>
        </label>
        <label for="options.after_comments.{{data._idx}}" class="wpshop-settings-form-label">
            <input type="checkbox" name="options.after_comments" id="options.after_comments.{{data._idx}}" class="wpshop-settings-switch-box"
                   value="1" {{data.after_comments== 1 ? 'checked' : ''}}>
            <?php echo esc_html__( 'After comments', 'wpcommunity' ) ?>
        </label>
        <div class="wpsc-ad-options-row">
            <label class="wpsc-inline-label" for="options.exclude.{{data._idx}}">
                <?php echo esc_html__( 'Exclude ids:', 'wpcommunity' ) ?>
            </label>
            <input type="text" name="options.exclude" value="{{data.exclude}}" id="options.exclude.{{data._idx}}" class="wpshop-settings-text" placeholder="<?php echo esc_attr__( 'comma separated ids', 'wpcommunity' ) ?>">
        </div>
        <div class="wpsc-ad-options-row">
            <label class="wpsc-inline-label" for="options.include.{{data._idx}}">
                <?php echo esc_html__( 'Only ids:', 'wpcommunity' ) ?>
            </label>
            <input type="text" name="options.include" value="{{data.include}}" id="options.include.{{data._idx}}" class="wpshop-settings-text" placeholder="<?php echo esc_attr__( 'comma separated ids', 'wpcommunity' ) ?>">
        </div>
        <div class="wpsc-ad-options-row">
            <label class="wpsc-inline-label" for="options.show_after_days.{{data._idx}}">
                <?php echo esc_html__( 'Show after days(s):', 'wpcommunity' ) ?>
            </label>
            <input type="text" name="options.show_after_days" value="{{data.show_after_days}}" id="options.show_after_days.{{data._idx}}" class="wpshop-settings-text">
        </div>
    </fieldset>
</script>

<script type="text/html" id="tmpl-wpsc-ad-block--category">
    <fieldset class="wpshop-settings-form-row__body wpsc-ad-options">
        <legend class="wpsc-ad-options__legend"><?php echo esc_html__( 'Output Options in Post Category', 'wpcommunity' ) ?></legend>
        <label class="wpsc-inline-label">
            <?php echo sprintf(
                esc_html__( 'After every %s post', 'wpcommunity' ),
                _ob_get_content( function () {
                    ?>
                    <input type="number" name="options.after_n" value="{{data.after_n}}" class="wpshop-settings-text">
                    <?php
                } )
            ) ?>
        </label>
        <div class="wpsc-ad-options-row">
            <label class="wpsc-inline-label" for="options.exclude.{{data._idx}}">
                <?php echo esc_html__( 'Exclude ids:', 'wpcommunity' ) ?>
            </label>
            <input type="text" name="options.exclude" value="{{data.exclude}}" id="options.exclude.{{data._idx}}" class="wpshop-settings-text" placeholder="<?php echo esc_attr__( 'comma separated ids', 'wpcommunity' ) ?>">
        </div>
        <div class="wpsc-ad-options-row">
            <label class="wpsc-inline-label" for="options.include.{{data._idx}}">
                <?php echo esc_html__( 'Only ids:', 'wpcommunity' ) ?>
            </label>
            <input type="text" name="options.include" value="{{data.include}}" id="options.include.{{data._idx}}" class="wpshop-settings-text" placeholder="<?php echo esc_attr__( 'comma separated ids', 'wpcommunity' ) ?>">
        </div>
    </fieldset>
</script>

<script type="text/html" id="tmpl-wpsc-ad-block--tag">
    <fieldset class="wpshop-settings-form-row__body wpsc-ad-options">
        <legend class="wpsc-ad-options__legend"><?php echo esc_html__( 'Output Options in Post Tags', 'wpcommunity' ) ?></legend>
        <label class="wpsc-inline-label">
            <?php echo sprintf(
                esc_html__( 'After every %s post', 'wpcommunity' ),
                _ob_get_content( function () {
                    ?>
                    <input type="number" name="options.after_n" value="{{data.after_n}}" class="wpshop-settings-text">
                    <?php
                } )
            ) ?>
        </label>
        <div class="wpsc-ad-options-row">
            <label class="wpsc-inline-label" for="options.exclude.{{data._idx}}">
                <?php echo esc_html__( 'Exclude ids:', 'wpcommunity' ) ?>
            </label>
            <input type="text" name="options.exclude" value="{{data.exclude}}" id="options.exclude.{{data._idx}}" class="wpshop-settings-text" placeholder="<?php echo esc_attr__( 'comma separated ids', 'wpcommunity' ) ?>">
        </div>
        <div class="wpsc-ad-options-row">
            <label class="wpsc-inline-label" for="options.include.{{data._idx}}">
                <?php echo esc_html__( 'Only ids:', 'wpcommunity' ) ?>
            </label>
            <input type="text" name="options.include" value="{{data.include}}" id="options.include.{{data._idx}}" class="wpshop-settings-text" placeholder="<?php echo esc_attr__( 'comma separated ids', 'wpcommunity' ) ?>">
        </div>
    </fieldset>
</script>
