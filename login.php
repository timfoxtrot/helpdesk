<?php
/*****************************************************************************
*	File: 		login.php
*	Purpose: 	Contains all the pages that deal with logging in 
*				and setting cookies
*	Author:		Tim Dominguez (timfox@coufu.com)
******************************************************************************/

ob_start();

include "functions.php";

switch ( $_GET[action] )
{
	default:				ticket_login();		break;
	case "submit";			loginsubmit();		break;
	case "register";		register_form();	break;
	case "registersubmit";	register_submit();	break;	
	case "logout";			logoutsubmit();		break;
	
	
}

//-------------------------------------------------------------------------
//	URL: 		login.php
//	Purpose:	Uhh..to login.
//-------------------------------------------------------------------------
function ticket_login()
{
	ticket_top( "Login" );
	if( !$_COOKIE[userid] )
	{
		$table = new Ctable;
		$table->pushth( "ADMIN LOGIN" );
		$table->push( '<font size="5">username</font>', inputtext( "username", '',0,0, "login"));
		$table->push( '<font size="5">password</font>', inputpw ( "password", '',0,"login" ) );
		echo "<form action=\"login.php?action=submit\" method=\"post\">";
		$table->show();
		echo "<br>";
		echo "<input type=\"submit\" value=\"Login\" /> ";
		echo "<input type=\"reset\" value=\"Reset\" /> ";
		echo "</form>";
		
		//echo "Click <a href=\"login.php?action=register\">here</a> to register";
		//echo "<br>";
		
		ticket_bottom();
	}
	else
	{	
		$table = new Ctable;
		$table->setwidth( "600" );
		$table->pushth( " " );
		$table->push( "<br>GMHA Helpdesk<br><br><b>To Do</b><br>
		<ul>
			<li>Time of unsolved tickets</li>
			<li>Password to view ticket</li>
			<li>SOP</li>

		</ul>
		" );
		$table->show();
		
		ticket_bottom( "y" );
	}
}
//-------------------------------------------------------------------------
//	URL: 		login.php?action=submit
//	Purpose:	Queries the database (table: users) and sets a cookie if the
//				username and password match
//-------------------------------------------------------------------------
function loginsubmit()
{

	//Connecting to the Database
	$db = new MyDB;
	$db->query( "SELECT * FROM users WHERE username = '$_POST[username]'" );
	$row = $db->getrow();
	
	//Error Check
	$errors = array();
	if( !$row )									array_push( $errors, "No username or username does not exist" );
	//Checking password
	$encryptedpass = md5($_POST[password]);     
	if ( $encryptedpass != $row[password] ) 	array_push( $errors, "Wrong Password" );
	
	//Checking if they're an active user
	if ( $row[active] != 1 ) 					array_push( $errors, "You are not an active user" );
	
	//Stopping the script if errors exist
	if ( $errors ) 
	{
		ticket_top( "Error Logging In" );
		
		$table = new Ctable;
		$table->setwidth ( "400" );
		$table->pushth ( "Error Logging In" );
		$table->show();
		
		//Printing List of Errors
		foreach ( $errors as $error ) 
		{
			echo "<li>$error \n";
			echo "<br><br>";
			ticket_bottom();
			exit; //the kill command hehe
		}
		ticket_bottom();
	} 

	//Logging
	$insertlog[userid] = $row[id];
	$insertlog[date]   = time();

	$db = new MyDB;
	$db->insertarray("log", $insertlog);
	
	//Setting the cookie..Yummy.
	if ($row[id] == 15){
		setcookie ( "userid", $row[id], time()+86400*30 ); 
	} else{
		setcookie ( "userid", $row[id], time()+86400 ); 
	}
	
	
	//Redirecting
	redirect( "admin.php", 0 , "Login Success", "" );
	
}

//-------------------------------------------------------------------------
//	URL: 		login.php?action=register
//	Purpose:	Register form (Inactive)
//-------------------------------------------------------------------------
/*function register_form()
{

	ticket_top();
	
	$groups = "<select name=\"groupid\"><option value=\"2\">Information Management</option><option value=\"3\">Tech Support</option></select>";
	
	$table = new CTable;
	$table->pushth( "Register (all fields are required)" );
	$table->push( "Name: ", inputtext( "name" ) );
	$table->push( "Email: ", inputtext( "email" ) );
	$table->push( "Password: ", inputpw( "pw" ) );
	$table->push( "Retype Password: ", inputpw( "pw2" ) );
	$table->push( "Group: ", $groups );
	
	echo "<form action=\"login.php?action=registersubmit\" method=\"post\">";
	$table->show();
	echo "<br><br>";
	echo "<input type=\"submit\" value=\"Submit\" /> ";
	echo "<input type=\"reset\" value=\"Reset\">";
	echo "</form>";
	
	ticket_bottom();
}
//-------------------------------------------------------------------------
//	URL: 		login.php?action=registersubmit
//	Function:	register_form();
//	Purpose:	submits the register form 
//-------------------------------------------------------------------------
function register_submit()
{
	ticketmysqlconnect();
	$result = mysql_query ( "SELECT * FROM users" );
	$errors = array();
	while ( $users = mysql_fetch_array( $result, MYSQL_ASSOC ) )
	{
		if( $_POST[name] == $users[username] ) 		array_push ( $errors, "That username already exists" ); 
	} 

	//Error Check
	if (!$_POST[name]) 								array_push ( $errors, "No username");
	if (!$_POST[pw])								array_push ( $errors, "Please specify password" );
	if ($_POST[pw] != $_POST[pw2])					array_push ( $errors, "Passwords don't match" );
	if (!$_POST[email]) 							array_push ( $errors, "Please specify email" );
	if ( validate_email($_POST[email]) != "valid" ) array_push ( $errors, "Email is not valid" );
	if (!$_POST[groupid]) 							array_push ( $errors, "Please specify a group" );
	
	//If there are errors this prints out a list of them and kills the script
	if ( $errors )
	{
		ticket_top( "Error");
		echo "Error Adding User";
		foreach ( $errors as $err )
		echo "<li>$err \n";
		exit;
	}
	//If there are no errors this adds the user to the database
	$insert[username] = "$_POST[name]"; 
	$insert[email]	  = "$_POST[email]";
	$insert[password] = md5( $_POST[pw] );
	$insert[groupid]  = "$_POST[groupid]";		
	$insert[active]   = "2";

	$db = new MyDB;
	$db->insertarray( "users" , $insert );

	ticket_top ();
	
	$table = new CTable;
	$table->setwidth("600");
	$table->pushth( "Success" );
	$table->push( "A notification has been sent to us. Please wait while we verify your user. An email will be sent once your user has been activated." );
	$table->show();
	
	ticketmysqlconnect();
	$result = mysql_query ( "SELECT * FROM users WHERE groupid = '1'");
	while ( $row = mysql_fetch_array( $result, MYSQL_ASSOC ))
	{
		$setmessage = "	<html>
					Hello Godlike person, there's a new user waiting to be approved. 
					
					Click 
					<a href=\"http://mysql.ite.net/ticket/login.php\">here</a>
					to log in.<br><br>
								
					If you were sent this email by mistake, please disregard and delete. Sorry for the incovenience.<br><br>
								
					Please do not reply to this email.</html>";
		$setmessage = wordwrap ( $setmessage, 300 );
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: ticket@itehq.net';
		$subject = "ITE Help Desk -- New user";
		$mail = mail ( $row[email], $subject, $setmessage, $headers );
	}
	
	ticket_bottom();
	
}*/

//-------------------------------------------------------------------------
//	URL: 		login.php?action=logout
//	Function:	logoutsubmit();
//	Purpose:	Logs out the user by overriding their current cookie
//				with a cookie that has no value
//-------------------------------------------------------------------------
function logoutsubmit()
{
	setcookie( "userid" );
	redirect( "index.php", 0, "Logout Successful", "" );
}
?>
