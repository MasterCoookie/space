<?php

function mk_offer($con, $player_name, $offer, $wanted, $comment)
{
    // creates trade offer, resources array in usual order
    $create_offer_query = "INSERT INTO market VALUES (null, '".$player_name."', ".$offer[0].", ".$offer[1].", ".$offer[2].", 
    ".$offer[3].", ".$offer[4].", ".$wanted[0].", ".$wanted[1].", ".$wanted[2].", ".$wanted[3].", ".$wanted[4].", now(), '".$comment."', false, false);";    

    if (!mysqli_query($con, $create_offer_query)) {
        echo mysqli_error($con);
    }
}

?>