<?php

class LoginModel
{


  private $db_conn;

  public $table_name = "user";

  public function __construct()
  {
    $root_path = substr(str_replace("\\", "/", dirname(__DIR__)), 0, -6);
    $this->db_conn = require $root_path . '/config.php';
  }

  public function findUser($email, $password)
  {




    try {

      $sql = "SELECT * FROM {$this->table_name} where email = :email_value AND password = :password_value";
      $stmt = $this->db_conn->prepare($sql);


      $stmt->bindValue(":email_value", $email, PDO::PARAM_STR);
      $stmt->bindValue(":password_value", $password, PDO::PARAM_STR);



      if ($stmt->execute()) {
        return $stmt->fetch(PDO::FETCH_ASSOC);

      } else {
        return false;
      }
    } catch (PDOException $e) {
      echo "{$e}->getMessage()} <br>";
    }
  }




}