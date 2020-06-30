<?php
    include 'dbconnect.php';
    $con = mysqli_connect($servername, $dbuser, $dbpass, $dbname);

    if (mysqli_connect_errno())
    {
        echo "1 Connection failed"; // err no 1 = connection error
        exit();
    }

    $username = mysqli_real_escape_string($con, $_POST["name"]);
    $usernameclean = filter_var($username, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
    $newscore = $_POST["score"];

    // double check num of users
    $namecheckquery = "SELECT username FROM players WHERE username='".$usernameclean."';";

    $namecheck = mysqli_query($con, $namecheckquery) or die("2 Namecheck failed");
    if (mysqli_num_rows($namecheck) != 1)
    {
        echo "5 No user or more than one";
        exit();
    }

    $updatequery = "UPDATE players SET score=".$newscore." WHERE username='".$usernameclean."';";
    mysqli_query($con, $updatequery) or die("7 Update query Fialed");

    echo "0";

?>