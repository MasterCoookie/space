<?php

//include 'general.php';

function add_research_q($con, $research_name, $lvl_after, $player_id)
{
    $research_query = "INSERT INTO q_research VALUES (null, '".$research_name."', ".$lvl_after.", ".$player_id.");";    

    if (!mysqli_query($con, $research_query)) {
        echo mysqli_error($con);
    } else {
        add_queued_job($con, 3, mysqli_insert_id($con));
    }
}

function increase_research_lvl($con, $research_id)
{
    $research_query = "SELECT research_name, lvl_after_done, player_id FROM q_research WHERE id=".$research_id.";";
    $res = mysqli_query($con, $research_query);

    if (mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_assoc($res))
        {
            $research_name = $row['research_name'];
            $lvl_after_done = $row['lvl_after_done'];
            $player_id = $row['player_id'];
        }
    }
    else
    {
        echo "Internal Server Error!";
    }

    $update_research_query = "UPDATE players SET ".$research_name."=".$lvl_after_done." WHERE id=".$player_id.";";
    if (mysqli_query($con, $update_research_query)) {
        finish_research($con, $research_id);
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

function finish_research($con, $research_id)
{
    $delete_research_query = "DELETE FROM q_research WHERE id=".$research_id.";";     

    if (!mysqli_query($con, $delete_research_query)) {
        echo mysqli_error($con);
    } else {
        del_queued_job($con, $research_id);
    }
}


?>