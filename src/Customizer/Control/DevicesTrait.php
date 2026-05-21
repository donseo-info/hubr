<?php

namespace WPShop\WPCommunity\Customizer\Control;

trait DevicesTrait {

    /**
     * @param string $namespace
     *
     * @return void
     */
    protected function render_devices_html( $namespace ) {
        $devices = [
            'desktop' => [
                'srt'    => esc_html_x( 'Desktop', 'customize control', 'wpcommunity' ),
                'active' => true,
            ],
            'tablet'  => [
                'srt'    => esc_html_x( 'Tablet', 'customize control', 'wpcommunity' ),
                'active' => false,
            ],
            'mobile'  => [
                'srt'    => esc_html_x( 'Mobile', 'customize control', 'wpcommunity' ),
                'active' => false,
            ],
        ];
        ?>
        <div class="wpcommunity-customize-control__devices <?php echo $namespace ?>__devices js-wpcommunity-customize-control-devices">
            <?php foreach ( $devices as $device => $params ): ?>
                <span class="wpcommunity-customize-control__devices-<?php echo $device ?> <?php echo $namespace ?>__devices-<?php echo $device ?><?php echo $params['active'] ? ' active' : '' ?> js-control-device-button"
                      data-device="<?php echo $device ?>"
                      title="<?php echo $params['srt'] ?>"
                      style="display: none">
                    <span class="screen-reader-text"><?php echo $params['srt'] ?></span>
                </span>
            <?php endforeach ?>
            <span class="wpcommunity-customize-control__reset-btn <?php echo $namespace ?>__reset-btn dashicons dashicons-image-rotate js-control-reset-btn"
                  title="<?php echo esc_attr_x( 'Reset', 'customize control', 'wpcommunity' ) ?>">
                <span class="screen-reader-text"><?php echo esc_html_x( 'Reset', 'customize control', 'wpcommunity' ) ?></span>
            </span>
        </div>
        <?php
    }
}
