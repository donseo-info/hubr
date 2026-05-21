<?php

namespace Wpshop\Core;

/**
 * Class Social
 *
 * 1.0.7    2024-08-06      Add filters 'wpshop_social_viber_link' and 'wpshop_social_viber_mobile_link' for viber link
 * 1.0.6    2021-07-20      Add filter wpshop_social_link_item, add rel="noopener" to link item
 * 1.0.5    2020-11-03      Add tik tok icon
 * 1.0.4    2020-09-07      Add filter 'wpshop_social_whatsapp_link' for whatsapp link
 * 1.0.3    2020-06-11      Edit link of button viber
 * 1.0.2    2019-05-18      Add $display argument to social_profiles function
 * 1.0.1                    add share & profile boolean to services, add get_share_services func
 * 1.0.0                    init
 */
class Social {

    protected $options;
    public $social_services;


    public function __construct( ThemeOptions $options ) {

        $this->options = $options;
        $this->init();

    }


    public function init() {

        $this->social_services = array(

            // GoodShare social
            'vkontakte'     => array( 'label' => __( 'Vkontakte', $this->options->text_domain ), 'counter' => true, 'share' => true, 'profile' => true ),
            'facebook'      => array( 'label' => __( 'Facebook', $this->options->text_domain ), 'counter' => true, 'share' => true, 'profile' => true ),
            'odnoklassniki' => array( 'label' => __( 'Odnoklassniki', $this->options->text_domain ), 'counter' => true, 'share' => true, 'profile' => true ),
            'telegram'      => array( 'label' => __( 'Telegram', $this->options->text_domain ), 'counter' => false, 'share' => true, 'profile' => true ),
            'twitter'       => array( 'label' => __( 'Twitter', $this->options->text_domain ), 'counter' => false, 'share' => true, 'profile' => true ),
            'livejournal'   => array( 'label' => __( 'LiveJournal', $this->options->text_domain ), 'counter' => false, 'share' => true, 'profile' => true ),
            'moimir'        => array( 'label' => __( 'My@Mail.Ru', $this->options->text_domain ), 'counter' => true, 'share' => true, 'profile' => true ),
            'linkedin'      => array( 'label' => __( 'LinkedIn', $this->options->text_domain ), 'counter' => true, 'share' => true, 'profile' => true ),

            'tumblr'        => array( 'label' => __( 'Tumblr', $this->options->text_domain ), 'counter' => true, 'share' => true, 'profile' => true ),
            'surfingbird'   => array( 'label' => __( 'Surfingbird', $this->options->text_domain ), 'counter' => true, 'share' => true, 'profile' => true ),
            'pinterest'     => array( 'label' => __( 'Pinterest', $this->options->text_domain ), 'counter' => true, 'share' => true, 'profile' => true ),
            'reddit'        => array( 'label' => __( 'Reddit', $this->options->text_domain ), 'counter' => true, 'share' => true, 'profile' => true ),
            'buffer'        => array( 'label' => __( 'Buffer', $this->options->text_domain ), 'counter' => true, 'share' => false, 'profile' => false ),        // NO!
            'stumbleupon'   => array( 'label' => __( 'StumbleUpon', $this->options->text_domain ), 'counter' => true, 'share' => true, 'profile' => true ),

            'evernote'      => array( 'label' => __( 'Evernote', $this->options->text_domain ), 'counter' => false, 'share' => true, 'profile' => true ),
            'delicious'     => array( 'label' => __( 'Delicious', $this->options->text_domain ), 'counter' => false, 'share' => true, 'profile' => true ),
            'blogger'       => array( 'label' => __( 'Blogger', $this->options->text_domain ), 'counter' => false, 'share' => true, 'profile' => true ),
            'liveinternet'  => array( 'label' => __( 'LiveInternet', $this->options->text_domain ), 'counter' => false, 'share' => false, 'profile' => false ), // NO!

            'digg'          => array( 'label' => __( 'Digg', $this->options->text_domain ), 'counter' => false, 'share' => true, 'profile' => true ),
            'instapaper'    => array( 'label' => __( 'Instapaper', $this->options->text_domain ), 'counter' => false, 'share' => false, 'profile' => false ),   // NO!
            'wordpress'     => array( 'label' => __( 'WordPress', $this->options->text_domain ), 'counter' => false, 'share' => true, 'profile' => true ),
            'baidu'         => array( 'label' => __( 'Baidu', $this->options->text_domain ), 'counter' => false, 'share' => false, 'profile' => false ),        // NO!
            'renren'        => array( 'label' => __( 'RenRen', $this->options->text_domain ), 'counter' => false, 'share' => false, 'profile' => false ),       // NO!
            'weibo'         => array( 'label' => __( 'Weibo', $this->options->text_domain ), 'counter' => false, 'share' => false, 'profile' => false ),        // NO!
            'pocket'        => array( 'label' => __( 'Pocket', $this->options->text_domain ), 'counter' => true, 'share' => true, 'profile' => true ),
            'xing'          => array( 'label' => __( 'Xing', $this->options->text_domain ), 'counter' => true, 'share' => true, 'profile' => true ),

            'sms'           => array( 'label' => __( 'SMS', $this->options->text_domain ), 'counter' => false, 'share' => true, 'profile' => false ),
            'skype'         => array( 'label' => __( 'Skype', $this->options->text_domain ), 'counter' => false, 'share' => true, 'profile' => false ),
            'viber'         => array( 'label' => __( 'Viber', $this->options->text_domain ), 'counter' => false, 'share' => true, 'profile' => false ),
            'whatsapp'      => array( 'label' => __( 'WhatsApp', $this->options->text_domain ), 'counter' => false, 'share' => true, 'profile' => false ),
            'line'          => array( 'label' => __( 'LINE', $this->options->text_domain ), 'counter' => false, 'share' => true, 'profile' => false ),

            // just links
            'youtube'       => array( 'label' => __( 'YouTube', $this->options->text_domain ), 'counter' => false, 'share' => false, 'profile' => true ),
            'instagram'     => array( 'label' => __( 'Instagram', $this->options->text_domain ), 'counter' => false, 'share' => false, 'profile' => true ),
            'tiktok'        => array( 'label' => __( 'TikTok', $this->options->text_domain ), 'counter' => false, 'share' => false, 'profile' => true ),
        );

    }


