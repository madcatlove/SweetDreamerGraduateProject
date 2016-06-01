<?php
use App\Libraries\Utils;
class MemberController extends BaseController {

	private $avgSleepRepo;
	private $sleepRecordRepo;

	public function __construct(AvgSleepRepo $avgSleepRepo, SleepRecordRepo $sleepRecordRepo) {
		$this->avgSleepRepo = $avgSleepRepo;
		$this->sleepRecordRepo = $sleepRecordRepo;
	}




	/**
	 * 회원정보 삽입 컨트롤러 
	 * @return JSON
	 */
	public function postMember() {


		// auto trim
		array_map('trim', Input::only('userid', 'userpw', 'bluetoothaddr') );

		// 유효성 검사 rule
		$rules = array(
				'userid' => Member::CONSTRAINT_USERID,
				'userpw' => Member::CONSTRAINT_USERPW
			);

		$validator = Validator::make(Input::all(), $rules);

		if( $validator->fails() ) {
			return Utils::result( $validator->messages(), true);
		}


		// Create new member
		$member = new Member;
		$member->userid = Input::get('userid');
		$member->userpw = Input::get('userpw');
		$member->bluetoothaddr = Input::get('bluetoothaddr');

		// commit
		$member->save();

		
		return Utils::result( $member->getKey() );
	}

	/**
	 * 회원정보를 가져온다.
	 */
	public function getMember() {

		$data = Member::all();

		return Utils::result($data);
	}

	/**
	 * 로그인 컨트롤러. 
	 */
	public function doLogin($userid) {

		Input::merge( array('userid' => $userid) );
		array_map('trim', Input::only('userid', 'userpw') );

		// 유효성 검사 rule
		$rules = array(
				'userid' => Member::CONSTRAINT_USERID,
				'userpw' => Member::CONSTRAINT_USERPW
			);

		$validator = Validator::make(Input::all(), $rules);


		if( $validator->fails() ) {

		}


		$member = Member::where( ['userid' => Input::get('userid')] )
						->where( ['userpw' => Input::get('userpw')] )
						->first();


		if( isset($member) && $member->getKey() >= 0 ) {
			
			// 토큰이 있다면 삭제.
			if( $member->token() ) {
				$member->token()->delete();
			}

			// 새로운 토큰 발급.
			$memberToken = new Membertoken();
			$memberToken->token = Membertoken::getNewToken();
			$memberToken->memberSeq = $member->getKey();
			$memberToken->expiretime = Membertoken::getNewExpire();
			$memberToken->save();

			return Utils::result( $memberToken->token);
		}
		else {
			return Utils::result( Utils::CANNOT_LOGIN, true);
		}


	}

	/**
	 * 멤버의 패스워드를 변경한다 
	 */
	public function updatePassword($membertoken) {

	
		array_map('trim', Input::only('new_userpw') );

		// 유효성 검사 rule
		$rules = array(
				'new_userpw' => Member::CONSTRAINT_USERPW
			);

		$validator = Validator::make(Input::only('new_userpw'), $rules);


		if( $validator->fails() ) {

		}


		// 토큰 꺼내옴.
		$member = Membertoken::where('token', $membertoken)->first();
		if( isset($member) ) $member = $member->member()->first();
		else unset($member);

		// 패스워드 변경 절차
		if( isset($member) && $member->getKey() >= 0) {
			$member->userpw = Input::get('new_userpw');
			$member->save();

			return Utils::result( $member->getKey() );
		}
		
		return Utils::result( Utils::CANNOT_LOGIN , true );
	}



	public function updateBluetooth($membertoken) {
		$bluetooth = Input::get('new_bluetooth');

		$member = Membertoken::where('token', $membertoken)->first()->member;

		$member->bluetoothaddr = $bluetooth;
		$affected = $member->save();

		return Utils::result( $affected );
	}






	public function getSleepQuality($membertoken) {

		$member = Member::whereHas('token', function($query) use($membertoken) {
			$query->where('token', $membertoken);
		})->first();

		$qualityData = new stdClass;
		$qualityData->memberSeq = $member->getKey();
		$qualityData->totalRecord = 0;
		$qualityData->deepRecord = 0;


		$sleepQuality = $this->avgSleepRepo->getAllAvgSleep($member);
		$sleepQuality->each( function($record) use(&$qualityData) {
			$qualityData->totalRecord += $record->totalRecord;
			$qualityData->deepRecord += $record->deepRecord;
		});


		return Utils::result($qualityData);
	}


	public function getAverageSleepTime($membertoken) {

		$member = Member::whereHas('token', function($query) use($membertoken) {
			$query->where('token', $membertoken);
		})->first();


		$avgSleepData = new stdClass;
		$avgSleepData->memberSeq = $member->getKey();
		$avgSleepData->totalSleepTime = 0;
		$avgSleepData->numItem = 0;

		$sleepTimes = $this->sleepRecordRepo->getMinMaxTimeGroupByRegIdx($member);
		$sleepTimes->each( function($record) use (&$avgSleepData) {

			$avgSleepData->totalSleepTime += $record->MAX_REGTIME - $record->MIN_REGTIME;
			$avgSleepData->numItem++; 
		});



		return Utils::result($avgSleepData);
	}


	public function getMemberSleepDistribution($membertoken) {

		
		$member = Member::whereHas('token', function($query) use($membertoken) {
			$query->where('token', $membertoken);
		})->first();

		$sleepDistribution = $this->sleepRecordRepo->getMemberSleepDistribution($member);

		return Utils::result($sleepDistribution);
	}

	public function getMemberSleepRecordRecent($membertoken) {

		$member = Member::whereHas('token', function($query) use($membertoken) {
			$query->where('token', $membertoken);
		})->first();


		// 회원의 최근 regIdx 가져옴.
		$recentRegIdx = $this->avgSleepRepo->getLastRegIdx($member);

		// 회원 regidx 에 대한 모든 레코드 가져옴
		$records = $this->sleepRecordRepo->getRecordsByRegIdx($member, $recentRegIdx);

		return Utils::result($records);
	}

}