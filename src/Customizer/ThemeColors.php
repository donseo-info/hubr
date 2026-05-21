<?php

namespace WPShop\WPCommunity\Customizer;

class ThemeColors {

    const CONTRAST_LVL_BG      = 'bg';
    const CONTRAST_LVL_TXT     = 'txt';
    const CONTRAST_LVL_LINK    = 'link';
    const CONTRAST_LVL_PRIMARY = 'primary';
    const CONTRAST_LVL_CONTROL = 'control';

    /**
     * Returns array formatted like this
     * <pre>
     * [
     *      'light' => [
     *          'wpsc-white-bg' => '#ffffff',
     *          'wpsc-body-bg'  => '#f9f3ed',
     *      ],
     *      'dark' => [
     *          'wpsc-white-bg' => '#ffffff',
     *          'wpsc-body-bg'  => '#f9f3ed',
     *      ],
     * ]
     * </pre>
     *
     * @return array
     */
    public static function get_variables_for_defaults() {
        $result = [];
        foreach ( self::get_variables_data() as $variable => $data ) {
            foreach ( $data['settings'] as $theme => $value ) {
                $result[ $theme ][ $variable ] = $value;
            }
        }

        return $result;
    }

    /**
     * Returns array formatted like this
     * <pre>
     * [
     *      'light' => [
     *          [
     *              'value' => '',
     *              'label' => '',
     *              'description' => '',
     *          ],
     *          [
     *              'value' => '',
     *              'label' => '',
     *              'description' => '',
     *          ]
     *      ],
     *      'dark' => [
     *          [
     *              'value' => '',
     *              'label' => '',
     *              'description' => '',
     *          ],
     *          [
     *              'value' => '',
     *              'label' => '',
     *              'description' => '',
     *          ]
     *      ],
     * ]
     * </pre>
     *
     * @return array
     */
    public static function get_variables_for_control() {
        $result = [];
        foreach ( self::get_variables_data() as $variable => $data ) {
            foreach ( $data['settings'] as $theme => $value ) {
                $result[ $theme ][] = [
                    'variable'       => $variable,
                    'value'          => $data['settings'][ $theme ],
                    'label'          => $data['label'],
                    'description'    => $data['description'],
                    'contrast_level' => $data['contrast_level'],
                ];
            }
        }

        return $result;
    }

