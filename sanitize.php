<?php
function sanitizeAlnum($con, $input)
{
    $output = mysqli_real_escape_string($con, $input);
    return filter_var($output, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
}

function sanitizeNum($input)
{
    return preg_replace("/[^0-9]/", "", $input);
}
?>