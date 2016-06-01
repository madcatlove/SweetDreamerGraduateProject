<?php

namespace App\Libraries;

class Utils {

	const CANNOT_LOGIN = 'CANNOT_LOGIN';
	const CANNOT_FIND_TOKEN = 'CANNOT_FIND_TOKEN';
	const EXPIRED_TOKEN = 'EXPIRED_TOKEN';
	const CANNOT_PASS_VALIDATOR = 'CANNOT_PASS_VALIDATOR';
	const CANNOT_FIND_ALARM = 'CANNOT_FIND_ALARM';
	const CANNOT_FIND_MEMBER = 'CANNOT_FIND_MEMBER';

	/**
	 * JSON 형태로 결과값 리턴.
	 */
	public static function result($data, $error = false) {

		$s = new \stdClass();
		$s->data = $data;
		$s->error = $error;

		return json_encode($s);
	}

	/**
	 * 알람 설정 숫자 반환 ( 0000000 7bit )
	 * @param Array
	 */
	public static function getMultipleAlarmToInteger( $alarm_array ) {

		$arraySize = sizeof($alarm_array);
		if( $arraySize !== 7 ) {
			$alarm_array = array_fill(0, 7, (int)0);
		}

		$settedAlarm =0;

		for($i = 0; $i < $arraySize; $i++) {
			$p =  ((int) $alarm_array[$i]) << (6-$i);
			$settedAlarm = $settedAlarm | $p;
		}

		return $settedAlarm;
	}

	/**
	 * 알람 숫자를 배열로 변환
	 * @param Integer
	 */
	public static function getMultipleAlarmToArray( $alarm_integer ) {
		$al_array = [];

		for($i = 0; $i < 7; $i++) {
			$p = 1 << 6-$i;

			$al_array[] = (int)(($alarm_integer & $p) >> 6-$i);
		}

		return $al_array;
	}


}