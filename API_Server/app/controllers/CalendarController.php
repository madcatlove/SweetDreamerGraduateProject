<?php

use App\Libraries\Utils;

class CalendarController extends BaseController {

	private $avgSleepRepo;
	private $sleepRecordRepo;

	public function __construct(AvgSleepRepo $avgSleepRepo, SleepRecordRepo $sleepRecordRepo) {
		$this->avgSleepRepo = $avgSleepRepo;
		$this->sleepRecordRepo = $sleepRecordRepo;
	}


	public function getCalendarList($membertoken, $year, $month) {
		
		$member = Member::whereHas('token', function($query) use($membertoken) {
			$query->where('token', $membertoken);
		})->first();

		$calendar = $this->avgSleepRepo->getAvgSleepInRange($member, $year, $month);

		$calendar->each(function(&$cal) {
			$p = ($cal->deepRecord / $cal->totalRecord) * 100;
			
			if($p > 75 ) $cal->deepSleepIndex = 3;
			else if( $p > 30) $cal->deepSleepIndex = 2;
			else $cal->deepSleepIndex = 1;
		});

		return Utils::result($calendar);
	}


	public function getCalendarDetail($membertoken, $regIdx) {

		$member = Member::whereHas('token', function($query) use($membertoken) {
			$query->where('token', $membertoken);
		})->first();


		$data = new stdClass;
		$data->sleepInfo = $this->avgSleepRepo->getAvgSleep($member, $regIdx);
		$data->sleepRecords = $this->sleepRecordRepo->getRecordsByRegIdx($member, $regIdx);


		return Utils::result($data);
	}



}
