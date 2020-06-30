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

if ($uname === "HasUABSDBFUI324N32857JASDFasdf" && $pwd === 'JKHASGDbnsdf74oxmniwqe84ho98sfcfbnjkalsd')
{
    include 'general.php';

    $job = check_scheduled_jobs($con, 9);
    if ($job === 0)
    {
        echo "NO JOBS";
        exit();
    }

    $timestamp = strtotime($job[3]) - strtotime("now");
    // id, type, job_id, time
    echo $job[0]."\t";
    echo $job[1]."\t";
    echo $job[2]."\t";

    echo $timestamp;

}
else
{
    echo "ACCESS DENIED!";
    exit();
}

?>