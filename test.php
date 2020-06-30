<?php
    include 'dbconnect.php';
    include 'general.php';
    include 'market.php';
    include 'research.php';

    $con = mysqli_connect($servername, $dbuser, $dbpass, $dbname);

    if (mysqli_connect_errno())
    {
        echo "1 Connection failed"; // err no 1 = connection error
        exit();
    }

    //print_r(get_planets_owned_ids($con, 17));
    //calculate_resource_production($con, 16);
    //print_r(get_building_lvl($con, 16, 5));
    //print_r(perform_building_upgrade($con, 16, 5));

    // $offer = array(100, 0, 50, 5, 0);
    // $wanted = array(0, 70, 0, 0, 100);
    // mk_offer($con, "vcvcxvcxcvxcv", $offer, $wanted, "Test offer");

    //add_research_q($con, "thurster_lvl", 4, 17, 300);
    //increase_research_lvl($con, 7);
    //echo "2";
    //add_building_queue($con, 5, 16, 120);
    //perform_building_upgrade($con, 16)
    //$design = array(100, 2, 10000, 3, 150, 2, 44, 1, 2);
    //create_ship_design($con, 17, "imperial", 7, $design, get_designs_of_ship_count($con, 17, 7) + 1);

    //print_r(get_designs_of_ship_count($con, 17, 7));

    //create_fleet($con, 17, 0, 16);

    //print_r(get_shipyard_q($con, 16));

    //add_shipyard_q($con, 16, 2, 13);

    //print_r(get_shipyard_q_types($con, 16));

    //build_ship_from_design($con, 16);

    //print_r(get_ships_count_by_class($con, 1, "l_fighters"));

    //print_r(get_local_fleet_id($con, 16));

    //print_r(get_class_of_ship_from_id($con, 12));

    //build_ship_from_design($con, 16);

    //$load = array(100, 75, 50, 5);

    //send_fleet($con, 1, 4, 19, 20, $load);

    //print_r(check_scheduled_jobs($con, 10));

    //print_r(get_shipyard_q($con, 16));

    //print_r(calculate_ship_build_time($con, 16, get_shipyard_q_types($con, 16)[0]));

    print_r(get_planet_data($con, 20))

    //fleet_arrive($con, 1);

    //print_r(get_ship_design_data($con, get_fleet_data($con, 1), 9, 1));
?>