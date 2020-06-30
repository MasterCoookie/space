<?php
    include 'dbconnect.php';
    include 'sanitize.php';
    $con = mysqli_connect($servername, $dbuser, $dbpass, $dbname);

    if (mysqli_connect_errno())
    {
        echo "1 Connection failed"; // err no 1 = connection error
        exit();
    }

    include 'general.php';

    $usernameclean = sanitizeAlnum($con, $_POST["name"]);
    $password = $_POST["password"];

    // returns "0" on failed login attempt
    echo check_login($con, $usernameclean, $password, true);

?>