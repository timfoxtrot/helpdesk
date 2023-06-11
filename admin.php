<?php
/*****************************************************************************
*	File: 		admin.php
*	Purpose: 	Admin Page (Dashboard)
*	Author:		Tim Dominguez (timfox@coufu.com)
******************************************************************************/
include "functions.php";

switch ($_GET[action]){
	default: 				members_only();			admin_page(1);			break;
	case "viewall";			members_only();			admin_page();			break;
	case "viewclosed";		members_only();			admin_page(2);			break;
	case "viewassign"; 		members_only();			admin_page(3);			break;
	case "urgent";			members_only();			admin_urgent();			break;
	case "noturgent";		members_only();			admin_noturgent();		break;
	case "solved"; 			members_only();			admin_solved();			break;
	case "unsolve";			members_only();			admin_unsolved();		break;
	case "delete";			members_only();			admin_deleteticket();	break;
	case "activateuser";	members_only("admin");	admin_activateuser();	break;	
	case "deleteuser";		members_only("admin");	admin_deleteuser();		break;
	case "resetpw";			members_only("admin");	admin_resetpw();		break;
}

//-------------------------------------------------------------------------
//	URL: 		admin.php
//	Purpose:	the main admin page 
//-------------------------------------------------------------------------
function admin_page($view = NULL){
	ticket_top( "Admin Page", "900", "YES");
	
	//Main Menu
	echo "<center><a href=\"form.php?page=worklog\">Worklog</a> |<a href=\"admin.php\">View Open</a> | <a href=\"admin.php?action=viewclosed\">View Closed</a> | <a href=\"admin.php?action=viewall\">View All</a> | <a href=\"search.php\">Search</a> | <a href=\"admin.php?action=viewassign\">View Assigned</a>";

	//Database connection
	$db = new MyDB;
	
	//view all
	if ($view == NULL)
		$view_query = "SELECT * FROM tickets WHERE active ='1' ORDER by level ASC, solved DESC, datecreated DESC";
	
	//view open tickets
	if ($view == 1)
		$view_query = "SELECT * FROM tickets WHERE solved = '2' AND active ='1' ORDER by level ASC, solved DESC, datecreated DESC";
	
	//view closed tickets
	if ($view == 2) 
		$view_query = "SELECT * FROM tickets WHERE solved = '1' AND active ='1' ORDER by solved DESC, datecreated DESC";

	//view current user's tickets
	if ($view == 3)
		$view_query = "SELECT * FROM tickets WHERE solved = '2' AND active = '1' AND assignedto = $_COOKIE[userid] ORDER by solved DESC, datecreated DESC";


	$table = new CTable;
	$table->setwidth(900);
	$table->setspacing(0);
	$table->setpadding(6);
	$table->setcolprops( 'width="20" class="tdtable"', 'width="430" bgcolor="ebebeb" class="tdtable"', 
						'width="100" bgcolor="white" class="tdtable"', 
						'width="100" bgcolor="ebebeb" class="tdtable"', 
						'width="25" class="tdtable"','width="100" class="tdtableright" bgcolor="ebebeb"');
	$table->pushth( "<center>#", "Contents","<center>Submitted by", "<center>Replies", "<center>Status", "<center>Options");
	$db->query($view_query); 
	while ($row = $db->getrow()){	
		$currenttime = time();
		if(timeDiff($row[datecreated],$currenttime, "hours") >= 48 AND $row[solved] == 2){
			$duration = timeDiff($row[datecreated],$currenttime, "days");
			$status = '<font size ="2" color="ff0000"><b>'.$duration.' days old</b></font>';
		}else{
			$status = "Pending";
		}
		
		if (strlen( $row[message]) > 60)	
			$dotdot="...";
		else 
			$dotdot= FALSE;

		if ($row[newstatus] == 0)
			$status = "<img src=\"new.gif\">";
		if ($row[level] == 1)
			$status = "<img src=\"urgent.gif\" width=15 height=15>";
		
		if ($row[solved] == 1){
			$status = "<img src=\"check.gif\">";
			$status.= '<br><font size="1"><i>Closed by:<br>'.getusername($row[whosolved]).'</font>';
		} else {
			if ( $row[assignedto] != 0)
				$status.= '<br><font size="1"><i>Assigned to:<br>'.getusername($row[assignedto]).'</font>';
		}
		
		//The options menu
		
		//Not urgent and unsolved
		if ( $row[level] == 2 && $row[solved] == 2) 
			$menu = "<font size=\"2\"><a href=\"admin.php?action=urgent&id=$row[ticketid]\">Urgent</a> | 
						<a href=\"post.php?ticketid=$row[ticketid]&s=1\">Close</a></font>";
		//Urgent and unsolved
		if ( $row[level] == 1 &&  $row[solved] == 2) 
			$menu = "<a href=\"admin.php?action=noturgent&id=$row[ticketid]\">Not urgent</a><br>
						<a href=\"post.php?ticketid=$row[ticketid]&s=1\">Close</a>";
		
		//Solved Tickets
		if ( $row[solved] == 1) 
			$menu = "<a href=\"admin.php?action=unsolve&id=$row[ticketid]\">Reopen</a>";
			
			
		//number of replies
		$result = mysql_query( "SELECT * FROM posts WHERE ticketid = '$row[ticketid]' AND active='1'");
		$posts = mysql_num_rows ($result);
		
		if($posts == 0){ 
			$num_posts = '';
		}else{
			$num_posts = $posts;
		}
		
		//getting the name of the user that posted last
		$userresult = mysql_query( "SELECT * FROM posts WHERE ticketid= '$row[ticketid]' AND active='1' ORDER by datecreated DESC");
		$userid = mysql_fetch_array ( $userresult, MYSQL_ASSOC );
		if ($userid){
			$username = getusername( $userid[userid] );
			$reply    = 'Last reply by: '.$username.'';
		}
		else{
			$reply = '';
		}
	
		//messages and date
		$message  = stripslashes( substr ( "$row[message]", 0, 60 ));
		$postdate = date( 'm/d/y, g:ia', $row[datecreated]);
		
		//creating the table
		$table->push ( "<center>$row[ticketid]", "<i><a href=\"viewticket.php?id=$row[ticketid]\">$message</a>$dotdot", 
						"<p align=\"right\"><b><a href=\"mailto:$row[email]\">$row[name]</a></b><br><font size=\"1\">
						<i>$postdate</i></font>", "<p align=\"right\">$num_posts<br><font size=\"1\">
						<i>$reply</i></font>", " <center>$status", "<center><font size=\"1\">$menu" );
		
	}
	$table->show();	
	
	echo "<br><br>";

	ticket_bottom();
}

