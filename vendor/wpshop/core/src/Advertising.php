<?php

namespace Wpshop\Core;

/**
 * Class Advertising
 *
 * Changelog
 *
 * 1.0.4    2021-09-30      add field fot output advertising in categories
 * 1.0.3    2020-03-03      add infinity scroll support
 * 1.0.2    2019-12-21      add function symbols_count
 *                          add `wpshop_advertising_single` and `wpshop_advertising_singular_post_types`
 * 1.0.1    fix closed </p> when opened <p> contains classes or styles
 * 1.0.0    init
 */
class Advertising {

    protected $options;
    protected $positions = array();

    protected $settings_section_page = 'site_advertising';
    protected $settings_section_id = 'site_advertising_section';

    protected $option_name = 'wpshop_advertising';
    protected $advertising_options = array();

    public $infinity_scroll_support = false;


    public function __construct( ThemeOptions $options ) {
        $this->options = $options;
    }


    public function init() {
        $this->advertising_options = get_option( $this->option_name, array() );

        add_filter( 'the_content', array( $this, 'the_content_filter' ), 110 );

        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'init_settings' ) );
    }


    /**
     * Enable infinity scroll support
     *
     * @param bool $enable
     */
    public function set_infinity_scroll_support( $enable = true ) {
        $this->infinity_scroll_support = $enable;
    }


    public function set_positions( $positions ) {
        $this->positions = $positions;
    }


    public function get_positions() {
        return $this->positions;
    }


    public function add_admin_menu() {
        add_submenu_page(
            'themes.php',
            esc_html__( 'Advertising', $this->options->text_domain ),
            esc_html__( 'Advertising', $this->options->text_domain ),
            'manage_options',
            'advertising',
            array( $this, 'page_layout' )
        );
    }


    public function sanitize_callback( $value ) {
        return $value;
    }


    public function init_settings() {
        register_setting(
            'advertising_group',
            $this->option_name,
            array( $this, 'sanitize_callback' )
        );

        add_settings_section(
            $this->settings_section_id,
            esc_html__( 'Advertising', $this->options->text_domain ),
            '',
            $this->settings_section_page
        );

        foreach ( $this->positions as $name => $position ) {

            if ( $position['type'] == 'before_content' ) {
                add_settings_field(
                    $name,
                    __( 'Before content', $this->options->text_domain ),
                    array( $this, 'render_advertise_field' ),
                    $this->settings_section_page,
                    $this->settings_section_id,
                    array( 'name' => $name, 'type' => $position['type'] )
                );
            } else if ( $position['type'] == 'middle_content' ) {
                add_settings_field(
                    $name,
                    __( 'Middle content', $this->options->text_domain ),
                    array( $this, 'render_advertise_field' ),
                    $this->settings_section_page,
                    $this->settings_section_id,
                    array( 'name' => $name, 'type' => $position['type'] )
                );
            } else if ( $position['type'] == 'after_content' ) {
                add_settings_field(
                    $name,
                    __( 'After content', $this->options->text_domain ),
                    array( $this, 'render_advertise_field' ),
                    $this->settings_section_page,
                    $this->settings_section_id,
                    array( 'name' => $name, 'type' => $position['type'] )
                );
            } else if ( $position['type'] == 'after_p' ) {
                add_settings_field(
                    $name,
                    __( 'After paragraph', $this->options->text_domain ),
                    array( $this, 'render_advertise_field' ),
                    $this->settings_section_page,
                    $this->settings_section_id,
                    array( 'name' => $name, 'type' => $position['type'] )
                );
            } else if ( $position['type'] == 'single' ) {
                add_settings_field(
                    $name,
                    $position['title'],
                    array( $this, 'render_advertise_field' ),
                    $this->settings_section_page,
                    $this->settings_section_id,
                    array( 'name' => $name, 'type' => $position['type'] )
                );
            } else {
                add_settings_field(
                    $name,
                    $position['title'],
                    array( $this, 'render_advertise_field' ),
                    $this->settings_section_page,
                    $this->settings_section_id,
                    array( 'name' => $name )
                );
            }

        }
    }


    public function page_layout() {
        // Check required user capability
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', $this->options->text_domain ) );
        }

        // Admin Page Layout
        echo '<div class="wrap">' . PHP_EOL;
        echo '	<h1>' . get_admin_page_title() . '</h1>' . PHP_EOL;
        echo '	<form action="options.php" method="post">' . PHP_EOL;

        settings_fields( 'advertising_group' );
        do_settings_sections( $this->settings_section_page );
        submit_button();

        echo '	</form>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
    }


    public function render_advertise_field( $args = array() ) {

        $type = ! empty( $args['type'] ) ? $args['type'] : 'regular';
        $name = ! empty( $args['name'] ) ? $args['name'] : '';
        if ( empty( $name ) ) return;

        $name_mob = $this->option_name . '[' . $name . '_mob]';
        $name_num = $this->option_name . '[' . $name . '_num]';
        $name_symbols = $this->option_name . '[' . $name . '_symbols]';
        $name_days = $this->option_name . '[' . $name . '_days]';
        $name_include = $this->option_name . '[' . $name . '_include]';
        $name_exclude = $this->option_name . '[' . $name . '_exclude]';
        $name_category_include = $this->option_name . '[' . $name . '_category_include]';
        $name_category_exclude = $this->option_name . '[' . $name . '_category_exclude]';
        $name_visible = $this->option_name . '[' . $name . '_visible]';

        $value = ( ! empty( $this->advertising_options[ $name ] ) ) ? $this->advertising_options[ $name ] : '' ;
        $value_num = ( ! empty( $this->advertising_options[ $name . '_num' ] ) ) ? $this->advertising_options[ $name . '_num' ] : '' ;
        $value_mob = ( ! empty( $this->advertising_options[ $name . '_mob' ] ) ) ? $this->advertising_options[ $name . '_mob' ] : '' ;
        $value_symbols = ( ! empty( $this->advertising_options[ $name . '_symbols' ] ) ) ? $this->advertising_options[ $name . '_symbols' ] : '' ;
        $value_days = ( ! empty( $this->advertising_options[ $name . '_days' ] ) ) ? $this->advertising_options[ $name . '_days' ] : '' ;
        $value_include = ( ! empty( $this->advertising_options[ $name . '_include' ] ) ) ? $this->advertising_options[ $name . '_include' ] : '' ;
        $value_exclude = ( ! empty( $this->advertising_options[ $name . '_exclude' ] ) ) ? $this->advertising_options[ $name . '_exclude' ] : '' ;
        $value_category_include = ( ! empty( $this->advertising_options[ $name . '_category_include' ] ) ) ? $this->advertising_options[ $name . '_category_include' ] : '' ;
        $value_category_exclude = ( ! empty( $this->advertising_options[ $name . '_category_exclude' ] ) ) ? $this->advertising_options[ $name . '_category_exclude' ] : '' ;
        $value_visible = ( ! empty( $this->advertising_options[ $name . '_visible' ] ) ) ? $this->advertising_options[ $name . '_visible' ] : array() ;


        echo '<div class="wpshop-flex-cols">';

        echo '<div class="wpshop-flex-col">';
        echo '<label for="' . $this->option_name . '_' . $name . '">' . __( 'Desktop', $this->options->text_domain ) . '</label>';
        echo '<textarea name="' . $this->option_name . '[' . $name . ']" id="' . $this->option_name . '_' . $name . '" class="large-text" rows="4">' . esc_attr( $value ) . '</textarea>';
        echo '</div>';

        echo '<div class="wpshop-flex-col">';
        echo '<label for="' . $name_mob . '">' . __( 'Mobile', $this->options->text_domain ) . '</label>';
        echo '<textarea name="' . $name_mob . '" id="' . $name_mob . '" class="large-text" rows="4">' . esc_attr( $value_mob ) . '</textarea>';
        echo '</div>';

        echo '</div>';

        if ( $type == 'after_p' ) {
            echo '<div style="margin-bottom: 8px;">' . __( 'Show after', $this->options->text_domain ) . ' <input name="' . $name_num . '" type="number" value="' . esc_attr( $value_num ) . '" style="width: 50px"> ' . __( 'paragraph. If set to 0 - not displayed', $this->options->text_domain ) . '.</div>';
        }

        if ( $type == 'regular' ) {
            echo '<div style="margin-bottom: 25px;"> ' . __( 'Show', $this->options->text_domain ) . ': &nbsp;';
            echo '<label style="margin-right: 15px;"><input type="checkbox" name="' . $name_visible . '[]" value="home"'; echo ( in_array('home', $value_visible) ) ? ' checked' : ''; echo '> ' . __( 'on the main', $this->options->text_domain ) . '</label>';
            echo '<label style="margin-right: 15px;"><input type="checkbox" name="' . $name_visible . '[]" value="post"'; echo ( in_array('post', $value_visible) ) ? ' checked' : ''; echo '> ' . __( 'in posts', $this->options->text_domain ) . '</label>';
            echo '<label style="margin-right: 15px;"><input type="checkbox" name="' . $name_visible . '[]" value="page"'; echo ( in_array('page', $value_visible) ) ? ' checked' : ''; echo '> ' . __( 'on pages', $this->options->text_domain ) . '</label>';
            echo '<label style="margin-right: 15px;"><input type="checkbox" name="' . $name_visible . '[]" value="archive"'; echo ( in_array('archive', $value_visible) ) ? ' checked' : ''; echo '> ' . __( 'in the archives', $this->options->text_domain ) . '</label>';
            echo '<label style="margin-right: 15px;"><input type="checkbox" name="' . $name_visible . '[]" value="search"'; echo ( in_array('search', $value_visible) ) ? ' checked' : ''; echo '> ' . __( 'in search', $this->options->text_domain ) . '</label>';
            echo '</div>';
        }

        echo '<div style="margin-bottom: 12px;"><span class="wpshop-rk-settings-label">' . __( 'Minimum characters', $this->options->text_domain ) . '</span> <input name="' . $name_symbols . '" type="number" min="0" value="' . esc_attr( $value_symbols ) . '" style="width: 100px"> ' . __( 'in the article to show ads', $this->options->text_domain ) . '</div>';
        echo '<div style="margin-bottom: 12px;"><span class="wpshop-rk-settings-label">' . __( 'Show through', $this->options->text_domain ) . '</span> <input name="' . $name_days . '" type="number" min="0" value="' . esc_attr( $value_days ) . '" style="width: 50px"> ' . __( 'days after the post was posted. 0 - show immediately', $this->options->text_domain ) . '</div>';
        echo '<div style="margin-bottom: 12px;"><span class="wpshop-rk-settings-label">' . __( 'Show only in', $this->options->text_domain ) . '</span> <input name="' . $name_include. '" type="text" value="' . esc_attr( $value_include ) . '" > (' . __( 'specify post IDs separated by commas', $this->options->text_domain ) . ')</div>';
        echo '<div style="margin-bottom: 12px;"><span class="wpshop-rk-settings-label">' . __( 'Do not show in', $this->options->text_domain ) . '</span> <input name="' . $name_exclude. '" type="text" value="' . esc_attr( $value_exclude ) . '" > (' . __( 'specify post IDs separated by commas', $this->options->text_domain ) . ')</div>';
        echo '<div style="margin-bottom: 12px;"><span class="wpshop-rk-settings-label">' . __( 'Show only in', $this->options->text_domain ) . '</span> <input name="' . $name_category_include. '" type="text" value="' . esc_attr( $value_category_include ) . '" > (' . __( 'specify category IDs separated by commas', $this->options->text_domain ) . ')</div>';
        echo '<div style="margin-bottom: 25px;"><span class="wpshop-rk-settings-label">' . __( 'Do not show in', $this->options->text_domain ) . '</span> <input name="' . $name_category_exclude. '" type="text" value="' . esc_attr( $value_category_exclude ) . '" > (' . __( 'specify category IDs separated by commas', $this->options->text_domain ) . ')</div>';
        echo '<input name="' . $this->option_name . '[' . $name . '_type]' . '" type="hidden" value="' . $type . '" >';

    }


    public function show_ad( $id = '', $post = null ) {

        $out = '';
        $show_ad = false;

        $code = ( ! empty( $this->advertising_options[ $id ] ) ) ? $this->advertising_options[ $id ] : '' ;
        $code_mob = ( ! empty( $this->advertising_options[ $id . '_mob' ] ) ) ? $this->advertising_options[ $id . '_mob' ] : '' ;
        $type = ( ! empty( $this->advertising_options[ $id . '_type' ] ) ) ? $this->advertising_options[ $id . '_type' ] : 'regular' ;
        $include = ( ! empty( $this->advertising_options[ $id . '_include' ] ) ) ? $this->advertising_options[ $id . '_include' ] : '' ;
        $exclude = ( ! empty( $this->advertising_options[ $id . '_exclude' ] ) ) ? $this->advertising_options[ $id . '_exclude' ] : '' ;
        $category_include = ( ! empty( $this->advertising_options[ $id . '_category_include' ] ) ) ? $this->advertising_options[ $id . '_category_include' ] : '' ;
        $category_exclude = ( ! empty( $this->advertising_options[ $id . '_category_exclude' ] ) ) ? $this->advertising_options[ $id . '_category_exclude' ] : '' ;
        $visible = ( ! empty( $this->advertising_options[ $id . '_visible' ] ) ) ? $this->advertising_options[ $id . '_visible' ] : array() ;
        $days = ( ! empty( $this->advertising_options[ $id . '_days' ] ) ) ? $this->advertising_options[ $id . '_days' ] : 0 ;
        $symbols = ( ! empty( $this->advertising_options[ $id . '_symbols' ] ) ) ? $this->advertising_options[ $id . '_symbols' ] : 0 ;


        if ( is_singular() ) {

            if ( ! $post instanceof \WP_Post ) {
                $post = get_post( $post );
            }
            if ( ! $post ) return '';

            // проверяем исключенные и включенные
            $show_ad = $this->do_show_ad( $post->ID, $include, $exclude, $category_include, $category_exclude );

            // проверяем по дням
            $offset = (int) current_time( 'timestamp' ) - (int) strtotime( $post->post_date );
            if ( ( $offset < (60 * 60 * 24 * $days) ) ) $show_ad = false;

            // проверяем по символам
            if ( ! empty( $symbols ) ) {
                $symbols_count = $this->symbols_count( $post->post_content );
                if ( $symbols_count < $symbols ) $show_ad = false;
            }

        }


        if ( $type == 'regular' ) {
            if ( is_front_page()    && in_array( 'home', $visible ) )      $show_ad = true;
            if ( is_single()        && ! in_array( 'post', $visible ) )    $show_ad = false;
            if ( is_page()          && ! in_array( 'page', $visible ) )    $show_ad = false;
            if ( is_archive()       && in_array( 'archive', $visible ) )   $show_ad = true;
            if ( is_search()        && in_array( 'search', $visible ) )    $show_ad = true;
        }

        $show_ad = apply_filters( 'wpshop_advertising_do_show', $show_ad, $id, $post, $type );

        // if infinity scroll enabled
        if ( $this->infinity_scroll_support ) {
            $code = $this->infinity_scroll_update_ad( $code );
            $code_mob = $this->infinity_scroll_update_ad( $code_mob );
        }


        if ( ! wp_is_mobile() && ! empty( $code ) && $show_ad ) {
            $out .= '<div class="b-r b-r--' . $id . '">';
            $out .= do_shortcode( $code );
            $out .= '</div>';
        }

        if ( wp_is_mobile() && ! empty( $code_mob ) && $show_ad ) {
            $out .= '<div class="b-r b-r--mob b-r--' . $id . '">';
            $out .= do_shortcode( $code_mob );
            $out .= '</div>';
        }

        return $out;

    }


    /**
     * Update yandex direct ad block ID in infinity scroll posts
     * https://yandex.ru/support/partner2/web/products-rtb/partner-code.html#partner-code__neverending-scroll
     *
     * @param $code
     *
     * @return mixed
     */
    public function infinity_scroll_update_ad( $code ) {

        // если находим яндекс директ
        if ( preg_match( '/Ya\.Context/ui', $code ) ) {

            // ищем ID блока
            if ( preg_match( '/renderTo:\s*"(.+?)"/', $code, $match ) ) {

                // если нашли ID блока
                if ( ! empty( $match[1] ) ) {
                    $block_id = $match[1];

                    // генерируем соль
                    $salt = time() . mt_rand(0, 9999);

                    // генерируем уникальный ID
                    $new_block_id = $block_id . $salt;

                    // меняем код на новый
                    $code = str_replace( $block_id, $new_block_id, $code );

                    // удаляем, если есть pageNumber
                    if ( substr_count( $code, 'pageNumber' ) ) {
                        $code = preg_replace( '/pageNumber:\s*[0-9]+(,|)/ui', '', $code );
                    }

                    // добавляем pageNumber
                    $code = str_replace( 'Ya.Context.AdvManager.render({', 'Ya.Context.AdvManager.render({ pageNumber: ' . $salt . ', ', $code );

                }
            }
        }

        return $code;
    }


    /**
     * Check include and exclude posts
     *
     * @param $post_id
     * @param string $include_ids
     * @param string $exclude_ids
     *
     * @return bool
     */
    public function do_show_ad( $post_id, $include_ids = '', $exclude_ids = '', $category_include = '', $category_exclude = '' ) {

        // если значения пустые - вернем true
        if ( empty( $include_ids ) && empty( $exclude_ids ) && empty( $category_include ) && empty( $category_exclude ) )
            return true;

        if ( ! empty( $include_ids ) || ! empty( $category_include ) ) {
            $include_ids = is_string( $include_ids ) ? explode( ',', $include_ids ) : array();
            $category_include = is_string( $category_include ) ? explode( ',', $category_include ) : array();
            if ( ! empty( $exclude_ids ) ) {
                $exclude_ids = is_string( $exclude_ids ) ? explode( ',', $exclude_ids ) : array();
                $show = in_array( $post_id, $include_ids ) || ( in_category( $category_include, $post_id ) && ! in_array( $post_id, $exclude_ids ) );
            } else {
                $show = in_array( $post_id, $include_ids ) || in_category( $category_include, $post_id );
            }
        } else {
            $exclude_ids = is_string( $exclude_ids ) ? explode( ',', $exclude_ids ) : array();
            $category_exclude = is_string( $category_exclude ) ? explode( ',', $category_exclude ) : array();
            $show = ! in_array( $post_id, $exclude_ids ) && ! in_category( $category_exclude, $post_id );
        }

        return $show;

    }


    /**
     * Content filter to show ad before middle after and after paragraphs
     *
     * @param $content
     *
     * @return string
     */
	public function the_content_filter( $content ) {

		if ( ! empty( $GLOBALS['wp_current_filter'] ) && in_array( 'get_the_excerpt', $GLOBALS['wp_current_filter'] ) ) {
			return $content;
		}

		$out = '';

		/**
		 * Check is single
		 */
		if ( 'single' == apply_filters( 'wpshop_advertising_single', 'single' ) ) {
			$single_check = is_single();
		} else {
			$single_check = is_singular( apply_filters( 'wpshop_advertising_singular_post_types', [] ) );
		}
		if ( ! $single_check ) return $content;

		/**
		 * Перед статьей
		 */
		if ( ! empty( $this->advertising_options['before_content'] ) || ! empty( $this->advertising_options['before_content_mob'] ) ) {
			$out .= $this->show_ad( 'before_content' );
		}

		/**
		 * Посередине и после абзацев
		 */
		$content_exp    = explode ( "</p>", $content );
		$middle_p       = round(count ( $content_exp )/2 ) - 1;
		$in_quote       = false;
		$paragraph_pos = 0;
		for ( $i = 0; $i < count ( $content_exp ); $i ++ ) {
			if ( $i > 0 ) {
				if ( mb_strpos( $content_exp[ $i - 1 ], '<blockquote' ) !== false ||
				     mb_strpos( $content_exp[ $i - 1 ], '<table' ) !== false
				) {
					$in_quote = true;
				}
			}
			if ( $i > 0 ) {
				if ( mb_strpos( $content_exp[ $i - 1 ], '</blockquote>' ) !== false ||
				     mb_strpos( $content_exp[ $i - 1 ], '</table>' ) !== false
				) {
					$in_quote = false;
				}
			}

			if ( ! $in_quote ) {
				$paragraph_pos ++;
			}

			/**
			 * После абзацев
			 */
			foreach ( $this->get_positions() as $position_name => $position ) {
				if ( $position['type'] != 'after_p' ) continue;
				$num = isset( $this->advertising_options[ $position_name . '_num' ] ) ? $this->advertising_options[ $position_name . '_num' ] : '';
				if ( $position['type'] == 'after_p' && ! empty( $num ) && ( ! empty( $this->advertising_options[ $position_name ] ) || ! empty( $this->advertising_options[ $position_name . '_mob' ] ) ) ) {
					if ( ($paragraph_pos -1) == $num && ( $i != ( count ( $content_exp )-1 ) ) ) {
						if ( $in_quote ) {
							$num++;
						} else {
							$out .= $this->show_ad($position_name);
						}
					}
				}
			}

			/**
			 * По середине
			 */
			if ( $i == $middle_p ) {
				if ( $in_quote ) {
					$middle_p++;
				} else {
					if ( ! empty( $this->advertising_options['middle_content'] ) || ! empty( $this->advertising_options['middle_content_mob'] ) ) {
						$out .= $this->show_ad( 'middle_content' );
					}
				}
			}
//			if ( $i > 0 ) {
//				if ( mb_strpos( $content_exp[$i-1], '</blockquote>' ) !== false || mb_strpos( $content_exp[$i-1], '</table>' ) !== false ) $open_tag = false;
//			}
			$trimmed = trim( $content_exp[$i] );
			if ( ! empty( $trimmed ) ) {
				$out .= $content_exp[$i];
				if ( preg_match('/<p(.*?)>/ui', $trimmed) ) $out .= "</p>";
			}
		}

		/**
		 * После статьи
		 */
		if ( ! empty( $this->advertising_options['after_content'] ) || ! empty( $this->advertising_options['after_content_mob'] ) ) {
			$out .= $this->show_ad( 'after_content' );
		}

		return $out;
	}


    /**
     * Symbols count
     *
     * @version 1.0
     * @param string $text
     * @return int
     */
    public function symbols_count( $text = '' ) {

        // удаляем все известные шорткоды
        $text = strip_shortcodes($text);
        // удаляем все остальные шорткоды
        $text = preg_replace( '~\[[^\]]+\]~', '', $text );
        // удаляем все теги
        $text = strip_tags($text);
        // переводит спецсимволы в символы
        $text = htmlspecialchars_decode($text);
        // два и больше пробела\перевода строки меняем на один
        $text = preg_replace('/([\s]{2,})/',' ', $text);
        // удаляем пробелы до и после текста
        $text = trim($text);
        // считаем и выводим
        $count = mb_strlen( utf8_decode( $text ) );

        return $count;
    }
}
