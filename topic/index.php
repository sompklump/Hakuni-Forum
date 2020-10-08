<?php
session_start();
$logged_in = $_SESSION["logged_in"];
$id = $_SESSION["id"];
$uname = $_SESSION["uname"];
$perms = null;
$c_tag_dis = null;
$topic_creator = null;

require("../../php/msql.php");

# GET CURRENT PAGE, SO WHEN LOGGING IN THEY REDIRECT HERE
$cur_link = null;
$cur_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$cur_link .= $_SERVER['REQUEST_URI'];

$log_name = "Log in";
$log_href = "../../login/?redirect=".urlencode($cur_link);
if(isset($_SESSION["logged_in"]) == true){
    $sql = "SELECT * FROM h1_users WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_array($result))
        {
            $uname = $row['name'];
        }
    }
    $log_name = "Log out";
    $log_href = "../../login/logout.php";
}

$stick_name = "Stick Topic";
$is_sticked = false;
$lock_name = "Lock Topic";
$is_locked = false;
$is_archived = false;
$archive_name = "Archive";
$is_deleted = false;
if(isset($_GET["id"])) {
    $topic_id = $_GET["id"];
    $sql = "SELECT * FROM h1_topics WHERE topic_id = '$topic_id'";
    $result = mysqli_query($conn, $sql);
    //print_r($result);
    // output data of each row
    if(mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_array($result))
        {
            $topic_title = $row["topic_title"];
            $topic_content = $row["topic_content"];
            $is_sticked = $row["sticky"];
            $is_locked = $row["locked"];
            $is_archived = $row["archived"];
            $is_deleted = $row["deleted"];
            $topic_creator = $row["topic_creator"];
            
            $u_sql = "SELECT * FROM h1_users WHERE id = '$topic_creator'";
            $u_result = mysqli_query($conn, $u_sql);
            //print_r($result);
            // output data of each row
            if(mysqli_num_rows($u_result) > 0) {
                while($u_row = mysqli_fetch_array($u_result))
                {
                    $group = $u_row["group"];
                    $topic_creator_name = $u_row["name"];

                    $g_sql = "SELECT * FROM h1_groups WHERE g_range = '$group'";
                    $g_result = mysqli_query($conn, $g_sql);
                    //print_r($result);
                    // output data of each row
                    if(mysqli_num_rows($g_result) > 0) {
                        while($g_row = mysqli_fetch_array($g_result))
                        {
                            $raw_tag = $g_row["group_html"];
                            $raw_tag = str_replace("%username%", $topic_creator_name, $raw_tag);
                            $c_tag_dis = str_replace("%tag_owner%", $g_row["group_name"], $raw_tag);
                        }
                    }
                }
            }
        }
    }
    else{
       echo "<script>window.location.replace('../../forum/')</script>"; 
    }
}
else{
    echo "<script>window.location.replace('../../forum/')</script>";
}

if($is_sticked){
    $stick_name = "Unstick Topic";
}
if($is_locked){
    $lock_name = "Unlock Topic";
}
if($is_archived){
    $archive_name = "Bring Back Topic";
}

if($is_locked == false && isset($logged_in) == true) {
    
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

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['cmt_topic'])) {
        $comment = mysqli_real_escape_string($conn, $_POST["comment_input"]);
        if(strlen($comment) > 1)
        {
            $date = date("Y-m-d h:i:s");
            $comment = str_replace("<blockquote>", "<pre class=\"prettyprint\"><code class=\"prettyprint\">", $comment);
            $comment = str_replace("</blockquote>", "</code></pre>", $comment);
            $sql = "INSERT INTO h1_posts (topic_id, post_creator, post_content, post_date, last_edited, deleted) VALUES ('$topic_id', '$id', '$comment', '$date', '$date', '0')";
            if (mysqli_query($conn, $sql)) {
                echo "<script>window.location.replace('../topic/?id=$topic_id')</script>";
            }
        }
    }
    if(isset($_POST['delTopic_btn'])){
        if(in_array("*", $user_arr['perms']) || in_array("delete", $user_arr['perms'])) {
            $t_id = $_GET["id"];
            $sql = "UPDATE h1_topics SET deleted = true WHERE topic_id = '$t_id'";
            if(mysqli_query($conn, $sql)){
                echo "<script>window.location.replace('../topic/?id=$topic_id')</script>";
            }
        }
    }
    if(isset($_POST['archive_btn'])){
        if(in_array("*", $user_arr['perms']) || in_array("archive", $user_arr['perms'])) {
            $sql = "SELECT archived FROM h1_topics WHERE topic_id = '$t_id'";
            $result = mysqli_query($conn, $sql);
            if(mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_array($result))
                {
                    $is_archived = $row['archived'];
                }
            }
            if($is_archived){
                $t_id = $_GET["id"];
                $sql = "UPDATE h1_topics SET archived = false WHERE topic_id = '$t_id'";
                if(mysqli_query($conn, $sql)){
                    echo "<script>window.location.replace('../topic/?id=$topic_id')</script>";
                }
            }
            else{
                $t_id = $_GET["id"];
                $sql = "UPDATE h1_topics SET archived = true WHERE topic_id = '$t_id'";
                if(mysqli_query($conn, $sql)){
                    echo "<script>window.location.replace('../topic/?id=$topic_id')</script>";
                }
            }
        }
    }
}

