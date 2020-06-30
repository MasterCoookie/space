<?php


include 'dbconnect.php';

$con = mysqli_connect($servername, $dbuser, $dbpass, $dbname);

if (mysqli_connect_errno())
{
    echo "1 Connection failed"; // err no 1 = connection error
    exit();
}

include 'sanitize.php';

$key = sanitizeAlnum($con, $_POST['key']);
$uname = sanitizeAlnum($con, $_POST['uname']);
$pwd = sanitizeAlnum($con, $_POST['pwd']);
$data = sanitizeAlnum($con, $_POST['data']);
$type = sanitizeNum($_POST['type']);

include 'general.php';

//echo check_login($con, $uname, $pwd);

// TODO - only allow checks for currently logged in player
if (($key === "planets_apiOIndfuih093hdasjkhasdlyUIYfasdfwpx1984932ndmk98cnlape") && (check_login($con, $uname, $pwd, false) === "0"))
{
    // error code 0 - no errors (stupid)
    echo "0";
    //You can either retrive the id of planets owned by player by passing "0" or the entire planet data "1"
    if ($type === "0")
    {
        calculate_resource_production($con, $data);
        foreach (get_planets_owned_ids($con, $data) as $id)
        {
            echo "\t$id";
        }
    }
    elseif ($type === "1")
    {
        foreach (get_planet_data($con, $data) as $planet_data)
        {
            echo "\t$planet_data";
        }
    }
    
}
else
{
    echo "You picked the wrong house fool!";
    exit();
}
?>