<?php
/**
 * Created by PhpStorm.
 * User: karmadon
 * Date: 04.01.16
 * Time: 10:36
 */

if ( !isset($sys_header_loaded) ) {

    session_start ();

    require(dirname( __FILE__ ) . '/config.php' );
    require(ABSPATH . SYS_DIR . '/load.php' );

    $sys_header_loaded = true;

}
