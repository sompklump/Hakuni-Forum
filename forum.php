<?php
session_start();
$logged_in = $_SESSION["logged_in"];
$id = $_SESSION["id"];
$uname = $_SESSION["uname"];

 require("../php/msql.php");

# GET CURRENT PAGE, SO WHEN LOGGING IN THEY REDIRECT HERE
$cur_link = null;
$cur_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$log_name = "Log in";
$log_href = "../../login/?redirect=".urlencode($cur_link);
if(isset($_SESSION["logged_in"]) == true){
    $log_name = "Log out";
    $log_href = "../../login/logout.php";
}

$forum_id = null;
$forum_title = null;
if(isset($_GET["id"])){
    $forum_id = $_GET["id"];
    $sql = "SELECT * FROM h1_forums WHERE forum_id = '$forum_id'";
    $result = mysqli_query($conn, $sql);
    //print_r($result);
    if(mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_array($result))
        {
            $forum_title = $row["forum_title"];
        }
    }
    else{
        echo "<script>window.location.replace('../forum/')</script>";
    }
}
else{
    echo "<script>window.location.replace('../forum/')</script>";
}

$user_arr = [];
if(isset($logged_in) == true) {
    $sql = "SELECT * FROM h1_users WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    //print_r($result);
    // output data of each row
    if(mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_array($result))
        {
            $group = $row["group"];
            $uname = $row['name'];
            
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

if(isset($_GET["act"]) == "crt"){
    if(in_array("create_topic", $user_arr['perms']) || in_array("*", $user_arr['perms'])) {
        $topic_title = mysqli_real_escape_string($conn, $_POST["topic_title"]);
        $topic_content = mysqli_real_escape_string($conn, $_POST["topic_content"]);
        $sql = "SELECT * FROM h1_topics WHERE topic_title = '$topic_title'";
        $result = mysqli_query($conn, $sql);
        //print_r($result);
        if(!mysqli_num_rows($result)) {
            if(strlen($_POST["topic_content"]) > 4 && strlen($_POST["topic_title"]) > 4) {
                $topic_creator = $id;
                $topic_content = str_replace("<blockquote>", "<pre class=\"prettyprint\"><code class=\"prettyprint\">", $topic_content);
                $topic_content = str_replace("</blockquote>", "</code></pre>", $topic_content);
                $date = date("Y-m-d h:i:s");
                $sql = "INSERT INTO h1_topics (forum_id, topic_title, topic_content, topic_creator, lst_user, topic_date, locked, sticky, deleted) VALUES ('$forum_id', '$topic_title', '$topic_content', '$topic_creator', '$topic_creator', '$date', '0', '0', '0')";
                $result = mysqli_query($conn, $sql);
                //print_r($result);
                if(!mysqli_num_rows($result)) {
                    echo "<script>window.location.replace('../forum/forum.php?id=$forum_id')</script>";
                }
            }
            else{
                $err_msg = "Name and content must be at least 5 letters!";
            }
        }
        else{
            $err_msg = "Topic with same name already exists!";
        }
    }
}

$web_title = null;
$sql = "SELECT * FROM h1_settings WHERE var = 'glob_name'";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_array($result))
    {
        $web_title = $row['value'];
    }
}

$web_title = null;
$footer_text = null;

