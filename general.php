<?php
// general functions

function check_login($con, $usernameclean, $password, $get_id)
{
        // check if name already exists
        $namecheckquery = "SELECT username, salt, hash FROM players WHERE username='".$usernameclean."';";

        $namecheck = mysqli_query($con, $namecheckquery) or die("2 query err"); // err code 2 = name check query failed
        if (mysqli_num_rows($namecheck) != 1)
        {
            return "5 No user or more than one";
            exit();
        }
    
        //get login info from db
        $userinfo = mysqli_fetch_assoc($namecheck);
        $salt = $userinfo["salt"];
        $hash = $userinfo["hash"];
    
    
        $loginhash = crypt($password, $salt);
        if ($hash != $loginhash)
        {
            return "6 Incorrect password";
            exit();
        }
        if ($get_id)
        {
            $player_query = "SELECT id FROM players WHERE username='".$usernameclean."';";
            $res = mysqli_query($con, $player_query);

            if (mysqli_num_rows($res) > 0)
            {
                while($row = mysqli_fetch_assoc($res))
                {
                    return "0\t".$row["id"];
                }
            }
            else
            {
                echo $usernameclean;
            }
        }
        return "0";
}

function create_planet($con, $firsplanet)
{
    /*creates planet instance in db
        id: int - id of planet
        owner: int - owners it
        planet_type: int - graphical set id
        terro_state: int: - lvl of terraformation
        fields_data: string - representation of lvls of buildings built on planet
        localization: string: - system:position_from_sun (ex. 110:2)
        fleets_ids: string - TODO
        defence_id: int - id of in defences table
        energy: int - current energy produced on given planet
        building_materials, special_materials, reg_fuel, hyper_fuel: ints - all represent amounts of resources
        building_q: string - queue of bouilding upgrade. Only one building in queue. Represents index in array of fields_data.
        shipyard_q_count: string - array of  ints -amounts of ships to be produced. Only represents quantity. Next col represents which ship will be actually produced
        shipyard_q_types: string - array of ints - types of ships. Prev col represents amount, this one - which ship will actually be produced. Each int is an id of design from ship_desings table
    */

    // generate random unique planet localization
    do {
        $random_localization = mt_rand(1, 255).":".mt_rand(1, 4);
        $localization_check = "SELECT FORM planets WHERE localization='".$random_localization."';";
    } while (mysqli_num_rows($localization_check) > 0);

    if ($firsplanet)
    {
        $createplanetquery = "INSERT INTO planets (planet_type, fields_data, localization, fleets_ids,
                                                building_materials, special_materials, reg_fuel) VALUES
                                                (0, 'S0, L0, 0, e0, b0, s0, F0, f0, 0, 0, 0, 0',
                                                '".$random_localization."', '0, 0, 0, 0', 250, 100, 75);";    

        mysqli_query($con, $createplanetquery) or die("8"); // planet err
        $last_id = mysqli_insert_id($con);
        return $last_id;
    } //TODO - create planet while colonizing
}

function get_planets_owned_ids($con, $player_id)
{
    // returns ids of all planets owned by given player
    $planets_query = "SELECT planets_owned FROM players WHERE id='".$player_id."'";
    $res = mysqli_query($con, $planets_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_assoc($res))
        {
            $planets_ids = explode(", ", $row["planets_owned"]);
        }
    }
    else
    {
        echo "Internal Server Error!";
    }
    return $planets_ids;
}

function get_owner_id($con, $planet_id)
{
    // given planet id, returns its owner
    $owner_query = "SELECT owner FROM planets WHERE id=".$planet_id.";";
    $res = mysqli_query($con, $owner_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_assoc($res))
        {
            return $row["owner"];
        }
    }
    else
    {
        echo "Internal Server Error!";
    }
}

function get_per_hour_income($con, $planet_id, $res_type)
{
    // given planets id and resource type returns its hourly income
    // res types: 4 - building materials, 5 - special materials, 6 - fuel, 7 - hyperfuel

    $prod_query = "SELECT fields_data, planet_type FROM planets WHERE id='".$planet_id."'";
    $res = mysqli_query($con, $prod_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_assoc($res))
        {
            $planet_type = $row["planet_type"];
            $fields = explode(", ", $row["fields_data"]);
            $prod = $fields[$res_type];
            $prod_lvl = substr($prod, 1);
        }
    }
    else
    {
        echo "Internal Server Error!";
    }

    // TODO - resource calculation
    $prod_per_hour = (((1 / $res_type) * 50) * $prod_lvl) + 25;

    return $prod_per_hour;
}

