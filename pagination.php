<?php
	/*
		Place code to connect to your DB here.
	*/
	include('functions.php');	// include your code to connect to DB.
	ticketmysqlconnect();

	$tbl_name="ticket";		//your table name
	// How many adjacent pages should be shown on each side?
	$adjacents = 3;
	
	/* 
	   First get total number of rows in data table. 
	   If you have a WHERE clause in your query, make sure you mirror it here.
	*/
	$query = "SELECT COUNT(*) as num FROM $tbl_name";
	$total_pages = mysql_fetch_array(mysql_query($query));
	$total_pages = $total_pages[num];
	
	/* Setup vars for query. */
	$targetpage = "pagination.php"; 			//your file name  (the name of this file)
	$limit = 2; 								//how many items to show per page
	$page = $_GET['page'];
	if($page) 
		$start = ($page - 1) * $limit; 			//first item to display on this page
	else
		$start = 0;								//if no page var is given, set start to 0
	
	/* Get data. */
	$sql = "SELECT * FROM $tbl_name LIMIT $start, $limit";
	$result = mysql_query($sql);
	
	/* Setup page vars for display. */
	if ($page == 0) $page = 1;					//if no page var is given, default to 1.
	$prev = $page - 1;							//previous page is page - 1
	$next = $page + 1;							//next page is page + 1
	$lastpage = ceil($total_pages/$limit);		//lastpage is = total pages / items per page, rounded up.
	$lpm1 = $lastpage - 1;						//last page minus 1
	
	/* 
		Now we apply our rules and draw the pagination object. 
		We're actually saving the code to a variable in case we want to draw it more than once.
	*/
	$pagination = "";
	if($lastpage > 1)
	{	
		$pagination .= "<div class=\"pagination\">";
		//previous button
		if ($page > 1) 
			$pagination.= "<a href=\"$targetpage?page=$prev\">? previous</a>";
		else
			$pagination.= "<span class=\"disabled\">? previous</span>";	
		
		//pages	
		if ($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
		{	
			for ($counter = 1; $counter <= $lastpage; $counter++)
			{
				if ($counter == $page)
					$pagination.= "<span class=\"current\">$counter</span>";
				else
					$pagination.= "<a href=\"$targetpage?page=$counter\">$counter</a>";					
			}
		}
		elseif($lastpage > 5 + ($adjacents * 2))	//enough pages to hide some
		{
			//close to beginning; only hide later pages
			if($page < 1 + ($adjacents * 2))		
			{
				for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
				{
					if ($counter == $page)
						$pagination.= "<span class=\"current\">$counter</span>";
					else
						$pagination.= "<a href=\"$targetpage?page=$counter\">$counter</a>";					
				}
				$pagination.= "...";
				$pagination.= "<a href=\"$targetpage?page=$lpm1\">$lpm1</a>";
				$pagination.= "<a href=\"$targetpage?page=$lastpage\">$lastpage</a>";		
			}
			//in middle; hide some front and some back
			elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
			{
				$pagination.= "<a href=\"$targetpage?page=1\">1</a>";
				$pagination.= "<a href=\"$targetpage?page=2\">2</a>";
				$pagination.= "...";
				for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
				{
					if ($counter == $page)
						$pagination.= "<span class=\"current\">$counter</span>";
					else
						$pagination.= "<a href=\"$targetpage?page=$counter\">$counter</a>";					
				}
				$pagination.= "...";
				$pagination.= "<a href=\"$targetpage?page=$lpm1\">$lpm1</a>";
				$pagination.= "<a href=\"$targetpage?page=$lastpage\">$lastpage</a>";		
			}
			//close to end; only hide early pages
			else
			{
				$pagination.= "<a href=\"$targetpage?page=1\">1</a>";
				$pagination.= "<a href=\"$targetpage?page=2\">2</a>";
				$pagination.= "...";
				for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
				{
					if ($counter == $page)
						$pagination.= "<span class=\"current\">$counter</span>";
					else
						$pagination.= "<a href=\"$targetpage?page=$counter\">$counter</a>";					
				}
			}
		}
		
		//next button
		if ($page < $counter - 1) 
			$pagination.= "<a href=\"$targetpage?page=$next\">next ?</a>";
		else
			$pagination.= "<span class=\"disabled\">next ?</span>";
		$pagination.= "</div>\n";		
	}
	
	$table = new CTable;
	$table->setwidth(900);
	$table->setspacing(0);
	$table->setpadding(6);
	$table->setcolprops( 'width="20" class="tdtable"', 'width="430" bgcolor="ebebeb" class="tdtable"', 
						'width="100" bgcolor="white" class="tdtable"', 
						'width="100" bgcolor="ebebeb" class="tdtable"', 
						'width="25" class="tdtable"','width="100" class="tdtableright" bgcolor="ebebeb"');
	$table->pushth( "<center>#", "Contents","<center>Submitted by", "<center>Replies", "<center>Status", "<center>Options");

		while($row = mysql_fetch_array($result))
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
			if ( $row[level] == 2 && $row[solved] == 2) 
				$menu = "<a href=\"admin.php?action=urgent&id=$row[ticketid]\">Urgent</a><br>
							<a href=\"admin.php?action=solved&id=$row[ticketid]\">Solved</a>";
				
			if ( $row[level] == 1 &&  $row[solved] == 2) 
				$menu = "<a href=\"admin.php?action=noturgent&id=$row[ticketid]\">Not urgent</a><br>
							<a href=\"admin.php?action=solved&id=$row[ticketid]\">Solved</a>";
			if ( $row[level] == 2 && $row[solved] == 1) 
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
	?>

<?=$pagination?>
	