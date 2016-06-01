<?php


/**
 * - member_token -
 *+------------+------------------+------+-----+---------------------+----------------+
 *| Field      | Type             | Null | Key | Default             | Extra          |
 *+------------+------------------+------+-----+---------------------+----------------+
 *| seq        | int(10) unsigned | NO   | PRI | NULL                | auto_increment |
 *| memberSeq  | int(10) unsigned | NO   | UNI | NULL                |                |
 *| token      | varchar(64)      | NO   |     | NULL                |                |
 *| expiretime | timestamp        | NO   |     | 0000-00-00 00:00:00 |                |
 *| created_at | timestamp        | NO   |     | 0000-00-00 00:00:00 |                |
 *| updated_at | timestamp        | NO   |     | 0000-00-00 00:00:00 |                |
 * +------------+------------------+------+-----+---------------------+----------------+
*/
class Membertoken extends Eloquent {
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'member_token';


	/**
	 * Priamry Key 
	 */
	protected $primaryKey = 'seq';



	/**
	 * 회원 테이블과 Many-To-One 관계 설정  (역관계 설정)
	 */
	public function member() {
		return $this->belongsTo('Member', 'memberSeq');
	}



	/**
	 * 토큰 생성
	 */
	public static function getNewToken() {
		$p = mhash( MHASH_SHA256, time() . '/' . mt_rand(1,100));

		return strtoupper(bin2hex($p));
	}
	
	/**
	 * 새로운 만료일 생성
	 */
	public static function getNewExpire($extra = 3600) {
		return date('Y-m-d H:i:s', time() + $extra);
	}

}