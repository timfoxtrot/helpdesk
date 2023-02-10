<?php

include 'functions.php';

//Page Handling
if(isset($_GET['page'])== false){
    echo "test";
} else{
switch (isset($_GET[page])){
	default:		main();		break;
	case 'submit';	submit();	break;
}}

?>