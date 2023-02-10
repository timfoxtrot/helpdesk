<?php

include "functions.php";


//$db = new MyDB;

//$result = mysql_query( "UPDATE users SET email = '' WHERE id ='16'" );


//print_r(getusername(1));

print_r(md5("h3lpdeskTickets"));

//print_r(email_user(1,"timfox@coufu.com", $server_url));

//$time = time();
//print_r($time);

//$ip = $_SERVER['REMOTE_ADDR'];;
//print_r($ip);



setcookie("userid", "1");
//print_r($_COOKIE[userid]);

//$time = time();

//echo date( 'Y-m-d H:i:s', $time );
//echo "<br><br>";


//$subject="Test mail";
//$to="tim.dominguez@itehq.net";
//$body="This is a test mail";
//mail($to,$subject,$body);


//$ip    = $REMOTE_ADDR;
//$ipadd = GetHostByName($ip);
//echo $ipadd;

//echo $_SERVER['REMOTE_ADDR'];  
//echo phpinfo();

//echo '<a href="tel:6717971337">6717971337</a>';

//echo getcategoryname(5);

//$db     = new MyDB;
//$result = mysql_query ('SELECT * FROM tickets WHERE whosolved = 1');
//$solved = mysql_num_rows ($result);
//echo $solved;

/*$db = new MyDB;
$db->query( 'SELECT * FROM scoreboard ORDER by score DESC');
while( $row = $db->getrow() ){
	echo ''.getusername($row[userid]).':'.$row[score].'<br>';
}*/



/*$date = strtotime('03/19/2013');

echo date( 'm/d/y, g:ia', $date);

echo validate_email ("tim.dominguez@yahoo.com");

echo $_SERVER[REMOTE_ADDR];

email_admin('934793797', 'TESTING TIM', 'tim.dominguez@itehq.net', 'DONT BE ALARMED ITS JUST A TEST', '9224869', 2, 2, '1.2.3.4');

*/

?>