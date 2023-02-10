<?php
/*****************************************************************************
*	File: 		search.php
*	Purpose: 	The search function and algorithm
*	Author:		Tim Dominguez (timfox@coufu.com)
******************************************************************************/
include "functions.php";

members_only();

$db = new MyDB;

// checks if a search has been submitted
if(!empty($_REQUEST['search']))
{
  // the table to search
  $table = "tickets";
  // explode search words into an array
  $arraySearch = explode(" ", $_REQUEST[search]);
  // table fields to search
  $arrayFields = array( "ticketid", "name", "email", "message" );
  $countSearch = count($arraySearch);
  $a = 0;
  $b = 0;
  $query = "SELECT * FROM ".$table." WHERE (";
  $countFields = count($arrayFields);
  while ($a < $countFields)
  {
    while ($b < $countSearch)
    {
      $query = $query."$arrayFields[$a] LIKE '%$arraySearch[$b]%'";
      $b++;
      if ($b < $countSearch)
      {
        $query = $query." AND ";
      }
    }
    $b = 0;
    $a++;
    if ($a < $countFields)
    {
      $query = $query.") OR (";
    }
  }
  $query = $query.")";
  $query_result = mysql_query($query);
  
  if(mysql_num_rows($query_result) < 1)
  {

	ticket_top ( "Search", "900");
    echo '<p>No matches found for "'.$_REQUEST[search].'"</p>';
  }
  else
  {
	ticket_top ( "Search", "900");
	echo '<p>Search Results for "'.$_REQUEST[search].'":</p>'."\n\n";

	$table = new CTable;
	$table->setwidth(900);
	$table->setspacing(0);
	$table->setpadding(6);
	$table->setcolprops( 'width="20" class="tdtable"', 'width="430" bgcolor="ebebeb" class="tdtable"', 
						'width="100" bgcolor="white" class="tdtable"', 
						'width="100" bgcolor="ebebeb" class="tdtable"', 
						'width="25" class="tdtable"','width="100" class="tdtableright" bgcolor="ebebeb"');
	$table->pushth( "<center>#", "Contents","<center>Submitted by", "<center>Replies", "<center>Status", "<center>Options");
    
    // output list of articles
    while($row = mysql_fetch_assoc($query_result))
    {
      // output whatever you want here for each search result

		$status = "---";
		
		if ( strlen( $row[message] ) > 60)	
			$dotdot="...";
		else 
			$dotdot= FALSE;
		if ( !$row[newstatus] )
			$status = "<img src=\"new.gif\">";
		if ( $row[level] == 1)
			$status = "<img src=\"urgent.gif\" width=15 height=15>";
			
		if ( $row[solved] == 1)
			$status = "<img src=\"check.gif\">";
		
		//The options menu
		if ( $row[level] == 2 && $row[solved] == 2) 
			$menu = "<a href=\"admin.php?action=urgent&id=$row[ticketid]\">Urgent</a><br>
						<a href=\"admin.php?action=solved&id=$row[ticketid]\">Solved</a>";
			
		if ( $row[level] == 1 &&  $row[solved] == 2) 
			$menu = "<a href=\"admin.php?action=noturgent&id=$row[ticketid]\">Not urgent</a><br>
						<a href=\"admin.php?action=solved&id=$row[ticketid]\">Solved</a>";
		if ( $row[level] == 2 && $row[solved] == 1) 
			$menu = "<a href=\"admin.php?action=unsolve&id=$row[ticketid]\">Unsolve</a>";
			
		
		$result = mysql_query( "SELECT * FROM posts WHERE ticketid = '$row[ticketid]' AND active = 1");
		$posts = mysql_num_rows ($result);
		$userresult = mysql_query( "SELECT * FROM posts WHERE ticketid= '$row[ticketid]' ORDER by datecreated DESC");
		$userid = mysql_fetch_array ( $userresult, MYSQL_ASSOC );
		if ($userid[userid])
			$username = getusername( $userid[userid] );
		$message  = stripslashes( substr ( "$row[message]", 0, 60 ));
		$postdate = date( "m-d-y, g:i a", $row[datecreated]);
		$table->push ( "<center>$row[ticketid]", "<i><a href=\"viewticket.php?id=$row[ticketid]\">$message</a>$dotdot", 
						"<p align=\"right\"><b><a href=\"mailto:$row[email]\">$row[name]</a></b><br><font size=\"1\">
						<i>$postdate</i></font>", "<p align=\"right\">$posts<br><font size=\"1\">
						<i>Last reply by: <b>$username</i></font>", " <center>$status", "<center><font size=\"1\">$menu" );
		
	}
	$table->show();
  }
}
else
{

	ticket_top ( "Filter");
}
?>

<p><form method="get">
  <input type="text" name="search" value="<?php echo $_REQUEST['search'] ?>" />
  <input type="submit" value="Search" />
</form></p>

<?php

ticket_bottom();

?>

