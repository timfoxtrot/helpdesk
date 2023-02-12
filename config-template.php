<?php
/*****************************************************************************
*	File: 		config.php
*	Purpose: 	Database configuration file
*	Author:		Tim Dominguez (timfox@coufu.com)
*   Date:       1/18/2023
******************************************************************************/


//Server URL
$server_url = "IP/helpdesk";

//From email. Set this or email won't send.
$config_from_email = "changeme@example.com";

class MyDB extends CMySql{
	function __construct(){
	
		$this->hostname = '';
		$this->username = '';
		$this->password = '';
		$this->database = '';
		
		CMySql::__construct();
	}
}

function ticketmysqlconnect(){

	//First we connect to database, then we login and pass, if it cannot connect, produces an error

	$hostname = '';
	$username = '';
	$password = '';
	$database = '';

	$link = mysql_connect( $hostname, $username, $password );
	if (!$link){
		die ('Could not connect: ' . mysql_error()); 
	}
	
	//This selects the database, and if it cannot connect, produces an error	
	$select_db = mysql_select_db( $database, $link);
	if (!$select_db){
		die ('Could not select: ' . mysql_error());
	}

	return $link;
}
?>