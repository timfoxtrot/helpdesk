<?php
/*****************************************************************************
*	File: 		config.php
*	Purpose: 	Database configuration file
*	Author:		Tim Dominguez (timfox@coufu.com)
*   Date:       1/18/2023
******************************************************************************/


//Server URL
$server_url = "https://10.168.123.213/helpdesk";

class MyDB extends CMySql{
	function __construct(){
	
		$this->hostname = 'localhost';
		$this->username = 'root';
		$this->password = '';
		$this->database = 'ticket';
		
		CMySql::__construct();
	}
}

function ticketmysqlconnect(){

	//First we connect to localhost, then we login and pass, if it cannot connect, produces an error
	$link = mysql_connect( 'localhost', 'root', '' );
	if (!$link){
		die ('Could not connect: ' . mysql_error()); 
	}
	
	//This selects the database, and if it cannot connect, produces an error	
	$select_db = mysql_select_db( 'ticket', $link);
	if (!$select_db){
		die ('Could not select: ' . mysql_error());
	}

	return $link;
}
?>