    /**
     * @return array[]
     */
    public static function get_variables_data() {
        return [
            'wpsc-primary-color'               => [
                'settings'       => [
                    'light' => '#3276ff', // до релиза: #665dcb
                    'dark'  => '#3276ff', // до релиза: #665dcb
                ],
                'label'          => __( 'Primary Color', 'wpcommunity' ),
                'description'    => __( 'The primary color with which your site will be associated', 'wpcommunity' ),
                'contrast_level' => self::CONTRAST_LVL_PRIMARY,
            ],
            'wpsc-secondary-color'             => [
                'settings'       => [
                    'light' => '#6c757d',
                    'dark'  => '#6c757d',
                ],
                'label'          => __( 'Secondary Color', 'wpcommunity' ),
                'description'    => __( 'The secondary color with which your site will be associated', 'wpcommunity' ),
                'contrast_level' => self::CONTRAST_LVL_PRIMARY,
            ],
            'wpsc-btn-primary-bg'              => [
                'settings'       => [
                    'light' => '#3276ff', // до релиза: #665dcb
                    'dark'  => '#2466e9', // до релиза: #665dcb
                ],
                'label'          => __( 'Button Primary Color', 'wpcommunity' ),
                'description'    => __( 'Primary color of standard buttons', 'wpcommunity' ),
                'contrast_level' => self::CONTRAST_LVL_PRIMARY,
            ],
            'wpsc-white-bg'                    => [
                'settings'       => [
                    'light' => '#ffffff',
                    'dark'  => '#1b1a26', // до релиза #282736
                ],
                'label'          => __( 'Box Background', 'wpcommunity' ),
                'description'    => __( 'Background color of structural boxes', 'wpcommunity' ),
                'contrast_level' => self::CONTRAST_LVL_BG,
            ],
            'wpsc-body-bg'                     => [
                'settings'       => [
                    'light' => '#f0f4fb', // до релиза #f9f3ed
                    'dark'  => '#131314', // до релиза #1c1c1c
                ],
                'label'          => __( 'Body Background', 'wpcommunity' ),
                'description'    => __( 'Background color of the document body', 'wpcommunity' ),
                'contrast_level' => self::CONTRAST_LVL_BG,
            ],
            'wpsc-light-bg'                    => [
                'settings'       => [
                    'light' => '#f3f8ff',
                    'dark'  => '#232136', // до релиза #1c1c1c
                ],
                'label'          => __( 'Body Background Additional', 'wpcommunity' ),
                'description'    => __( 'Additional background color of the document body', 'wpcommunity' ),
                'contrast_level' => self::CONTRAST_LVL_BG,
            ],
            'wpsc-link-color'                  => [
                'settings'       => [
                    'light' => '#1b55f2', // до релиза #6a3bcd
                    'dark'  => '#8ac0f5',
                ],
                'label'          => __( 'Link Color', 'wpcommunity' ),
                'description'    => __( 'Color of links on site pages', 'wpcommunity' ),
                'contrast_level' => self::CONTRAST_LVL_LINK,
            ],
            'wpsc-link-hover-color'            => [
                'settings'       => [
                    'light' => '#f3374d',
                    'dark'  => '#ffffff',
                ],
                'label'          => __( 'Link Color on Hover', 'wpcommunity' ),
                'description'    => __( 'Color of links when hovering', 'wpcommunity' ),
                'contrast_level' => self::CONTRAST_LVL_LINK,
            ],
            'wpsc-link-text-light-hover-color' => [
                'settings'       => [
                    'light' => '#f3374d',
                    'dark'  => '#ffffff',
                ],
                'label'          => __( 'Link Color on Hover Light', 'wpcommunity' ),
                'description'    => __( 'Color of links when hovering over light text', 'wpcommunity' ),
                'contrast_level' => self::CONTRAST_LVL_LINK,
            ],
            'wpsc-text-color'                  => [
                'settings'       => [
                    'light' => '#212529',
                    'dark'  => '#e3e3e3',
                ],
                'label'          => __( 'Text Color', 'wpcommunity' ),
                'description'    => __( 'Text color on site pages', 'wpcommunity' ),
                'contrast_level' => self::CONTRAST_LVL_TXT,
            ],
            'wpsc-text-light-color'            => [
                'settings'       => [
                    'light' => '#4c555e',
                    'dark'  => '#DEDEDE99',
                ],
                'label'          => __( 'Text Color Light', 'wpcommunity' ),
                'description'    => __( 'Additional text color on site pages', 'wpcommunity' ),
                'contrast_level' => self::CONTRAST_LVL_TXT,
            ],
            'wpsc-text-lighter-color'          => [
                'settings'       => [
                    'light' => '#9fa8b0',
                    'dark'  => '#DEDEDE4C',
                ],
                'label'          => __( 'Text Color Lighter', 'wpcommunity' ),
                'description'    => __( 'One more additional color on site pages', 'wpcommunity' ),
                'contrast_level' => self::CONTRAST_LVL_TXT,
            ],
        ];
    }

