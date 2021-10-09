<?php
  require 'vendor/autoload.php';
  use ISO8583\Protocol;
  use ISO8583\Message;
  date_default_timezone_set("Asia/Jakarta");
  $bank_Config = file_get_contents("bank_configuration.json");
  $decoded_bank_Config = json_decode($bank_Config);


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
      $tempData = tempLoad($decoderInput->RRN);
      $issuer = $tempData['Issuer'];
    }else //message 1
    {
      $STAN = createSTAN();
      $isoMessage = formToIsoMessage($input, $STAN, $bank_Config);
      $issuer = $decodedInput->issuer;
      $RRN = createRRN($STAN);
    }
    $issuer_host = $decoded_bank_Config->Bank->$issuer->host;
    $issuer_port = $decoded_bank_Config->Bank->$issuer->port;
    $response = sendMessage($issuer_host,$issuer_port,$IsoMessage);
    $response_ISO_message = new Message(new Protocol(),['lengthPrefix' => 0]);
    $response_ISO_message->unpack("$response");
    $response_code = $response_ISO_message->getField(39);

    if($response_code == "00")
    {
      if(isset($decodedInput->PVV)) //message 2
      {
        tempDelete($decodedInput->RRN);
        paymentVerification($decodedInput->RRN,$response);
      }
      else //message 1
      {
        tempSave($RRN,$decodedInput->CVV,$decodedInput->exp,$decodedInput->issuer,$decodedInput->cNumber);
        writePayment($decodedInput->username,$decodedInput->issuer,$isoMessage,$RRN,$STAN,200000);
      }
    }



    //  }
//  socket_close($spawn);
//  socket_close($socket);

  function writePayment(string $user, string $issuer, string $message, string $RRN, string $STAN, int $nominal)
  {
    if($stmt = $con->prepare("INSERT INTO log_pembayaran (username, nominal_pembayaran, SystemTraceAuditNumber, RetrievalReferenceNumber, Message, issuer) VALUES(?,?,?,?,?,?)"))
    {
      $stmt->bind_param('sissss',$user,$nominal,$STAN,$RRN,$message,$issuer);
      $stmt->execute();
      return true;
    }
    return false;
  }
  function paymentVerification($RRN, $Response)
  {
    $result = $con->query("UPDATE log_pembayaran SET ResponseMessage = $Response, PaymentStatus = 1 WHERE RetrievalReferenceNumber = $RRN;");
    return $result
  }
  function sendMessage(string $host, string $port, string $message)
  {
    $socket = socket_create(AF_INET, SOCK_STREAM, 0);
    $result = socket_connect($socket, $host, $port);
    socket_write($socket, $message, strlen($message));
    $out = socket_read($socket,2048);
    socket_close($socket);
    return $out;
  }
  function tempSave(string $RRN, string $CVV, string $exp, string $issuer, string $cNumber)
  {
    if($stmt = $con->prepare("INSERT INTO temporary (RRN, CVV, exp, cnumber, Issuer) VALUES(?,?,?,?,?);"))
    {
      $stmt->bind_param('sssss',$RRN,$CVV,$exp,$issuer,$cNumber);
      $stmt->execute();
      return true;
    }
    return false;
  }
  function tempLoad(string $RRN)
  {
    if($result = $con->query("SELECT RRN,CVV,exp,cnumber,Issuer FROM temporary WHERE RRN = $RRN;"))
    {
      if($result->num_rows > 0)
      {
        $firstRow = $result->fetch_assoc();
        return $firstRow;
      }
    }
  }
  function tempDelete(string $RRN)
  {
    $con->query("DELETE FROM temporary WHERE RRN = $RRN;");
  }





?>