    public function get_share_services() {
        $services = array();

        foreach ( $this->social_services as $k => $service ) {
            if ( $service['share'] ) {
                $services[ $k ] = $service['label'];
            }
        }

        return apply_filters( 'wpshop_social_share_services', $services );
    }


    public function share_buttons( $social_buttons = array(), $args = array() ) {

        $social_url         = esc_attr( get_permalink() );
        $social_title       = esc_attr( get_the_title() );
        $social_description = esc_attr( get_the_excerpt() );
        $social_image_id    = get_post_thumbnail_id();
        $social_image_url   = wp_get_attachment_image_src( $social_image_id, apply_filters( 'wpshop_social_image', 'thumbnail' ) );
        $social_image_url   = ! empty( $social_image_url[0] ) ? $social_image_url[0] : '';

        $options = wp_parse_args(
            $args,
            array(
                'show_label'    => true,
                'show_counters' => true,
                'show_all_data' => false,
            )
        );

        foreach ( $social_buttons as $social_button ) :

            if ( ! isset( $this->social_services[ $social_button ] ) ) continue;

            $attributes = '';

            if ( $social_button == 'vkontakte' || $social_button == 'moimir' || $social_button == 'wordpress' ) {
                $attributes = ' data-image="' . esc_attr( $social_image_url ) . '"';
            }

            if ( $social_button == 'pinterest' || $options['show_all_data'] ) {
                $attributes = ' data-url="' . $social_url . '" data-title="' . esc_attr( $social_title ) . '" data-description="' . esc_attr( $social_description ) . '" data-image="' . esc_attr( $social_image_url ) . '"';
            }

            echo '<span class="social-button social-button--' . $social_button . '" data-social="' . $social_button . '"'. $attributes .'>';

            if ( $options['show_label'] ) {
                echo '<span>' . $this->social_services[ $social_button ]['label'] . '</span>';
            }
            if ( $this->social_services[$social_button]['counter'] && $options['show_counters'] ) {
                echo '<span data-counter="' . $social_button . '"></span>';
            }
            echo '</span>';
        endforeach;

    }


    /**
     * Return or display social profiles
     *
     * @param array $profiles
     * @param bool $pseudo_link
     * @param bool $display
     * @return string
     */
    public function social_profiles( $profiles = array(), $pseudo_link = true, $display = true ) {

        $out = '';

        foreach ( $profiles as $k => $profile ) {

            if ( $k == 'whatsapp' ) $profile = apply_filters( 'wpshop_social_whatsapp_link', 'https://api.whatsapp.com/send?phone=' ) . $profile;
            if ( $k == 'viber' ) {
                if ( ! wp_is_mobile() ) {
                    $profile = apply_filters( 'wpshop_social_viber_link', 'viber://chat?number=' ) . $profile;
                }
                else {
                    $profile = str_replace( '+', '', $profile );
                    $profile = apply_filters( 'wpshop_social_viber_mobile_link', 'viber://add?number=' ) . $profile;
                }
            }

            if ( $pseudo_link ) {
                if ( $k != 'viber' ) $profile = base64_encode( $profile );
                $out .= apply_filters(
                    'wpshop_social_link_item',
                    '<span class="social-button social-button--' . $k . ' js-link" data-href="' . $profile . '" data-target="_blank"></span>',
                    $k,
                    $profile,
                    $pseudo_link
                );
            } else {
                $out .= apply_filters( 'wpshop_social_link_item',
                    '<a class="social-button social-button--' . $k . '" href="' . esc_attr( $profile ) . '" target="_blank" rel="noopener"></a>',
                    $k,
                    $profile,
                    $pseudo_link
                );
            }
        }

        if ( $display ) {
            echo $out;
        } else {
            return $out;
        }
    }


}
