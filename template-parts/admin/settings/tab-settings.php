<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\Membership;
use WPShop\WPCommunity\Vote;
use function WPShop\WPCommunity\theme_container;

/**
 * @var array{'label':string} $args
 */

$settings = theme_container()->get( Settings::class );
?>

<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'Settings', 'wpcommunity' ), null, $settings->doc_link( 'doc' ) . '/settings#settings' ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'registration.require_invite', __( 'Require Invite on Registration', 'wpcommunity' ) ); ?>
</div>

<?php $settings->render_subheader( __( 'Infinite Scroll', 'wpcommunity' ) ) ?>
<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'content.infinite_scroll.home', __( 'Enable Infinite on Home', 'wpcommunity' ) ); ?>
</div>
<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'content.infinite_scroll.popular', __( 'Enable Infinite on Popular', 'wpcommunity' ) ); ?>
</div>
<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'content.infinite_scroll.subs', __( 'Enable Infinite on Subscriptions', 'wpcommunity' ) ); ?>
</div>
<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'content.infinite_scroll.bookmarks', __( 'Enable Infinite on Bookmarks', 'wpcommunity' ) ); ?>
</div>

<?php $settings->render_subheader( __( 'Full Content in Post Cards', 'wpcommunity' ) ) ?>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'content.full.home', __( 'Show Full Content in the Feed on the Home Page', 'wpcommunity' ) ); ?>
</div>
<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'content.full.popular', __( 'Show Full Content in the Feed on the Popular', 'wpcommunity' ) ); ?>
</div>
<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'content.full.subs', __( 'Show Full Content in the Feed on the Subscriptions', 'wpcommunity' ) ); ?>
</div>
<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'content.full.bookmarks', __( 'Show Full Content in the Feed on the Bookmarks', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'Content Accessibility by Default', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_select( 'content.default_access.post_type--post', __( 'Post', 'wpcommunity' ), [
        Membership::ACCESS_PRIVATE => __( 'Private', 'wpcommunity' ),
        Membership::ACCESS_PUBLIC  => __( 'Public', 'wpcommunity' ),
    ] ); ?>
</div>
<div class="wpshop-settings-form-row">
    <?php $settings->render_select( 'content.default_access.post_type--page', __( 'Page', 'wpcommunity' ), [
        Membership::ACCESS_PRIVATE => __( 'Private', 'wpcommunity' ),
        Membership::ACCESS_PUBLIC  => __( 'Public', 'wpcommunity' ),
    ] ); ?>
</div>


<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'Publishing', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_select( 'publish.default_status', __( 'Default Post Status', 'wpcommunity' ), [
        'publish' => __( 'Publish' ),
        'pending' => __( 'Pending' ),
    ] ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'publish.can_create_tags', __( 'Allow to Create Tags', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'publish.edit_time', __( 'Edit Time (sec.)', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input(
        'publish.exclude_categories',
        __( 'Excluded Categories', 'wpcommunity' ),
        [],
        __( 'IDs of the categories that will be excluded in the publication form.', 'wpcommunity' ) ); ?>
</div>


<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'Related Posts', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'related.enabled', __( 'Enable Related Posts', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'related.title', __( 'Title', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'related.count', __( 'Show Posts Count', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_select( 'related.search_by', __( 'Search Related By', 'wpcommunity' ), \WPShop\WPCommunity\Features\RelatedProducts::search_by_options() ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'related.exclude_categories', __( 'Exclude Category Ids', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'related.exclude_tags', __( 'Exclude Tag Ids', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'related.exclude_posts', __( 'Exclude Post Ids', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_select( 'related.order', __( 'Order By', 'wpcommunity' ), \WPShop\WPCommunity\Features\RelatedProducts::order_options() ); ?>
</div>

<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'Comments', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'comments.enabled_for_guests', __( 'Enable Comment for Guests', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox(
        'comments.for_members_only',
        __( 'Enable for Members Only', 'wpcommunity' ),
        [],
        __( 'in this case the “Enable Comment for Guests” option will be ignored', 'wpcommunity' )
    ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'comments.show_by_button', __( 'Show Comments by Button', 'wpcommunity' ) ); ?>
</div>


<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'Like Settings', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_select( 'likes.vote_method', __( 'Vote Method', 'wpcommunity' ), Vote::options() ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_select( 'likes.icon', __( 'Like Icons', 'wpcommunity' ), [
        'chevron'    => __( 'Chevrons', 'wpcommunity' ),
        'thumb'      => __( 'Thumbs', 'wpcommunity' ),
        'heart'      => __( 'Hearts', 'wpcommunity' ),
        'plus_minus' => __( 'Plus and Minus', 'wpcommunity' ),
    ] ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'likes.show_score', __( 'Show Score', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'likes.show_dislikes', __( 'Show Dislike Button', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'Breadcrumbs', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'breadcrumbs.enabled', __( 'Enable Breadcrumbs', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'breadcrumbs.use_plugins', __( 'Use Breadcrumbs from Plugins', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'breadcrumbs.home_title', __( 'Homepage Text', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'breadcrumbs.home_link', __( 'Homepage link', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'breadcrumbs.separator', __( 'Separator', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'breadcrumbs.show_paged', __( 'Show Paged', 'wpcommunity' ) ); ?>
</div>


<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'Google reCAPTCHA (v3)', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'grecaptcha.site_key', __( 'Site Key', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_password_input( 'grecaptcha.secret_key', __( 'Secret Key', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'grecaptcha.sign_up.enabled', __( 'Enable on Register', 'wpcommunity' ) ); ?>
</div>

