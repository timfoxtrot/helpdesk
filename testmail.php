<?php

/*$to = "timfox@coufu.com";
$subject = "My subject";
$txt = "Hello world!";
$headers = "From: no-reply@guamairport.net" . "\r\n" .
"CC: somebodyelse@example.com";

$test_email = mail($to,$subject,$txt,$headers);


if($test_email) echo "success";
if(!$test_email) echo "failed";*/

include 'drtlib.php';
include 'functions.php';

$test_email = email_admin(1, "lol", "lol@lol.com", "test", "1", "somehwere", "lol", "haha", "ok");

print_r($test_email);

if($test_email) echo "success";
if(!$test_email) echo "failed";

?>