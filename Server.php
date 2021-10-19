<?php
  include "ISO8583-JSON-XML/php-version/lib/RoyISO8583.php";

  date_default_timezone_set("Asia/Jakarta");
  $bank_Config = file_get_contents("bank_configuration.json");
  $decoded_bank_Config = json_decode($bank_Config);

  error_reporting(E_ALL);
  ob_implicit_flush();

  $DATABASE_HOST = 'localhost';
  $DATABASE_USER = 'root';
  $DATABASE_PASS = '';
  $DATABASE_NAME = 'pembayaran';
  $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

  require __DIR__ . '/message.php';
  include __DIR__ . '/socket_address.php';

  set_time_limit(0);


  realtimeDebug("creating socket...");
  $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("could not create socket\n");
  realtimeDebug("binding socket to $host:$port...");
  socket_bind($sock, $host, $port) or die("could not connect to socket \n");
  realtimeDebug("listening...");
  socket_listen($sock, 5) or die("could not set up socket listener\n");

  $clients = array();
  do
  {
    $read = array();
    $read[] = $sock;
    $read =  array_merge($read,$clients);

    if(socket_select($read, $write, $except, $tv_sec = 5) < 1)
    {
      continue;
    }

    if(in_array($sock, $read))
    {
      $msgsock = socket_accept($sock);
      $clients[] = $msgsock;
      $key = array_keys($clients, $msgsock);
    }
    foreach ($clients as $key => $client)
    {
      if(in_array($client,$read))
      {

        $input = socket_read($client, 1024) or die("could not read input");
        realtimeDebug("read: $input");
        $decodedInput = json_decode($input);

        $STAN = createSTAN($con);
        $isoMessage = formToIsoMessage($input, $STAN, $bank_Config);
        realtimeDebug("Message Created: $isoMessage");
        $issuer = $decodedInput->issuer;
        $RRN = createRRN($STAN);

        $issuer_host = $decoded_bank_Config->Bank->$issuer->host;
        $issuer_port = $decoded_bank_Config->Bank->$issuer->port;

        realtimeDebug("Sending Message to Issuer...");
        $response = sendMessage($issuer_host,$issuer_port,$isoMessage);

        realtimeDebug("Message Received: $response");
        $response_ISO_message = new RoyISO8583();
        $response_ISO_message->setISO($response);
        $response_code = $response_ISO_message->getData()[39];

        if($response_code == "00")
        {
          writePayment($decodedInput->username,$decodedInput->issuer,$isoMessage,$RRN,$STAN,200000,$response,$con);
        }
        socket_write($client, $response_code,strlen($response_code));
        unset($clients[$key]);
        socket_close($client);
      }
    }

  }while(true);

  function writePayment(string $user, string $issuer, string $message, string $RRN, string $STAN, int $nominal, string $response , mysqli $con)
  {
    if($stmt = $con->prepare("INSERT INTO log_pembayaran (username, nominal_pembayaran, SystemTraceAuditNumber, RetrievalReferenceNumber, Message, issuer, ResponseMessage) VALUES(?,?,?,?,?,?,?)"))
    {
      $stmt->bind_param('sisssss',$user,$nominal,$STAN,$RRN,$message,$issuer,$response);
      $stmt->execute();
      return true;
    }
    return false;
  }

  function sendMessage(string $host, string $port, string $message)
  {
    $socket = socket_create(AF_INET, SOCK_STREAM, 0);
    $result = socket_connect($socket, $host, $port);

    socket_write($socket, addHeader($message));
    $out = socket_read($socket,2048);
    socket_close($socket);
    return $out;
  }
  function addHeader(string $message)
  {
    $temp = $message;
    $temp = mb_strlen($message,'8bit');
    return $temp;
  }
  function removeHeader(string $message)
  {

  }



?>
