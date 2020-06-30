<?php

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

if ($uname === "AF94OKDmiodf3521301jxncaisd9xnadCKPL0" && $pwd === 'qplxNMXCIU2474jMSKj9iS0PLAMXBXYWIQ3285')
{
    include 'general.php';
    fleet_arrive($con, $job_id);
    echo "Fleet reached planet.";
}
else
{
    echo "ACCESS DENIED";
    exit();
}

?>