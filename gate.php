<?php
  include __DIR__ . '/socket_address.php';
  if($_SERVER["REQUEST_METHOD"] == "POST")
  {
    //ubah data form jadi json
    echo json_encode($_POST);
    $message = new stdclass();
    if(isset($_POST['username'])){$message->username =  $_POST['username'];}
    if(isset($_POST['cnumber'])){$message->cnumber =  $_POST['cnumber'];}
    if(isset($_POST['exp'])){$message->exp =  $_POST['exp'];}
    if(isset($_POST['cvc'])){$message->CVV =  $_POST['cvc'];}
    if(isset($_POST['owner'])){$message->owner =  $_POST['owner'];}
    if(isset($_POST['issuer'])){$message->issuer =  $_POST['issuer'];}
    $messageJson = json_encode($message);

    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("could not create socket\n");
    $result = socket_connect($sock, $host, $port) or die("could not connect to server\n");
    socket_write($sock,$messageJson,strlen($messageJson)) or die("could not send data to server\n");
    $response = socket_read($sock, 2048);
    socket_close($sock);

    if($response == "00")
    {
      header("location: success.php",true,301);
      exit();
    }
    else
    {
      header("location: failed.php",true,301);
      exit();
    }
  }
  else
  {
    include __DIR__ . '/gate_frontend.php';
  }

?>
