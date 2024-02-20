<?php

$path = substr(str_replace("\\", "/", dirname(__DIR__)), 0, -6);
require $path . '/admin/model/LoginModel.php';



class LoginController
{
  public function login($email, $password)
  {
    $password = md5($password);



    $model = new LoginModel();
    $user = $model->findUser($email, $password);


    if ($user === false) {

      session_start();
      // $error_msg = "Invalid email or password. Please try again.";
      $_SESSION["toast_message"] = "Invalid email or password. Please try again.";
      $_SESSION["toast_type"] = "text-bg-danger";
      // Redirect the user to the welcome page after successfully adding album photos
      header("Location: http://localhost/xampp/MARS/myPrj/admin/index.php");
      exit();


    } else {

      session_start();
      $_SESSION['user'] = ["name" => $user["name"], "email" => $user["email"]];
      $_SESSION["toast_message"] = "Successfully Logged";
      $_SESSION["toast_type"] = "text-bg-success";

      // Redirect the user to the welcome page after successfully adding album photos
      header("Location: http://localhost/xampp/MARS/myPrj/admin/welcome.php");
      exit();
    }


  }


  public function logout()
  {

    // Destroy the session and redirect to the login page
    session_destroy();
    session_start();
    $_SESSION["toast_message"] = "Successfully Logged Out";
    $_SESSION["toast_type"] = "text-bg-success";
    header("Location: http://localhost/xampp/MARS/myPrj/admin");
    exit();
  }

  public function test()
  {

    echo "TEST TEST <br>";
    exit;
  }

}
