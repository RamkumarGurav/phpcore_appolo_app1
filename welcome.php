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

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
    welcome
  </title>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <!-- <link rel="stylesheet" href="../public/example.css"> -->


</head>

<body>

  <div class="container mx-auto mt-4">
    <h2>Welcome,
      <?= $_SESSION['user']['name'] ?>
    </h2>
    <form action="welcome.php" method="post">
      <button type="submit" class="btn btn-primary" name="logout">Logout</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
</body>

</html>