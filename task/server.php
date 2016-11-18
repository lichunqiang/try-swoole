<?php

use Swoole\Server;

$serv = new Server('127.0.0.1', 9502);

$serv->set([
	//'process_name' => 'task_server',
	'task_worker_num' => 4
]);

$serv->on('receive', function (Server $serv, $fd, $from_id, $data) {
	echo 'Receive task signle from ', $from_id, 'fd-', $fd, PHP_EOL;
	$task_id = $serv->task($data, $fd % 4);
	$serv->send($fd, "Got AsyncTask: id={$task_id}");
	
	echo "Dispath AsyncTask: id=$task_id\n";
});


$serv->on('task', function (Server $serv, $task_id, $from_id, $data) {
	echo "New AsyncTask[id=$task_id]" . PHP_EOL;
	
	//send to caller worker
	$serv->finish('OK');
});

$serv->on('finish', function (Server $serv, $task_id, $data) {
	echo "AsyncTask[$task_id] Finish: $data" . PHP_EOL;
});


$serv->on('workerStart', function (Server $serv, $worker_id) {
	swoole_set_process_name("php_task_worker_{$worker_id}");
});

$serv->start();