function get_building_lvl($con, $planet_id, $building_type, $ret_all)
{
    // either reurns string of buildings or lvl of one given
    // if ret_all is set to true fun will return all buildings as a string
    $building_lvls_query = "SELECT fields_data FROM planets WHERE id='".$planet_id."'";
    $res = mysqli_query($con, $building_lvls_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_assoc($res))
        {
            if ($ret_all)
            {
                return $row["fields_data"];
            }
            $fields = explode(", ", $row["fields_data"]);
            $prod = $fields[$building_type];
            return substr($prod, 1);
        }
    }
    else
    {
        echo "Internal Server Error!";
    }
}

function get_building_being_built($con, $planet_id)
{
    // given id returns curr building queue
    $building_query = "SELECT building_q FROM planets WHERE id=".$planet_id.";";
    $res = mysqli_query($con, $building_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_assoc($res))
        {
            return $row['building_q'];
        }
    }
    else
    {
        echo "Internal Server Error!";
    }
}

function perform_building_upgrade($con, $planet_id)
{
    // given id upgrades building currently in queue (building_q col)
    $building_type = (int)get_building_being_built($con, $planet_id);
    $curr_buildings = get_building_lvl($con, $planet_id, $building_type, true);
    $buildings_split = explode(", ", $curr_buildings);
    $building_upgraded = $buildings_split[$building_type];
    $building_upgraded_lvl = substr($building_upgraded, 1);
    $building_name = mb_substr($building_upgraded, 0, 1);

    $temp[] = $building_name;
    $temp[] = ++$building_upgraded_lvl;

    $after_upgrade_building = join("", $temp);

    $buildings_split[$building_type] = $after_upgrade_building;

    $upgraded_buildings = join(", ", $buildings_split);


    $upgrade_building_query = "UPDATE planets SET fields_data='".$upgraded_buildings."', building_q=null WHERE id='".$planet_id."'";
    if (mysqli_query($con, $upgrade_building_query)) {
        //del_queued_job($con, $planet_id);
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

function add_building_queue($con, $building_type, $planet_id, $time_to_build)
{
    // adds building to building queue
    $building_q_query = "UPDATE planets SET building_q='".$building_type."' WHERE id=".$planet_id.";";
    if (mysqli_query($con, $building_q_query)) {
        add_queued_job($con, 0, $planet_id, $time_to_build);
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

function get_planet_data($con, $planet_id)
{
    // given id returns all planet datata
    $planet_query = "SELECT * FROM planets WHERE id=".$planet_id.";";
    $res = mysqli_query($con, $planet_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_row($res))
        {
            return $row;
        }
    }
    else
    {
        echo "Internal Server Error!";
    }
}

function calculate_resource_production($con, $player_id)
{
    // given player id calculates and updates his resources based on his last update

    //time calculation, may move to separate func later on
    $last_seen_query = "SELECT last_seen FROM players WHERE id='".$player_id."'";
    $res = mysqli_query($con, $last_seen_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_assoc($res))
        {
            $last_seen = $row["last_seen"];
        }
    }
    else
    {
        echo "Internal Server Error!";
    }

    $planets_owned = get_planets_owned_ids($con, $player_id);
    $time_difference = time() - strtotime($last_seen);


    foreach($planets_owned as $planet_id)
    {
        $resources_query = "SELECT building_materials, special_materials, reg_fuel, hyper_fuel FROM planets WHERE id='".$planet_id."'";
        $res = mysqli_query($con, $resources_query);

        if (mysqli_num_rows($res) > 0)
        {
            
            while($row = mysqli_fetch_row($res))
            {
                // res_type is also representrs cur resources wen u substract 4
                for($res_type = 4; $res_type < 7; $res_type++)
                {
                    $cur_resources = $row[$res_type - 4];
                    $per_sec_prod = get_per_hour_income($con, $planet_id, $res_type) / 3600;
                    $income = $time_difference * $per_sec_prod;
                    $res_after_production[] = $cur_resources + $income;
                }
            }

            // insert produced resources into db
            $update_res_query = "UPDATE planets SET building_materials='".$res_after_production[0]."', special_materials='".$res_after_production[1]."', reg_fuel='".$res_after_production[2]."' WHERE id='".$planet_id."'";
            if (mysqli_query($con, $update_res_query)) {
                
            } else {
                echo "Error updating record: " . mysqli_error($con);
            }
        }
        else
        {
            echo "Internal Server Error!";
        }

    }

    // after upadting all planets update the user last seen variable
    $update_player_query = "UPDATE players SET last_seen=now() WHERE id='".$player_id."'";
    if (mysqli_query($con, $update_player_query)) {
        
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

function add_queued_job($con, $type, $job_id, $time_to_exec)
{
    /* very important - plan new job (kinda one-time cron job).

        type - what kind of job will be performed
        0 - building queue, 1 - fleet, 2 - shipyard, 3 - lab

        id - represents different ids. Depends on job type.
        building - id of planet, fleet - id of fleet, shipyard - id of planet, lab - q_research record id

        exec_time - time in seconds SINCE NOW in wchich the job will be performed
    */
    $queued_query = "INSERT INTO rasberry_q VALUES (null, ".$type.", ".$job_id.", (now() + INTERVAL ".$time_to_exec." SECOND));";

    if (!mysqli_query($con, $queued_query)) {
        echo mysqli_error($con);
    }
}

function del_queued_job($con, $id)
{
    // removes job by id. ALAWAYS REMOVE JOB AFTER PEFORMING IT
    $delete_queued_query = "DELETE FROM rasberry_q WHERE id=".$id.";";

    if (!mysqli_query($con, $delete_queued_query)) {
        echo mysqli_error($con);
    }
}

function check_scheduled_jobs($con, $time_to_run)
{
    /* returns all jobs scheduled to be done time_to_run seconds since now or less
    if none reutrns 0*/
    $q_query = "SELECT id, type, queued_jobs_id, exec_time FROM rasberry_q WHERE (exec_time < (now() + INTERVAL ".$time_to_run." SECOND))";
    $res = mysqli_query($con, $q_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_row($res))
        {
            return $row;
        }
    }
    else
    {
        return 0;
    }
}

function create_ship_design($con, $player_id, $design_name, $type, $design_data, $count)
{
    //create new ship desingn
    $design_query = "INSERT INTO ship_designs VALUES (null, ".$player_id.", ".$count.", '".$design_name."', '".$type."', ".$design_data[0].", ".$design_data[1].", ".$design_data[2].", ".$design_data[3]."
    , ".$design_data[4].", ".$design_data[5].", ".$design_data[6].", ".$design_data[7].", ".$design_data[8].");";

    if (!mysqli_query($con, $design_query)) {
        echo mysqli_error($con);
    }
}


function get_shipyard_q($con, $planet_id)
{
    // retruns shipyard queue counts (fist shipyard col)
    $shipyard_query = "SELECT shipyard_q_count FROM planets WHERE id=".$planet_id.";";
    $res = mysqli_query($con, $shipyard_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_assoc($res))
        {
            return explode(", ", $row["shipyard_q_count"]);
        }
    }
    else
    {
        echo "Internal Server Error!";
    }
}

function get_shipyard_q_types($con, $planet_id)
{
    // retruns shipyard queue types (ids of designs) (second shipyard col)
    $shipyard_query = "SELECT shipyard_q_types FROM planets WHERE id=".$planet_id.";";
    $res = mysqli_query($con, $shipyard_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_assoc($res))
        {
            return explode(", ", $row["shipyard_q_types"]);
        }
    }
    else
    {
        echo "Internal Server Error!";
    }
}