//-------------------------------------------------------------------------
//	URL: 		admin.php?action=urgent
//	Purpose:	Marks a ticket as urgent 
//-------------------------------------------------------------------------
function admin_urgent(){
	$id  = "$_GET[id]";
	$db= new MyDB; 
	$result = mysql_query ( "UPDATE tickets SET level = '1' WHERE ticketid = '$id'");
	
	redirect( "admin.php", 0 );
}

//-------------------------------------------------------------------------
//	URL: 		admin.php?action=noturgent
//	Purpose:	Marks the ticket as solved and if urgent, removes the
//				urgent status
//-------------------------------------------------------------------------
function admin_noturgent(){
	$db= new MyDB;
	$result = mysql_query( "UPDATE tickets SET level = '2' WHERE ticketid = '$_GET[id]'");
	redirect( "admin.php", 0 );
}

//-------------------------------------------------------------------------
//	URL: 		admin.php?action=solved
//	Purpose:	Marks the ticket as solved and if urgent, removes the
//				urgent status
//-------------------------------------------------------------------------
function admin_solved(){
	$whosolved = $_COOKIE[userid];
	$db= new MyDB;
	$time = time();
	$result = mysql_query( 'UPDATE tickets SET solved = "1", datesolved = "'.$time.'", whosolved = "'.$whosolved.'" WHERE ticketid = '.$_GET[id].'');
	
	//scoreboard
	$scoreboard = mysql_query( 'SELECT * FROM scoreboard WHERE userid = '.$_COOKIE[userid].'');
	$scoreboard = mysql_fetch_array($scoreboard);
	if ( $scoreboard ){
		$result = mysql_query ('SELECT * FROM tickets WHERE whosolved = '.$_COOKIE[userid].'');
		$solved = mysql_num_rows ($result);
		$update = mysql_query( 'UPDATE scoreboard SET score = '.$solved.' WHERE userid = '.$_COOKIE[userid].'');
	}else{
		$insert[userid] = $_COOKIE[userid];
		$insert[score]  = 1;
		$db = new MyDB;
		$db->insertarray( "scoreboard" , $insert );
	}
	
	//Sending Mail when the ticket is closed
	$db->query( 'SELECT email FROM tickets WHERE ticketid = '.$_GET[id].'');
	$row = $db->getrow();
	//message
	$setmessage ="
		<html>
		Your <a href=\"https://10.6.2.21/eticket/viewticket.php?id=$_GET[id]\">ticket</a> has been closed.<br><br>
						
		Please do not reply to this email.</html>";
	
	$setmessage = wordwrap ( $setmessage, 300 );
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	global $config_from_email;
	$headers .= 'From: ' . $config_from_email;
	$subject = "[Closed] GMHA Troubleshooting Ticket";
	$mail = mail ( $row[email], $subject, $setmessage, $headers );
	
	redirect( "admin.php", 0 );
}

