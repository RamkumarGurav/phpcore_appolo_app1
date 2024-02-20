<?php
session_start();
//{--------------DB DETAILS--------------
require_once 'config.php';
//--------------------------------------------------}

if (isset($_SESSION["user"])) {
  header("Location: http://localhost/xampp/MARS/myPrj/welcome.php");
  exit();
}











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





try {
  // Create a PDO connection
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $server_username, $server_password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);






  //{--------------REGISTER--------------
  if (isset($_POST["register"])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $data = ["name" => $name, "email" => $email, "password" => $password];


    $inserted_id = createOne($conn, "user", $data);

    if (!$inserted_id) {
      echo "Unable to Register user <br>";
      exit;
    }

    $user = findOneByColumnName($conn, "user", 'id', $inserted_id);
    $_SESSION['user'] = ["name" => $user["name"], "email" => $user["email"]];
    $_SESSION["toast_message"] = "Successfully Registered";
    $_SESSION["toast_type"] = "text-bg-success";

    // Redirect the user to the welcome page after successfully adding album photos
    header("Location: http://localhost/xampp/MARS/myPrj/welcome.php");
    exit();


  }
  //--------------------------------------------------}


} catch (PDOException $e) {
  // Handle PDO exceptions
  echo "{$e->getMessage()} ";
}

//{--------------closing database connection--------------
$conn = null;
//--------------------------------------------------}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
    Register
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

<body>

  <?php
  require_once 'navbar.php';
  ?>
  <div class=" mx-auto mt-4 " style="max-width:500px;">
    <h2 class="text-center">Register</h2>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
      <div class="mb-3">
        <label for="name" class="form-label">name</label>
        <input type="text" class="form-control" id="name" name="name" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>

      <button type="submit" class="btn btn-primary" name="register">Register</button>
    </form>
  </div>




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

    $(document).ready(function () {
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

    })

  </script>
</body>

</html>