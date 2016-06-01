<?php
use App\Libraries\Utils;

class AlarmController extends BaseController {


	/**
	 * 특정 회원의 알람 리스트를 모두 가져옴.
	 * @param String membertoken
	 * @return Array
	 */
	public function getAlarm($membertoken) {

		// 멤버 토큰을 통해 멤버 가져옴.
		// $memberToken = Membertoken::where('token', $membertoken)->first();
		// $member = $memberToken->member()->first();


		$member = Member::whereHas('token', function($query) use($membertoken) {
			$query->where('token', $membertoken);
		})->first();

		// 알람 리스트 가져옴.
		$alarms = $member->alarms()->get();


		return Utils::result( $alarms );
	}



	/**
	 * 특정회원 알람을 등록
	 * @param String membertoken
	 * @return Array
	 */
	public function postAlarm($membertoken) {

		// 멤버 토큰을 통해 멤버 가져옴.
		$memberToken = Membertoken::where('token', $membertoken)->first();
		$member = $memberToken->member()->first();


		// 유효성 검사
		$rules = [
			'isrepeat' => Alarm::CONSTRAINT_ISREPEAT,
			'waketime' => Alarm::CONSTRAINT_WAKETIME,
			'waketime2' => Alarm::CONSTRAINT_WAKETIME,
			'repeatday' => Alarm::CONSTRAINT_REPEATDAY
		];
		$validator = Validator::make(Input::only('isrepeat', 'waketime', 'waketime2' ,'repeatday'), $rules);
		if( $validator->fails() ) 
			return Utils::result( Utils::CANNOT_PASS_VALIDATOR , true);


		// 알람 셋팅
		$memberAlarm = new Alarm;
		$memberAlarm->memberSeq = $member->getKey();
		$memberAlarm->repeat_day = Input::get('repeatday');
		$memberAlarm->isrepeat = Input::get('isrepeat');
		$memberAlarm->waketime = Input::get('waketime');
		$memberAlarm->waketime2 = Input::get('waketime2');
		$memberAlarm->save();

		return Utils::result( $memberAlarm->getKey() );
	}


	/**
	 * 특정 회원의 알람을 업데이트함.
	 * @param String membertoken
	 * @param Integer alarmseq
	 * @return Array
	 */
	public function updateAlarm($membertoken, $alarmseq) {

		// 멤버 토큰을 통해 멤버 가져옴.
		$memberToken = Membertoken::where('token', $membertoken)->first();
		$member = $memberToken->member()->first();

		// 유효성 검사
		$rules = [
			'isrepeat' => Alarm::CONSTRAINT_ISREPEAT,
			'waketime' => Alarm::CONSTRAINT_WAKETIME,
			'waketime2' => Alarm::CONSTRAINT_WAKETIME,
			'repeatday' => Alarm::CONSTRAINT_REPEATDAY
		];
		$validator = Validator::make(Input::only('isrepeat', 'waketime', 'waketime2','repeatday'), $rules);
		if( $validator->fails() ) 
			return Utils::result( Utils::CANNOT_PASS_VALIDATOR , true);

		$memberAlarm = Alarm::find($alarmseq);


		// 알람 업데이트
		if( isset($memberAlarm) ) {
			if( $memberAlarm->memberSeq == $member->seq ) {
				$memberAlarm->repeat_day = Input::get('repeatday');
				$memberAlarm->isrepeat = Input::get('isrepeat');
				$memberAlarm->waketime = Input::get('waketime');
				$memberAlarm->waketime2 = Input::get('waketime2');
				$affectedRow = $memberAlarm->update();

				return Utils::result( $affectedRow );
			}
		}

		return Utils::result(  Utils::CANNOT_FIND_ALARM, true);


	}


	/**
	 * 특정 회원의 지정된 알람을 삭제
	 * @param String membertoken
	 * @param Integer alarmseq
	 * @return Array
	 */
	public function removeAlarm($membertoken, $alarmseq) {


		// 멤버 토큰을 통해 멤버 가져옴.
		$memberToken = Membertoken::where('token', $membertoken)->first();
		$member = $memberToken->member()->first();

		$memberAlarm = Alarm::find($alarmseq);


		// 알람 업데이트
		if( isset($memberAlarm) ) {
			if( $memberAlarm->memberSeq == $member->seq ) {
				$affectedRow = $memberAlarm->delete();
				return Utils::result( $affectedRow );
			}
		}

		return Utils::result(  Utils::CANNOT_FIND_ALARM, true);

	}

}