<?php

/**
* - member_alarm -
* +------------+------------------+------+-----+---------------------+----------------+
* | Field      | Type             | Null | Key | Default             | Extra          |
* +------------+------------------+------+-----+---------------------+----------------+
* | seq        | int(10) unsigned | NO   | PRI | NULL                | auto_increment |
* | memberSeq  | int(10) unsigned | NO   | MUL | NULL                |                |
* | repeat_day | int(10) unsigned | YES  |     | 0                   |                |
* | isrepeat   | tinyint(1)       | NO   |     | NULL                |                |
* | waketime   | datetime         | NO   |     | NULL                |                |
* | created_at | timestamp        | NO   |     | 0000-00-00 00:00:00 |                |
* | updated_at | timestamp        | NO   |     | 0000-00-00 00:00:00 |                |
* | deleted_at | timestamp        | YES  |     | NULL                |                |
* +------------+------------------+------+-----+---------------------+----------------+
*/
class Alarm extends Eloquent {

	const CONSTRAINT_ISREPEAT = 'required|numeric';
	const CONSTRAINT_WAKETIME = 'required|date';
	const CONSTRAINT_REPEATDAY = 'required|integer';


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'member_alarm';


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


}