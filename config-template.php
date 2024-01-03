<?php
/*****************************************************************************
 *	File: 		config.php
 *	Purpose: 	Database configuration file
 *	Author:		Tim Dominguez (timfox@coufu.com)
 ******************************************************************************/


//Server URL
$server_url = "";

//From email. Set this or email won't send.
$config_from_email = "";

//Send to email
$admin_email = "";

//Org Name
$org_name = "";

//Setting the timezone (for Guam time) for date functions
date_default_timezone_set( 'Etc/GMT-10' );

//Logo
function logo(){
    $logourl   = 'corgi.png';
    $logoimage = '<img src ="'.$logourl.'" border=0>';

    return $logoimage;
}

//Helpdesk Message
function helpdeskmessage(){
    $message = '';

    return $message;
}

//Sql connect class
class MyDB extends CMySql{
    function __construct(){

        $this->hostname = '';
        $this->username = '';
        $this->password = '';
        $this->database = '';

        CMySql::__construct();
    }
}

//legacy mysqlconnect
function ticketmysqlconnect(){

    $hostname = 'database';
    $username = '';
    $password = '';
    $database = '';

    $link = mysql_connect( $hostname, $username, $password );
    if (!$link){
        die ('Could not connect: ' . mysql_error());
    }
    $select_db = mysql_select_db( $database, $link);
    if (!$select_db){
        die ('Could not select: ' . mysql_error());
    }

    return $link;
}
?>