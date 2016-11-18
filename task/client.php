<?php

use Swoole\Client;

$client = new Client(SWOOLE_SOCK_TCP);

$client->connect('127.0.0.1', 9502, 1);

if (!$client->send('client')) {
	die('send failed');
}
$data = $client->recv(65535, true);
var_dump($data);


$client->close();