function add_shipyard_q($con, $planet_id, $ship_count, $ship_type)
{
    // adds new building queue to shipyard (both count and type)
    if (get_shipyard_q($con, $planet_id)[0] == 0)
    {
        $shipyard_empty = true;
    }
    else
    {
        $ship_count = ", ".$ship_count;
        $ship_type = ", ".$ship_type;
    }
    $add_shipyard_q_query = "UPDATE planets SET shipyard_q_count=CONCAT(shipyard_q_count, '".$ship_count."'), shipyard_q_types=CONCAT(shipyard_q_types, '".$ship_type."') WHERE id=".$planet_id.";";
    
    if (mysqli_query($con, $add_shipyard_q_query)) {
        if ($shipyard_empty)
        {
            add_queued_job($con, 2, $planet_id, calculate_ship_build_time($con, $planet_id, $ship_type));
        }
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

function calculate_ship_build_time($con, $planet_id, $ship_type)
{
    // calculates building based on type. Value is set for basic types
    $shipyard_lvl = get_building_lvl($con, $planet_id, 0, false);
    if ($ship_type < 11)
    {
        // TODO - calc build time
        return (60 / $shipyard_lvl) * $ship_type / 2;
    }
    else
    {
        $ship_class = get_class_of_ship_from_id($con, $ship_type);
        if ($ship_class === 'destroyers')
        {
            $base_val = 7200;
        }
        elseif($ship_class === 'cruiser')
        {
            $base_val = 10800;
        }
        
        // TODO - calc build time
        return ($base_val / $shipyard_lvl) * 10;
    }
}

function get_designs_of_ship_count($con, $player_id, $class)
{
    // as there is a limit of designs for each player, this fun returns how many designs of given class a player owns
    $designs_query = "SELECT id FROM ship_designs WHERE owner=".$player_id." AND ship_class='".$class."';";
    $res = mysqli_query($con, $designs_query);

    return mysqli_num_rows($res);
}

function get_ship_design_count($con, $design_id)
{
    // retrns design count by id
    $designs_query = "SELECT design_count FROM ship_designs WHERE id=".$design_id.";";
    $res = mysqli_query($con, $designs_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_assoc($res))
        {
            return $row["design_count"];
        }
    }
    else
    {
        echo "Internal Server Error!";
    }
}

function get_class_of_ship_from_id($con, $design_id)
{
    // returns ship class by id
    $class_query = "SELECT ship_class FROM ship_designs WHERE id=".$design_id.";";
    $res = mysqli_query($con, $class_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_assoc($res))
        {
            return $row["ship_class"];
        }
    }
    else
    {
        echo "Internal Server Error!";
    }
}

