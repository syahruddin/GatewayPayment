<?php
  require 'vendor/autoload.php';
  use ISO8583\Protocol;
  use ISO8583\Message;
  date_default_timezone_set("Asia/Jakarta");
  $bank_Config = file_get_contents("bank_configuration.json");


  $DATABASE_HOST = 'localhost';
  $DATABASE_USER = 'root';
  $DATABASE_PASS = '';
  $DATABASE_NAME = 'pembayaran';
  $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

  require __DIR__ . '/ISO8583_Converter.php';
  require __DIR__ . '/message.php';

  set_time_limit(0);
  $port = 80;
  $host = "localhost";
  $sock = socket_create(AF_INET, SOCK_STREAM, 0) or die("could not create socket\n");

  $result = socket_bind($sock, $host, $port) or die("could not connect to socket\n");

//    while(true) {
    $result = socket_listen($sock, 3) or die("could not set up socket listener\n");

    $spawn = socket_accept($sock) or die("could not accept incoming connection\n");

    $input = socket_read($spawn, 1024) or die("could not read input");
    echo $input;
    $decodedInput = json_decode($input);
    if(isset($decodedInput->PVV)) //message 2
    {
      $isoMessage = searchByRRN($decodedInput->RRN);
      $isoMessage = addTrackTwo($isoMessage);
    }else //message 1
    {
      $STAN = createSTAN();
      $isoMessage = formToIsoMessage($input, $STAN, $bank_Config);
    }


    //  }
  socket_close($spawn);
//  socket_close($socket);

  function writePayment(string $user)
  {
    if($stmt = $con->prepare("INSERT INTO log_pembayaran (username, nominal_pembayaran) VALUES(?,?)"))
    {
      $stmt->bind_param('si',$user,100000);
      $stmt->execute();
      return true;
    }
    return false;
  }




?>
