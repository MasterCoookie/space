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

if ($uname === "jhq3485jjrng980masligUSF83rmnzmdsa" && $pwd === 'USdbzxfasdfn83ldg380fnh327yfoenc73o0cnJ')
{
    $id = sanitizeNum($_POST['id']);
    echo $id;

    include 'general.php';
    del_queued_job($con, $id);
}
else
{
    echo "ACCESS DENIED!";
    exit();
}
?>