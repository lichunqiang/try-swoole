<?php
use Swoole\Http\Server;

$serv = new Server('0.0.0.0', 9501);

$serv->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
	$response->header('Content-Type', 'text/json;charset=uft-8;');
	
	$response->end(json_encode(['name' => 'light']));
});


$serv->start();
