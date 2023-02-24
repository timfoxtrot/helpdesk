<?php
//-------------------------------------------------------------------------
//	URL: 		report.php
//	Purpose:	Generating Reports
//  Author: 	Tim Fox Dominguez
//  Date:       3/19/2013	
//-------------------------------------------------------------------------

include 'functions.php';


switch ($_GET[action]){

	//All actions within this page
	default:			members_only();		report();			break;
	case 'date';		members_only();		report_date();		break;
	case 'category';	members_only();		report_category();	break;
	case 'user';		members_only();		report_user();		break;
}
function report(){
	//----------------------//
	//URL: report.php		//
	//Purpose: default page //
	//----------------------//
	
	ticket_top( 'Generate a Report', '600');
	
	//Report by Dates
	$startdate = '<input type="text" name="startdate" id="datepicker" size="28">';
	$enddate   = '<input type="text" name="enddate"  id="datepicker2" size="28">';

	$table = new CTable;
	$table->setwidth(600);
	$table->setspacing(0);
	$table->setpadding(6);
	$table->setcolprops ( 'width="90" bgcolor="ebebeb"', 'width="500"', 'width="10" bgcolor="ebebeb"');
	$table->pushth ( '<b>Report by Date</b>');
	$table->push('<b>Start Date:</b>',''.$startdate.'' );
	$table->push('<b>End Date:</b>',''.$enddate.'' );
	echo '<form action="report.php?action=date" method="post">';
	$table->show();
	addcoolline(600);
	echo "<input type=\"submit\" value=\"Submit Dates\" /> ";
	echo "<input type=\"reset\" value=\"Reset\">";
	echo "</form>";
	echo '<br><br>';
	
	//Report by Category
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
	echo '<br><br>';
	
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