if($is_deleted){
    if(in_array("*", $user_arr['perms']) || in_array("see_deleted", $user_arr['perms'])){
        //
    }
    else{
        echo "<script>window.location.replace('../../forum/')</script>";
    }
}
if($is_archived){
    if(in_array("*", $user_arr['perms']) || in_array("archive", $user_arr['perms'])){
        //
    }
    else{
        echo "<script>window.location.replace('../../forum/')</script>";
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
    <link rel="icon" href="../../assets/favicon.ico" type="image/x-icon">
    <title><?= $web_title ?> <?= $topic_title ?></title>
    <script src="https://cdn.ckeditor.com/ckeditor5/12.3.1/classic/ckeditor.js"></script>
    <script src="../../ckeditor5-build-classic/src/ckeditor.js?<?= filemtime("../../assets/css/style.css") ?>"></script>
    
    <!-- Bootstrap core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">

    <!-- Custom styles for this template -->
    <link href="../css/scrolling-nav.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link href="../../admin/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css?<?= filemtime("../../assets/css/style.css") ?>" rel="stylesheet" type="text/css">

</head>

<body id="page-top">

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" id="mainNav">
    <div class="container">
      <a class="navbar-brand js-scroll-trigger" href="../../">Hakuni</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" active href="../../">Home</a>
          </li>
            <?php
            if(in_array("delete_topic", $user_arr['perms']) || in_array("stick_topic", $user_arr['perms']) || in_array("lock_topic", $user_arr['perms']) || in_array("*", $user_arr['perms'])){
                echo "
                    <ul class='navbar-nav ml-auto'>
                      <li class='nav-item dropdown'>
                        <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                          Action
                        </a>
                        <div class='dropdown-menu dropdown-menu-right animated--grow-in' aria-labelledby='navbarDropdown'>
                            <form method='post'>";
                            // USEFUL ADMIN PERMS
                            if(in_array("stick_topic", $user_arr['perms']) || in_array("*", $user_arr['perms'])){ echo "<a class='dropdown-item' href='stick_topic.php?id=$topic_id'>$stick_name</a>";}
                            if(in_array("lock_topic", $user_arr['perms']) || in_array("*", $user_arr['perms'])){ echo "<a class='dropdown-item' href='lock_topic.php?id=$topic_id'>$lock_name</a>";}
                
                            // DIVIDER LINE
                            if(in_array("*", $user_arr['perms']) || in_array("delete", $user_arr['perms']) || in_array("archive", $user_arr['perms'])){ echo "<div class='dropdown-divider'></div>";}
                
                            // DELETE AND ARCHIVE
                            if(in_array("archive", $user_arr['perms']) || in_array("*", $user_arr['perms'])){ echo "<button class='btn btn-link dropdown-item' type='submit' name='archive_btn'>$archive_name</button>";}
                            if(in_array("delete", $user_arr['perms']) || in_array("*", $user_arr['perms'])){ echo "<button type='button' class='btn btn-link dropdown-item' onclick=\"document.getElementById('del_topic_modal').style.display='block'\">Delete Topic</button>";}
                        echo "</form>
                        </div>
                      </li>
                    </ul>";
            }
            ?>
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="<?= $log_href ?>"><?= $log_name ?></a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
    <div id="del_topic_modal" class="modal">
        <div class="w3-modal-content">
            <form method="post">
                <header class="w3-container"> 
                    <span onclick="document.getElementById('del_topic_modal').style.display='none'" class="w3-button w3-display-topright">&times;</span>
                    <h2>Are you sure you want to delete this topic?</h2>
                </header>
                <div class="w3-container">
                    <center><button type="submit" name="delTopic_btn" id="delTopic_btn">Yes, delete topic</button></center>
                </div>
                <footer class="w3-container">
                    
                </footer>
                <br>
            </form>
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
        var modal = document.getElementById('del_topic_modal');
        if(getQueryVariable("act") == "delt"){
            modal.style.display = "block";
        }
        // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }
    </script>

  <section id="topic">
    <div class="container bg-light header">
        <div><h1><?= $topic_title ?>&nbsp;<?php if($is_locked){echo '<sup><small><span class="glyphicon glyphicon-lock"></span></small></sup>';} ?><?php if($is_sticked){echo '&nbsp;<sup><small><span class="glyphicon glyphicon-pushpin"></span></small></sup>';} ?></h1> <?php if($is_archived){ echo '<p><span class="header" style="display:inline-block;background-color:#cc7139;color:rgb(255,255,255);border-radius:4px; padding: 0.6%;"><b>Archived</b></span></p>';} if($is_deleted){ echo '<p><span class="header" style="display:inline-block;background-color:#2e2e2e;color:rgb(255,255,255);border-radius:4px; padding: 0.6%;"><b>Deleted</b></span></p>';} ?></div>
        <hr>
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <?= $topic_content ?>
                <br>
                <p></p>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <p>By <a href='../../profile/?id=<?= $topic_creator ?>'><?= $c_tag_dis ?></a></p>
            </div>
        </div>
        <?php
        if($id == $topic_creator){
            echo '<div class="row">
                <div class="col">
                    <form method="post">
                        <p><button type="submit" name="editTopic_btn" class="btn"><b>Edit</b></button>
                            <button type="submit" name="removeTopic_btn" class="btn"><b>Remove</b></button></p>
                    </form>
                </div>
            </div>';
        }
        ?>
        <br>
    </div>
  </section>

  <section id="comment" class="bg-light box-shadow-top">
    <div class="container">
        <h2>Comments</h2>
        <br>
      <div class="row">
        <div class="col-lg-8 mx-auto">
            <?php
            if(in_array("comment", $user_arr['perms']) || in_array("*", $user_arr['perms'])) {
                if($is_locked == 0){
                    echo "<form method='post'><textarea name='comment_input' placeholder='Comment...' id='comment_input'>
                        </textarea><br><button type='submit' name='cmt_topic'>Comment</button></form>";
                }
                else{
                    echo "<input disabled class='locked' value='This topic is locked...'>";
                }
            }
            else{
                echo "<input disabled value='You need to be logged in to comment...'>";
            }
            ?>
        </div>
      </div>
        <script>
            ClassicEditor
            .create( document.querySelector( '#comment_input' ))
            .catch( error => {
                console.error( error );
            } );
    </script>
        <hr>
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <?php
                    $sql = "SELECT * FROM h1_posts WHERE deleted = '0' AND topic_id = '$topic_id' ORDER BY post_id DESC";
                    $result = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_array($result))
                        {
                            //echo "<script>alert(" . mysqli_num_rows($result) . ")</script>";
                            $post_id = $row["post_id"];
                            $post_content = $row["post_content"];
                            $post_creator = $row["post_creator"];
                            $u_sql = "SELECT * FROM h1_users WHERE id = '$post_creator'";
                            $u_result = mysqli_query($conn, $u_sql);
                            while($u_row = mysqli_fetch_array($u_result))
                            {
                                $u_group = $u_row["group"];
                                $u_name = $u_row["name"];
                                $g_sql = "SELECT * FROM h1_groups WHERE g_range = '$u_group'";
                                $g_result = mysqli_query($conn, $g_sql);
                                //print_r($result);
                                // output data of each row
                                if(mysqli_num_rows($g_result) > 0) {
                                    while($g_row = mysqli_fetch_array($g_result))
                                    {
                                        $g_perms = $g_row["permissions"];
                                        $raw_tag = $g_row["group_html"];
                                        $raw_tag = str_replace("%username%", $u_name, $raw_tag);
                                        $tag_dis = str_replace("%tag_owner%", $g_row["group_name"], $raw_tag);
                                        $comment_tools = "";
                                        if(isset($logged_in) == true) {
                                            if($id == $post_creator || in_array("*", $user_arr['perms']) || in_array("del_comment", $user_arr['perms'])) {
                                                $comment_tools = "<a class='nav-link' style='float:right;' href='del_comment.php?id={$post_id}'>Delete</a>";
                                            }
                                        }
                                        echo "<div class='row'>
                                            <div class='col-lg-8 mx-auto'>
                                                $comment_tools
                                                <h3><a href='../../profile/?id=$post_creator'>{$tag_dis}</a></h3>
                                                <br>
                                                <span>
                                                    $post_content
                                                </span>
                                                <hr>
                                            </div>
                                          </div>";
                                    }
                                }
                            }
                        }
                    }
                    else{
                        echo "<div class='row'>
                                    <div class='col-lg-8 mx-auto'>
                                        <h2>There seems to be no activity 😭</h2>
                                    </div>
                                  </div>";
                    }
                  ?>
            </div>
        </div>
    </div>
  </section>

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
  <script src="../../admin/js/sb-admin-2.min.js"></script>

</body>

</html>