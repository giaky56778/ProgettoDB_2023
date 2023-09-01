<?php
  $path = explode('/',$_SERVER['REQUEST_URI']);
  $string="";
  for($i=0;$i<sizeof($path)-3;$i++){
    $string=$string."../";
  }
  if (isset($_SESSION['utente']) && isset($_SESSION['psw'])){
    $user = $_SESSION['utente'];
    $psw = $_SESSION['psw'];
  }else {
    $user = 'accesso';
    $psw = 'progettodatabase';
  }
  $conn = pg_connect("host=localhost port=5432 dbname=progetto user=$user password=$psw");
  if (!$conn) {
    session_start();
    session_destroy();
    header("Location: ../login.php");
  }
?>