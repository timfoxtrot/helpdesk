<?php
/*****************************************************************************
*	File: 		settings.php
*	Purpose: 	Contains pages that deal with user management.
*	Author:		Tim Dominguez (timfox@coufu.com)
******************************************************************************/
include "functions.php";

switch ( $_GET[action] ) {
	case "changepw"; 		members_only();		changepw();			break;
	case "submit";			members_only();		usersubmit();		break;
	case "addcat";      	members_only();     addcat();			break;
	case "addlocation"; 	members_only();		addlocation();		break;
	case "deleteloc";		members_only();		deleteloc();		break;
	case "deletecat";   	members_only();		deletecat();        break;
	case "assign";			members_only();		assign();			break;
	case "reassigncat";		members_only();		reassigncat();		break;
	default:				members_only();		userdefault();		break;
	
}
//-------------------------------------------------------------------------
//	URL:		settings.php
//	Purpose:	User control
//-------------------------------------------------------------------------
function userdefault() {
	ticket_top();
	
	//Changing Password
	$table = new CTable; 
	$table->setwidth(600);
	$table->pushth( "Change Password" );
	$table->push ( "New Password: ", inputpw( "password" ) );
	$table->push ( "Retype Password: ", inputpw( "password2" ) );

    echo "<form action=\"settings.php?action=changepw\" method=\"post\">";
	$table->show();
	addcoolline(600);
	echo "<input type=\"submit\" value=\"Change Password\" /> ";
	echo "<input type=\"reset\" value=\"Reset\">";
	echo "</form>";
	echo "<br><br>";
	
	//Add/Delete Category
	$category = '<select name="category">';
	$db = new MyDB;
	$db->query( 'SELECT * FROM categories WHERE active = 1 ORDER by name ASC' ); 
	while($row = $db->getrow()){
		$category .= '<option value ="'.$row[categoryid].'">'.$row[name].'</option>';
	}
	$category .= '</select>';
	$delete = '<input type="submit" value="Delete"> ';
	$add    = '<input type="submit" value="Add">';
	
	$table = new CTable; 
	$table->setwidth(600);
	$table->pushth( 'Add/Delete Categories','' );
	$table->push ( 'Existing Categories: '.$category.'', ''.$delete.'' );
    echo "<form action=\"settings.php?action=deletecat\" method=\"post\">";
	$table->show();
	echo "</form>";
	
	$table = new CTable; 
	$table->setwidth(600);
	$table->push ( 'New Category: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.inputtext('newcategory','',28).''.$add.'' );
    echo "<form action=\"settings.php?action=addcat\" method=\"post\">";
	$table->show();
	echo "</form>";
	addcoolline(600);
	echo '<br><br>';
	
	//addcoolline(600);

	//Add/Delete Location
	$location = '<select name="location">';
	$db = new MyDB;
	$db->query( 'SELECT * FROM locations WHERE active = 1 ORDER by name ASC' ); 
	while($row = $db->getrow()){
		$location .= '<option value ="'.$row[locationid].'">'.$row[name].'</option>';
	}
	$location .= '</select>';
	$delete = '<input type="submit" value="Delete"> ';
	$add    = '<input type="submit" value="Add">';
	
	$table = new CTable; 
	$table->setwidth(600);
	$table->pushth( 'Add/Delete Location','' );
	$table->push ( 'Existing Locations: '.$location.'', ''.$delete.'' );
    echo "<form action=\"settings.php?action=deleteloc\" method=\"post\">";
	$table->show();
	echo "</form>";
	
	$table = new CTable; 
	$table->setwidth(600);
	$table->push ( 'New Location: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.inputtext('newlocation','',28).''.$add.'' );
    echo "<form action=\"settings.php?action=addlocation\" method=\"post\">";
	$table->show();
	echo "</form>";
	addcoolline(600);
	echo '<br><br>';
	
	//addcoolline(600);
	
	
	
	if ( getusergroupid( $_COOKIE[userid] ) == 1 ) {
		$groups = "<select name=\"groupid\"><option value=\"1\">Admin</option><option value=\"2\">Helpdesk</option></select>";
		$table = new CTable;
		$table->setwidth(600);
		$table->pushth ( "Add User" );
		$table->push ( "Username: ", inputtext( "username" ) );
		$table->push ( "Full Name: ",inputtext( "fullname" ) );
		$table->push ( "Email: ", inputtext( "email" ) );
		$table->push ( "Group: ", $groups  );

        echo "<form action=\"settings.php?action=submit\" method=\"post\">";
		$table->show();
		addcoolline(600);
		echo "<input type=\"submit\" value=\"Add User\" /> ";
		echo "<input type=\"reset\" value=\"Reset\">";
		echo "</form>";
	}
	
	echo "<br><br>";
	if ( getusergroupid( $_COOKIE[userid] ) == 1 ) {
		ticketmysqlconnect();
		$result1 = mysql_query ( "SELECT * FROM users WHERE active ='1'");
		
		$table = new CTable;
		$table->setwidth(600);
		$table->setspacing(0);
		$table->setpadding(6);			
		$table->setcolprops( 'width="100" class="tdtable"', 'width="355" class="tdtable"', 'class="tdtableright"');
		$table->pushth( "Existing Users" );
		while ( $row1 = mysql_fetch_array( $result1, MYSQL_ASSOC ) )
			//if ( $row1[email] )		$email = "$row1[email]";
			$table->push ( "$row1[username]", "$row1[email]<br>", "<center><font size=\"1\"><a href=\"admin.php?action=resetpw&id=$row1[id]\">Reset PW</a><br><a href=\"admin.php?action=deleteuser&id=$row1[id]\">Delete</a>");
		$table->show();
	}
	echo "<br><br>";

	ticket_bottom();
}

//-------------------------------------------------------------------------
//	URL:		settings.php?action=submit
//	Purpose:	Submits data and adds user to database
//-------------------------------------------------------------------------
function usersubmit() {
	//Error Check
	$errors = array();
	if (!$_POST[username]) 						array_push ( $errors, "No username");
	if (!$_POST[fullname]) 						array_push ( $errors, "No fullname");
	if (!$_POST[email])							array_push ( $errors, "No Email" );
	if (!$_POST[groupid]) 						array_push ( $errors, "Please specify a group" );
	
	//If there are errors this prints out a list of them and kills the script
	if ( $errors )
	{
		ticket_top( "Error");
		echo "Error Adding User";
		foreach ( $errors as $err )
		echo "<li>$err \n";
		exit;
	}
	//If there are no errors this adds the user to the database
	$insert[username] = "$_POST[username]"; 
	$insert[fullname] = "$_POST[fullname]";
	$insert[password] = md5( "giaa2023" );
	$insert[email]	  = "$_POST[email]";
	$insert[groupid]  = "$_POST[groupid]";	
	$insert[active]   = "1";

	$db = new MyDB;
	$db->insertarray( "users" , $insert );

	ticket_top ();
	echo "Added";
}
//-------------------------------------------------------------------------
//	URL:		settings.php?action=changepw
//	Purpose:	Change current password
//-------------------------------------------------------------------------
function changepw() {
	if ($_POST[password] != $_POST[password2])
	{
		redirect( "settings.php", 2, "Error", "Passwords don't match" );
		exit;
	}	
	else
	{
		$newpass = md5 ( $_POST[password] );
		$db = new MyDB;
		$result = mysql_query ( "UPDATE users SET password = '$newpass' WHERE id = '$_COOKIE[userid]' LIMIT 1");
		
		redirect ( "settings.php", 0, "Success", "You have successfully changed your password" );
	}

}
//-------------------------------------------------------------------------
//	URL:		settings.php?action=addcat
//	Purpose:	Adding a Category
//-------------------------------------------------------------------------
function addcat() {
	if (!$_POST[newcategory]){
		redirect ('settings.php', 2, 'Error', 'No Fields');
		exit;
	}
	else{
		$insert[name]   = ''.$_POST[newcategory].'';
		$insert[active] = 1; 
		$db = new MyDB;
		$db->insertarray( "categories" , $insert );
		redirect ('settings.php', 0);
	}	
}

//-------------------------------------------------------------------------
//	URL:		settings.php?action=addlocation
//	Purpose:	Adding a Location
//-------------------------------------------------------------------------
function addlocation() {
	if (!$_POST[newlocation]){
		redirect ('settings.php', 2, 'Error', 'No Fields');
		exit;
	}
	else{
		$insert[name]   = ''.$_POST[newlocation].'';
        $insert[active] = 1;
		$db = new MyDB;
		$db->insertarray( "locations" , $insert );
		redirect ('settings.php', 0);
	}	
}

//-------------------------------------------------------------------------
//	URL:		settings.php?action=deletecat
//	Purpose:	Deleting a Category
//-------------------------------------------------------------------------
function deleteloc(){
	$db= new MyDB;
	$result = mysql_query( 'UPDATE locations SET active = 2 WHERE locationid ='.$_POST[location].' LIMIT 1');
	redirect( 'settings.php', 0 );
}

//-------------------------------------------------------------------------
//	URL:		settings.php?action=deletecat
//	Purpose:	Deleting a Category
//-------------------------------------------------------------------------
function deletecat(){
	$db= new MyDB;
	$result = mysql_query( 'UPDATE categories SET active = 2 WHERE categoryid ='.$_POST[category].' LIMIT 1');
	redirect( 'settings.php', 0 );
}

//-------------------------------------------------------------------------
//	URL:		settings.php?action=assign
//	Purpose:	Assigning User to Ticket
//-------------------------------------------------------------------------
function assign(){
	$db = new myDB;
	$result = mysql_query( 'UPDATE tickets SET assignedto = '.$_POST[users].' WHERE ticketid = '.$_POST[id].' LIMIT 1');
	redirect ('viewticket.php?id='.$_POST[id].'',0);
}	

//-------------------------------------------------------------------------
//	URL:		settings.php?action=assigncategory
//	Purpose:	reassigning category
//-------------------------------------------------------------------------
function reassigncat(){
	$db = new myDB;
	$result = mysql_query('UPDATE tickets SET category='.$_POST[categories].' WHERE ticketid='.$_POST[id].' LIMIT 1');
	redirect('viewticket.php?id='.$_POST[id].'',0);
}
?>