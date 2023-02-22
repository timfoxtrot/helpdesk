<?php
/*****************************************************************************
*	File: 		form.php
*	Purpose: 	Ticket submission form 
*	Author:		Tim Dominguez (timfox@coufu.com)
******************************************************************************/

include 'functions.php';

//Page Handling
switch ( $_GET[page] ){
	default:		ticketform();			break;
	case 'submit';	submit($server_url);	break;
}

//Data Handling
function submit($link){

	//Updated: 8/13/2013
	//By: Tim Dominguez (timfox@coufu.com)
	
	//$_POST variables
	$name 		= $_POST[name];			$phone    = $_POST[phonenumber];
	$message 	= $_POST[message];		$email    = $_POST[email];
	$location	= $_POST[location]; 	$category = $_POST[category];
	
	$password = strtoupper(substr(md5(time()), -4));
	
	//Error Check
	if(!$name)	$errorname 	= TRUE;  if(!$phone)   $errorphone   = TRUE;
	if(!$email)	$erroremail = TRUE;  if(!$message) $errormessage = TRUE;
	
	
	//1st tier of check. Missing info or confirm.
	if($_POST[check] == FALSE OR !$name OR !$message OR !$phone OR !$email){
	
		ticket_top();
		
		$headermessage = '<font size="4">';
		if (!$name OR !$message OR !$phone OR !$email){
			$headermessage .= '<color="#ff0000">Please fill in the missing fields';
		} else{
			$headermessage .= 'Submit this data?';
		}
		$headermessage .= '</font>';
		
		echo $headermessage;
		
		//Final Check 
		if ($name AND $message AND $phone AND $email)
			$check = TRUE;
		
		submissionform($name, $email, $phone, $message, $location, 
			$category, $errorname, $erroremail, $errorphone, $errormessage, $check);
	}
	
	//Gone through all error checks. Time to insert into database
	if($_POST[check] == TRUE AND $name AND $message AND $phone AND $email){
		
		//necessary variables
		$ip      		= $REMOTE_ADDR;
		$ipadd  		= $_SERVER['REMOTE_ADDR'];
		$message 		= addslashes($message);
		$datecreated 	= time();
		
		//inserting database
		$insertitem[active]     = 1;
		$insertitem[name]		= $name;
		$insertitem[email]		= $email;
		$insertitem[phonenumber]= $phone;
		$insertitem[location]   = $location;
		$insertitem[category]   = $category;
		$insertitem[message]	= $message;
		$insertitem[newstatus]  = 0;
		$insertitem[level] 		= 2;
		$insertitem[solved]		= 2;
		$insertitem[ip]  		= $ipadd;
		$insertitem[datecreated]= $datecreated;
		$insertitem[assignedto] = 0;
		$insertitem[datesolved] = "";
		$insertitem[whosolved]  = "";
		$insertitem[password]   = $password;
		
		//Connecting to Database
		$db = new MyDB;
		$db->insertarray ( "tickets", $insertitem );
		
		//Page Message
		$db->query("SELECT * FROM tickets WHERE datecreated = '$datecreated'");
		$row 	  = $db->getrow();
		$ticketid = $row[ticketid];
		
		ticket_top('SUCCESS!');
		$table = new Ctable;
		$table->setwidth(600);
		$table->pushth('Success!');
		$table->push("<center><br><br>Thank you for using the GMHA Helpdesk Form. Your ticket # is <b>$ticketid</b> and password is <b>$password</b>Click 
							<a href=\"viewticket.php?id=$ticketid&pass=$password\">here</a> to view your ticket. <br><br>");
		$table->show();
		
		ticket_bottom("y");
		
		//Emailing the user their ticket number for reference. 
		email_user($ticketid, $email, $link, $password);
		
		//Emailing admins to let them know there is a new ticket. 
		email_admin($ticketid, $name, $email, $message, $phone, $location, $category, $ipadd, $link);
	}	
}
?>