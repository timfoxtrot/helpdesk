<?php

include "functions.php";
ticket_top( "Mitel Study Guide", 700 );

$db = new MyDB;

$db->query ( 'SELECT * FROM mitel' );
$count = 1;
$pcount = 1;

while ($row = $db->getrow()) {

	$table = new Ctable;
	$table->setwidth( "700" );
	$table->setspacing(0);
	$table->setcolprops ( 'width="90" bgcolor="ebebeb"','width="610"', 'bgcolor="ebebeb"');
	$table->pushth ( "<b>Number</b>: ", $row[number], "" );
	$table->push ( "<b>Type</b>: ", $row[type], "" );
	$table->push ( "<b>Question</b>: ", $row[question], "" );
	$table->push ( "<b>Answers</b>: "," 1. $row[choice1]", "" );
	$table->push ( "", "2. $row[choice2]", "" );
	if ($row[choice3])
		$table->push ( "", "3. $row[choice3]", "" );
	if ($row[choice4])
	$table->push ( "", "4. $row[choice4]", "" );
	if ($row[choice5])
		$table->push ( "", "5. $row[choice5]", "" );
	if ($row[choice6])
		$table->push ( "", "6. $row[choice6]", "" );
	if ($row[choice7])
		$table->push ( "", "7. $row[choice7]", "" );
	$table->push ( "<b>Solution</b>: ", "<button id=\"b$count\">Show</button><p id=\"p$count\" style=\"display:none;\">$row[solution]</p>", "" );
	$table->show();
	addcoolline('700');
	echo '<br><br>';
	
	echo '
		<script>
			$("#b'.$count.'").click(function () {
			$("#p'.$count.'").show("fast");
		});
		</script>
		
	
	';
	
	$count++;
	$pcount++;
}
?>