$sql = "SELECT * FROM h1_settings";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_array($result))
    {
        if($row['var'] == "glob_name"){
            $web_title = $row['value'];
        }
        if($row['var'] == "footer_text"){
            $footer_text = $row['value'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.jsdelivr.net/gh/google/code-prettify@master/loader/run_prettify.js"></script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../assets/favicon.ico" type="image/x-icon">
    <title><?= $web_title ?> <?= $forum_title ?></title>
    <script src="https://cdn.ckeditor.com/ckeditor5/12.3.1/classic/ckeditor.js"></script>
    <!-- Bootstrap core CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link href="css/scrolling-nav.css" rel="stylesheet">
    <link href="../admin/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../assets/css/style.css?<?= filemtime("../assets/css/style.css") ?>" rel="stylesheet" type="text/css">
</head>

<body id="page-top">
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" id="mainNav">
    <div class="container">
      <a class="navbar-brand" href="../">Hakuni</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link" href="../">Home</a>
          </li>
            <?php
            if(in_array("create_topic", $user_arr['perms']) || in_array("*", $user_arr['perms'])){
                echo "<li class='nav-item'>
            <a class='nav-link' onclick=\"document.getElementById('new_topic_modal').style.display='block'\" href='#'>New topic</a>
          </li>";
            }
            ?> 
          <li class="nav-item">
            <a class="nav-link" href="<?= $log_href ?>"><?= $log_name ?></a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
    <div id="new_topic_modal" class="modal">
        <div class="w3-modal-content">
            <form action="?id=<?= $forum_id ?>&act=crt" method="post">
                <header class="w3-container"> 
                    <span onclick="document.getElementById('new_topic_modal').style.display='none'" class="w3-button w3-display-topright">&times;</span>
                    <h2>Create a new topic</h2>
                </header>
                <div class="w3-container">
                    <p>Topic Name</p>
                    <input class="text-field" placeholder="Name" name="topic_title" maxlength="50" spellcheck="true" required>
                    <p></p>
                    <br>
                    <p>Topic Content</p>
                    <textarea rows="8" class="textarea-field" placeholder="Content" name="topic_content" id="topic_content" maxlength="3472" spellcheck="true"></textarea>
                </div>
                <span class="error"><p><?= $err_msg ?></p></span>
                <footer class="w3-container">
                    <button type="submit">Create Topic</button>
                </footer>
                <br>
            </form>
            <script>
                ClassicEditor
                    .create( document.querySelector( '#topic_content' ) )
                    .catch( error => {
                        console.error( error );
                    } );
            </script>
        </div>
    </div>
    <script>
        function getQueryVariable(variable)
        {
            var query = window.location.search.substring(1);
            var vars = query.split("&");
            for (var i=0;i<vars.length;i++) {
               var pair = vars[i].split("=");
               if(pair[0] == variable){return pair[1];}
            }
        return(false);
        }
        // Get the modal
        var modal = document.getElementById('new_topic_modal');
        if(getQueryVariable("act") == "crt"){
            modal.style.display = "block";
        }
        // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }
    </script>
    <div class="dark-bga">
        <section class="header-in">
            <div class="container header light-bg">
                <table class='table table-bordered' id='dataTable' width='100%' cellspacing='0'>
                    <thead>
                        <tr>
                          <th>Topic</th>
                          <th>By</th>
                          <th>Created</th>
                        </tr>
                    </thead>
                    <div class='row'>
                        <div class='col-lg-8 mx-auto'>
                            <br>
                            <tbody>
                <?php
                    $topics_loaded = 0;
                    $sql = "SELECT *, DATE_FORMAT(topic_date, '%Y.%m.%d %k:%i') AS formatted_date FROM h1_topics WHERE sticky = '1' AND forum_id = '$forum_id' ORDER BY topic_id DESC";
                    $result = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_array($result))
                        {
                            $is_deleted = $row['deleted'];
                            $is_archived = $row['archived'];
                            if($is_archived || $is_deleted) {
                                if(in_array("*", $user_arr['perms'])){
                                    $topics_loaded++;
                                    
                                    $topic_date = $row["formatted_date"];
                                    $topic_id = $row["topic_id"];
                                    $topic_title = $row["topic_title"];
                                    $topic_creator = $row["topic_creator"];
                                    $u_sql = "SELECT * FROM h1_users WHERE id = '$topic_creator'";
                                    $u_result = mysqli_query($conn, $u_sql);
                                    while($u_row = mysqli_fetch_array($u_result))
                                    {
                                        $u_group = $u_row["group"];
                                        $u_name = $u_row["name"];
                                            
                                        $g_sql = "SELECT * FROM h1_groups WHERE g_range = '$u_group'";
                                        $g_result = mysqli_query($conn, $g_sql);
                                        $group_tag = $u_name;
                                        while($g_row = mysqli_fetch_array($g_result))
                                        {
                                            $group_html = $g_row["group_html"];
                                            if(!empty($group_html)){
                                                $group_tag = str_replace("%username%", $u_name, $group_html);
                                            }
                                            echo "<tr><td><a href='topic/?id={$topic_id}'><b><h4>{$topic_title}</h4></b></a></td>
                                                  <td><h4><a href='../profile/?id=$topic_creator'>{$group_tag}</a></h4></td>
                                                  <td><h4>$topic_date</h4></td></tr>";
                                        }
                                    }
                                }
                                else{
                                    if($is_archived && $is_deleted) {
                                        if(in_array("delete", $user_arr['perms']) && in_array("archive", $user_arr['perms'])) {
                                            $topics_loaded++;
                                    
                                            $topic_date = $row["formatted_date"];
                                            $topic_id = $row["topic_id"];
                                            $topic_title = $row["topic_title"];
                                            $topic_creator = $row["topic_creator"];
                                            $u_sql = "SELECT * FROM h1_users WHERE id = '$topic_creator'";
                                            $u_result = mysqli_query($conn, $u_sql);
                                            while($u_row = mysqli_fetch_array($u_result))
                                            {
                                                $u_group = $u_row["group"];
                                                $u_name = $u_row["name"];

                                                $g_sql = "SELECT * FROM h1_groups WHERE g_range = '$u_group'";
                                                $g_result = mysqli_query($conn, $g_sql);
                                                $group_tag = $u_name;
                                                while($g_row = mysqli_fetch_array($g_result))
                                                {
                                                    $group_html = $g_row["group_html"];
                                                    if(!empty($group_html)){
                                                        $group_tag = str_replace("%username%", $u_name, $group_html);
                                                    }
                                                    echo "<tr><td><a href='topic/?id={$topic_id}'><b><h4>{$topic_title}</h4></b></a></td>
                                                          <td><h4><a href='../profile/?id=$topic_creator'>{$group_tag}</a></h4></td>
                                                          <td><h4>$topic_date</h4></td></tr>";
                                                }
                                            }
                                        }
                                    }
                                    else{
                                        if($is_archived){
                                            if(in_array("archive", $user_arr['perms'])){
                                                $topics_loaded++;
                                    
                                                $topic_date = $row["formatted_date"];
                                                $topic_id = $row["topic_id"];
                                                $topic_title = $row["topic_title"];
                                                $topic_creator = $row["topic_creator"];
                                                $u_sql = "SELECT * FROM h1_users WHERE id = '$topic_creator'";
                                                $u_result = mysqli_query($conn, $u_sql);
                                                while($u_row = mysqli_fetch_array($u_result))
                                                {
                                                    $u_group = $u_row["group"];
                                                    $u_name = $u_row["name"];

                                                    $g_sql = "SELECT * FROM h1_groups WHERE g_range = '$u_group'";
                                                    $g_result = mysqli_query($conn, $g_sql);
                                                    $group_tag = $u_name;
                                                    while($g_row = mysqli_fetch_array($g_result))
                                                    {
                                                        $group_html = $g_row["group_html"];
                                                        if(!empty($group_html)){
                                                            $group_tag = str_replace("%username%", $u_name, $group_html);
                                                        }
                                                        echo "<tr><td><a href='topic/?id={$topic_id}'><b><h4>{$topic_title}</h4></b></a></td>
                                                              <td><h4><a href='../profile/?id=$topic_creator'>{$group_tag}</a></h4></td>
                                                              <td><h4>$topic_date</h4></td></tr>";
                                                    }
                                                }
                                            }
                                        }
                                        if($is_deleted){
                                            if(in_array("delete", $user_arr['perms'])){
                                                $topics_loaded++;
                                    
                                                $topic_date = $row["formatted_date"];
                                                $topic_id = $row["topic_id"];
                                                $topic_title = $row["topic_title"];
                                                $topic_creator = $row["topic_creator"];
                                                $u_sql = "SELECT * FROM h1_users WHERE id = '$topic_creator'";
                                                $u_result = mysqli_query($conn, $u_sql);
                                                while($u_row = mysqli_fetch_array($u_result))
                                                {
                                                    $u_group = $u_row["group"];
                                                    $u_name = $u_row["name"];

                                                    $g_sql = "SELECT * FROM h1_groups WHERE g_range = '$u_group'";
                                                    $g_result = mysqli_query($conn, $g_sql);
                                                    $group_tag = $u_name;
                                                    while($g_row = mysqli_fetch_array($g_result))
                                                    {
                                                        $group_html = $g_row["group_html"];
                                                        if(!empty($group_html)){
                                                            $group_tag = str_replace("%username%", $u_name, $group_html);
                                                        }
                                                        echo "<tr><td><a href='topic/?id={$topic_id}'><b><h4>{$topic_title}</h4></b></a></td>
                                                              <td><h4><a href='../profile/?id=$topic_creator'>{$group_tag}</a></h4></td>
                                                              <td><h4>$topic_date</h4></td></tr>";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            else{
                                $topics_loaded++;
                                    
                                $topic_date = $row["formatted_date"];
                                $topic_id = $row["topic_id"];
                                $topic_title = $row["topic_title"];
                                $topic_creator = $row["topic_creator"];
                                $u_sql = "SELECT * FROM h1_users WHERE id = '$topic_creator'";
                                $u_result = mysqli_query($conn, $u_sql);
                                while($u_row = mysqli_fetch_array($u_result))
                                {
                                    $u_group = $u_row["group"];
                                    $u_name = $u_row["name"];

                                    $g_sql = "SELECT * FROM h1_groups WHERE g_range = '$u_group'";
                                    $g_result = mysqli_query($conn, $g_sql);
                                    $group_tag = $u_name;
                                    while($g_row = mysqli_fetch_array($g_result))
                                    {
                                        $group_html = $g_row["group_html"];
                                        if(!empty($group_html)){
                                            $group_tag = str_replace("%username%", $u_name, $group_html);
                                        }
                                        echo "<tr><td><a href='topic/?id={$topic_id}'><b><h4>{$topic_title}</h4></b></a></td>
                                        <td><h4><a href='../profile/?id=$topic_creator'>{$group_tag}</a></h4></td>
                                        <td><h4>$topic_date</h4></td></tr>";
                                    }
                                }
                            }
                        }
                    }
                  ?>
                <?php
                    $sql = "SELECT *, DATE_FORMAT(topic_date, '%Y.%m.%d %k:%i') AS formatted_date FROM h1_topics WHERE sticky = '0' AND forum_id = '$forum_id' ORDER BY topic_id DESC";
                    $result = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_array($result))
                        {
                            $is_deleted = $row['deleted'];
                            $is_archived = $row['archived'];
                            if($is_archived || $is_deleted) {
                                if(in_array("*", $user_arr['perms'])){
                                    $topics_loaded++;
                                    
                                    $topic_date = $row["formatted_date"];
                                    $topic_id = $row["topic_id"];
                                    $topic_title = $row["topic_title"];
                                    $topic_creator = $row["topic_creator"];
                                    $u_sql = "SELECT * FROM h1_users WHERE id = '$topic_creator'";
                                    $u_result = mysqli_query($conn, $u_sql);
                                    while($u_row = mysqli_fetch_array($u_result))
                                    {
                                        $u_group = $u_row["group"];
                                        $u_name = $u_row["name"];
                                            
                                        $g_sql = "SELECT * FROM h1_groups WHERE g_range = '$u_group'";
                                        $g_result = mysqli_query($conn, $g_sql);
                                        $group_tag = $u_name;
                                        while($g_row = mysqli_fetch_array($g_result))
                                        {
                                            $group_html = $g_row["group_html"];
                                            if(!empty($group_html)){
                                                $group_tag = str_replace("%username%", $u_name, $group_html);
                                            }
                                            echo "<tr><td><a href='topic/?id={$topic_id}'><b><h4>{$topic_title}</h4></b></a></td>
                                                  <td><h4><a href='../profile/?id=$topic_creator'>{$group_tag}</a></h4></td>
                                                  <td><h4>$topic_date</h4></td></tr>";
                                        }
                                    }
                                }
                                else{
                                    if($is_archived && $is_deleted) {
                                        if(in_array("delete", $user_arr['perms']) && in_array("archive", $user_arr['perms'])) {
                                            $topics_loaded++;
                                    
                                            $topic_date = $row["formatted_date"];
                                            $topic_id = $row["topic_id"];
                                            $topic_title = $row["topic_title"];
                                            $topic_creator = $row["topic_creator"];
                                            $u_sql = "SELECT * FROM h1_users WHERE id = '$topic_creator'";
                                            $u_result = mysqli_query($conn, $u_sql);
                                            while($u_row = mysqli_fetch_array($u_result))
                                            {
                                                $u_group = $u_row["group"];
                                                $u_name = $u_row["name"];

                                                $g_sql = "SELECT * FROM h1_groups WHERE g_range = '$u_group'";
                                                $g_result = mysqli_query($conn, $g_sql);
                                                $group_tag = $u_name;
                                                while($g_row = mysqli_fetch_array($g_result))
                                                {
                                                    $group_html = $g_row["group_html"];
                                                    if(!empty($group_html)){
                                                        $group_tag = str_replace("%username%", $u_name, $group_html);
                                                    }
                                                    echo "<tr><td><a href='topic/?id={$topic_id}'><b><h4>{$topic_title}</h4></b></a></td>
                                                          <td><h4><a href='../profile/?id=$topic_creator'>{$group_tag}</a></h4></td>
                                                          <td><h4>$topic_date</h4></td></tr>";
                                                }
                                            }
                                        }
                                    }
                                    else{
                                        if($is_archived){
                                            if(in_array("archive", $user_arr['perms'])){
                                                $topics_loaded++;
                                    
                                                $topic_date = $row["formatted_date"];
                                                $topic_id = $row["topic_id"];
                                                $topic_title = $row["topic_title"];
                                                $topic_creator = $row["topic_creator"];
                                                $u_sql = "SELECT * FROM h1_users WHERE id = '$topic_creator'";
                                                $u_result = mysqli_query($conn, $u_sql);
                                                while($u_row = mysqli_fetch_array($u_result))
                                                {
                                                    $u_group = $u_row["group"];
                                                    $u_name = $u_row["name"];

                                                    $g_sql = "SELECT * FROM h1_groups WHERE g_range = '$u_group'";
                                                    $g_result = mysqli_query($conn, $g_sql);
                                                    $group_tag = $u_name;
                                                    while($g_row = mysqli_fetch_array($g_result))
                                                    {
                                                        $group_html = $g_row["group_html"];
                                                        if(!empty($group_html)){
                                                            $group_tag = str_replace("%username%", $u_name, $group_html);
                                                        }
                                                        echo "<tr><td><a href='topic/?id={$topic_id}'><b><h4>{$topic_title}</h4></b></a></td>
                                                              <td><h4><a href='../profile/?id=$topic_creator'>{$group_tag}</a></h4></td>
                                                              <td><h4>$topic_date</h4></td></tr>";
                                                    }
                                                }
                                            }
                                        }
                                        if($is_deleted){
                                            if(in_array("delete", $user_arr['perms'])){
                                                $topics_loaded++;
                                    
                                                $topic_date = $row["formatted_date"];
                                                $topic_id = $row["topic_id"];
                                                $topic_title = $row["topic_title"];
                                                $topic_creator = $row["topic_creator"];
                                                $u_sql = "SELECT * FROM h1_users WHERE id = '$topic_creator'";
                                                $u_result = mysqli_query($conn, $u_sql);
                                                while($u_row = mysqli_fetch_array($u_result))
                                                {
                                                    $u_group = $u_row["group"];
                                                    $u_name = $u_row["name"];

                                                    $g_sql = "SELECT * FROM h1_groups WHERE g_range = '$u_group'";
                                                    $g_result = mysqli_query($conn, $g_sql);
                                                    $group_tag = $u_name;
                                                    while($g_row = mysqli_fetch_array($g_result))
                                                    {
                                                        $group_html = $g_row["group_html"];
                                                        if(!empty($group_html)){
                                                            $group_tag = str_replace("%username%", $u_name, $group_html);
                                                        }
                                                        echo "<tr><td><a href='topic/?id={$topic_id}'><b><h4>{$topic_title}</h4></b></a></td>
                                                              <td><h4><a href='../profile/?id=$topic_creator'>{$group_tag}</a></h4></td>
                                                              <td><h4>$topic_date</h4></td></tr>";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            else{
                                $topics_loaded++;
                                    
                                $topic_date = $row["formatted_date"];
                                $topic_id = $row["topic_id"];
                                $topic_title = $row["topic_title"];
                                $topic_creator = $row["topic_creator"];
                                $u_sql = "SELECT * FROM h1_users WHERE id = '$topic_creator'";
                                $u_result = mysqli_query($conn, $u_sql);
                                while($u_row = mysqli_fetch_array($u_result))
                                {
                                    $u_group = $u_row["group"];
                                    $u_name = $u_row["name"];

                                    $g_sql = "SELECT * FROM h1_groups WHERE g_range = '$u_group'";
                                    $g_result = mysqli_query($conn, $g_sql);
                                    $group_tag = $u_name;
                                    while($g_row = mysqli_fetch_array($g_result))
                                    {
                                        $group_html = $g_row["group_html"];
                                        if(!empty($group_html)){
                                            $group_tag = str_replace("%username%", $u_name, $group_html);
                                        }
                                        echo "<tr><td><a href='topic/?id={$topic_id}'><b><h4>{$topic_title}</h4></b></a></td>
                                        <td><h4><a href='../profile/?id=$topic_creator'>{$group_tag}</a></h4></td>
                                        <td><h4>$topic_date</h4></td></tr>";
                                    }
                                }
                            }
                        }
                    }
                    if($topics_loaded <= 0){
                        echo "<div class='row'>
                                        <div class='col-lg-8 mx-auto'>
                                            <h2>There seems to be no activity 😭</h2>
                                        </div>
                                      </div><br>";
                    }
                    
                  ?>
                            </tbody>
                        </div>
                    </div>
                </table>
                <br>
            </div>
        </section>
    </div>
   

  <!-- Footer -->
  <footer class="py-5 bg-dark">
    <div class="container">
      <p class="m-0 text-center text-white"><?= $footer_text ?></p>
    </div>
    <!-- /.container -->
  </footer>

  <!-- Bootstrap core JavaScript -->
  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Plugin JavaScript -->
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Custom JavaScript for this theme -->
  <script src="../js/scrolling-nav.js"></script>

</body>

</html>