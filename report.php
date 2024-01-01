<?php

//Tim Dominguez 12/19/2023

//dependencies
include 'functions.php';

ticket_top('Generate Reports', '600');

//Date Form

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
echo '<form action="report.php" method="post">';
$table->show();
addcoolline(600);
echo "<input type=\"submit\" value=\"Submit Dates\" /> ";
echo "<input type=\"reset\" value=\"Reset\">";
echo "</form>";
echo '<br><br>';

//display report
if($_POST){
    
    $startdate = strtotime($_POST[startdate]);
	$enddate   = strtotime($_POST[enddate]);

    ?>
    <div class="container">
        <form method='post' action='export.php'>
            <input type='submit' value='export' name='export'>
    
            <table border='1' style='border-collapse:collapse;'>
                <tr>
                    <th>TICKET_ID</th>
                    <th>DATE_CREATED</th>
                    <th>DATE_CREATED_FORMAT</th>
                    <th>DATE_SOLVED</th>     
                    <th>DATE_SOLVED_FORMAT</th>     
                    <th>WHO_SOLVED</th>
                    <th>NAME</th>
                    <th>EMAIL</th>
                    <th>PHONE_NUMBER</th>
                    <th>DIVISION</th>
                    <th>CATEGORY</th>
                    <th>IP_ADDRESS</th>
                    <th>MESSAGE</th>
                    <th>STATUS</th>

                </tr>
        <?php

        //database
        ticketmysqlconnect();
        $result = mysql_query( "SELECT * FROM tickets WHERE datecreated between $startdate AND $enddate AND active = 1");
        $user_arr = array();

        while($row = mysql_fetch_array ($result, MYSQL_ASSOC)){

            if($row[datesolved]){
                $datesolved       = $row[datesolved];
                $datesolvedformat = date('m/d/y, g:ia', $row[datesolved]);
            } else{
                $datesolved       = NULL;
                $datesolvedformat = NULL;
            }

            //remove line break
            $message     = str_replace(array("\r", "\n"), '', $row[message]);
            //remove comma
            $message     = str_replace(',', '', $message);

            $ticketid    = $row[ticketid];
            $datecreated = $row[datecreated];
            $datecreatedformat = date('m/d/y, g:ia', $row[datecreated]);
            //$datesolved  = date('m/d/y, g:ia', $row[datesolved]);
            $whosolved   = getusername($row[whosolved]);
            $name        = $row[name];
            $email       = $row[email];
            $phonenumber = $row[phonenumber];
            $division    = getlocationname($row[location]);
            $category    = getcategoryname($row[category]);
            $ip_address  = $row[ip];
            $status      = getstatus($row[solved]);
            $user_arr[] = array($ticketid,$datecreated, $datesolved, $whosolved, $name, $email, $phonenumber, $division, $category, $ip_address, $message, $status);
            
            ?>
                <tr>
                    <td><?php echo $ticketid; ?></td>
                    <td><?php echo $datecreated; ?></td>
                    <td><?php echo $datecreatedformat; ?></td>
                    <td><?php echo $datesolved; ?></td> 
                    <td><?php echo $datesolvedformat; ?></td> 
                    <td><?php echo $whosolved; ?></td>                   
                    <td><?php echo $name; ?></td>
                    <td><?php echo $email; ?></td>
                    <td><?php echo $phonenumber; ?></td>
                    <td><?php echo $division; ?></td>
                    <td><?php echo $category; ?></td>
                    <td><?php echo $ip_address; ?></td>
                    <td><?php echo $message; ?></td>
                    <td><?php echo $status; ?></td>
                </tr>
            <?php
        }
        echo "</table>";

        $serialize_user_arr = serialize($user_arr);

        ?>
            <textarea name='export_data' style='display: none;'><?php echo $serialize_user_arr; ?></textarea>
        
        </form>

    </div><?php
}
?>