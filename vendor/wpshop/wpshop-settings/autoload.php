<?php

class WpshopSettingsAutoloadV10 {

    /**
     * @var string|null
     */
    protected $lib_dir;

    /**
     * @param string $class
     *
     * @return void
     */
    public function load( $class ) {
        if ( substr( $class, 0, 16 ) !== 'Wpshop\\Settings\\' ) {
            return;
        }

        $class_file = str_replace( 'Wpshop\\Settings\\', '', $class );
        $class_file = str_replace( '\\', DIRECTORY_SEPARATOR, $class_file ) . '.php';

        if ( ! $this->lib_dir ) {
            // search last version of the library
            $current_version = '1.0';
            $this->lib_dir   = __DIR__ . DIRECTORY_SEPARATOR . 'src';
            if ( defined( 'WP_CONTENT_DIR' ) ) {
                if ( defined( 'GLOB_BRACE' ) ) {
                    $files = glob( WP_CONTENT_DIR . '/{themes,plugins}/*/vendor/wpshop/wpshop-settings/v*.php', GLOB_BRACE );
                } else {
                    $files = array_merge(
                        glob( WP_CONTENT_DIR . '/themes/*/vendor/wpshop/wpshop-settings/v*.php' ),
                        glob( WP_CONTENT_DIR . '/plugins/*/vendor/wpshop/wpshop-settings/v*.php' )
                    );
                }

                foreach ( $files as $file ) {
                    $path_parts = pathinfo( $file );
                    $version    = substr( $path_parts['filename'], 1 );
                    if ( version_compare( $current_version, $version, '<' ) ) {
                        $current_version = $version;
                        $this->lib_dir   = dirname( $file ) . DIRECTORY_SEPARATOR . 'src';
                    }
                }
            }
        }

        include $this->lib_dir . DIRECTORY_SEPARATOR . $class_file;
    }

    /**
     * @return void
     */
    public static function register() {
        $loaders = array_filter( spl_autoload_functions(), function ( $function ) {
            if ( is_array( $function ) && $function[0] instanceof self ) {
                return true;
            }

            return false;
        } );

        if ( ! $loaders ) {
            spl_autoload_register( [ new self(), 'load' ], true, true );
        }
    }
}

WpshopSettingsAutoloadV10::register();
