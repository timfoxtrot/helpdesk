<?php
/*****************************************************************************
*	File: 		functions.php
*	Purpose: 	Contains all common functions used within the IT&E
*				Ticketing Script
*	Author:		Tim Dominguez (timfox@coufu.com)
*   Updated:    1/18/2023
******************************************************************************/

include "drtlib.php";
include "config.php";

//Displays the header (top) of the pages
function ticket_top ($title = NULL, $width = 600, $refresh = NULL){	
	echo "<html>";
	echo "<head>";

	//Refresh on admin page
	if ($refresh == "YES") echo "<meta http-equiv=\"refresh\" content=\"10\">";
	
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\">";
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\">";
	echo '<link rel="stylesheet" href="js/jquery-ui-1.10.2.custom.min.css" />
			<script src="https://code.jquery.com/jquery-3.6.3.js"></script>
			<script src="js/jquery-ui-1.10.2.custom.min.js"></script>
			<link rel="stylesheet" href="/resources/demos/style.css" />
			<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.jquery.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.4.1/jquery.maskedinput.js"></script>
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.css">
			<script>
				$(function() {
				$( "#datepicker" ).datepicker();
				$( "#datepicker2" ).datepicker();
			});
			</script>
			<script>
				$(function (){
				$("#phonenumber").mask("(999) 999-9999");
				});
			</script>
			<script>
    $(document).ready(function () {

        $("#formABC").submit(function (e) {

            //stop submitting the form to see the disabled button effect
            //e.preventDefault();

            //disable the submit button
            $("#btnSubmit").attr("disabled", true);

            //disable a normal button
            $("#btnTest").attr("disabled", true);

            return true;

        });
    });
</script>
			';
	echo "<title>GMHA Help Desk ";
	if($title)	echo "[ $title ]";
	echo "</title>";
	echo "</head>";
	echo "<body>";
	echo "<center>";
	$tbl = new Ctable;
	$tbl->push( array ( "<a href=\"login.php\"><img src=\"gmhalogo.png\" border=0></a>" ) );
	$tbl->show();
	echo "<br>";
	
	//Navigation (Only appears if the user is logged in)
	if( $_COOKIE[userid] ){
		$username = getusername( $_COOKIE[userid] );
		$table = new Ctable;
		$table->setwidth( "$width" );
		$table->push( "Hello, <b>$username</b>", "<p align=\"right\"><a href=\"index.php\">Home</a> | <a href=\"admin.php\">Tickets</a> | <a href=\"users.php\">Users</a> | <a href=\"report.php\">Reports</a> | <a href=\"login.php?action=logout\">Logout</a>" );		
		$table->show();
	}
}

//Default Page. Main Form
function ticketform(){

	//Updated 2/16/2023
	//By: Tim Dominguez (timfox@coufu.com)

	//Top Banner
	ticket_top();
	
	//Blank Form, no values
	submissionform();
	
	//Footer
	ticket_bottom();
}

//Displays Bottom of the Page
function ticket_bottom($coolline = NULL, $width = 600){
	if ($coolline){
		$table = new Ctable;
		$table->setwidth( "$width" );
		$table->pushth( " " );
		$table->show();
		echo "<br><font size=\"1\"><a href=\"login.php\">&copy;</a>Copyright <a href=\"mailto:timothy-jed.dominguez@gmha.org\">GMHA</a><br>";
	}else{
		echo "<br><font size=\"1\"><a href=\"login.php\">&copy;</a>Copyright <a href=\"mailto:timothy-jed.dominguez@gmha.org\">GMHA</a><br>";
	}
}	

//Login (and/or permission) check 
function members_only($permission = NULL ){
	//This checks if the function states if only admin can view the page
	if ($permission == "admin") {
		$db = new MyDB;
		$db->query("SELECT groupid FROM users WHERE id = '$_COOKIE[userid]'");
		$row = $db->getrow();
		
		if  ($row[groupid] != 1) $error = TRUE;
	}
	
	//If they aren't logged in, error.
	if (!$_COOKIE[userid])   $error = TRUE; 
	
	//If there are any errors, this kills the script and redirects them to the index page. 
	if ( $error ) {
		redirect( "index.php", 2,  "Access Denied", "You do not have correct privileges to view this page. Go away" );
		exit;
	}
}

//Getting the group name from the id
function getgroupname ($id){

	if( $id == 1 )  $groupname = "Admin";
	if( $id == 2 )  $groupname = "Helpdesk";
	if( $id == 3 )	$groupname = "Helpdesk";
	
	return $groupname;
}

//Getting groupname from userid
function getusergroupname ($userid){
	ticketmysqlconnect();
	$result = mysql_query ("SELECT * from users WHERE id = '$userid'"); 
	$row    = mysql_fetch_array ( $result, MYSQL_ASSOC);
	
	if( $row[groupid] == 1 ) $groupname = "Admin";
	if( $row[groupid] == 2 ) $groupname = "Helpdesk";
	if( $row[groupid] == 3 ) $groupname = "Helpdesk";
	
	return $groupname;
}

