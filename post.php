<?php
/*****************************************************************************
*	File: 		post.php
*	Purpose: 	Includes all scripts dealing with posting replies. 
*	Author:		Tim Dominguez (timfox@coufu.com)
******************************************************************************/
include "functions.php";

switch ( $_GET[page] ) 
{
	default: 			members_only();		main_submit();					break;
	case "submit";		members_only();		submit_data($server_url);		break;
	case "edit";		members_only();		edit_post();					break;
	case "delete";		members_only();		delete_post();					break;
}
	
//-------------------------------------------------------------------------
//	URL: 		post.php
//	Purpose:	Shows the submission form 
//-------------------------------------------------------------------------
function main_submit()
{
	ticket_top();
	if ($_GET[s] == 1){
		$checkyes = true;
		$subject  = 'Resolution Details';
	} else{
		$checkno  = true;
		$subject  = '';
	}
	
	$input  = inputtext ( 'subject', ''.$subject.'', '28' );
	$input .= "<b>Solved? &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>";
	$input .= inputradio ('solved', '2', $checkno);
	$input .= "<b>No</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$input .= inputradio ('solved', '1', $checkyes );
	$input .= "<b>Yes</b>";
	
	$table = new CTable;
	$table->setwidth ( "600" );
	$table->setspacing(0);
	$table->setcolprops ( 'width="90" bgcolor="ebebeb"', 'width="500"', 'width="10" bgcolor="ebebeb"');
	$table->pushth ( "Post Reply" );
	$table->push ( "<b>Subject:</b> ", $input , "" );
	$table->push ( "<b>Message:</b> ", inputtextarea ( "message", "", "74", "9" ) );
	
	echo "<form action=\"post.php?page=submit\" method=\"post\">";
	$table->show();
	addcoolline( 600 );
	echo "<br>";
	echo "<input type=\"hidden\" name=\"ticketid\" value=\"$_GET[ticketid]\">";
	echo "<input type=\"hidden\" name=\"userid\" value=\"$_COOKIE[userid]\">";
	echo "<input type=\"submit\" value=\"Submit\">";
	echo "<input type=\"reset\" value=\"Reset\">";
	echo "</form>";
}
//-------------------------------------------------------------------------
//	URL: 		post.php?page=submit
//	Purpose:	Submits the post to add or edit. 
//-------------------------------------------------------------------------
function submit_data($link) {

	if ($_POST[id]) {
		$time = time();
		$whoedited = getusername ( $_COOKIE[userid] );
		$db = new MyDB;
		$result = mysql_query( "UPDATE posts SET 
							subject = '$_POST[subject]' , 
							message = '$_POST[message]',
							dateedited ='$time',
							whoedited = '$whoedited' WHERE postid = '$_POST[id]'" );
		$db->query( "SELECT * FROM posts WHERE postid = '$_POST[id]'");
		$row = $db->getrow();
		if ( !$result )
		{
			echo "Error message = " .mysql_error();
		}
		
		redirect( "viewticket.php?id=$row[ticketid]", 2, "Success", "Post edited" );
	}
	
		
	else {
		if ($_POST[solved] == 1){
		
			$whosolved = $_COOKIE[userid];
			$db= new MyDB;
			$time = time();
			$result = mysql_query( 'UPDATE tickets SET solved = "1", datesolved = "'.$time.'", whosolved = "'.$whosolved.'" WHERE ticketid = '.$_POST[ticketid].'');
			
			$insertitem[ticketid]	= "$_POST[ticketid]";	
			$insertitem[active]		= "1";
			$insertitem[userid] 	= "$_POST[userid]";
			$insertitem[subject]	= "$_POST[subject]";
			$insertitem[message]	= "$_POST[message]";
			$insertitem[datecreated]= time();
			$insertitem[dateedited] = "";
			$insertitem[whoedited]  = "";
			
			$db = new MyDB;
			$db->insertarray( "posts", $insertitem );
			
			
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
			$username = getusername($_COOKIE[userid]);
			$db->query( 'SELECT email FROM tickets WHERE ticketid = '.$_POST[ticketid].'');
			$row = $db->getrow();
			//message
			$setmessage ="
				<html>
				Your ticket <a href=\"$link/viewticket.php?id=$_POST[ticketid]\"> (#$_POST[ticketid])</a> has been closed with the following message:<br><br>
				<b>Subject:</b> $_POST[subject]<br>
				<b>Message:</b> \"$_POST[message]\" - $username<br><br>
						
				Please do not reply to this email.</html>";
			
			$setmessage = wordwrap ( $setmessage, 300 );
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: gmhahelpdesk@gmha.org';
			$subject = "Solved - GMHA Helpdesk Ticket";
			$mail = mail ( $row[email], $subject, $setmessage, $headers );
			
			redirect( "admin.php", 0 );
		}else{
	
			$insertitem[ticketid]	= "$_POST[ticketid]";	
			$insertitem[active]		= "1";
			$insertitem[userid] 	= "$_POST[userid]";
			$insertitem[subject]	= "$_POST[subject]";
			$insertitem[message]	= "$_POST[message]";
			$insertitem[datecreated]= time();
			$insertitem[dateedited] = "";
			$insertitem[whoedited]  = "";
			
			$db = new MyDB;
			$db->insertarray( "posts", $insertitem );
			
			$db->query ("SELECT * FROM tickets WHERE ticketid = '$_POST[ticketid]'");
			$row2 = $db->getrow();
			
			if ( $row2[email] ){
				$setmessage ="
					<html>
					
					Someone has posted a reply to your ticket (<b>$_POST[ticketid]</b>).<br><br>
										
					To view your ticket, please click the following link:<br><br>
									
					<a href=\"$link/viewticket.php?id=$_POST[ticketid]\">$link/viewticket.php?id=$_POST[ticketid]</a><br><br>
									
					Please do not reply to this email.</html>";
					
				$setmessage = wordwrap ( $setmessage, 300 );
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: gmhahelpdesk@gmha.org';
				$subject = "GMHA Helpdesk Reply";
				$mail = mail ( $row2[email], $subject, $setmessage, $headers );
					
				//Returning an error if the email didn't send. 
				/*if ( !$mail ){
					echo "The email script did not work";			
					exit;
				}*/
			}
			redirect( "viewticket.php?id=$_POST[ticketid]",0, "Success!", "Your message was posted successfully" );
		}
		
		//redirect( "viewticket.php?id=$_POST[ticketid]", 2, "Success!", "Your message was posted successfully" );
	}
}
//-------------------------------------------------------------------------
//	URL: 		post.php?page=edit
//	Purpose:	Edits a post
//-------------------------------------------------------------------------
function edit_post()
{
	$db = new MyDB;
	$db->query( "SELECT * FROM posts WHERE postid='$_GET[postid]'");
	$row = $db->getrow();
	
	/*First things first: This checks if the user is either an admin ( Godlike hehe ) 
		or the actually owner of the requested post. If neither, it will redirect them back
		to the admin page with an error message*/
	if ( $_COOKIE[userid] == $row[userid] OR getusergroupid( $_COOKIE[userid]) == 1 ) 
	{
		ticket_top();
		
		$table = new CTable;
		$table->setwidth ( "600" );
		$table->setspacing(0);
		$table->setcolprops ( 'width="90" bgcolor="ebebeb"', 'width="500"', 'width="10" bgcolor="ebebeb"');
		$table->pushth ( "Edit Post" );
		$table->push ( "<b>Subject:</b> ", inputtext ( "subject", "$row[subject]", "28" ), "" );
		$table->push ( "<b>Message:</b> ", inputtextarea ( "message", "$row[message]", "74", "9" ) );
	
		echo "<form action=\"post.php?page=submit\" method=\"post\">";
		$table->show();
		addcoolline( 600 );
		echo "<br>";
		echo "<input type=\"hidden\" name=\"id\" value=\"$_GET[postid]\">";
		echo "<input type=\"hidden\" name=\"userid\" value=\"$_COOKIE[userid]\">";
		echo "<input type=\"submit\" value=\"Edit\">";
		echo "<input type=\"reset\" value=\"Reset\">";
		echo "</form>";
		
	}

	else
	{
		redirect( "admin.php", 2, "ERROR!" , "That is not your post!");
	}
}
//-------------------------------------------------------------------------
//	URL: 		post.php?page=delete
//	Purpose:	Deletes a post. In actuality, it doesn't delete the post
//				completely. It sets 'active' to 2 and the script is set
//				so that it only shows posts who's active column is '1'
//-------------------------------------------------------------------------
function delete_post()
{
	$db = new MyDB;
	$db->query( "SELECT * FROM posts WHERE postid='$_GET[postid]'");
	$row = $db->getrow();
	
	/*First things first: This checks if the user is either an admin ( Godlike hehe ) 
		or the actually owner of the requested post. If neither, it will redirect them back
		to the admin page with an error message*/
	if ( $_COOKIE[userid] == $row[userid] OR getusergroupid( $_COOKIE[userid]) == 1 ) 
	{
		$result = mysql_query ( "UPDATE posts SET active = '2' WHERE postid='$_GET[postid]'" );
		redirect( "viewticket.php?id=$row[ticketid]", 0 );
	}
	
	else
	{
		redirect( "admin.php", 2, "ERROR!" , "That is not your post!");
	}
}

?>