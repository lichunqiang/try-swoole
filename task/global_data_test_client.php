<?php

use Swoole\Client;

$client = new Client(SWOOLE_SOCK_TCP);

$client->connect('127.0.0.1', 9502, 1);

if (!$client->send(serialize(['cmd' => 'set', 'key' => 'name', 'val' => 'light']))) {
	die('send failed');
}
$data = $client->recv();
var_dump($data);

if (!$client->send(serialize(['cmd' => 'get', 'key' => 'name']))) {
	die('send failed');
}
$data = $client->recv();
var_dump($data);

$client->close();
