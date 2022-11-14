<?php
if(!isset($_SESSION)) session_start();
if(isset($_SESSION['username']))
{
    unset($_SESSION['username']);
}
session_destroy();
//echo '<br><strong>You have been logged out</strong><br>';
//sleep(1);
header('Location: ./login.php');

?>