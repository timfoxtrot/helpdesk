<?php
/*****************************************************************************
*	File: 		index.php
*	Purpose: 	Dashboard page work in progress
*	Author:		Tim Dominguez (timfox@coufu.com)
******************************************************************************/
include 'functions.php';

//Page Handling
switch($_GET[page]){
    default:             main();           break;
}

//Dashboard page
function main(){
    //Top Banner
    ticket_top();

    /***************************************
    *   Contents
    ****************************************/
     
    $ticket_number   = '<input type = "text" name="id"  size="5">';
    $ticket_password = '<input type = "text" name="pass" size="5">';

    if($_COOKIE[userid]) $workloglink = '<br><a href="form.php?page=worklog">Submit Worklog</a>';

    $table = new Ctable;
    $table->setwidth(850);
    $table->setspacing(0);
	$table->setpadding(10);
    $table->pushth('Helpful Links', 'Access Ticket');
    $table->push('
        <br><a href="form.php">Submit New Ticket</font></a>
        '.$workloglink.'
        <br><a href="https://gmha.org:2096">GMHA Webmail</a>
        <br><br>'

    , ' <form action="viewticket.php" method="post">
            <table>
            <tr>
                <td>Num:</td>
                <td>'.$ticket_number.'</td>
            </tr>
            <tr>
                <td>Pass:</td>
                <td>'.$ticket_password.'</td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" value="Search"></td>
            </tr>
            </table>
        </form>'
    );
    $table->show();
    addcoolline(850);

    echo '<IFRAME WIDTH=850 HEIGHT=700 FRAMEBORDER=0 SRC="https://app.smartsheet.com/b/publish?EQBCT=f38fc80f14e7486f83b3359ed26576e4"></IFRAME>';


    //Footer
    ticket_bottom();
}



?>