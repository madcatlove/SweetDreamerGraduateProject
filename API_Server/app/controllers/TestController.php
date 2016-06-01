<?php

use App\Libraries\Utils;
use App\Libraries\MQTT;

class TestController extends BaseController {

	private $avgSleepRepo;
	private $sleepRecordRepo;

	public function __construct(AvgSleepRepo $avgSleepRepo , SleepRecordRepo $sleepRecordRepo) {
		$this->avgSleepRepo = $avgSleepRepo;
		$this->sleepRecordRepo = $sleepRecordRepo;
	}

	public function test1() {

		echo spl_object_hash( $this->avgSleepRepo);

		// regidx 태스트
		$member = Member::find(1);
		//$this->avgSleepRepo->getLastRegIdx($member);

		//print("\n");

		// getLastRegTime 태스트
		//$time = $this->avgSleepRepo->getLastRegTime($member, 2);
		//echo $time;


		// getAvgSleep
		//$avgSleep = $this->avgSleepRepo->getAvgSleep($member, 2);
		//print_r($avgSleep);

		//increaseTotalRecord
		//$this->avgSleepRepo->increaseTotalRecord($member, 2);

		// increaseDeepRecord
		//$this->avgSleepRepo->increaseDeepRecord($member, 2);

		// addAvgSleep ( 값을 한번 줘보자 )
		//$r = $this->avgSleepRepo->addAvgSleep($member, 5);
		//print_r($r);

		// addAvgSleep ( 자동 )
		// $r = $this->avgSleepRepo->addAvgSleep($member);
		// print_r($r);



		// - getAvgSleepInRange -
		$d = $this->avgSleepRepo->getAvgSleepInRange($member, 2015, 7);
		print_r($d);

	}


	// sleepRecordRepo 태스트
	public function test2() {

		// 태스트셋 만듬
		$time1 = mktime(3, 0, 0, 10, 23, 2015); // mm-dd yyyy
		$member = Member::find(1);

		$avgSleepSeq = $this->avgSleepRepo->addAvgSleep($member);
		// getLastRegIdx ( avgSleepRepo )

		$lastRegIdx = $this->avgSleepRepo->getLastRegIdx($member);


		for($i = 0; $i < 900; $i++ ) {
		 	$sleepStep = (mt_rand() % 4) + 1;
			
			if( $sleepStep ==3 || $sleepStep == 4) {
				$this->avgSleepRepo->increaseDeepRecord($member, $lastRegIdx);	
			}
			else {
			
			}
			$this->avgSleepRepo->increaseTotalRecord($member,$lastRegIdx);

		 	$this->sleepRecordRepo->addSleepRecord($member, $lastRegIdx, $sleepStep, date('Y-m-d H:i:s', $time1) );
		 	$time1 += 30;
		}


		// $time2 = mktime(3, 0, 0, 7, 16, 2015); // 2015-07-16
		// for($i = 0; $i < 3000; $i++ ) {
		// 	$sleepStep = (mt_rand() % 4) + 1;
		// 	$this->sleepRecordRepo->addSleepRecord($member, 2, $sleepStep, date('Y-m-d H:i:s', $time2) );
		// 	$time2 += 30;
		// }

		// echo 'OK'.PHP_EOL;



		// -- getMinMaxTimeGroupByRegIdx --
		//$member = Member::find(1);
		//$data = $this->sleepRecordRepo->getMinMaxTimeGroupByRegIdx($member);
	

		return Utils::result($data);
	}


	public function test3() {



		$mqtt = new MQTT("my.n-pure.net", 1883, "phpMQTT Pub Example"); //Change client name to something unique
		if ($mqtt->connect()) {
			$mqtt->publish("madcat","Hello World! at ".date("r"),0);
			$mqtt->close();
		}


	}

}
