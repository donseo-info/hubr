<?php

namespace Wpshop\Core;

/**
 * Class Partner
 *
 * 1.0.0    2019-05-25      Init
 */
class Partner {

	public $partner_id = 0;

	protected $options;

	public function __construct( ThemeOptions $options ) {
		$this->options = $options;
	}


	/**
	 * Display link
	 *
	 * @param array $args
	 */
	public function the_link( $args = array() ) {
		echo $this->get_the_link( $args );
	}


	/**
	 * Get link
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function get_the_link( $args = array() ) {

		$prefix  = __( 'Powered by theme', THEME_TEXTDOMAIN );
		$postfix = '';

		if ( ! empty( $args['prefix'] ) ) {
			$prefix = $args['prefix'];
		}

		if ( ! empty( $args['postfix'] ) ) {
			$postfix = $args['postfix'];
		}

		if ( ! empty( $args['partner_id'] ) ) {
			$this->partner_id = $args['partner_id'];
		}

		$url = $this->prepare_link();

		$link = '<span data-href="' . $url . '" class="pseudo-link js-link" data-target="_blank">' . $this->options->theme_title . '</span>';

		return $prefix . ' ' . $link . ' ' . $postfix;
	}


	/**
	 * Prepare UTM
	 *
	 * @return string
	 */
	public function prepare_link() {
		return null;
	}


	/**
	 * Find theme link
	 *
	 * @return string
	 */
	public function find_link() {
		$url = 'https://wpshop.ru/';
		if ( ! empty( $this->options->theme_name ) ) {
			$url = 'https://wpshop.ru/themes/' . $this->options->theme_name;
		}

		return $url;
	}
}