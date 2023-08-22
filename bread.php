<?php

include 'functions.php';

members_only("admin");

$db = new MyDb;
$db->query('SELECT * FROM log ORDER by date DESC');

ticket_top();

$table = new CTable;
$table->setwidth(600);
$table->setspacing(0);
$table->setpadding(6);
$table->pushth( ''.$header.'');
$table->show();
echo '<br>';

$table = new CTable;
$table->setwidth(320);
$table->setspacing(0);
$table->setpadding(6);
$table->setcolprops('width="20" class="tdtable"', 'width="100" bgcolor="ebebeb" class="tdtable"', 
					 'width="100" bgcolor="white" class="tdtable"');
$table->pushth( "<center>#", "<center>User","<center>Date");

while($row = $db->getrow()){
    $username   = getusername($row[userid]);
    $dateformat = date( 'm/d/y, g:ia', $row[date]);

    $table->push("<center>$row[id]", "<center>$username","<center>$dateformat");
}
$table->show();



?>