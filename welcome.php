<?php
session_start();



// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user'])) {
  header("Location: http://localhost/xampp/MARS/myPrj");
  exit();
}

// Logout logic
if (isset($_POST['logout'])) {
  session_destroy();
  header("Location: http://localhost/xampp/MARS/myPrj");
  exit();
}

// Database connection
$servername = "localhost";
$server_username = "root";
$server_password = "";
$dbname = "appolo_album_db";

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $server_username, $server_password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Fetch years and albums from database
  $sql1 = "SELECT * FROM year";
  $stmt1 = $conn->prepare($sql1);
  $result1 = $stmt1->execute();
  $years = $stmt1->fetchAll(PDO::FETCH_ASSOC);

  $sql2 = "SELECT * FROM album";
  $stmt2 = $conn->prepare($sql2);
  $result2 = $stmt2->execute();
  $albums = $stmt2->fetchAll(PDO::FETCH_ASSOC);
  $json_albums = json_encode($albums);
  // echo "$json_albums <br>";
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
}


//{--------------HELPERS--------------




function moveImageToFolder($id, $fy, $file_name, $file_tmp, $root_uplaods_folder, $destination_folder)
{
  // Check if the "uploads" folder exists, if not, create it
  if (!file_exists($root_uplaods_folder)) {
    mkdir($root_uplaods_folder);
  }


  // Create "album" folder inside the "uploads" folder
  $album_folder = $root_uplaods_folder . "/album";
  if (!file_exists($album_folder)) {
    mkdir($album_folder);
  }

  // Create "fiscal_year" folder inside the "album" folder
  $fiscal_year_folder = $album_folder . "/" . $fy;
  if (!file_exists($fiscal_year_folder)) {
    mkdir($fiscal_year_folder);
  }

  // Create "cover_images" folder inside the "fiscal_year" folder
  $cover_images_folder = $fiscal_year_folder . "/cover_images";
  if (!file_exists($cover_images_folder)) {
    mkdir($cover_images_folder);
  }

  // Create "album_images" folder inside the "fiscal_year" folder
  $album_images_folder = $fiscal_year_folder . "/album_images";
  if (!file_exists($album_images_folder)) {
    mkdir($album_images_folder);
  }


  // Get file extension
  $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

  // New filename based on album ID
  $new_filename = $id . '.' . $file_extension;


  if ($destination_folder === "cover_images") {

    // Move the uploaded image to the "uploads" folder
    move_uploaded_file($file_tmp, $cover_images_folder . '/' . $new_filename);
  }



  if ($destination_folder === "album_images") {

    // Move the uploaded image to the "uploads" folder
    move_uploaded_file($file_tmp, $album_images_folder . '/' . $new_filename);
  }

  return $new_filename;
}

//--------------------------------------------------}


//{--------------DATABASA ACTIONS--------------



function createOne($conn, $table_name, $data)
{

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
    return $conn->lastInsertId();
  } else {
    return false;
  }
}

function updateByColumnName($conn, $table_name, $columnName, $columnValue, $data)
{

  $sql = "UPDATE $table_name ";

  $assignments = array_keys($data);
  array_walk($assignments, function (&$value) {
    $value = "$value = ?";
  });

  $sql .= "SET " . implode(", ", $assignments) . " WHERE $columnName = ?";



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

  $type = PDO::PARAM_STR; // Default type is string
  if (is_bool($columnValue)) {
    $type = PDO::PARAM_BOOL;
  } elseif (is_int($columnValue)) {
    $type = PDO::PARAM_INT;
  } elseif (is_null($columnValue)) {
    $type = PDO::PARAM_NULL;
  }

  $stmt->bindValue($i, $columnValue, $type);

  return $stmt->execute();
}

function findOneByColumnName($conn, $table_name, $columnName, $columnValue)
{

  $sql = "SELECT * FROM $table_name where $columnName=:c_value";
  $stmt = $conn->prepare($sql);

  $type = PDO::PARAM_STR; // Default type is string
  if (is_bool($columnValue)) {
    $type = PDO::PARAM_BOOL;
  } elseif (is_int($columnValue)) {
    $type = PDO::PARAM_INT;
  } elseif (is_null($columnValue)) {
    $type = PDO::PARAM_NULL;
  }
  $stmt->bindValue(":c_value", $columnValue, $type);

  if ($stmt->execute()) {
    return $stmt->fetch(PDO::FETCH_ASSOC);

  } else {
    return false;
  }

}



//--------------------------------------------------}