//-------------------------------------------------------------------------
//	URL: 		admin.php?action=unsolve
//	Purpose:	Removes the solved status just in case the tech made 
//				a mistake
//-------------------------------------------------------------------------
function admin_unsolved(){
	$db= new MyDB;
	$result = mysql_query( 'UPDATE tickets SET solved = "2", datesolved="NULL", whosolved="NULL" WHERE ticketid = '.$_GET[id].'');
	redirect( "admin.php", 0 );
}
//-------------------------------------------------------------------------
//	URL: 		admin.php?action=delete
//	Purpose:	Deletes the ticket. But in actuality, just sets the active
//				status to inactive and everything else (solved, level, datesolved) 
//				back to their defaults. Can activate later just in case it was 
//				deleted by mistake.
//-------------------------------------------------------------------------
function admin_deleteticket(){
	$db= new MyDB;
	$result = mysql_query( "UPDATE tickets SET active = '2', datesolved='NULL', whosolved='NULL', level = '2', solved = '2' WHERE ticketid = '$_GET[id]'");
	redirect( "admin.php", 0 );
}
//-------------------------------------------------------------------------
//	URL: 		admin.php?action=activateuser
//	Purpose:	Activates the user
//-------------------------------------------------------------------------
function admin_activateuser($link){
	$db = new MyDB;
	$result = mysql_query( "UPDATE users SET active ='1' WHERE id = '$_GET[id]'");
	if ( !$result ){
		echo "Error message = " .mysql_error();
		exit;
	}
	
	$db->query( "SELECT * FROM users WHERE id = '$_GET[id]'");
	$row = $db->getrow();
	$setmessage = "	<html>
					Hey, $row[username]. Your account has been activated. <br><br>
					
					Click 
					<a href=\"$link/login.php\">here</a>
					to log in.<br><br>
								
					If you were sent this email by mistake, please disregard and delete. Sorry for the incovenience.<br><br>
								
					Please do not reply to this email.</html>";
	$setmessage = wordwrap ( $setmessage, 300 );
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	global $config_from_email;
	$headers .= 'From: ' . $config_from_email;
	$subject = "GMHA Help Desk Account Activated!";
	$mail = mail ( $row[email], $subject, $setmessage, $headers );
	
	redirect ( "admin.php", 0 );
}
//-------------------------------------------------------------------------
//	URL: 		admin.php?action=deleteuser
//	Purpose:	Deletes the user
//-------------------------------------------------------------------------
function admin_deleteuser(){
	$db = new MyDB;
	$db->query('SELECT * FROM users WHERE id = '.$_GET[id].'');
	$row = $db->getrow();
	if ($row[groupid] != 1){
		$db->query( "DELETE FROM users WHERE id = " . $_GET[id] . " LIMIT 1" );
		
		redirect( "admin.php", 0 );
	}else{
		redirect ("admin.php", 4, "NO WAY, JOSE", "YOURE NOT ALLOWED TO DELETE TIM");
	}
}
//-------------------------------------------------------------------------
//	URL: 		admin.php?action=resetpw
//	Purpose:	Resets the password to "gmha2023"
//-------------------------------------------------------------------------
function admin_resetpw(){
	$newpw = md5 ( "gmha2023" );
	$db = new MyDB;
	$db->query ( "UPDATE users SET password = '$newpw' WHERE id = '$_GET[id]'" );
	
	redirect ( "users.php", 0, "Success", "The password has been reset to gmha2023" );
}
?>

