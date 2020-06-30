<?php
    include 'dbconnect.php';
    include 'sanitize.php';
    include 'general.php';
    include 'market.php';
    include 'research.php';
    include 'dbconnect.php';
    include 'buildings.php';
    $con = mysqli_connect($servername, $dbuser, $dbpass, $dbname);

    if (mysqli_connect_errno())
    {
        echo "1 Connection failed"; // err no 1 = connection error
        exit();
    }

    echo "2";
    //add_building_q($con, 16, 4);
    //print_r(get_planets_owned_ids($con, 17));

?>