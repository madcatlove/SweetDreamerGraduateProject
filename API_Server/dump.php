<?php
set_time_limit(0);
mysql_connect('localhost', 'ssm2015','1234');
mysql_select_db('ssm2015');

$_starttime = microtime(true);

$query = 'select * from member_rawdata order by regdate, seq';

$res = mysql_query( $query );

$cnt = 0;
while( $data = mysql_fetch_array($res) ) {
	
	$rawData =  ($data['rawdata'] * (1.8/ 4096.0)) / 2000.0;
	$rawData =   $rawData * 1000000;
	
	$file_name = trim($data['regdate']);
	$file_name = str_replace(':' , '-', $file_name) . '.txt';
	$file_name  = $cnt + 1;

	$str = ''.$rawData.PHP_EOL;
	
	file_put_contents($file_name, $str, FILE_APPEND);
	
	echo 'DONE : ' . $cnt . ' ==> ' . $file_name . PHP_EOL;
	$cnt++;
}

