<?php
session_start();
if (isset($_SESSION["user"])) {
  header("Location: http://localhost/xampp/MARS/myPrj/admin/welcome.php");
  exit();
}

$root_path = str_replace("\\", "/", dirname(__DIR__));
include $root_path . "/admin/controller/LoginController.php";

if (isset($_POST["login"])) {
  $email = $_POST["email"];
  $password = $_POST["password"];



  $login_controller = new LoginController();
  $login_controller->login($email, $password);

}



?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
    Login
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
  require_once $root_path . '/admin/includes/navbar.php';
  ?>
  <div class=" mx-auto mt-4 " style="max-width:500px;">
    <h2 class="text-center">Login</h2>
    <!-- <?php if (isset($error_msg)): ?>
      <p class="bg-white p-2 text-danger   rounded ">Error:
        <?= $error_msg ?>
      </p>/xampp/MARS/myPrj/admin/i.php
    <?php endif; ?> -->
    <!-- <form action="/xampp/MARS/myPrj/admin/doLogin.php" method="post"> -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>

      <button type="submit" class="btn btn-primary" name="login">Login</button>
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