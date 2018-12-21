<?php
/**
 * Created by PhpStorm.
 * User: Fermin Perdomo
 * Date: 21/12/2018
 * Time: 8:51 AM
 */


spl_autoload_register('bc_cc_fp_autoloader');
function bc_cc_fp_autoloader( $class_name ) {
    //echo realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
    if ( false !== strpos( $class_name, 'Bcccfp' ) ) {
        $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR;
        $class_file = str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php';
        require_once $classes_dir . $class_file;
    }
}