//Getting groupid from userid
function getusergroupid($userid){
	ticketmysqlconnect();
	$result = mysql_query ("SELECT * from users WHERE id = '$userid'"); 
	$row = mysql_fetch_array ( $result, MYSQL_ASSOC);
	$groupid = "$row[groupid]";
	return $groupid;
}

//Adding a cool looking line hehe
function addcoolline($width = 450){
	$table = new Ctable;
	$table->setwidth( "$width" );
	$table->pushth( " " );
	$table->show();
}

//Getting the username from the userid
function getusername( $id ){
	ticketmysqlconnect();
	$result = mysql_query( "SELECT fullname FROM users WHERE id = '$id'" );
	$row      = mysql_fetch_array ($result, MYSQL_ASSOC);
	$username = $row[fullname];
	
	return $username;
}

//Getting location name from location id
function getlocationname($id){
	
	ticketmysqlconnect();
	$result = mysql_query( "SELECT name FROM locations WHERE locationid = '$id'" );
	$row = mysql_fetch_array ( $result, MYSQL_ASSOC);
	$locationname = $row[name];
	return $locationname;
	
}

//Getting category name
function getcategoryname( $id ){
	ticketmysqlconnect();
	$result       = mysql_query('SELECT name FROM categories WHERE categoryid = '.$id.'');
	$categoryname = mysql_fetch_array($result);
	$categoryname = $categoryname[0];
	return $categoryname;
}

//Submission Form
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
	$table->push("<b>Message:</b> ", inputtextarea("message", "$message", "74", "9", "$messageclass"));
	
	//Disable submit button when clicked
	$anti_jill = '<form ';
	if($check == TRUE) $anti_jill .='id="formABC"';
	$anti_jill .= 'action="index.php?page=submit" method="post">';

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

//Emailing Users
function email_user($ticketid, $email, $link){
	
	//Updated: 1/20/2023
	//By: Tim Dominguez (timfox@coufu.com)
	
	$setmessage = "
		<html>
		Thank you for using the GMHA Helpdesk Form.<br><br>
			
		Your ticket number is <b>$ticketid</b>.<br><br>
						
		To view your ticket, please click the following link:<br><br>
						
		<a href=\"$link/viewticket.php?id=$ticketid\">$link/viewticket.php?id=$ticketid</a><br><br>
						
		Please do not reply to this email.</html>";
	
	$setmessage = wordwrap($setmessage, 300);
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: gmhahelpdesk@gmha.org';
	$subject =  "GMHA Helpdesk";
	
	$mail = mail($email, $subject, $setmessage, $headers);
	
	//Error check if email is broken
	if(!$mail)	echo 'Error: The email user script did not go through';
}

//Emailing Admins about new ticket
function email_admin($ticketid, $name, $email, $message, $phone, $locationid, $categoryid, $ipadd, $link){

	//Updated: 1/18/2023
	//By: Tim Dominguez (timfox@coufu.com)
	
	$db = new MyDB;
	$db->query("SELECT * FROM users WHERE active ='1'");
	while($users = $db->getrow()){
	
		$newmessage 	= stripslashes( nl2br($message));
		$location   	= getlocationname($locationid);
		$category   	= getcategoryname($categoryid);
	
		$adminmessage ='
			<html>
			<b>Ticket#:</b> '.$ticketid.' <br>
			<b>Name:</b>    '.$name.'<br>
			<b>Email: </b>  '.$email.'<br>
			<b>Callback:</b><a href="tel:'.$phone.'">'.$phone.'</a><br>
			<b>Location:</b>'.$location.'<br>
			<b>Category:</b>'.$category.'<br>
			<b>IP Address:</b> '.$ipadd.'<br>
			<b>Message:</b>'.$newmessage.'<br><br>
			
			Click 
			<a href="'.$link.'/viewticket.php?id='.$ticketid.'">here</a> 
				
			to view it.<br><br>
			</html>';
					
		$adminmessage = wordwrap ( $adminmessage, 300 );
		$adminheaders  = 'MIME-Version: 1.0' . "\r\n";
		$adminheaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$adminheaders .= 'From: gmhahelpdesk@gmha.org';
		$adminsubject = "NEW GMHA Helpdesk Ticket";
		$mail = mail ( $users[email], $adminsubject, $adminmessage, $adminheaders );

	}
	//Error check if email is broken
	//if(!$mail)	echo 'Error: The email admin script did not go through';

}

//Setting the timezone (for Guam time) for date functions
date_default_timezone_set( 'Etc/GMT-10' );

//Calculating time differences (for ticket duration purposes)
function timeDiff($firstTime,$lastTime,$value = NULL){

    // convert to unix timestamps
    //$firstTime=strtotime($firstTime);  --- removing because datecreated is already in unix format
    //$lastTime=strtotime($lastTime);

    // perform subtraction to get the difference (in seconds) between times
    $difference = $lastTime-$firstTime;

	$mins 	= abs(floor($difference/60));
	$hours  = abs(floor($mins/60));
	$days   = abs(floor($hours/24));

    //no value returns seconds
    switch($value){
        default:        	return $difference;   break;
		case "minutes";  	return $mins;         break;
		case "hours";   	return $hours;        break;
        case "days";    	return $days;         break;
    }
}

?>