<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});



/* Controller for user */
Route::get('member', 'MemberController@getMember');
Route::post('member', 'MemberController@postMember');
Route::post('member/{userid}/login', 'MemberController@doLogin');
Route::put('member/{membertoken}/password', 'MemberController@updatePassword')->before('verifyToken');
Route::put('member/{membertoken}/bluetooth', 'MemberController@updateBluetooth')->before('verifyToken');

Route::get('member/{membertoken}/sleepQuality', 'MemberController@getSleepQuality'); // 회원 수면 퀄리티 가져옴
Route::get('member/{membertoken}/averageSleepTime', 'MemberController@getAverageSleepTime'); // 특정 회원 평균 수면시간
Route::get('member/{membertoken}/sleepDistribution', 'MemberController@getMemberSleepDistribution'); // 특정 회원 수면 분포도 데이터
Route::get('member/{membertoken}/recentSleepRecord', 'MemberController@getMemberSleepRecordRecent'); // 특정 회원 가장 최근 수면 데이터

Route::get('member/{membertoken}/calendar/{year}/{month}', 'CalendarController@getCalendarList'); // 캘린더 리스트
Route::get('member/{membertoken}/calendar/{regIdx}', 'CalendarController@getCalendarDetail'); // 캘린더 자세히

Route::get('member/rawdata', 'DataprocController@userRawDataReceiver');

/* Controller for alarm */
Route::get('alarm/{membertoken}', 'AlarmController@getAlarm')->before('verifyToken'); // 회원별 알람 리스트 받아오기
Route::post('alarm/{membertoken}', 'AlarmController@postAlarm')->before('verifyToken'); ; // 회원별 알람 등록
Route::delete('alarm/{membertoken}/{alarmseq}', 'AlarmController@removeAlarm')->before('verifyToken'); ; // 특정 알람 삭제
Route::put('alarm/{membertoken}/{alarmseq}', 'AlarmController@updateAlarm')->before('verifyToken'); ; // 알람 업데이트


/* Controller for data processing */
Route::post('data/proc', 'DataprocController@rawDataReceiver'); // RAW 데이터 리시버
Route::post('data/proc2', 'DataprocController@rawDataReceiver'); // 가공된 데이터 리시버
Route::get('data/proc/view', 'DataprocController@rawDataViewer');
Route::get('data/proc/dump', 'DataprocController@rawDumpData');


/* Controller for tests */
Route::get('test/1', 'TestController@test1');
Route::get('test/2', 'TestController@test2');
Route::get('test/3', 'TestController@test3');