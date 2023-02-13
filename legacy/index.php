<?php
/*****************************************************************************
*	File: 		index.php
*	Purpose: 	Contains the index page and everything that isn't admin related.
*	Author:		Tim Dominguez (timfox@coufu.com)
******************************************************************************/

include "functions.php";

switch ( $_GET[page] ) 
{
	default: 			main_submit();		break;
	case "submit";		submit_data();		break;
}

//-------------------------------------------------------------------------
//	URL: 		index.php
//	Function:	main_submit();
//	Purpose:	Shows the submission form 
//-------------------------------------------------------------------------
function main_submit()
{
	ticket_top();
	//Pending Tickets
	$db = new MyDB;
	$pending = mysql_query( "SELECT * FROM tickets WHERE solved='2' AND active='1'");
	$pending = mysql_num_rows ($pending	);
	
	//Urgent Tickets
	$urgent = mysql_query ("SELECT * FROM tickets WHERE level ='1' AND solved ='2' AND active='1'");
	$urgent = mysql_num_rows ($urgent);
	
	//Solved
	$solved = mysql_query ("SELECT * FROM tickets WHERE solved ='1' AND active='1'");
	$solved = mysql_num_rows ($solved);
	
	
	
	/*$table = new CTable;
	$table->setwidth(600);
	$table->pushth("");
	//$table->setcolprops ( 'width="10" bgcolor="ebebeb"', 'width="500"', 'width="10" bgcolor="ebebeb"');
	$table->push( "", "<p align=\"justify\">Hi, welcome to the IT&E Help Desk Application. If you are having a problem with your computer, please fill out the 
					form below and one of our great computer techs will assist you as soon as they can. Also, keep in my mind that you aren't 
					just limited to computers. Feel free to submit any problems you may have!", "");
	$table->show();*/
	$inputtext = inputtext( "name", "", "28" );
	//$inputtext.= '<i><font size="1">Before submitting please try to reboot your machine';
	
	//Locations
	$location = '<select name="location"><option value="1">Harmon</option><option value="2">Micronesia Mall</option><option value="3">Agana</option><option value="4">GPO</option><option value="5">Macheche</option><option value="6">AAFES</option></select>';
	
	//Categories
	$category = '<select name="category">';
	$db->query( 'SELECT * FROM categories WHERE active = 1 ORDER by categoryid ASC' ); 
	while($row = $db->getrow()){
		$category .= '<option value ="'.$row[categoryid].'">'.$row[name].'</option>';
	}
	$category .= '</select>';
	
	
	$table = new CTable;
	$table->setwidth ( "600" );
	$table->setspacing(0);
	$table->setcolprops ( 'width="90" bgcolor="ebebeb"', 'width="500"', 'width="10" bgcolor="ebebeb"');
	$table->pushth ( '<b>Submit Ticket</b>', '<p align="right">Pending: '.$pending.' | Urgent: '.$urgent.' | Solved: '.$solved.'', '' );
	$table->push ( '<b>Name:</b>', ''.$inputtext.' <b>Location:</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$location.'', '' );
	$table->push ( '<b>Email:</b>', ''.inputtext ( "email", "", "28" ).' <b>Callback Number:</b> &nbsp;&nbsp;'.inputtext("phonenumber","","16").'' );
	$table->push ('<b>Category:</b>', ''.$category.'');
	
	$table->push ( "<b>Message:</b> ", inputtextarea ( "message", "", "74", "9" ) );
	

	echo "<form action=\"index.php?page=submit\" method=\"post\">";
	$table->show();
	addcoolline( 600 );
	//echo '<font size="3" color="#ff0000"><b><i>*Before submitting your ticket please try to reboot your machine</font></i></b>';
	echo "<br>";
	echo "<input type=\"hidden\" name=\"ip\" value=\"$_SERVER[REMOTE_ADDR]\">";
	echo "<input type=\"submit\" value=\"Submit\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<input type=\"reset\" value=\"Reset\">";
	echo "</form>";

	ticket_bottom();
}

//-------------------------------------------------------------------------
//	URL: 		index.php?page=submit
//	Function:	submit_data();
//	Purpose:	Checks with the submitter to see if they their information 
//				if correct and then submits when they confirm
//-------------------------------------------------------------------------
function submit_data()
{
	if ( !$_POST[check] )
	{
		if ( !$_POST[message] OR !$_POST[name] OR !$_POST[email]  OR !$_POST[phonenumber] ) 
		{
			redirect( "index.php", 2, "Missing Fields", "Please fill out the form" );
			exit;
		}	
	}
	
	/*if ($_POST[email]) 
	{
		if ( validate_email($_POST[email]) != "valid" )
		{
			redirect( "index.php", 2, "Email Invalid", "The email you submitted is not valid." );
			exit;
		}
	}*/
		
if ( $_GET[id] && $_POST[check] == 'y' )
	{
		$db = new MyDB;
		$result = mysql_query( "UPDATE tickets SET active = '1' WHERE ticketid = '$_GET[id]'" );
		$db->query( "SELECT * FROM tickets ORDER by datecreated DESC LIMIT 1");
		$row = $db->getrow();
		
		ticket_top( "SUCCESS!" );
		
		if ( $row[email] ) 
			$ifmailmessage = "An email has been sent to the email you provided with the same link.";
			
		$table = new Ctable;
		$table->setwidth( "600" );
		$table->pushth ( "Success!" );
		$table->push ( "<center><br><br>Thank you for submitting your inquiry. Your ticket # is <b>$row[ticketid].</b> Click 
							<a href=\"viewticket.php?id=$row[ticketid]\">here</a> to view your ticket. $ifmailmessage <br><br>");
		$table->show();
			
		ticket_bottom( "y" );
	
		//----------------------------------------------------
		//	Sending an Email to the user
		//----------------------------------------------------
			
		if ( $row[email] )
		{
			$setmessage ="
				<html>
				Thank you for using the GMHA Troubleshooting form.<br><br>
					
				Your ticket number is <b>$row[ticketid]</b>.<br><br>
								
				To view your ticket, please click the following link:<br><br>
								
				<a href=\"https://10.6.2.21/eticket/viewticket.php?id=$row[ticketid]\">https://10.6.2.21/eticket/viewticket.php?id=$row[ticketid]</a><br><br>
								
				If you were sent this email by mistake, please disregard and delete. Sorry for the incovenience.<br><br>
								
				Please do not reply to this email.</html>";
				
			$setmessage = wordwrap ( $setmessage, 300 );
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            global $config_from_email;
			$headers .= 'From: ' . $config_from_email;
			$subject = "GMHA Troubleshooting ticket";
			$mail = mail ( $row[email], $subject, $setmessage, $headers );
				
			//Returning an error if the email didn't send. 
			if ( !$mail )
			{
				echo "The email script did not work";			
				exit;
			}
		}
			
		//----------------------------------------------------
		//	Sending an email to admin to notify them 
		//	that there is a new ticket. 
		//----------------------------------------------------
			
		$db->query( "SELECT * FROM users WHERE active='1'");
		while ( $users = $db->getrow())
		{	
			$email      = "<a href=\"mailto:$row[email]\">$row[email]</a>";
			$newmessage = stripslashes( nl2br( $row[message] ));
			$location   = getlocationname( $row[location]);
			
			$adminmessage ='
				<html>
				<b>Ticket#:</b> '.$row[ticketid].' <br>
				<b>Name:</b>    '.$row[name].'<br>
				<b>Email: </b>  '.$email.'<br>
				<b>Callback:</b><a href="tel:'.$row[phonenumber].'">'.$row[phonenumber].'</a><br>
				<b>Location:</b>'.$location.'<br>
				<b>Category:</b>'.getcategoryname($row[category]).'<br>
				<b>IP Address:</b> '.$row[ip].'<br>
				<b>Message:</b><font size="3">'.$row[subject].'</font></b>'.$newmessage.'<br><br>
				
				Click 
				<a href="https://10.6.2.21/eticket/viewticket.php?id='.$row[ticketid].'">here</a> 
					
				to view it.<br><br>
				</html>';
					
			$adminmessage = wordwrap ( $adminmessage, 300 );
			$adminheaders  = 'MIME-Version: 1.0' . "\r\n";
			$adminheaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            global $config_from_email;
			$adminheaders .= 'From: ' . $config_from_email;
			$adminsubject = "New GHMA Troubleshooting Ticket";
			$mail = mail ( $users[email], $adminsubject, $adminmessage, $adminheaders );
				
		}
	}
	
	else
	{
		if ( !$_GET[id] && !$_POST[check] ) 
		{
			//ip address
			$ip    = $REMOTE_ADDR;
			$ipadd = $_SERVER['REMOTE_ADDR'];
		
			$message				=addslashes( $_POST[message]);
			$insertitem[active]     = 2;
			$insertitem[name]		= $_POST[name];
			$insertitem[email]		= $_POST[email];
			$insertitem[phonenumber]= $_POST[phonenumber];
			$insertitem[location]   = $_POST[location];
			$insertitem[category]   = $_POST[category];
			$insertitem[message]	= $message;
			$insertitem[newstatus]  = 0;
			$insertitem[level] 		= 2;
			$insertitem[solved]		= 2;
			$insertitem[ip]  		= $ipadd;
			$insertitem[datecreated]= time();
			if ($_POST[category] == 9){
				$insertitem[assignedto] = 2;
			}else{
				$insertitem[assignedto] = 0;
			}
			$insertitem[datesolved] = "";
			$insertitem[whosolved] = "";
			
				
			$db = new MyDB;
			$db->insertarray ( "tickets", $insertitem );
				
			ticket_top ();
				
			$db->query( "SELECT * FROM tickets ORDER by datecreated DESC LIMIT 1");
			$row = $db->getrow();
					
			$table = new CTable;
			$table->setwidth(600);
			$table->pushth( "Confirm" );
			$table->push( "<b>Name: </b>", $row[name] );
			$table->push( "<b>Email: </b>", $row[email] );
			$table->push( "<b>Phone Number: </b>", $row[phonenumber] );
			$table->push( "<b>Location: </b>", getlocationname($row[location]));
			$table->push( "<b>Category: </b>", getcategoryname($row[category]));
			$table->push( "<b>Message: </b>", stripslashes( nl2br( $row[message] )));
			$table->show();
			
			echo "<br>";
			addcoolline(600);
			echo "Submit this data?";
			echo "<form action=\"index.php?page=submit&id=$row[ticketid]\" method=\"post\">";
			echo "<br>";
			echo "<input type=\"hidden\" name=\"check\" value=\"y\" />";
			
			echo "<input type=\"submit\" value=\"Yes\" /> ";
			echo "</form>";
				
			ticket_bottom();
		}
	}
	
}


?>