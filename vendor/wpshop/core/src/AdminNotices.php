<?php

namespace Wpshop\Core;

/* Example of render callback
 *
 function() {
	 echo '<div class="notice notice-info">';
	 printf( '<a href="%s" class="notice-dismiss" style="position: static; float: right; text-decoration: none;"></a>', $close_link );
	 echo '<p>' .
	    __( 'Your message', THEME_TEXTDOMAIN ) .
	 '</p>';
	 echo '</div>';
}
 */

/**
 * Class AdminNotices
 * @package Wpshop\Core
 *
 * 1.0.0 2019-12-02 init
 */
class AdminNotices {

	/**
	 * @var array
	 */
	protected $notices = [];

	/**
	 * @var ThemeOptions
	 */
	protected $options;

	/**
	 * AdminNotices constructor.
	 *
	 * @param ThemeOptions $options
	 */
	public function __construct( ThemeOptions $options ) {
		$this->options = $options;
	}

	/**
	 * @return void
	 */
	public function init( $with_tick_box = false ) {
		add_action( 'wp_loaded', [ $this, '_hide_notices' ] );
		add_action( 'admin_notices', [ $this, '_admin_notices' ] );

		if ( $with_tick_box ) {
			add_action( 'admin_menu', 'add_thickbox' );
		}

		do_action( __METHOD__, $this );
	}

	/**
	 * @param string   $type
	 * @param callable $render_callback
	 *
	 * @return mixed
	 */
	public function add_notice( $type, $render_callback ) {
		if ( ! is_callable( $render_callback ) ) {
			throw new \InvalidArgumentException( sprintf( '%s require $render_callback to be callable', __METHOD__ ) );
		}
		$this->notices[ $type ] = $render_callback;

		return $type;
	}

	/**
	 * @return void
	 */
	public function _hide_notices() {
		$type  = $this->options->theme_slug . '-hide-notice';
		$nonce = $this->options->theme_slug . '_notice_nonce';

		if ( isset( $_GET[ $type ], $_GET[ $nonce ] ) ) {
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET[ $nonce ] ) ), $this->options->theme_slug . '_hide_notices_nonce' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', $this->options->text_domain ) );
			}

			$notice = sanitize_text_field( wp_unslash( $_GET[ $type ] ) );

			update_user_meta( get_current_user_id(), $this->options->theme_slug . '_dismissed_' . $notice . '_notice', true );

			$url = get_bloginfo( 'url' ) . $_SERVER["REQUEST_URI"];
			$url = remove_query_arg( [ $type, $nonce ], $url );
			wp_redirect( $url, 302 );
			die();
		}
	}

	/**
	 * @return void
	 */
	public function _admin_notices() {
		foreach ( $this->notices as $type => $method ) {
			if ( get_user_meta( get_current_user_id(), $this->options->theme_slug . '_dismissed_' . $type . '_notice', true ) ) {
				continue;
			}

			$close_link = esc_url( wp_nonce_url(
				add_query_arg( $this->options->theme_slug . '-hide-notice', $type ),
				$this->options->theme_slug . '_hide_notices_nonce',
				$this->options->theme_slug . '_notice_nonce'
			) );

			call_user_func( $method, $close_link, $type );
		}
	}
}
