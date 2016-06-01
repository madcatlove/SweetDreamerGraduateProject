<?php

interface AvgSleepRepo {

	// 주어진 회원의 마지막 regIdx 번호 리턴
	public function getLastRegIdx($member);

	// 주어진 회원과 regIdx에서 가장 마지막 시간 반환
	public function getLastRegTime($member, $regIdx);

	// 새로운 avgSleep 생성
	public function addAvgSleep($member, $regIdx);

	// 주어진 회원과 regIdx의 레코드 반환
	public function getAvgSleep($member, $regIdx);

	// 주어진 회원의 모든 avgSleep 데이터 반환
	public function getAllAvgSleep($member);

	// 주어진 회원과 regIdx에서 전체 수면 카운팅 올림.
	public function increaseTotalRecord($member, $regIdx);

	// 주어진 회원과 regIdx에서 깊은 수면 갯수 올림.
	public function increaseDeepRecord($member, $regIdx);

	// 주어진 회원, 년, 달에서 리스트 가공
	public function getAvgSleepInRange($member, $year, $month);
}


class AvgSleepRepoImpl implements AvgSleepRepo {

	// 주어진 회원의 마지막 regIdx 번호 리턴
	public function getLastRegIdx($member) {
		$avgSleep = $member->avgSleeps()->orderBy('regIdx', 'desc')->first();

		if( !isset($avgSleep) ) return -1;
		return $avgSleep->regIdx;
	}

	// 주어진 회원과 regIdx에서 가장 마지막 시간 반환
	public function getLastRegTime($member, $regIdx) {

		$avgSleep = $member->avgSleeps()->where('regIdx', $regIdx)->first();
		$recentDate = $avgSleep->sleepRecords()->max('regtime'); // 가장 최근 시간 가져옴

		return $recentDate;
	}

	// 새로운 avgSleep 생성
	public function addAvgSleep($member, $regIdx = -1) {

		$memberAvgSleep = new MemberAvgSleep;
		$memberAvgSleep->memberSeq = $member->getKey();

		if( $regIdx !== -1) {
			$memberAvgSleep->regIdx = (int) $regIdx;
		}
		else {
			$memberAvgSleep->regIdx = $this->getLastRegIdx($member) + 1;
		}

		$memberAvgSleep->save();

		return $memberAvgSleep->seq;
	}

	// 주어진 회원과 regIdx의 레코드 반환
	public function getAvgSleep($member, $regIdx) {

		$avgSleep = $member->avgSleeps()->where('regIdx', $regIdx)->first();

		return $avgSleep;
	}

	// 주어진 회원과 regIdx에서 전체 수면 카운팅 올림.
	public function increaseTotalRecord($member, $regIdx) {

		$avgSleep = $member->avgSleeps()->where('regIdx', $regIdx)->first();
		$avgSleep->totalRecord++;
		$aff = $avgSleep->update();

		return $aff;
	}

	// 주어진 회원과 regIdx에서 깊은 수면 갯수 올림.
	public function increaseDeepRecord($member, $regIdx) {

		$avgSleep = $member->avgSleeps()->where('regIdx', $regIdx)->first();
		$avgSleep->deepRecord++;
		$aff = $avgSleep->update();

		return $aff;

	}


	// 주어진 회원의 모든 avgSleep 데이터 반환
	public function getAllAvgSleep($member) {
		$avgSleep = $member->avgSleeps()
						   ->orderBy('regIdx', 'desc')
						   ->get();


		return $avgSleep;
	}


	// 주어진 회원, 년, 달에서 리스트 가공
	public function getAvgSleepInRange($member, $year, $month) {

		// JOIN 할 sleepRecord
		$joinStatement = '(SELECT MIN(member_sleepRecord.regtime) AS START_TIME, MAX(regtime) AS END_TIME,  avgSleepSeq FROM `member_sleepRecord` GROUP BY avgSleepSeq ) AS Jmember_sleepRecord';

		$avgSleep = $member
			->avgSleeps()
			->where('member_avgSleep.memberSeq', $member->getKey() )
			->whereHas('sleepRecords', function($query) use($member, $year, $month) {
				$query
					  ->whereRaw( DB::raw('MONTH(regtime) = '. $month) )
					  ->whereRaw( DB::raw('YEAR(regtime) = '. $year) );
			})
			->groupBy('member_avgSleep.regIdx')
			->join( DB::raw($joinStatement), function($join) {
				$join->on('member_avgSleep.seq' , '=', 'Jmember_sleepRecord.avgSleepSeq');

			})
			->get();


		return $avgSleep;
	}


}




//----------------------------------------------------
// 전역 등록 
App::bind('AvgSleepRepo', 'AvgSleepRepoImpl');
