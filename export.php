<?php

$filename = 'helpdesktickets.csv';
$export_data = unserialize($_POST['export_data']);

// Create File
$file = fopen($filename,"w+");

$headers = ["TICKETID", "DATE_CREATED", "DATE_CREATED_FORMAT", "DATE_SOLVED", "DATE_SOLVED_FORMAT", "WHO_SOLVED", "NAME", "EMAIL", "PHONE_NUMBER", "DIVISION", "CATEGORY", "IP_ADDRESS", "MESSAGE", "STATUS"];
fputcsv($file,$headers);

foreach ($export_data as $line){
    fputcsv($file,$line);
}

fclose($file);

// Download
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=".$filename);
header("Content-Type: application/csv; "); 

readfile($filename);

// Deleting File
unlink($filename);

exit();

?>