    /**
     * @return array[]
     */
    public static function get_presets() {
        return [
            [
                'name'   => __( 'WPCommunity', 'wpcommunity' ),
                'colors' => [
                    'wpsc-primary-color'               => [
                        'light' => '#665dcb',
                        'dark'  => '#665dcb',
                    ],
                    'wpsc-secondary-color'             => [
                        'light' => '#6c757d',
                        'dark'  => '#6c757d',
                    ],
                    'wpsc-btn-primary-bg'              => [
                        'light' => '#665dcb',
                        'dark'  => '#665dcb',
                    ],
                    'wpsc-white-bg'                    => [
                        'light' => '#ffffff',
                        'dark'  => '#282736',
                    ],
                    'wpsc-body-bg'                     => [
                        'light' => '#f9f3ed',
                        'dark'  => '#1c1c1c',
                    ],
                    'wpsc-light-bg'                    => [
                        'light' => '#f3f8ff',
                        'dark'  => '#1c1c1c',
                    ],
                    'wpsc-link-color'                  => [
                        'light' => '#6a3bcd',
                        'dark'  => '#8ac0f5',
                    ],
                    'wpsc-link-hover-color'            => [
                        'light' => '#f3374d',
                        'dark'  => '#ffffff',
                    ],
                    'wpsc-link-text-light-hover-color' => [
                        'light' => '#f3374d',
                        'dark'  => '#ffffff',
                    ],
                    'wpsc-text-color'                  => [
                        'light' => '#212529',
                        'dark'  => '#e3e3e3',
                    ],
                    'wpsc-text-light-color'            => [
                        'light' => '#4c555e',
                        'dark'  => '#DEDEDE99',
                    ],
                    'wpsc-text-lighter-color'          => [
                        'light' => '#9fa8b0',
                        'dark'  => '#DEDEDE4C',
                    ],
                ],
            ],
            [
                'name'   => __( 'Preset 1', 'wpcommunity' ),
                'colors' => [
                    'wpsc-primary-color'               => [
                        'light' => '#9da553',
                        'dark'  => '#646b2c',
                    ],
                    'wpsc-secondary-color'             => [
                        'light' => '#6c757d',
                        'dark'  => '#6c757d',
                    ],
                    'wpsc-btn-primary-bg'              => [
                        'light' => '#909848',
                        'dark'  => '#646a2b',
                    ],
                    'wpsc-white-bg'                    => [
                        'light' => '#eff1c6',
                        'dark'  => '#121207',
                    ],
                    'wpsc-body-bg'                     => [
                        'light' => '#e0e4a6',
                        'dark'  => '#212310',
                    ],
                    'wpsc-light-bg'                    => [
                        'light' => '#d6db95',
                        'dark'  => '#1e1f0e',
                    ],
                    'wpsc-link-color'                  => [
                        'light' => '#2a2c13',
                        'dark'  => '#b2b967',
                    ],
                    'wpsc-link-hover-color'            => [
                        'light' => '#272912',
                        'dark'  => '#bdc473',
                    ],
                    'wpsc-link-text-light-hover-color' => [
                        'light' => '#323516',
                        'dark'  => '#afb764',
                    ],
                    'wpsc-text-color'                  => [
                        'light' => '#1c1d0d',
                        'dark'  => '#cfcfc7ff',
                    ],
                    'wpsc-text-light-color'            => [
                        'light' => '#111106',
                        'dark'  => '#cfcfc7ff',
                    ],
                    'wpsc-text-lighter-color'          => [
                        'light' => '#1e1f0f',
                        'dark'  => '#dbdbd6ff',
                    ],
                ],
            ],
            [
                'name'   => __( 'Preset 2', 'wpcommunity' ),
                'colors' => [
                    'wpsc-primary-color'               => [
                        'light' => '#629a5c',
                        'dark'  => '#336130',
                    ],
                    'wpsc-secondary-color'             => [
                        'light' => '#6c757d',
                        'dark'  => '#6c757d',
                    ],
                    'wpsc-btn-primary-bg'              => [
                        'light' => '#679f61',
                        'dark'  => '#42773e',
                    ],
                    'wpsc-white-bg'                    => [
                        'light' => '#eaf6e7',
                        'dark'  => '#101b0d',
                    ],
                    'wpsc-body-bg'                     => [
                        'light' => '#cfe9ca',
                        'dark'  => '#162814',
                    ],
                    'wpsc-light-bg'                    => [
                        'light' => '#c3e3bd',
                        'dark'  => '#132010',
                    ],
                    'wpsc-link-color'                  => [
                        'light' => '#172b15',
                        'dark'  => '#a4d09d',
                    ],
                    'wpsc-link-hover-color'            => [
                        'light' => '#284e25',
                        'dark'  => '#b8dcb2',
                    ],
                    'wpsc-link-text-light-hover-color' => [
                        'light' => '#172b15',
                        'dark'  => '#96c68f',
                    ],
                    'wpsc-text-color'                  => [
                        'light' => '#132010',
                        'dark'  => '#f1f9f0',
                    ],
                    'wpsc-text-light-color'            => [
                        'light' => '#0c1509',
                        'dark'  => '#cce7c6',
                    ],
                    'wpsc-text-lighter-color'          => [
                        'light' => '#0f190c',
                        'dark'  => '#c5e4bf',
                    ],
                ],
            ],
        ];
    }
}
