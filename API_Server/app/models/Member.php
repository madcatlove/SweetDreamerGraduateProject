<?php


/**
 * - member - 
 * +---------------+-----------+------+-----+---------+----------------+
 *| Field         | Type      | Null | Key | Default | Extra          |
 *+---------------+-----------+------+-----+---------+----------------+
 *| seq           | int(11)   | NO   | PRI | NULL    | auto_increment |
 *| userid        | char(100) | NO   | UNI | NULL    |                |
 *| userpw        | char(200) | NO   |     | NULL    |                |
 *| bluetoothaddr | char(50)  | NO   |     | NULL    |                |
 *+---------------+-----------+------+-----+---------+----------------+
*/

use Illuminate\Auth\UserInterface;
class Member extends Eloquent /*implements UserInterface*/ {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'member';


	/**
	 * Hidden form when model to JSON
	 */
	protected $hidden = array('userpw');

	/**
	 * Priamry Key 
	 */
	protected $primaryKey = 'seq';


	const CONSTRAINT_USERPW = 'required|max:100';
	const CONSTRAINT_USERID = 'required|max:100|unique:member,userid|alpha_num';





	/**
	 * 유저의 고유 구별자 리턴
	 */
	public function getAuthIdentifier() {
		return $this->getKey();
	}

	/**
	 * 유저 인증 패스워드 리턴
	 */
	public function getAuthPassword() {
		return $this->userpw;
	}




	/**
	 * 회원별 알람 테이블과 One-To-Many 관계 설정 
	 */
	public function alarms() {
		return $this->hasMany('Alarm', 'memberSeq');
	}

	/**
	 * 회원별 토큰 테이블과 One-To-One 관계 설정
	 */
	public function token() {
		return $this->hasOne('Membertoken', 'memberSeq');
	}

	/**
	 *  회원별 평균 수면 데이터 One-to-Many 관계 설정
	 */
	public function avgSleeps() {
		return $this->hasMany('MemberAvgSleep', 'memberSeq');
	}

}