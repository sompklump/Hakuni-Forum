<?php
session_start();
$logged_in = $_SESSION["logged_in"];
$id = $_SESSION["id"];
$uname = $_SESSION["uname"];
$uuid = $_SESSION["uuid"];
$power = 0;

require("../php/msql.php");

$web_title = null;
$sql = "SELECT * FROM h1_settings WHERE var = 'glob_name'";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_array($result))
    {
        $web_title = $row['value'];
    }
}

# GET CURRENT PAGE, SO WHEN LOGGING IN THEY REDIRECT HERE
$cur_link = null;
$cur_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$log_name = "Log in";
$log_href = "../login/?redirect=".urlencode($cur_link);
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
    $log_href = "../login/logout.php";
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
    <!-- AdSense -->
    <script data-ad-client="ca-pub-3929669368640587" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../assets/favicon.ico" type="image/x-icon">
    <title><?= $web_title ?> Forum</title>
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
        <a class="navbar-brand js-scroll-trigger" href="../profile/?id=<?= $id ?>"><?= $uname ?></a>
      <a class="navbar-brand js-scroll-trigger" href="#page-top">Forum</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="<?= $log_href ?>"><?= $log_name ?></a>
          </li>
        </ul>
      </div>
    </div>
      <script>
      var keynum, lines = 1;

      function limitLines(obj, e) {
        // IE
        if(window.event) {
          keynum = e.keyCode;
        // Netscape/Firefox/Opera
        } else if(e.which) {
          keynum = e.which;
        }

        if(keynum == 13) {
          if(lines == obj.rows) {
            return false;
          }else{
            lines++;
          }
        }
      }
      </script>
  </nav>
    
  <div class="dark-bga">
        <section class="header-in">
                    <?php
                    $sql = "SELECT * FROM h1_parents ORDER BY parent_order ASC";
                    $result = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_array($result))
                        {
                            $parent_id = $row["parent_id"];
                            $parent_name = $row["parent_name"];
                            echo "<div class='container'>
                                    <div class='row p-title'><h1>$parent_name</h1></div>
                                </div>
                            <div class='container header light-bg'>";
                            $forum_sql = "SELECT * FROM h1_forums WHERE parent_id = '$parent_id' ORDER BY forum_order ASC";
                            $forum_result = mysqli_query($conn, $forum_sql);
                            if(mysqli_num_rows($forum_result) > 0) {
                                while($forum_row = mysqli_fetch_array($forum_result))
                                {
                                    $forum_id = $forum_row["forum_id"];
                                    $forum_title = $forum_row["forum_title"];
                                    $forum_desc = $forum_row["forum_description"];
                                    echo "<div class='row'>
                                            <div class='col-lg-8 mx-auto'>
                                                <a href='forum.php?id={$forum_id}'><b><h2>{$forum_title}</h2></b></a>
                                                <div class='header'><p>$forum_desc</p></div>
                                                <hr>
                                            </div>
                                        </div>";
                                }
                            }
                            else{
                                echo "<div class='row'>
                                                <div class='col-lg-8 mx-auto'>
                                                    <h2>There seems to be no activity 😭</h2>
                                                </div>
                                              </div>";
                            }
                            echo "</div><br>";
                        }
                    }
                    ?>
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
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Plugin JavaScript -->
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Custom JavaScript for this theme -->
  <script src="js/scrolling-nav.js"></script>

</body>

</html>
