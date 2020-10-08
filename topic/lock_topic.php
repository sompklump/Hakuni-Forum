<?php
session_start();
$logged_in = $_SESSION["logged_in"];
$id = $_SESSION["id"];
$uname = $_SESSION["uname"];

require("../../php/msql.php");

$t_id = $_GET["id"];

$user_arr = [];
if(isset($logged_in) == true) {
    $sql = "SELECT * FROM h1_users WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    //print_r($result);
    // output data of each row
    if(mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_array($result))
        {
            $power = $row["power"];
            $group = $row["group"];
            
            $g_sql = "SELECT * FROM h1_groups WHERE g_range = '$group'";
            $g_result = mysqli_query($conn, $g_sql);
            //print_r($result);
            // output data of each row
            if(mysqli_num_rows($g_result) > 0) {
                while($g_row = mysqli_fetch_array($g_result))
                {
                    $perms = $g_row["permissions"];
                }
            }
        }
    }
    $perms = (explode(",", strtolower($perms)));
    $user_arr = [
        'perms' => $perms
    ];
}

if(isset($logged_in) == true) {
    $lock = 0;
    $sql = "SELECT * FROM h1_topics WHERE topic_id = '$t_id'";
    $result = mysqli_query($conn, $sql);
    //print_r($result);
    // output data of each row
    if(mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_array($result))
        {
            $lock = $row["locked"];
        }
    }
    if(in_array("*", $user_arr['perms']) || in_array("lock_topic", $user_arr['perms'])) {
        $set_lock = null;
        if($lock == 0){
            $set_lock = 1;
        }
        else {
            $set_lock = 0;
        }
        $sql = "UPDATE h1_topics SET locked = '$set_lock' WHERE topic_id = '$t_id'";
        if(mysqli_query($conn, $sql)){
            echo "<script>window.location.replace('../topic/?id=$t_id')</script>";
        }
    }
}
?>