function create_fleet($con, $player_id, $type ,$localization)
{
    // creates new fleet
    $create_fleet_query = "INSERT INTO fleets (owner, fleet_type, destenation) VALUES (".$player_id.", ".$type.", ".$localization.");";

    if (!mysqli_query($con, $create_fleet_query)) {
        echo mysqli_error($con);
    }
}

function build_ship_from_design($con, $planet_id)
{
    // builds ship on planet (similar to perform_building_upgrade() but for ships)
    $types = get_shipyard_q_types($con, $planet_id);
    $shipyard_q_count = get_shipyard_q($con, $planet_id);

    $type = $types[0];

    if ($shipyard_q_count[0] === '1')
    {
        $types = implode(", ", array_slice($types, 1));
        $shipyard_q_count = implode(", ", array_slice($shipyard_q_count, 1));
    }
    else
    {
        $types = implode(", ", $types);
        $shipyard_q_count[0]--;
        $shipyard_q_count = implode(", ", $shipyard_q_count);
    }

    $remove_shipyard_q_query = "UPDATE planets SET shipyard_q_count='".$shipyard_q_count."', shipyard_q_types='".$types."' WHERE id=".$planet_id.";";
    if (mysqli_query($con, $remove_shipyard_q_query)) {
        add_ship_to_fleet($con, $type, $planet_id, 1);
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

function get_ships_count_by_class($con, $fleet_id, $ship_class)
{
    // reunrs ships of given class in given fleet
    $ships_query = "SELECT ".$ship_class." FROM fleets WHERE id=".$fleet_id.";";
    $res = mysqli_query($con, $ships_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_row($res))
        {
            return explode(", ", $row[0]);
        }
    }
    else
    {
        echo "Internal Server Error!";
    }
}


function get_local_fleet_id($con, $planet_id)
{
    //returns local fleet id
    $fleet_query = "SELECT id FROM fleets WHERE destenation=".$planet_id." AND fleet_type=0;";
    $res = mysqli_query($con, $fleet_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_row($res))
        {
            return $row[0];
        }
    }
    else
    {
        echo "Internal Server Error!";
    }
}

