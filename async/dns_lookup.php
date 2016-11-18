<?php
swoole_async_dns_lookup('www.lxpgw.com', function ($host, $ip) {
	echo "{$host} - {$ip}", PHP_EOL;
});

echo 'Look up dns', PHP_EOL;
