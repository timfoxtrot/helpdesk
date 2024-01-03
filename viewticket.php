<?php
/*****************************************************************************
*	File: 		viewticket.php
*	Purpose: 	To view tickets.
*	Author:		Tim Dominguez (timfox@coufu.com)
******************************************************************************/

include "functions.php";

//password protection
if ($_GET){ 
	$ticketid = $_GET[id]; 
	$ticketpass = $_GET[pass];
}
if ($_POST){ 
	$ticketid = $_POST[id]; 
	$ticketpass = $_POST[pass];
	$_GET[id] = $_POST[id];
	$_GET[pass] = $_POST[pass];
}

viewticket_protection($ticketid, $ticketpass);

ticket_top( "View Ticket", 700 );

//The ticket itself

$db = new MyDB;
if ( $_COOKIE[userid] ) {
	$result = mysql_query( "UPDATE tickets SET newstatus = '1' WHERE ticketid = '$_GET[id]'" );
}
$db->query ( "SELECT * FROM tickets WHERE ticketid = '$_GET[id]'" );
$row = $db->getrow();

$status = "Pending";

if ( $row[solved] == 1 )
{
	$whosolved   = getusername( $row[whosolved] );
	$solveformat = date( 'm/d/y, g:ia', $row[datesolved]);
	$status = "<img src=\"check.gif\" width=13 height=13> <font size=\"1\"><i>Solved by $whosolved; $solveformat";
}

if ( $row[level] == 1 && $row[solved] != 1 )
	$status = "URGENT<img src=\"urgent.gif\" width=13 height=13>";
	
$email = "<a href=\"mailto:$row[email]\">$row[email]</a>";

if ( !$row[email] )
	$email = "None provided";
	
	
$dateformat	= date( 'm/d/y, g:ia', $row[datecreated]);
$newmessage = stripslashes( nl2br( $row[message] ));

$table = new Ctable;
$table->setwidth( "700" );
$table->setspacing(0);
$table->setcolprops ( 'width="90" bgcolor="ebebeb"','width="610"', 'bgcolor="ebebeb"');
$table->pushth ( "Ticket #$row[ticketid]", "<p align =\"right\">$dateformat", "" );
$table->push ( "<b>Name</b>: ", $row[name], "" );
$table->push ( "<b>Email</b>: ", $email, "" );
$table->push ( "<b>Phone</b>: ", $row[phonenumber], "" );
$table->push ( "<b>Location</b>: ", getlocationname($row[location]), "" );
$table->push ( "<b>Category</b>: ", getcategoryname($row[category]), "" );
$table->push ( "<b>IP Address: ", $row[ip], "" );
$table->push ( "<b>Status</b>:", $status);
$table->push ( '<b>Assigned to</b>:', ''.getusername($row[assignedto]).'');
$table->push ( "<b>Message</b>: ", "<b><font size=\"3\">$row[subject]</font></b>$newmessage<br><br>", "" );
$table->show();

addcoolline( "700" );

echo "<br><br>";

//assigning users
if ($_COOKIE[userid] ){ 
	$db = new MyDB;
	$db->query( 'SELECT * FROM users WHERE active = 1' );
	$useroption = '<select name="users">';
	while($row = $db->getrow()){
		$useroption .= '<option value ="'.$row[id].'">'.$row[username].'</option>';
	}
	$useroption .= '</select>';

	$submit = '<input type="submit" value="Submit">';
		
	$table = new CTable; 
	$table->setwidth(300);
	$table->pushth( 'Assign to User','' );
	$table->push ( 'Existing Users: '.$useroption.'', ''.$submit.'' );
	echo '<form action="settings.php?action=assign" method="post">';
	$table->show();
	echo '<input type="hidden" name="id" value="'.$_GET[id].'">';
	echo "</form>";
	addcoolline(300);
	echo '<br><br>';
}

