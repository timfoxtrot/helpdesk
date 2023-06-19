<?php
/*****************************************************************************
*	File: 		form.php
*	Purpose: 	Ticket submission form 
*	Author:		Tim Dominguez (timfox@coufu.com)
******************************************************************************/

include 'functions.php';

//Page Handling
switch ( $_GET[page] ){
	case 'submit';		  submit($server_url);					 			 break;
	case 'worklog';	      members_only(); 		worklog();					 break;
	case 'worklogsubmit'; members_only();		worklogsubmit($server_url);  break;
	default:			  ticketform();							             break;
}

//Default Page. Main Form
function ticketform(){

	//Updated 2/16/2023
	//By: Tim Dominguez (timfox@coufu.com)

	//Top Banner
	ticket_top();
	
	//Blank Form, no values
	submissionform();

	//search form
	//ticketsearchform();
	
	//Footer
	ticket_bottom();
}

//Logging tickets for end users 
function worklog(){
	
	ticket_top();

	//Text Box
	$inputtext = inputtext( 'name', ''.$name.'', '25','', ''.$nameclass.'');

	$db = new MyDB;

	//Locations
	$location  =  '<script type="text/javascript">$(function() {$(".chzn-select").chosen();});</script>';
	$location .=  '<select name="location" class="chzn-select">';
	$db->query('SELECT * FROM locations ORDER by name ASC');
	while($row = $db->getrow()){
		$selected = NULL;
		if($locationid == $row[locationid])	$selected = 'selected="selected"';
		$location .= '<option value ="'.$row[locationid].'" '.$selected.'>'.$row[name].'</option>';
	}
	$location .= '</select>';
	
	//Categories
	$category = '<select name ="category">';
	$db->query('SELECT * FROM categories WHERE active = 1 ORDER by name ASC');
	while($row = $db->getrow()){
		$selected = NULL;
		if($categoryid == $row[categoryid])	$selected = 'selected="selected"';
		$category .= '<option value ="'.$row[categoryid].'"'.$selected.'>'.$row[name].'</option>';
	}
	$category .= '</select>';

	//Phone Formatting
	$callbacknumber = '<input type="text" name="phonenumber" value="'.$phone.'" id="phonenumber" size="16">';

	//Setting Values for the Form
	$table = new CTable;
	$table->setwidth(600);
	$table->setspacing(0);
	$table->setcolprops('width="300" bgcolor="ebebeb"', 'width="500"','width="10" bgcolor="ebebeb"');
	$table->pushth('<b>WORKLOG</b>', '', '' );
	$table->push('<b>End User:</b>', ''.$inputtext.' <b>Location:</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$location.'', '' );
	//$table->push('<b>UserEmail:</b>', ''.inputtext("email", "$email", "25", "", "$emailclass").' <b>Callback Number:</b> &nbsp;'.$callbacknumber);
	$table->push('<b>Category:</b>', ''.$category.'');
	$table->push("<b>Message:</b> ", inputtextarea("message", "$message", "70", "9", "$messageclass"));
	
	//Disable submit button when clicked
	echo '<form id="formABC" action="index.php?page=worklogsubmit" method="post">';

	print_r($anti_jill);
	
	//Creating the Form
	$table->show();
	addcoolline(600);
	echo '<br>';
	echo '<input type="submit" value ="Submit" id="btnSubmit">';
	echo '&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '<input type="reset" value ="Reset">';
	echo '</form>';

	ticket_bottom();

}

function worklogsubmit($link){

	//$_POST variables
	$username   = getusername($_COOKIE[userid]);
	if($_POST[name]){
		$name = ''.$username.' on behalf of '.$_POST[name].'';
	}else{
		$name = $username;
	}
	$phone      = "n/a";
	$message 	= $_POST[message];		$email    = "n/a";
	$location	= $_POST[location]; 	$category = $_POST[category];

	//More Variables
			
	$ip      		= $REMOTE_ADDR;
	$ipadd  		= $_SERVER['REMOTE_ADDR'];
	$message 		= addslashes($message);
	$datecreated 	= time();
	$assignedto 	= $_COOKIE[userid];
	$password		= strtoupper(substr(md5(time()), -4));

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
	$insertitem[assignedto] = $assignedto;
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

	email_admin($ticketid, $name, $email, $message, $phone, $location, $category, $ipadd, $link);

	redirect("admin.php",0);

}

//Submission Form for regular tickets
function submissionform( $name = NULL, $email = NULL, $phone = NULL, $message = NULL, $locationid = NULL, $categoryid = NULL, $errorname = FALSE, $erroremail = FALSE, $errorphone = FALSE, $errormessage = FALSE, $check = FALSE){
	
	//Updated 1/18/2023
	//By: Tim Dominguez (timfox@coufu.com)

	//Highlighting Boxes for Errors
	if ($errorname    == TRUE) $nameclass 	 = 'textboxerror';
	if ($erroremail   == TRUE) $emailclass 	 = 'textboxerror';
	if ($errorphone   == TRUE) $phoneclass 	 = 'textboxerror';
	if ($errormessage == TRUE) $messageclass = 'textboxerror';

	//Pending Tickets
	$db      = new MyDB;
	$pending = mysql_query( "SELECT * FROM tickets WHERE solved='2' AND active='1'");
	$pending = mysql_num_rows($pending);
	
	//Urgent Tickets
	$urgent = mysql_query ("SELECT * FROM tickets WHERE level ='1' AND solved ='2' AND active='1'");
	$urgent = mysql_num_rows($urgent);
	
	//Solved
	$solved = mysql_query("SELECT * FROM tickets WHERE solved ='1' AND active='1'");
	$solved = mysql_num_rows($solved);
	
	//Text Box
	$inputtext = inputtext( 'name', ''.$name.'', '25','', ''.$nameclass.'');

	//Locations
	$location  =  '<script type="text/javascript">$(function() {$(".chzn-select").chosen();});</script>';
	$location .=  '<select name="location" class="chzn-select">';
	$db->query('SELECT * FROM locations ORDER by name ASC');
	while($row = $db->getrow()){
		$selected = NULL;
		if($locationid == $row[locationid])	$selected = 'selected="selected"';
		$location .= '<option value ="'.$row[locationid].'" '.$selected.'>'.$row[name].'</option>';
	}
	$location .= '</select>';
	
	//Categories
	$category = '<select name ="category">';
	$db->query('SELECT * FROM categories WHERE active = 1 ORDER by name ASC');
	while($row = $db->getrow()){
		$selected = NULL;
		if($categoryid == $row[categoryid])	$selected = 'selected="selected"';
		$category .= '<option value ="'.$row[categoryid].'"'.$selected.'>'.$row[name].'</option>';
	}
	$category .= '</select>';

	//Phone Formatting
	$callbacknumber = '<input type="text" name="phonenumber" value="'.$phone.'" id="phonenumber" size="16">';

	//Setting Values for the Form
	$table = new CTable;
	$table->setwidth(600);
	$table->setspacing(0);
	$table->setcolprops('width="90" bgcolor="ebebeb"', 'width="500"','width="10" bgcolor="ebebeb"');
	if($_COOKIE[userid]){
		$table->pushth('<b>Submit</b>', '<p align="right">Pending: '.$pending.' | Urgent: '.$urgent.' | Solved: '.$solved.'', '' );
	} else {
		$table->pushth('<b>Submit</b>', '', '' );
	}
	$table->push('<b>Name:</b>', ''.$inputtext.' <b>Location:</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$location.'', '' );
	$table->push('<b>Email:</b>', ''.inputtext("email", "$email", "25", "", "$emailclass").' <b>Callback Number:</b> &nbsp;'.$callbacknumber);
	$table->push('<b>Category:</b>', ''.$category.'');
	$table->push("<b>Message:</b> ", inputtextarea("message", "$message", "70", "9", "$messageclass"));
	
	//Disable submit button when clicked
	$anti_jill = '<form ';
	if($check == TRUE) $anti_jill .='id="formABC"';
	$anti_jill .= 'action="form.php?page=submit" method="post">';

	print_r($anti_jill);
	
	//Creating the Form
	$table->show();
	addcoolline(600);
	echo '<br>';
	echo '<input type="hidden" name="check" value="'.$check.'">';
	echo '<input type="submit" value ="Submit" id="btnSubmit">';
	echo '&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '<input type="reset" value ="Reset">';
	echo '</form>';

}

function ticketsearchform(){
	$ticket_number   = '<input type = "text" name="id"  size="5">';
    $ticket_password = '<input type = "text" name="pass" size="5">';

    $table = new Ctable;
    $table->setwidth(200);
    $table->setspacing(0);
	$table->setpadding(10);
    $table->pushth('Ticket Search');
    $table->push('<center><form action="viewticket.php" method="post">
            <table>
            <tr>
                <td>Num:</td>
                <td>'.$ticket_number.'</td>
            </tr>
            <tr>
                <td>Pass:</td>
                <td>'.$ticket_password.'</td>
            </tr>
            <tr>
                <td></td>
                <td><center><input type="submit" value="Search"></td>
            </tr>
            </table>
        </form>'
    );
    $table->show();
}

//Data Handling
function submit($link){

	//Updated: 8/13/2013
	//By: Tim Dominguez (timfox@coufu.com)
	
	//$_POST variables
	$name 		= $_POST[name];			$phone    = $_POST[phonenumber];
	$message 	= $_POST[message];		$email    = $_POST[email];
	$location	= $_POST[location]; 	$category = $_POST[category];
	
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
		$password = strtoupper(substr(md5(time()), -4));
		
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
		$table->push("<center><br><br>Thank you for using the GMHA Helpdesk Form. Your ticket # is <b>$ticketid</b> and password is <b>$password</b><br>Click 
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