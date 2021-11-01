<?php
include "ISO8583-JSON-XML/php-version/lib/RoyISO8583.php";
error_reporting(E_ALL);
ob_implicit_flush();

require __DIR__ . '/message.php';

set_time_limit(0);
$host = "localhost";
$port = "9000";
$alwaysAccept = true;

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
      realtimeDebug("===========================================================================");
      $input = socket_read($client, 1024) or die("could not read input");
      realtimeDebug("read: $input");
      $inputIsoMessage = new RoyISO8583();
      $inputIsoMessage->setISO($input);
      realtimeDebug("Received Message:");
      printMessage($inputIsoMessage);

      realtimeDebug("---------------------------------------------------------------------------");
      realtimeDebug("Reply Message:");
      $reply = createreply($inputIsoMessage, $alwaysAccept);
      printMessage($reply);;
      $isoReply = $reply->getISO();


      socket_write($client,$isoReply);
      unset($clients[$key]);
      socket_close($client);
      realtimeDebug("===========================================================================");
    }
  }
}while(true);

function printMessage(RoyISO8583 $message)
{
  realtimeDebug("MTI: $message->type");
  foreach($message->getData() as $key=>$val)
  {
    realtimeDebug("$key: $val");
  }
}
function createreply(RoyISO8583 $message, bool $accept)
{
  $reply = new RoyISO8583();
  $reply->setType("0110");
  foreach($message->getData() as $key=>$val)
  {
    if($key != 64) $reply->addData("$key","$val");
  }
  if($accept)
  {
    $reply->addData("39","00");
  }
  else
  {
    $reply->addData("39","55");
  }
  createMAC($reply);
  return $reply;
}

?>