function add_ship_to_fleet($con, $ship_type, $planet_id, $ship_count)
{
    // adds ship to local fleet on given planet

    // 3 first types are light fighters
    // decrement ship type so it can represent ship type in table
    if ($ship_type < 4)
    {
        $ship_class = 'l_fighters';
        $ship_type--;
    }
    // 2 next are heavy fighters
    elseif ($ship_type > 3 && $ship_type < 6)
    {
        $ship_class = 'h_fighters';
        $ship_type -= 4;
    }
    // 3 next are corvettas
    elseif ($ship_type > 5 && $ship_type < 9)
    {
        $ship_class = 'corvettas';
        $ship_type -= 6;
    }
    // 2 last are bombers
    elseif ($ship_type > 8 && $ship_type < 11)
    {
        $ship_class = 'bombers';
        $ship_type -= 9;
    }
    // if ship design is greater than 10 it means we need to use custom design
    elseif($ship_type > 10)
    {
        $ship_class = get_class_of_ship_from_id($con, $ship_type);
        $ship_type = get_ship_design_count($con, $ship_type) - 1;
    }

    $fleet_id = get_local_fleet_id($con, $planet_id);
    $ships = get_ships_count_by_class($con, $fleet_id, $ship_class);

    $ships[$ship_type] += 1;

    $ships = implode(", ",$ships);

    $add_ship_query = "UPDATE fleets SET ".$ship_class."='".$ships."' WHERE id=".$fleet_id.";";
    if (mysqli_query($con, $add_ship_query)) {
        
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

function get_fleet_data($con, $fleet_id)
{
    // gets entire fleet data
    # 0 - 4 - general info, 5 - 8 - cargo, 9 and more - ship counts
    $get_fleet_query = "SELECT id, owner, destenation, sent_from, mission_type, building_carried, special_carried, fuel_carried, hyper_carried, l_fighters, h_fighters, corvettas, bombers, destroyers FROM fleets WHERE id=".$fleet_id.";";
    $res = mysqli_query($con, $get_fleet_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_row($res))
        {
            return $row;
        }
    }
    else
    {
        echo "Internal Server Error!";
    }
}

function send_fleet($con, $fleet_id, $mission_type, $destenation, $time_to_arrive, $load)
{
    /* sends fleet to given destenation.
    it will arrive in given time in seconds

    mission types:
    0 if not moving, 1 - attack, 2 - station, 3 - defend, 4 - transport, 5 - return from mission

    confuzing syntax - destenation in sent from is actually fleets position b4 changing this record as destenation
    represents fleets postion if it isnt moving */
    $send_query = "UPDATE fleets SET sent_from=destenation, destenation=".$destenation.", mission_type=".$mission_type.", building_carried=".$load[0].", special_carried=".$load[1].", fuel_carried=".$load[2].", hyper_carried=".$load[3].", in_move=1 WHERE id=".$fleet_id.";";
    if (mysqli_query($con, $send_query)) {
        add_queued_job($con, 1, $fleet_id, $time_to_arrive);
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

function fleet_arrive($con, $fleet_id)
{
    // event that occurs (pi) when fleet reaches its destenation
    $fleet_data = get_fleet_data($con, $fleet_id);
    $fleet_data[4] = (int)$fleet_data[4];
    
    if ($fleet_data[4] === 1)
    {
        #TODO - FIGHT
    }
    elseif ($fleet_data[4] === 2)
    {
        change_fleet_location($con, $fleet_data);
    }
    elseif ($fleet_data[4] === 3)
    {
        #TODO - DEFEND
    }
    elseif ($fleet_data[4] === 4)
    {
        transport_resources($con, $fleet_data);
    }
    elseif ($fleet_data[4] === 5)
    {
        fleet_return($con, $fleet_data);
    }

    if ($fleet_data[4] !== 5 && $fleet_data[4] !== 2)
    {
        add_queued_job($con, 1, $fleet_data[0], calculate_fleet_flight_time($con));
    }
}

function calculate_fleet_flight_time($con, $fleet_data)
{
    # TODO - TEMP!!!
    return 20;
}

function change_fleet_location($con, $fleet_data)
{
    // changes fleet location (requires its entire data as it may carry resources)
    $update_fleet_query = "UPDATE fleets SET mission_type=0, in_move=0, building_carried=0, special_carried=0, fuel_carried=0, hyper_carried=0 WHERE id=".$fleet_data[0].";";
    if (mysqli_query($con, $update_fleet_query)) {
        add_resources($con, $fleet_data[3], array_slice($fleet_data, 5, 4));
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

function fleet_return($con, $fleet_data)
{
    // as in change_fleet_location()
    $update_fleet_query = "UPDATE fleets SET destenation=sent_from, sent_from=null, mission_type=0, in_move=0, building_carried=0, special_carried=0, fuel_carried=0, hyper_carried=0 WHERE id=".$fleet_data[0].";";
    if (mysqli_query($con, $update_fleet_query)) {
        add_resources($con, $fleet_data[3], array_slice($fleet_data, 5, 4));
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

function transport_resources($con, $fleet_data)
{
    // removes resources from fleet and sends the fleet back
    $update_fleet_query = "UPDATE fleets SET destenation=sent_from, mission_type=5, building_carried=0, special_carried=0, fuel_carried=0, hyper_carried=0 WHERE id=".$fleet_data[0].";";
    if (mysqli_query($con, $update_fleet_query)) {
        add_resources($con, $fleet_data[2], array_slice($fleet_data, 5, 4));
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

function add_resources($con, $planet_id, $values)
{
    // adds given resources to planet
    // resources amounts passed in array of ints ($values) in usual order
    $update_planet_query = "UPDATE planets SET building_materials=building_materials+".$values[0].", special_materials=special_materials+".$values[1].", reg_fuel=reg_fuel+".$values[2].", reg_fuel=reg_fuel+".$values[3]." WHERE id=".$planet_id.";";
    if (mysqli_query($con, $update_planet_query)) {
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

function get_ship_design_data($con, $fleet_data, $column_index, $ship_index)
{
    /* given fleet data, index of col (starting form 0 which represents l_fighters, 1 for h_fighters and so on),
    and number of ship design - $ship_index

    this way one number allows to both see the quantity of ships of given design (not usefull here but much more useful
    in other places) and find the actual design data itself 

    ex.
    if a fleets destroyers col == 3, 6, 0 and
    the player has designed 2 different destoryers it means that in this fleet there are 3 ships of his first design,
    6 of his second one and 0 of his not yet existing one


    returns all info of this ship design */
    $ship_index++;
    $classes = array("l_fighter", "h_fighters", "corvettas", "bombers", "destroyers");
    if ($column_index < 13)
    {
        // use all avilable desings
        $design_query = "SELECT * FROM ship_designs WHERE owner=0 AND ship_class='".$classes[$column_index - 9]."' AND design_count=".$ship_index.";";
    }
    else
    {
        $design_query = "SELECT * FROM ship_designs WHERE owner=".$fleet_data[1]." AND ship_class='".$classes[$column_index - 9]."' AND design_count=".$ship_index.";";
    }
    
    $res = mysqli_query($con, $design_query);

    if (mysqli_num_rows($res) > 0)
    {
        $data = mysqli_fetch_assoc($res);
    }
    else
    {
        echo "Internal Server Error!";
    }

    if ($column_index < 13)
    {
        // replace some values with players as they are alaways equal to research lvl
        $research_query = "SELECT thurster_lvl FROM players WHERE id=".$fleet_data[1].";";
        $res = mysqli_query($con, $research_query);
        
        if (mysqli_num_rows($res) > 0)
        {
            while($row = mysqli_fetch_row($res))
            {
                $data['drive_lvl'] = $row['thurster_lvl'];
            }
            return $data;
        }
        else
        {
            echo "Internal Server Error! 2";
        }
    }
    else
    {
        return $data;
    }
}

?>
