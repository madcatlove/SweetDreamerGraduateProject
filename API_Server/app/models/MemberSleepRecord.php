<?php

/*
+-------------+------------------+------+-----+---------------------+----------------+
| Field       | Type             | Null | Key | Default             | Extra          |
+-------------+------------------+------+-----+---------------------+----------------+
| seq         | int(10) unsigned | NO   | PRI | NULL                | auto_increment |
| memberSeq   | int(10) unsigned | NO   | MUL | NULL                |                |
| regtime     | datetime         | NO   |     | NULL                |                |
| regIdx      | int(11)          | NO   |     | NULL                |                |
| sleepStep   | int(11)          | NO   |     | NULL                |                |
| reghour     | int(11)          | NO   |     | NULL                |                |
| created_at  | timestamp        | NO   |     | 0000-00-00 00:00:00 |                |
| updated_at  | timestamp        | NO   |     | 0000-00-00 00:00:00 |                |
| noise       | varchar(200)     | YES  |     |                     |                |
| avgSleepSeq | int(10) unsigned | NO   | MUL | NULL                |                |
+-------------+------------------+------+-----+---------------------+----------------+

*/
class MemberSleepRecord extends Eloquent {

	protected $table = 'member_sleepRecord';
	protected $primaryKey = 'seq';


	/*
	 * MemberAvgSleep 테이블과 Many-to-one 관게
	 */
	public function avgSleep() {
		return $this->belongsTo('MemberAvgSleep', 'avgSleepSeq' );
	}

}