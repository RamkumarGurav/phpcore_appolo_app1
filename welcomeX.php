<?php

session_start();


if (!isset($_SESSION['user'])) {
  header("Location: http://localhost/xampp/MARS/myPrj");
  exit();
}



if (isset($_POST['logout'])) {
  session_destroy();
  header("Location: http://localhost/xampp/MARS/myPrj");
  exit();
}
?>


<?php
$servername = "localhost";
$server_username = "root";
$server_password = "";
$dbname = "appolo_album_db";

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $server_username, $server_password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $sql1 = "SELECT *  FROM year";
  $stmt1 = $conn->prepare($sql1);
  $sql2 = "SELECT  *  FROM album";
  $stmt2 = $conn->prepare($sql2);


  $result1 = $stmt1->execute();
  $years = $stmt1->fetchAll(PDO::FETCH_ASSOC);

  $result2 = $stmt2->execute();
  $albums = $stmt2->fetchAll(PDO::FETCH_ASSOC);





} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
}

$conn = null;

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
    welcome
  </title>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://getbootstrap.com/docs/5.3/assets/css/docs.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    :root {
      box-sizing: border-box;
    }

    .bg-green {
      background-color: green;
    }
  </style>


</head>

<body class="pb-4">

  <?php require_once 'navbar.php' ?>

  <div class="container mx-auto mt-4">
    <h2>Welcome,
      <?= $_SESSION['user']['name'] ?>
    </h2>
    <form action="welcome.php" method="post">
      <button type="submit" class="btn btn-primary" name="logout">Logout</button>
    </form>

    <button type="button" class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#addAlbumPhotoModal">
      Add Album Photos
    </button>
  </div>

  <form action="welcome.php" method="post" class="container mx-auto mt-4 border shadow form p-2 px-4"
    style="max-width:600px;">
    <div class="mb-3">
      <label for="year_id" class="form-label">Year</label>
      <select class="form-select" id="year_id" name="year_id" required>
        <option value="">
        </option>
        <?php foreach ($years as $year): ?>

          <option value="<?= $year["id"] ?>">
            <?= $year["fiscal_year"] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="album_id" class="form-label">Album Name</label>
      <select class="form-select" id="album_id" name="album_id" required>
        <option value="">
        </option>
        <?php foreach ($albums as $album): ?>

          <option value="<?= $album["id"] ?>">
            <?= $album["name"] ?>
          </option>
        <?php endforeach; ?>

      </select>
    </div>
    <div class="mb-3">
      <label for="album_image" class="form-label">Image URL</label>
      <input type="text" class="form-control" id="album_image" name="album_image" required>
    </div>
    <div class="mb-3">
      <label for="album_image_name" class="form-label">Image Name</label>
      <input type="text" class="form-control" id="album_image_name" name="album_image_name" required>
    </div>
    <button type="submit" class="btn btn-primary" name="add_album_photo">Submit</button>
  </form>


  <!-- Notification Toast -->
  <?php if (isset($_SESSION["toast"])): ?>

    <div id="myToast"
      class="toast fade   <?= $_SESSION["toast"]["toastType"] ?? "text-bg-primary" ?>  align-items-center mx-auto  position-fixed   border-0 z-2"
      style="bottom:40px;right:10px;min-width:300px;" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body w-100 d-flex justify-content-evenly">
          <?= $_SESSION["toast"]["message"] ?>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
            aria-label="Close"></button>
        </div>
      </div>

    </div>
  <?php endif; ?>

  <script>
    $(document).ready(function () {
      $("#myToast").toast({
        animation: true
      });

      // Get the toast element
      var toastElement = $("#myToast");
      // Add "hide" class to the toast after 2 seconds
      setTimeout(function () {
        toastElement.addClass("show");
      }, 700);
      setTimeout(function () {
        toastElement.addClass("hide");
        toastElement.removeClass("show")
      }, 4500);

    });


  </script>
</body>

</html>


<?php


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $servername = "localhost";
  $server_username = "root";
  $server_password = "";
  $dbname = "appolo_album_db";

  try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $server_username, $server_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



    $table_name = "image";
    $data = ["album_id" => $_POST['album_id'], "album_image" => $_POST['album_image'], "name" => $_POST["album_image_name"]];

    $columns = implode(",", array_keys($data));
    $placeholders = implode(",", array_fill(0, count($data), "?"));
    $sql = "INSERT INTO $table_name ($columns) VALUES($placeholders)";


    $stmt = $conn->prepare($sql);

    $i = 1;
    foreach ($data as $value) {
      $type = PDO::PARAM_STR; // Default type is string
      if (is_bool($value)) {
        $type = PDO::PARAM_BOOL;
      } elseif (is_int($value)) {
        $type = PDO::PARAM_INT;
      } elseif (is_null($value)) {
        $type = PDO::PARAM_NULL;
      }
      $stmt->bindValue($i, $value, $type);
      $i++;
    }

    if ($stmt->execute()) {
      // echo $conn->lastInsertId();
      echo "success <br>";
      $_SESSION["toast"] = ["message" => "Successfully Added Album Imgage", "toastType" => "text-bg-success"];
    } else {
      $_SESSION["toast"] = ["message" => "Unable execute Query", "toastType" => "text-bg-danger"];
    }



  } catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }

  $conn = null;
}

?>




