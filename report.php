<?php
//-------------------------------------------------------------------------
//	URL: 		report.php
//	Purpose:	Generating Reports
//  Author: 	Tim Fox Dominguez
//  Date:       2/24/2023
//-------------------------------------------------------------------------

include 'functions.php';


switch ($_GET[action]){

	//All actions within this page
	default:			members_only();		report();			break;
	case 'generate';	members_only();		generate();			break;
}
function report(){
	//----------------------//
	//URL: report.php		//
	//Purpose: default page //
	//----------------------//
	
	ticket_top( 'Generate a Report', '600');
	
	//Options
	$startdate  = '<input type="text" name="startdate" id="datepicker" size="15">';
	$enddate    = '<input type="text" name="enddate"  id="datepicker2" size="15">';
	$box_open   = '<input type="checkbox" name="open"><label for="open">Open</label>';
	$box_closed = '<input type="checkbox" name="closed"><label for="closed">Closed</label>';
	
	//Categories
	$category  = '<select name="category">';
	$db = new MyDB;
	$db->query('SELECT * FROM categories WHERE active =1 ORDER by name ASC');
	$category .= '<option value="all">All</option>';
	while($row = $db->getrow()){
		$category .= '<option value="'.$row[categoryid].'">'.$row[name].'</option>';
	}
	$category .= '</select>';

	//Users
	$users  = '<select name="users">';
	$db = new MyDB;
	$db->query('SELECT * FROM users WHERE active=1 ORDER by fullname ASC');
	$users .= '<option value="all">All</option>';
	while($row = $db->getrow()){
		$users .= '<option value="'.$row[id].'">'.$row[fullname].'</option>';
	}
	$users .= '</select>';


	$table = new CTable;
	$table->setwidth(600);
	$table->setspacing(0);
	$table->setpadding(10);
	$table->pushth ( '<b>Input Dates</b>','<b>Options</b>');
	$table->push('
		<form action="report.php?action=generate" method="post">
			<table>
				<tr>
					<td>Start Date:</td>
					<td>'.$startdate.'</td>
				</tr>
				<tr>
					<td>End Date:</td>
					<td>'.$enddate.'</td>
				</tr>
			</table>',
		'
		<table>
			<tr>
				<td>Status</td><td>'.$box_open.''.$box_closed.'</td>
			</tr>
			<tr>
				<td>Category:</td><td>'.$category.'</td>
			</tr>
			<tr>
				<td>Users:</td><td>'.$users.'</td>
			</tr>
		</table>
		');
	$table->show();
	addcoolline(600);
	echo '<input type="submit" value="Submit" />';
	echo '<input type="reset" value="Reset">';
	echo '</form>';
	echo '<br><br>';
	
	/*Report by Category
	$category = '<select name="category">';
	$db = new MyDB;
	$db->query( 'SELECT * FROM categories WHERE active = 1 ORDER by name ASC' ); 
	while($row = $db->getrow()){
		$category .= '<option value ="'.$row[categoryid].'">'.$row[name].'</option>';
	}
	$category .= '</select>';
	$submit = '<input type="submit" value="Submit"> ';
	
	$table = new CTable; 
	$table->setwidth(600);
	$table->pushth( 'Report by Category','' );
	$table->push ( 'Existing Categories: '.$category.'', ''.$submit.'' );
	echo '<form action="report.php?action=category" method="post">';
	$table->show();
	echo "</form>";
	addcoolline(600);
	echo '<br><br>';*/
	
	/*Leaderboard
	if ($_COOKIE[userid]){
		$db = new myDB;
		$table = new CTable;
		$table->setwidth ( "250" );
		$table->setspacing(0);
		$table->setcolprops ( 'width="90" bgcolor="ebebeb"', 'width="250"', 'width="10" bgcolor="ebebeb"');
		$table->pushth ( '<b>Leaderboard</b>', '(tickets solved)','' );
		
		$db->query( 'SELECT * FROM scoreboard ORDER by score DESC');
		while( $row = $db->getrow() ){
			$score = $row[score];
			$table->push ('<b>'.getusername($row[userid]).'</b>', '&nbsp;&nbsp;<font size="3"><a href="report.php?action=user&userid='.$row[userid].'">'.$score.'</a>');
		}
		$table->show();
		addcoolline(250);
	}*/
}

function generate(){

	if (!$_POST[startdate] OR !$_POST[enddate]){
		redirect( "report.php", 2, "Missing Fields", "Please fill in all the values" );
		exit;
	}
	
	ticket_top();

	$db = new MyDB;
	
	if($_POST[startdate] OR $_POST[enddate] OR $_POST[open] OR $_POST[closed] OR $_POST[category] OR $_POST[users]){
		
		$startdate = strtotime($_POST[startdate]);
		$enddate   = strtotime($_POST[enddate]);

		if($_POST[category] != "all") $category = 'AND category ='.$_POST[category].' ';
		if($_POST[users]    != "all") $users    = 'AND solved   ='.$_POST[users].'';

		$where = 'WHERE datecreated between '.$startdate.' AND '.$enddate.' '.$category.''.$users.'';
	}


	//if fields blank select everything
	$query = 'SELECT * FROM tickets '.$where.'';
	
	$db->query($query);
	$numoftickets = $db->getnumrows();

	$table = new CTable;
	$table->setwidth(600);
	$table->setspacing(0);
	$table->setpadding(6);
	$table->pushth( 'TEST');
	$table->show();
	echo '<br>';
	
	$table = new CTable;
	$table->setwidth(900);
	$table->setspacing(0);
	$table->setpadding(6);
	$table->setcolprops( 'width="20" class="tdtable"', 'width="430" bgcolor="ebebeb" class="tdtable"', 
						'width="100" bgcolor="white" class="tdtable"', 
						'width="100" bgcolor="ebebeb" class="tdtable"', 
						'width="25" class="tdtable"','width="100" class="tdtableright" bgcolor="ebebeb"');
	$table->pushth( "<center>#", "Contents","<center>Submitted by", "<center>Replies", "<center>Status", "<center>Options");
	while ( $row = $db->getrow() ) 
	{	
		$status = "---";
		
		if ( strlen( $row[message] ) > 60)	
			$dotdot="...";
		else 
			$dotdot= FALSE;
		if ( $row[newstatus] == 0 )
			$status = "<img src=\"new.gif\">";
		if ( $row[level] == 1)
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
						<a href=\"admin.php?action=solved&id=$row[ticketid]\">Close</a></font>";
		//Urgent and unsolved
		if ( $row[level] == 1 &&  $row[solved] == 2) 
			$menu = "<a href=\"admin.php?action=noturgent&id=$row[ticketid]\">Not urgent</a><br>
						<a href=\"admin.php?action=solved&id=$row[ticketid]\">Close</a>";
		
		//Solved Tickets
		if ( $row[solved] == 1) 
			$menu = "<a href=\"admin.php?action=unsolve&id=$row[ticketid]\">Reopen</a>";
			
			
		//number of replies
		$result = mysql_query( "SELECT * FROM posts WHERE ticketid = '$row[ticketid]' AND active='1'");
		$posts = mysql_num_rows ($result);
		
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
						<i>$postdate</i></font>", "<p align=\"right\">$posts<br><font size=\"1\">
						<i>$reply</i></font>", " <center>$status", "<center><font size=\"1\">$menu" );
		
	}
	$table->show();	
}
function report_date(){

	//-----------------------------//
	//URL: report.php?action=date  //
	//Purpose: report by date      //
	//-----------------------------//

	//errors
	if (!$_POST[startdate] OR !$_POST[enddate]){
		redirect( "report.php", 2, "Missing Fields", "Please fill in all the values" );
		exit;
	}

	ticket_top();
	
	//Database connection
	$db = new MyDB;
	$startdate = strtotime($_POST[startdate]);
	$enddate   = strtotime($_POST[enddate]);
	
	//Date values
	if ( $_POST[startdate] == $_POST[enddate] ){
		$tomorrow 	= $startdate + 86400;
		$where 		= 'datecreated >'.$startdate.' AND datecreated < '.$tomorrow.' AND active = "1" ORDER by level ASC, solved DESC, datecreated DESC';
		//$header 	= '<center>'.$numberoftickets.' Tickets from '.$_POST[startdate].''; 
	} else{
		$where 		= 'datecreated >= '.$startdate.' AND datecreated <= '.$enddate.' AND active ="1" ORDER by level ASC, solved DESC, datecreated DESC';
		//$header 	= '<center>Tickets from '.$_POST[startdate].' to '.$_POST[enddate].'';
	}
	
	$db->query( 'SELECT * FROM tickets WHERE '.$where.'' );
	$numberoftickets = $db->getnumrows();
	
	if ( $_POST[startdate] == $_POST[enddate] ){
		$header 	= '<center>'.$numberoftickets.' Tickets from '.$_POST[startdate].''; 
	} else{
		$header 	= '<center>'.$numberoftickets.' Tickets from '.$_POST[startdate].' to '.$_POST[enddate].'';
	}
	
	$table = new CTable;
	$table->setwidth(600);
	$table->setspacing(0);
	$table->setpadding(6);
	$table->pushth( ''.$header.'');
	$table->show();
	echo '<br>';
	
	$table = new CTable;
	$table->setwidth(900);
	$table->setspacing(0);
	$table->setpadding(6);
	$table->setcolprops( 'width="20" class="tdtable"', 'width="430" bgcolor="ebebeb" class="tdtable"', 
						'width="100" bgcolor="white" class="tdtable"', 
						'width="100" bgcolor="ebebeb" class="tdtable"', 
						'width="25" class="tdtable"','width="100" class="tdtableright" bgcolor="ebebeb"');
	$table->pushth( "<center>#", "Contents","<center>Submitted by", "<center>Replies", "<center>Status", "<center>Options");
	while ( $row = $db->getrow() ) 
	{	
		$status = "---";
		
		if ( strlen( $row[message] ) > 60)	
			$dotdot="...";
		else 
			$dotdot= FALSE;
		if ( $row[newstatus] == 0 )
			$status = "<img src=\"new.gif\">";
		if ( $row[level] == 1)
			$status = "<img src=\"urgent.gif\" width=15 height=15>";
			
		if ( $row[solved] == 1)
			$status = "<img src=\"check.gif\">";
		
		//The options menu
		
		//Not urgent and unsolved
		if ( $row[level] == 2 && $row[solved] == 2) 
			$menu = "<font size=\"2\"><a href=\"admin.php?action=urgent&id=$row[ticketid]\">Urgent</a> | 
						<a href=\"admin.php?action=solved&id=$row[ticketid]\">Solve!</a></font>";
		//Urgent and unsolved
		if ( $row[level] == 1 &&  $row[solved] == 2) 
			$menu = "<a href=\"admin.php?action=noturgent&id=$row[ticketid]\">Not urgent</a><br>
						<a href=\"admin.php?action=solved&id=$row[ticketid]\">Solve!</a>";
		
		//Solved Tickets
		if ( $row[solved] == 1) 
			$menu = "<a href=\"admin.php?action=unsolve&id=$row[ticketid]\">Unsolve</a>";
			
			
		//number of replies
		$result = mysql_query( "SELECT * FROM posts WHERE ticketid = '$row[ticketid]' AND active='1'");
		$posts = mysql_num_rows ($result);
		
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
						<i>$postdate</i></font>", "<p align=\"right\">$posts<br><font size=\"1\">
						<i>$reply</i></font>", " <center>$status", "<center><font size=\"1\">$menu" );
		
	}
	$table->show();	
}
function report_category(){
	//---------------------------------//
	//URL: report.php?action=category  //
	//Purpose: report by category      //
	//---------------------------------//
	
	
	
	$db = new MyDB;
	$db->query( 'SELECT * FROM tickets WHERE category = '.$_POST[category].'' );
	$numberoftickets = $db->getnumrows();
	$header 	= '<center>'.$numberoftickets.' Tickets under '.getcategoryname($_POST[category]).''; 
	
	ticket_top();
	
	$table = new CTable;
	$table->setwidth(600);
	$table->setspacing(0);
	$table->setpadding(6);
	$table->pushth( ''.$header.'');
	$table->show();
	echo '<br>';
	
	$table = new CTable;
	$table->setwidth(900);
	$table->setspacing(0);
	$table->setpadding(6);
	$table->setcolprops( 'width="20" class="tdtable"', 'width="430" bgcolor="ebebeb" class="tdtable"', 
						'width="100" bgcolor="white" class="tdtable"', 
						'width="100" bgcolor="ebebeb" class="tdtable"', 
						'width="25" class="tdtable"','width="100" class="tdtableright" bgcolor="ebebeb"');
	$table->pushth( "<center>#", "Contents","<center>Submitted by", "<center>Replies", "<center>Status", "<center>Options");
	while ( $row = $db->getrow() ) 
	{	
		$status = "---";
		
		if ( strlen( $row[message] ) > 60)	
			$dotdot="...";
		else 
			$dotdot= FALSE;
		if ( $row[newstatus] == 0 )
			$status = "<img src=\"new.gif\">";
		if ( $row[level] == 1)
			$status = "<img src=\"urgent.gif\" width=15 height=15>";
			
		if ( $row[solved] == 1)
			$status = "<img src=\"check.gif\">";
		
		//The options menu
		
		//Not urgent and unsolved
		if ( $row[level] == 2 && $row[solved] == 2) 
			$menu = "<font size=\"2\"><a href=\"admin.php?action=urgent&id=$row[ticketid]\">Urgent</a> | 
						<a href=\"admin.php?action=solved&id=$row[ticketid]\">Solve!</a></font>";
		//Urgent and unsolved
		if ( $row[level] == 1 &&  $row[solved] == 2) 
			$menu = "<a href=\"admin.php?action=noturgent&id=$row[ticketid]\">Not urgent</a><br>
						<a href=\"admin.php?action=solved&id=$row[ticketid]\">Solve!</a>";
		
		//Solved Tickets
		if ( $row[solved] == 1) 
			$menu = "<a href=\"admin.php?action=unsolve&id=$row[ticketid]\">Unsolve</a>";
			
			
		//number of replies
		$result = mysql_query( "SELECT * FROM posts WHERE ticketid = '$row[ticketid]' AND active='1'");
		$posts = mysql_num_rows ($result);
		
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
						<i>$postdate</i></font>", "<p align=\"right\">$posts<br><font size=\"1\">
						<i>$reply</i></font>", " <center>$status", "<center><font size=\"1\">$menu" );
		
	}
	$table->show();	
}
function report_user(){
	//----------------------------------//
	//URL: report.php?action=user 	    //
	//Purpose: report of tickets solved //
	//         by specified user        //
	//----------------------------------//
	
	ticket_top();
	
	$db = new MyDB;
	$db->query( 'SELECT * FROM tickets WHERE whosolved = '.$_GET[userid].'' );
	$numberoftickets = $db->getnumrows();
	if ($numberoftickets > 1){
		$tickets = 'Tickets';
	}else{
		$tickets = 'Ticket';
	}
	$header 	= '<center>'.$numberoftickets.' '.$tickets.' solved by '.getusername($_GET[userid]).'';
	
	$table = new CTable;
	$table->setwidth(600);
	$table->setspacing(0);
	$table->setpadding(6);
	$table->pushth( ''.$header.'');
	$table->show();
	echo '<br>';
	
	$table = new CTable;
	$table->setwidth(900);
	$table->setspacing(0);
	$table->setpadding(6);
	$table->setcolprops( 'width="20" class="tdtable"', 'width="430" bgcolor="ebebeb" class="tdtable"', 
						'width="100" bgcolor="white" class="tdtable"', 
						'width="100" bgcolor="ebebeb" class="tdtable"', 
						'width="25" class="tdtable"','width="100" class="tdtableright" bgcolor="ebebeb"');
	$table->pushth( "<center>#", "Contents","<center>Submitted by", "<center>Replies", "<center>Status", "<center>Options");
	while ( $row = $db->getrow() ) 
	{	
		$status = "---";
		
		if ( strlen( $row[message] ) > 60)	
			$dotdot="...";
		else 
			$dotdot= FALSE;
		if ( $row[newstatus] == 0 )
			$status = "<img src=\"new.gif\">";
		if ( $row[level] == 1)
			$status = "<img src=\"urgent.gif\" width=15 height=15>";
			
		if ( $row[solved] == 1)
			$status = "<img src=\"check.gif\">";
		
		//The options menu
		
		//Not urgent and unsolved
		if ( $row[level] == 2 && $row[solved] == 2) 
			$menu = "<font size=\"2\"><a href=\"admin.php?action=urgent&id=$row[ticketid]\">Urgent</a> | 
						<a href=\"admin.php?action=solved&id=$row[ticketid]\">Solve!</a></font>";
		//Urgent and unsolved
		if ( $row[level] == 1 &&  $row[solved] == 2) 
			$menu = "<a href=\"admin.php?action=noturgent&id=$row[ticketid]\">Not urgent</a><br>
						<a href=\"admin.php?action=solved&id=$row[ticketid]\">Solve!</a>";
		
		//Solved Tickets
		if ( $row[solved] == 1) 
			$menu = "<a href=\"admin.php?action=unsolve&id=$row[ticketid]\">Unsolve</a>";
			
			
		//number of replies
		$result = mysql_query( "SELECT * FROM posts WHERE ticketid = '$row[ticketid]' AND active='1'");
		$posts = mysql_num_rows ($result);
		
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
						<i>$postdate</i></font>", "<p align=\"right\">$posts<br><font size=\"1\">
						<i>$reply</i></font>", " <center>$status", "<center><font size=\"1\">$menu" );
		
	}
	$table->show();	
	
	
}
?>