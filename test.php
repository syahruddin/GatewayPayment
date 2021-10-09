<?php
  $socket = socket_create(AF_INET, SOCK_STREAM, 0);
  $result = socket_connect($socket, "localhost", 9000);
  $in = "message testing 1 2 3";
  socket_write($socket, $in, strlen($in));
?>
