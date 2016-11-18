<?php

use Swoole\Server;

$serv = new Server('127.0.0.1', 9502);

$atomic = new Swoole\Atomic(0);

//只启动一个worker
$serv->set([
	'task_worker_num' => 1,
	'worker_num' => 1,
]);

$serv->on('workerStart', function (Server $server, $worker_id) {
	
	if ($worker_id >= $server->setting['worker_num']) {
		swoole_set_process_name("php_global_data_task_proc_{$worker_id}");
	} else {
		swoole_set_process_name("php_global_data_worker_proc_{$worker_id}");
	}
	echo "Worker start: Master Pid-{$server->master_pid} | Manager Pid-{$server->manager_pid}", PHP_EOL;
});


$serv->on('workerStop', function (Server $server, $worker_id) {
	echo "WorkerStop[{$worker_id}]|pid=" . posix_getpid(), PHP_EOL;
});


$serv->on('start', function (Server $server) {
	echo "Master Pid-{$server->master_pid} | Manager Pid-{$server->manager_pid}", PHP_EOL;
	echo 'Swoole Version: ', SWOOLE_VERSION, PHP_EOL;
});

//接收到发送过来的请求
$serv->on('receive', function (Server $server, $fd, $from_id, $rdata) {
	global $atomic;
	echo 'On the worker process and the atomic is ' . $atomic->get() . PHP_EOL;
	$atomic->add(1);
	$data = unserialize($rdata);
	//print_r($server->stats());
	switch ($data['cmd']) {
		case 'get':
			//should be sync.
			$start = microtime(true);
			$res = $server->taskwait($data, 0.5, 0);
			echo 'use' . ((microtime(true) - $start) * 1000) . 'ms', PHP_EOL;
			$server->send($fd, $res);
			break;
		case 'set':
			$server->task($data, 0);
			$server->send($fd, 'OK');
			break;
		case 'del':
			$server->task($data, 0);
			break;
		default:
			echo 'server' . $rdata . PHP_EOL;
	}
});

//处理任务
$serv->on('task', function (Server $server, $task_id, $from_id, $data) {
	global $atomic;
	echo 'On Task process, and the atomic is ' . $atomic->get(), PHP_EOL;
	static $globalData = [];
	switch ($data['cmd']) {
		case 'get':
			$key = $data['key'];
			$val = isset($globalData[$key]) ? $globalData[$key] : '';
			$server->finish($val);
			break;
		case 'set':
			$key = $data['key'];
			$val = $data['val'];
			$globalData[$key] = $val;
			break;
		case 'del':
			$key = $data['key'];
			unset($globalData[$key]);
			break;
	}
	echo "AsyncTask[PID=" . posix_getpid() . "]: task_id={$task_id}." . PHP_EOL;
});

$serv->on('finish', function (Server $server, $task_id) {
	echo $task_id, PHP_EOL;
});

$serv->start();
