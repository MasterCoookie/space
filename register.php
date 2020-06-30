<?php
    include 'dbconnect.php';
    include 'sanitize.php';
    include 'general.php';
    $con = mysqli_connect($servername, $dbuser, $dbpass, $dbname);

    if (mysqli_connect_errno())
    {
        echo "1 Connection failed"; // err no 1 = connection error
        exit();
    }

    $usernameclean = sanitizeAlnum($con, $_POST["name"]);
    $password = $_POST["password"];


    // check if name already exists
    $namecheckquery = "SELECT username FROM players WHERE username='".$usernameclean."';";

    $namecheck = mysqli_query($con, $namecheckquery) or die("2 query err"); // err code 2 = name check query failed

    if (mysqli_num_rows($namecheck) > 0)
    {
        echo "3 Name Already Exists"; // name already exists
        exit();
    }

    // add user to table
    $salt = "\$5\$round=5000\$"."rocknroll".$usernameclean."\$";
    $hash = crypt($password, $salt);

    $planets_id = create_planet($con, true);
    
    $insertuserquery = "INSERT INTO players (username, hash, salt, last_seen, planets_owned, credits) VALUES ('".$usernameclean."', '".$hash."', '".$salt."', now(), '".$planets_id."', 100);";
    mysqli_query($con, $insertuserquery) or die("4"); // inserion err
    $player_id = mysqli_insert_id($con);

    $set_planet_owner_query = "UPDATE planets SET owner=".$player_id." WHERE id=".$planets_id.";";
    if (mysqli_query($con, $set_planet_owner_query)) {
        
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }

    calculate_resource_production($con, $player_id);

    //echo "0";

?>