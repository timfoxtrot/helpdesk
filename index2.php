<?php
/*****************************************************************************
*	File: 		index2.php
*	Purpose: 	Dashboard page work in progress
*	Author:		Tim Dominguez (timfox@coufu.com)
******************************************************************************/
include 'functions.php';

//Page Handling
switch($_GET[page]){
    case "ticketform";   ticketform();     break;
    default:             main();           break;
}

//Dashboard page
function main(){
    //Top Banner
    ticket_top();

    /***************************************
    *   Contents
    ****************************************/
     
    $ticket_number   = '<input type = "text" name="ticketnum"  size="5">';
    $ticket_password = '<input type = "text" name="ticketpass" size="5">';

    $table = new Ctable;
    $table->setwidth(600);
    $table->setspacing(0);
	$table->setpadding(10);
    $table->pushth('Helpful Links', 'Access Ticket');
    $table->push('
        <br><a href="index2.php?page=ticketform">Submit New Ticket</a>
        <br><a href="https://gmha.org:2096">GMHA Webmail</a>
        <br><br>'

    , '<table>
        <tr>
            <td>Num:</td>
            <td>'.$ticket_number.'</td>
        </tr>
        <tr>
            <td>Pass:</td>
            <td>'.$ticket_password.'</td>
        </tr>
       </table>');
    $table->show();
    addcoolline(600);


    //Footer
    ticket_bottom();
}



?>