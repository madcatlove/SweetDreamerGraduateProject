<?php

interface SleepRecordRepo {

	// 특정 회원 특정 달에 대해 요약된 기록 리턴
	public function getSummaryByMonth($member, $month);

	// 특정 regIdx 요청에 대해 모든 기록을 리턴( regtime 오름차순 )
	public function getRecordsByRegIdx($member, $regidx);


	// 주어진 회원의 시간별 수면 분포를 리턴 (0~24)
	public function getMemberSleepDistribution($member);

	// 수면 데이터 기록
	public function addSleepRecord($member, $regidx, $sleepStep, $regtime = NULL);

	// 주어진 회원의 regidx별 시작시간 끝시간 가져옴.
	public function getMinMaxTimeGroupByRegIdx($member);


}


class SleepRecordRepoImpl implements SleepRecordRepo {

	// 특정 회원 특정 달에 대해 요약된 기록 리턴
	public function getSummaryByMonth($member, $month) {

	}

	// 특정 regIdx 요청에 대해 모든 기록을 리턴( regtime 오름차순 )
	public function getRecordsByRegIdx($member, $regIdx) {

		$records = MemberSleepRecord::where('memberSeq', $member->getKey() )
									->where('regIdx', $regIdx)
									->orderBy('regtime', 'asc')
									->get();


		return $records;
	}


	// 주어진 회원의 시간별 수면 분포를 리턴 (0~24)
	public function getMemberSleepDistribution($member) {

		$sleepDistribution = MemberSleepRecord::select( DB::raw('memberSeq, regHour, count(regHour) AS regHourCount'))
											   ->where('memberSeq', $member->getKey() )
											   ->groupBy('reghour')->get();

		
		return $sleepDistribution;
	}

	// 수면 데이터 기록
	public function addSleepRecord($member, $regidx, $sleepStep, $regtime = NULL) {
		if( $regtime == NULL ) $regtime = date('Y-m-d H:i:s', time() );


		// avgSleepSeq를 구하기 위함.
		$avgSleep = MemberAvgSleep::where('memberSeq', $member->getKey())->where('regIdx', $regidx)->first();
		if( !isset($avgSleep) ) {
			return FALSE;
		}



		//echo sprintf("%d %d %s\n", $regidx, $sleepStep, $regtime);
		$msr = new MemberSleepRecord;

		$msr->memberSeq = $member->getKey();
		$msr->regIdx = $regidx;
		$msr->sleepStep = $sleepStep;
		$msr->reghour = date('H', strtotime($regtime)) ;
		$msr->regtime = $regtime;
		$msr->avgSleepSeq = $avgSleep->getKey();

		$msr->save();

		return $msr->seq;
	}

	// 주어진 회원의 regidx별 시작시간 끝시간 가져옴.
	public function getMinMaxTimeGroupByRegIdx($member) {

		// explain select min(regtime), max(regtime), count(seq), regIdx from member_sleepRecord where memberSeq = 1 group by regIdx order by regIdx desc;
		// select min(regtime) AS MIN_REGTIME, max(regtime) AS MAX_REGTIME, count(seq) AS NUM_RECORDS, regIdx, sum( member_sleepRecord.sleepStep = 3 OR member_sleepRecord.sleepStep = 4) from `member_sleepRecord` where `memberSeq` = 1 group by `regIdx` order by `regIdx` desc 

		$data = MemberSleepRecord::select( DB::raw(' min(regtime) AS MIN_REGTIME, max(regtime) AS MAX_REGTIME, count(seq) AS NUM_RECORDS, regIdx, count( member_sleepRecord.sleepStep = 3)'))
				->where('memberSeq', $member->getKey() )
				->groupBy('regIdx')
				->orderBy('regIdx', 'desc')
				->get();

		if(isset($data) ) {
			$data = $data->map(function($record) {
				$record->MIN_REGTIME = strtotime($record->MIN_REGTIME);
				$record->MAX_REGTIME = strtotime($record->MAX_REGTIME);
				return $record;
			});
		}

		return $data;
	}
}


//----------------------------------------------------
// 전역 등록 
App::bind('SleepRecordRepo', 'SleepRecordRepoImpl');