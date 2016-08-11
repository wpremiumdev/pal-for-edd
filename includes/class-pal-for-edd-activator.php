<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.mbjtechnolabs.com/
 * @since      1.0.0
 *
 * @package    Paypal_For_Edd
 * @subpackage Paypal_For_Edd/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Paypal_For_Edd
 * @subpackage Paypal_For_Edd/includes
 * @author     gmexsoftware <gmexsoftware@gmail.com>
 */
class Paypal_For_Edd_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        self::create_files();
    }

    private static function create_files() {
        $upload_dir = wp_upload_dir();
        $files = array(
            array(
                'base' => PAYPAL_FOR_EDD_WORDPRESS_LOG_DIR,
                'file' => '.htaccess',
                'content' => 'deny from all'
            ),
            array(
                'base' => PAYPAL_FOR_EDD_WORDPRESS_LOG_DIR,
                'file' => 'index.html',
                'content' => ''
            )
        );
        foreach ($files as $file) {
            if (wp_mkdir_p($file['base']) && !file_exists(trailingslashit($file['base']) . $file['file'])) {
                if ($file_handle = @fopen(trailingslashit($file['base']) . $file['file'], 'w')) {
                    fwrite($file_handle, $file['content']);
                    fclose($file_handle);
                }
            }
        }
    }
}