//reassigning category
if($_COOKIE[userid]){
	$db = new MYDB;
	$db->query('SELECT * FROM categories WHERE active = 1 ORDER by name');
	
	//Form Setup
	$category_option = '<select name="categories">';
	while($row = $db->getrow()){
		$category_option .= '<option value ="'.$row[categoryid].'">'.$row[name].'</option>';
	}
	$category_option .= '</select>';
	$category_submit = '<input type="submit" value="Submit">';

	//Start Form
	$table = new CTable;
	$table->setwidth(300);
	$table->pushth('Reassign Category', '');
	$table->push('Categories:'.$category_option.'',''.$category_submit.'');	
	echo '<form action="settings.php?action=reassigncat" method="post">';
	$table->show();
	echo '<input type="hidden" name="id" value="'.$_GET[id].'">';
	echo "</form>";
	addcoolline(300);
	echo '<br><br>';

}



//Replies
$db = new MyDB;
$solution = mysql_query ("SELECT * FROM tickets WHERE ticketid =$_GET[id]");
$solution = mysql_fetch_array ($solution, MYSQL_ASSOC);
$addreply = '<table width="600"><tr><td><p align="left"><a href="admin.php?action=delete&id='.$_GET[id].'">Delete</a> | <a href="post.php?ticketid='.$_GET[id].'">Add Reply</a>';
if ( $solution[solved] == 2)
	$addreply.= '| <a href="post.php?ticketid='.$_GET[id].'&s=1">Close</a></td></tr></table>';
if ( $solution[solved] == 1)
	$addreply.= '| <a href="admin.php?action=unsolve&id='.$_GET[id].'">Reopen</a></td></tr></table>';
if ( $_COOKIE[userid] )
	echo "$addreply";

$db->query( "SELECT * FROM posts WHERE ticketid = '$_GET[id]' and active='1'" );
$numberofreplies = $db->getnumrows();

while ( $posts = $db->getrow() )
{
	$username 	 = getusername ( $posts[userid] );
	$groupname   = getusergroupname ( $posts[userid] );
	$postdate	 = date( 'm/d/y, g:ia', $posts[datecreated]);
	$postmessage = nl2br( $posts[message] );
	if ($_COOKIE[userid] == $posts[userid] OR getusergroupid( $_COOKIE[userid]) == 1 ) 
		$editdelete = "<font size=\"1\">[<a href=\"post.php?page=edit&postid=$posts[postid]\">Edit Post</a> | 
						<a href=\"post.php?page=delete&postid=$posts[postid]\">Delete Post]";
	
	
	if ($posts[dateedited]){
		$whoedited = getusername($posts[whoedited]);
		$dateedit = date( 'm/d/y, g:ia', $posts[dateedited]);
		$dateedited = "<i><font size=\"1\">Last edited by:$whoedited; $dateedit</font></i>";
	}
	
	if( !$username ) 
			continue;

	if ($posts[dateedited]){
		$table = new Ctable;
		$table->setwidth( "600" );
		$table->setspacing(0);
		$table->setcolprops ( 'width="120" bgcolor="ebebeb"', 'width="480" valign="top"', 'bgcolor="ebebeb"');
		$table->pushth ( "<b>$posts[subject]</b>", "<p align =\"right\">$postdate", "" );
		$table->push ( "<b><i><font size=\"4\">$username</b></i></font><br>$groupname <br><br>$editdelete", "$postmessage <br><br><br><hr>$dateedited", "" );
		$table->show();
		addcoolline( "600" );
		echo "<br>";
	} else {
		$table = new Ctable;
		$table->setwidth( "600" );
		$table->setspacing(0);
		$table->setcolprops ( 'width="120" bgcolor="ebebeb"', 'width="480" valign="top"', 'bgcolor="ebebeb"');
		$table->pushth ( "<b>$posts[subject]</b>", "<p align =\"right\">$postdate", "" );
		$table->push ( "<b><i><font size=\"4\">$username</b></i></font><br>$groupname <br><br>$editdelete", "$postmessage <br><br><br><hr>", "" );
		$table->show();
		addcoolline( "600" );
		echo "<br>";
	}
}

if ( $_COOKIE[userid] && $numberofreplies > 3 )
		echo "$addreply";
echo "<br>";



ticket_bottom();
?>
