<?php

use Swoole\Timer;

$time = 1;

echo $time, PHP_EOL;

//间隔1S调用
$timerId = Timer::tick(1000, function () use (&$time) {
	$time++;
	echo $time, PHP_EOL;
});

//一分钟后停止
Timer::after(59000, function () use ($timerId) {
	Timer::clear($timerId);
	echo 'stop', PHP_EOL;
});

