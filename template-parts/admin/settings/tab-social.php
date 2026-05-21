<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\Social;
use function WPShop\WPCommunity\theme_container;

$social   = theme_container()->get( Social::class );
$settings = theme_container()->get( Settings::class );

?>

<style>
    .wpcommunity-social--hidden {
        display: none;
    }
</style>

<div class="wpshop-settings-form-row" style="margin-top: 20px">
    <input type="text" class="wpshop-settings-text js-wpcommunity-search-profile" placeholder="<?php echo __( 'search profile...', 'wpcommunity' ) ?>">
</div>

<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'Social Networks', 'wpcommunity' ) ); ?>
</div>

<div class="js-wpcommunity-social-container">
    <?php
    $n = 0;
    foreach ( $social->get_services() as $profile => $item ): $n ++ ?>
        <div class="wpshop-settings-form-row wpcommunity-social<?php echo $n > 8 ? ' wpcommunity-social--hidden' : '' ?> js-wpcommunity-social-profiles" data-profile="<?php echo $profile ?>">
            <?php $settings->render_input( 'social_profile.' . $profile, $item['label'] ); ?>
        </div>
    <?php endforeach ?>
    <div class="wpshop-settings-form-row js-wpcommunity-social-more">
        <a href="#"><?php echo __( 'show more', 'wpcommunity' ) ?></a>
    </div>
</div>

<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'Enable Share Buttons', 'wpcommunity' ) ); ?>
</div>

<div class="js-wpcommunity-social-container">
    <?php
    $n = 0;
    foreach ( $social->get_share_services() as $profile => $item ): $n ++ ?>
        <div class="wpshop-settings-form-row wpcommunity-social<?php echo $n > 8 ? ' wpcommunity-social--hidden' : '' ?> js-wpcommunity-social-profiles" data-profile="<?php echo $profile ?>">
            <?php $settings->render_checkbox( 'social_share.' . $profile, $item['label'] ); ?>
        </div>
    <?php endforeach ?>
    <div class="wpshop-settings-form-row js-wpcommunity-social-more">
        <a href="#"><?php echo __( 'show more', 'wpcommunity' ) ?></a>
    </div>
</div>

<script>
    (function () {
        var searchInput = document.querySelector('.js-wpcommunity-search-profile');

        if (!searchInput) {
            return;
        }

        var timeout;
        searchInput.addEventListener('keyup', function (e) {
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                filterProfiles(e.target.value.toUpperCase());
            }, 100);
        });

        document.querySelectorAll('.js-wpcommunity-social-more a').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                e.target.closest('.js-wpcommunity-social-container').querySelectorAll('.js-wpcommunity-social-profiles').forEach(function (el) {
                    el.classList.remove('wpcommunity-social--hidden');
                });
                e.target.closest('.js-wpcommunity-social-more').remove();
            });
        });

        function filterProfiles(text) {
            document.querySelectorAll('.js-wpcommunity-social-profiles').forEach(function (el) {
                if (text && text.length > 1) {
                    el.style.display = matchText(el, text) ? 'flex' : 'none';
                } else {
                    el.style.display = '';
                }
            });
            document.querySelectorAll('.js-wpcommunity-social-more').forEach(function (el) {
                if (text && text.length > 1) {
                    el.style.display = 'none';
                } else {
                    el.style.display = '';
                }
            });
        }

        function matchText(el, text) {
            if (el.dataset.profile && el.dataset.profile.toUpperCase().indexOf(text) !== -1) {
                return true;
            }

            var label = el.querySelector('label');
            if (label.textContent && label.textContent.toUpperCase().indexOf(text) !== -1) {
                return true;
            }

            return false;
        }
    })()
</script>