// Insert album  logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_album'])) {
  try {
    // Insert image into image table
    $table_name = "album";
    $year_id = $_POST['year_id'];
    $album_name = $_POST['album_name'];
    $data = ["year_id" => $year_id, "name" => $album_name];

    $album_id = createOne($conn, "album", $data);
    if ($album_id) {

      // Retrieve the uploaded image details
      $file_name = $_FILES['cover_image']['name'];
      $file_tmp = $_FILES['cover_image']['tmp_name'];
      $yearRecord = findOneByColumnName($conn, "year", "id", $year_id);

      $fy = null;
      if ($yearRecord) {
        $fy = $yearRecord["fiscal_year"];
      }

      $movedImageName = moveImageToFolder($album_id, $fy, $file_name, $file_tmp, "uploads", "cover_images");

      $data = ["cover_image" => $movedImageName];

      $isTableUpdated = updateByColumnName($conn, "album", "id", $album_id, $data);

      if (!$isTableUpdated) {

        echo "failed to update the album table <br>";
      }





      header("Location: welcome.php");
      exit();
    } else {
      // Set error message
      $toast = ["message" => "Successfully Added Album Image", "toastType" => "text-bg-success"];
    }
  } catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }
}

// Insert album photo logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_album_photo'])) {
  // $toast = null;
  try {
    // Insert image into image table
    $table_name = "image";
    $year_id = $_POST["year_id"];
    $album_id = $_POST["album_id"];
    $albub_images = $_POST["album_image_name"];
    $data = ["album_id" => $_POST['album_id'], "name" => $_POST["album_image_name"]];

    print_r($_POST);
    print_r($_FILES);
    exit;
    $inserted_album_image_id = createOne($conn, $table_name, $data);
    if (!$inserted_album_image_id) {
      echo "Failed to create album image  <br>";
      exit;
    }


    // Retrieve the uploaded image details
    $file_name = $_FILES['album_image']['name'];
    $file_tmp = $_FILES['album_image']['tmp_name'];
    $yearRecord = findOneByColumnName($conn, "year", "id", $year_id);

    $fy = null;
    if ($yearRecord) {
      $fy = $yearRecord["fiscal_year"];
    }

    $movedImageName = moveImageToFolder($inserted_album_image_id, $fy, $file_name, $file_tmp, "uploads", "album_images");

    $data = ["album_image" => $movedImageName];

    $isTableUpdated = updateByColumnName($conn, "image", "id", $inserted_album_image_id, $data);

    if (!$isTableUpdated) {

      echo "failed to update the album table <br>";
    }

    header("Location: welcome.php");
    exit();


  } catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }
}
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
  <script src="https://kit.fontawesome.com/4fa732e726.js" crossorigin="anonymous"></script>

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

