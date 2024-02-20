<?php
// Start the session to access session variables
session_start();
// Database connection
require_once 'config.php';


// Check if the user is not logged in, redirect to the login page
if (!isset($_SESSION['user'])) {
  header("Location: http://localhost/xampp/MARS/myPrj");
  exit();
}

// Logout logic
if (isset($_POST['logout'])) {
  // Destroy the session and redirect to the login page



  session_destroy();

  $_SESSION["toast_message"] = "Successfully Logged Out";
  $_SESSION["toast_type"] = "text-bg-success";
  header("Location: http://localhost/xampp/MARS/myPrj");
  exit();
}

// Check if the session variable for the album form is not set
if (!isset($_SESSION["isAddAlbumFormOpen"])) {
  // If not set, initialize it to 1
  $_SESSION["isAddAlbumFormOpen"] = 1;
}





try {
  // Create a PDO connection
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $server_username, $server_password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Retrieve years and albums data from the database
  $years = findAll($conn, "year");
  $albums = findAll($conn, "album");
  // Convert albums data to JSON for JavaScript usage
  $json_albums = json_encode($albums);

} catch (PDOException $e) {
  // Handle PDO exceptions
  echo "{$e->getMessage()} ";
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

function findAll($conn, $table_name)
{
  $sql = "SELECT * FROM $table_name";
  $stmt = $conn->prepare($sql);
  $result = $stmt->execute();
  if (!$result) {
    echo "failed to fetch records from table $table_name";
    exit;
  }
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

//--------------------------------------------------}

//{--------------INSERT ALBUM LOGIC--------------
if (isset($_POST['add_album'])) {
  try {
    // Define the table name
    $table_name = "album";

    // Retrieve data from the form submission
    $year_id = $_POST['year_id'];
    $album_name = $_POST['album_name'];

    // Prepare the data to be inserted into the database
    $data = ["year_id" => $year_id, "name" => $album_name];

    // Call the createOne function to insert data into the album table
    $album_id = createOne($conn, "album", $data);

    // Check if the album was successfully inserted
    if ($album_id) {
      // Retrieve the uploaded cover image details
      $file_name = $_FILES['cover_image']['name'];
      $file_tmp = $_FILES['cover_image']['tmp_name'];

      // Retrieve the fiscal year from the year table based on the year_id
      $yearRecord = findOneByColumnName($conn, "year", "id", $year_id);

      // Initialize fiscal year variable
      $fy = null;

      // Check if the fiscal year record exists
      if ($yearRecord) {
        // Assign the fiscal year to the variable
        $fy = $yearRecord["fiscal_year"];
      }

      // Move the uploaded cover image to the appropriate folder
      $movedImageName = moveImageToFolder($album_id, $fy, $file_name, $file_tmp, "uploads", "cover_images");

      // Prepare data for updating the album table with the cover image name
      $data = ["cover_image" => $movedImageName];

      // Update the album table with the cover image name
      $isTableUpdated = updateByColumnName($conn, "album", "id", $album_id, $data);

      // Check if the table update was successful
      if (!$isTableUpdated) {
        // Display error message if the table update failed
        echo "failed to update the album table <br>";
      }

      // Set the session variable to indicate that the add album form is open
      $_SESSION["isAddAlbumFormOpen"] = 1;

      $_SESSION["toast_message"] = "Successfully added the Album";
      $_SESSION["toast_type"] = "text-bg-success";

      // Redirect the user to the welcome page after successful album creation
      header("Location: http://localhost/xampp/MARS/myPrj/welcome.php");
      exit();
    } else {
      // Display error message if album creation failed
      echo "failed to create album  <br>";
    }
  } catch (PDOException $e) {
    // Catch any PDO exceptions and display error message
    echo $e->getMessage();
  }
}
//--------------------------------------------------}

//{--------------INSERT ALBUM IMAGE LOGIC--------------
if (isset($_POST['add_album_photo'])) {
  try {
    // Define the table name for storing images
    $table_name = "image";

    // Retrieve data from the form submission
    $data1 = $_POST; // Contains album data
    $data2 = $_FILES; // Contains image files

    // Initialize an array to store formatted data for each image
    $formattedData = array();

    // Iterate over album image names and format data
    foreach ($data1['album_image_name'] as $index => $imageName) {
      // Format data for each image
      $formattedData[] = array(
        'year_id' => $data1['year_id'], // Year ID associated with the album
        'album_id' => $data1['album_id'], // Album ID where the image belongs
        'album_image_name' => $imageName, // Name of the image
        'album_image' => array(
          'name' => $data2['album_image']['name'][$index], // Original filename of the image
          'type' => $data2['album_image']['type'][$index], // Mime type of the image
          'tmp_name' => $data2['album_image']['tmp_name'][$index], // Temporary filename of the image
          'error' => $data2['album_image']['error'][$index], // Error code of the image upload
          'size' => $data2['album_image']['size'][$index] // Size of the image
        )
      );
    }

    // Iterate over each formatted image data
    foreach ($formattedData as $item) {
      // Prepare data for creating a new image record without the image itself
      $dataForImageWithoutImage = ["album_id" => $item["album_id"], "name" => $item["album_image_name"]];

      // Retrieve year ID and image details from the formatted data
      $year_id = $item["year_id"];
      $file_name = $item['album_image']['name'];
      $file_tmp = $item['album_image']['tmp_name'];

      // Create a new image record in the database without the actual image
      $inserted_album_image_id = createOne($conn, $table_name, $dataForImageWithoutImage);

      // Check if the image record creation was successful
      if (!$inserted_album_image_id) {
        // Display error message if image creation failed
        echo "Failed to create album image  <br>";
        exit;
      }

      // Retrieve the fiscal year from the year table based on the year_id
      $yearRecord = findOneByColumnName($conn, "year", "id", $year_id);

      // Initialize fiscal year variable
      $fy = null;

      // Check if the fiscal year record exists
      if ($yearRecord) {
        // Assign the fiscal year to the variable
        $fy = $yearRecord["fiscal_year"];
      }

      // Move the uploaded image to the appropriate folder
      $movedImageName = moveImageToFolder($inserted_album_image_id, $fy, $file_name, $file_tmp, "uploads", "album_images");

      // Prepare data for updating the image table with the actual image name
      $dataForUpdate = ["album_image" => $movedImageName];

      // Update the image table with the actual image name
      $isTableUpdated = updateByColumnName($conn, "image", "id", $inserted_album_image_id, $dataForUpdate);

      // Check if the table update was successful
      if (!$isTableUpdated) {
        // Display error message if the table update failed
        echo "failed to update the image with id:$inserted_album_image_id <br>";
      }
    }

    // Set the session variable to indicate that the add album form is closed
    $_SESSION["isAddAlbumFormOpen"] = 0;
    $_SESSION["toast_message"] = "Successfully added the Album Image ";
    $_SESSION["toast_type"] = "text-bg-success";
    // Redirect the user to the welcome page after successfully adding album photos
    header("Location: http://localhost/xampp/MARS/myPrj/welcome.php");
    exit();
  } catch (PDOException $e) {
    // Catch any PDO exceptions and display error message
    echo $sql . "<br>" . $e->getMessage();
  }
}
//--------------------------------------------------}
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

  <?php require_once 'navbar.php' ?>

  <!-- <div class="container mx-auto mt-4">
    <h2>Welcome,
      <?= $_SESSION['user']['name'] ?>
    </h2>
    <form action="welcome.php" method="post">
      <button type="submit" class="btn btn-primary" name="logout">Logout</button>
    </form> -->


  </div>

  <div class="d-flex justify-content-center my-4 gap-2">
    <button id="addAlbumBtn" class="add-album btn btn-warning">+ Add Album</button>
    <button id="addAlbumImageBtn" class="add-album-image btn btn-warning ">+ Add Album Image</button>
  </div>

  <!-- if your form logic in the same page don't use mention the action attribute in the form -->
  <form id="addAlbumForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
    enctype="multipart/form-data"
    class="container <?= $_SESSION["isAddAlbumFormOpen"] === 1 ? "d-block" : "d-none " ?> mx-auto mt-4 border shadow px-5 py-5 position-relative "
    style="max-width:750px;">
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
      <div class="d-flex ">
        <input type="file" class="form-control me-2" id="cover_image" name="cover_image" style="width:300px;" required>
        <div id="coverImagePreviewContainer" class="d-flex justify-content-center  align-items-center  "
          style="height:38px;width:41px;"></div>
      </div>

    </div>





    <button type="submit" class="btn btn-primary" name="add_album">Submit</button>
  </form>



  <!-- if your form logic in the same page don't use mention the action attribute in the form -->
  <form id="addAlbumImageForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
    enctype="multipart/form-data"
    class="container <?= $_SESSION["isAddAlbumFormOpen"] === 1 ? "d-none" : "d-block " ?> mx-auto  border my-4 shadow px-5 py-3 position-relative "
    style="max-width:750px;">
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
          <div class="d-flex align-items-center justify-content-between ">
            <div class="">
              <input type="text" class="form-control" name="album_image_name[]" required placeholder="Image name">
            </div>
            <div class=" d-flex gap-2 align-items-center  justify-content-between ">
              <input type="file" class="form-control" name="album_image[]" class="album_image1" required>
              <div class="albumImagePreviewContainer  d-flex align-items-center  justify-content-center "
                style="width:41px;height:41px;"></div>
            </div>

            <button type=" button" class="btn btn-danger delete-image-field ms-3"><i
                class="fa-solid fa-trash"></i></button>
          </div>
        </div>
      </div>
      <div type="button" id="addImageButton" class="border text-muted text-center rounded-pill">Add New Line</div>
    </div>



    <button type="submit" class="btn btn-primary" name="add_album_photo">Submit</button>
  </form>


  <!-- Notification Toast -->
  <?php if (isset($_SESSION["toast_message"])): ?>
    <div id="myToast"
      class="toast fade   <?= $_SESSION["toast_type"] ?? "text-bg-primary" ?>  align-items-center mx-auto  position-fixed   border-0 z-2"
      style="bottom:40px;right:10px;min-width:300px;" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body w-100 d-flex justify-content-evenly">
          <?= $_SESSION["toast_message"] ?>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
            aria-label="Close"></button>
        </div>
      </div>

    </div>

    <?php $_SESSION["toast_message"] = null;
    $_SESSION["toast_type"] = null;
    ?>
  <?php endif; ?>




  <script>
    // Accessing the $json_albums variable inside JavaScript
    var albumsData = <?php echo $json_albums ?>;

    $(document).ready(function () {

      // Event listener for the year select element
      $("#year").change(function () {
        // Get the selected year_id
        var year_id = $(this).val();

        // Filter albums based on the selected year_id
        var filteredAlbums = albumsData.filter((album) => album.year_id == year_id);

        // Log the filtered albums to the console
        console.log(filteredAlbums);

        // Clear existing options in the album_id select element
        $("#album_id").empty();

        // Add new options based on the filtered albums
        $("#album_id").append('<option value=""></option>');
        $.each(filteredAlbums, function (index, album) {
          $("#album_id").append('<option value="' + album.id + '">' + album.name + '</option>');
        });
      });

      // Add event listener for image input change
      $('#cover_image').change(function (event) {
        // Get the selected file
        const file = event.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function (e) {
            const imagePreviewContainer = $('#coverImagePreviewContainer');
            const imgElement = $('<img>').attr('src', e.target.result)
              .addClass('img-fluid ')
              .css({ width: '38px', height: '38px', objectFit: 'cover' });
            imagePreviewContainer.empty(); // Clear previous image previews
            imagePreviewContainer.append(imgElement);
          };
          reader.readAsDataURL(file);
        }
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

      // Show Add Album Image Form
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
        // Reset image previews in the cloned field
        clonedField.find("img").attr("src", "").remove(); // Remove the src attribute to clear the image preview
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

      // Add event listener for image input change in Add Album Image Form
      $(document).on('change', '#addAlbumImageForm input[type="file"]', function (event) {
        const fileInput = $(this); // Get the file input element
        // Get the selected file
        const file = $(this)[0].files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function (e) {
            const imagePreviewContainer = fileInput.next('.albumImagePreviewContainer'); // Get the immediate sibling with class 'albumImagePreviewContainer'
            const imgElement = $('<img>').attr('src', e.target.result)
              .addClass('img-fluid')
              .css({ width: '38px', height: '38px', objectFit: 'cover' });
            imagePreviewContainer.empty(); // Clear previous image previews
            imagePreviewContainer.append(imgElement);
          };
          reader.readAsDataURL(file);
        }
      });



      // Code for Toast notificaions

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