<?php
session_start();
$logged_in = $_SESSION["logged_in"];
$id = $_SESSION["id"];
$uname = $_SESSION["uname"];

require("../../php/msql.php");

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
    $cm_id = $_GET["id"];
    $topic_id = null;
    $c_id = null;
    $sql = "SELECT * FROM h1_posts WHERE post_id = '$cm_id'";
    $result = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_array($result))
    {
        $topic_id = $row["topic_id"];
        $c_id = $row["post_creator"];
    }
    //print_r($result);
    // output data of each row
    if($id == $c_id || in_array("*", $user_arr['perms']) || in_array("del_comment", $user_arr['perms'])) {
        if(mysqli_num_rows($result) > 0) {
            $sql = "UPDATE h1_posts SET deleted = '1' WHERE post_id LIKE '$cm_id'";
            echo "<script>alert('SQL = $sql')</script>";
            if(mysqli_query($conn, $sql)){
                //echo "<script>alert('Comment Has Been Deleted')</script>";
                echo "<script>window.location.replace('../topic/?id=$topic_id')</script>";
                //die();
            }
        }
        echo "<script>alert('Comment NOT Deleted')</script>";
        echo "<script>window.location.replace('../topic/?id=$topic_id')</script>";
        //die();
    }
}
else{
    echo "<script>window.location.replace('../')</script>";
}
?>