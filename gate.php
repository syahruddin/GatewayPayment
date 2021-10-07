<?php

    $port = 80;
    $host = "localhost";
    if($_SERVER["REQUEST_METHOD"] == "POST")
    {
      if(isset($_POST['username']))
      {
        //ubah data form jadi json
        echo json_encode($_POST);
        $message = new stdclass();
        if(isset($_POST['username'])){$message->username =  $_POST['username'];}
        if(isset($_POST['cnumber'])){$message->cnumber =  $_POST['cnumber'];}
        if(isset($_POST['exp'])){$message->exp =  $_POST['exp'];}
        if(isset($_POST['cvc'])){$message->cvc =  $_POST['cvc'];}
        if(isset($_POST['owner'])){$message->owner =  $_POST['owner'];}
        $messageJson = json_encode($message);

        $sock = socket_create(AF_INET, SOCK_STREAM, 0) or die("could not create socket\n");

        $result = socket_connect($sock, $host, $port) or die("could not connect to server\n");

        socket_write($sock,$messageJson,strlen($messageJson)) or die("could not send data to server\n");
        socket_close($sock);
      }
      else if(isset($_POST['PVV']))
      {
        $message = new stdclass();
        if(isset($_POST['PVV'])){$message->cvc =  $_POST['PVV'];}
        if(isset($_POST['RRN'])){$message->owner =  $_POST['RRN'];}
        $messageJson = json_encode($message);
        $sock = socket_create(AF_INET, SOCK_STREAM, 0) or die("could not create socket\n");

        $result = socket_connect($sock, $host, $port) or die("could not connect to server\n");

        socket_write($sock,$messageJson,strlen($messageJson)) or die("could not send data to server\n");
        socket_close($sock);
      }
      else if(isset($_POST['RRN']))
      {
        $RRN = $_POST['RRN'];
        include __DIR__ . '/Auth.php';
      }
    }else
    {
      include __DIR__ . '/gate_frontend.php';
    }


?>
