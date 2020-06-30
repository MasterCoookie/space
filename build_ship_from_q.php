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

if ($uname === "lkjasdIU832jnv99SDIJF0asdk1AKJ0ojsdYDSJ01" && $pwd === '4nzxm285ngyuHDK93jG783kjmxc0plqa1zXc')
{
    include 'general.php';
    build_ship_from_design($con, $job_id);
    if (get_shipyard_q($con, $job_id)[0] != 0)
    {
        add_queued_job($con, 2, $job_id, calculate_ship_build_time($con, $job_id, get_shipyard_q_types($con, $job_id)[0]));
    }
    echo "Ship built on planet id: ".$job_id;
}
else
{
    echo "ACCESS DENIED";
    exit();
}

?>