<?php
include "ISO8583-JSON-XML/php-version/lib/RoyISO8583.php";
error_reporting(E_ALL);
ob_implicit_flush();

require __DIR__ . '/message.php';

set_time_limit(0);
$host = "localhost";
$port = "9000";

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
      unset($clients[$key]);
      socket_close($client);
    }
  }

}while(true);
 ?>
