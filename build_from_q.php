<?php

//ubuntu pull test
include 'dbconnect.php';

$con = mysqli_connect($servername, $dbuser, $dbpass, $dbname);

if (mysqli_connect_errno())
{
    echo "1 Connection failed"; // err no 1 = connection error
    exit();
}

include 'sanitize.php';


$uname = sanitizeAlnum($con, $_POST['uname']);
$pwd = sanitizeAlnum($con, $_POST['pwd']);
$job_id = sanitizeNum($_POST['job_id']);

if ($uname === "nhi9fdujh4389fdjg89jkjkcGYFjn39hGHASDxzc" && $pwd === '7db27gREBG83764gBHUEGjhadjbzxmbcnvqp1')
{
    include 'general.php';
    perform_building_upgrade($con, $job_id);
    echo "Performed on planet id: ".$job_id;
}
else
{
    echo "ACCESS DENIED";
    exit();
}