<body class="pb-4" style="min-height:100vh;">

  <!-- <?php require_once 'navbar.php' ?> -->

  <div class="container mx-auto mt-4">
    <h2>Welcome,
      <?= $_SESSION['user']['name'] ?>
    </h2>
    <form action="welcome.php" method="post">
      <button type="submit" class="btn btn-primary" name="logout">Logout</button>
    </form>


  </div>

  <div class="d-flex justify-content-center my-4 gap-2">
    <button id="addAlbumBtn" class="add-album btn btn-warning">+ Add Album</button>
    <button id="addAlbumImageBtn" class="add-album-image btn btn-warning ">+ Add Album Image</button>
  </div>


  <form id="addAlbumForm" action="welcome.php" method="post" enctype="multipart/form-data"
    class="container d-block mx-auto mt-4 border shadow px-5 py-5 position-relative " style="max-width:600px;">
    <h3 class="text-muted text-center">Add Album </h3>
    <button id="albumCloseBtn" class=" btn fw-bold  rounded position-absolute p-0" style="top:10px;right:20px;"><i
        class="fa-solid fa-xmark"></i></button>
    <div class="mb-3">
      <label for="year_id" class="form-label"> Year</label>
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
      <label for="album_name" class="form-label">Album Name</label>
      <input type="text" class="form-control" id="album_name" name="album_name" required>
    </div>
    <div class="mb-3">
      <label for="cover_image" class="form-label">Cover Image</label>
      <input type="file" class="form-control" id="cover_image" name="cover_image" required>
      <!-- <div id="coverImageHelp" class="form-text">Choose a cover image for your album.</div> -->

    </div>





    <button type="submit" class="btn btn-primary" name="add_album">Submit</button>
  </form>

  <form id="addAlbumImageForm" action="welcome.php" method="post" enctype="multipart/form-data"
    class="container d-none mx-auto  border my-4 shadow px-5 py-3 position-relative " style="max-width:600px;">
    <h3 class="text-muted text-center">Add Album Image</h3>
    <button id="albumImageCloseBtn" class=" btn btn-white fw-bold  rounded position-absolute p-0"
      style="top:10px;right:20px;"><i class="fa-solid fa-xmark"></i></button>
    <div class="mb-3">
      <label for="year" class="form-label"> Year</label>
      <select class="form-select" id="year" name="year_id" required>
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



    <div class=" mb-3 p-1">
      <p class="">Upload Images</p>
      <div id="imageUploadFields">
        <div class="mb-3 image-upload-field">
          <div class="d-flex align-items-center">
            <div class="me-auto">
              <input type="text" class="form-control" name="album_image_name[]" required placeholder="Image name">
            </div>
            <div class="ms-3">
              <input type="file" class="form-control" name="album_image[]" required>
            </div>
            <button type="button" class="btn btn-danger delete-image-field ms-3"><i
                class="fa-solid fa-trash"></i></button>
          </div>
        </div>

      </div>
      <div type="button" id="addImageButton" class="border text-muted text-center rounded-pill">Add New Line</div>
    </div>



    <button type="submit" class="btn btn-primary" name="add_album_photo">Submit</button>
  </form>


  <!-- Notification Toast -->
  <?php if (isset($toast)): ?>

    <div id="myToast"
      class="toast fade   <?= $toast["toastType"] ?? "text-bg-primary" ?>  align-items-center mx-auto  position-fixed   border-0 z-2"
      style="bottom:40px;right:10px;min-width:300px;" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body w-100 d-flex justify-content-evenly">
          <?= $toast["message"] ?>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
            aria-label="Close"></button>
        </div>
      </div>

    </div>
  <?php endif; ?>

  <script>
    // Accessing the $json_albums variable inside JavaScript
    var albumsData = <?php echo $json_albums; ?>;



    // Logging the albums data
    console.log(albumsData);


    $(document).ready(function () {
      // Event listener for the year select element
      $("#year").change(function () {
        // Get the selected year_id
        var year_id = $(this).val();

        var filteredAlbums = albumsData.filter((album) => album.year_id == year_id);

        console.log(filteredAlbums);


        // Clear existing options
        $("#album_id").empty();

        // Add new options based on the response
        $("#album_id").append('<option value=""></option>');
        $.each(filteredAlbums, function (index, album) {
          $("#album_id").append('<option value="' + album.id + '">' + album.name + '</option>');
        });

      });


      // Show Add Album Form
      $("#addAlbumBtn").click(function () {
        $("#addAlbumForm").addClass("d-block");
        $("#addAlbumForm").removeClass("d-none");
        $("#addAlbumImageForm").removeClass("d-block");
        $("#addAlbumImageForm").addClass("d-none");
      });

      // Close Form
      $("#albumCloseBtn").click(function () {
        $("#addAlbumForm").removeClass("d-block");
        $("#addAlbumForm").addClass("d-none");
      });


      // Show Add Album Form
      $("#addAlbumImageBtn").click(function () {
        $("#addAlbumImageForm").addClass("d-block");
        $("#addAlbumImageForm").removeClass("d-none");
        $("#addAlbumForm").removeClass("d-block");
        $("#addAlbumForm").addClass("d-none");
      });

      // Close Form
      $("#albumImageCloseBtn").click(function () {
        $("#addAlbumImageForm").removeClass("d-block");
        $("#addAlbumImageForm").addClass("d-none");
      });




      // Add Image button click event
      $("#addImageButton").click(function () {
        // Clone the first image upload field and append it to the imageUploadFields div
        var clonedField = $(".image-upload-field").first().clone();
        $("#imageUploadFields").append(clonedField);
        // Clear the input values in the cloned field
        clonedField.find("input[type='text']").val("");
        clonedField.find("input[type='file']").val("");
        // Add delete button to the cloned field
        clonedField.find(".delete-image-field").removeClass("d-none");
      });

      // Delete Image button click event
      $("#imageUploadFields").on("click", ".delete-image-field", function () {
        // Check if there is only one image box left
        if ($(".image-upload-field").length > 1) {
          // Remove the parent div of the delete button
          $(this).closest(".image-upload-field").remove();
        }
      });



    });

  </script>
</body>

</html>