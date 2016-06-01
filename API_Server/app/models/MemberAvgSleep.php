<?php


/*
+-------------+------------------+------+-----+---------------------+----------------+
| Field       | Type             | Null | Key | Default             | Extra          |
+-------------+------------------+------+-----+---------------------+----------------+
| seq         | int(10) unsigned | NO   | PRI | NULL                | auto_increment |
| memberSeq   | int(10) unsigned | NO   | MUL | NULL                |                |
| totalRecord | int(11)          | YES  |     | 0                   |                |
| deepRecord  | int(11)          | YES  |     | 0                   |                |
| regIdx      | int(11)          | NO   |     | NULL                |                |
| created_at  | timestamp        | NO   |     | 0000-00-00 00:00:00 |                |
| updated_at  | timestamp        | NO   |     | 0000-00-00 00:00:00 |                |
+-------------+------------------+------+-----+---------------------+----------------+

*/
class MemberAvgSleep extends Eloquent {

	protected $table = 'member_avgSleep';
	protected $primaryKey ='seq';


	/*
	 * Member 와 Many-to-One
	 */
	public function member() {
		return $this->belongsTo('Member', 'memberSeq');
	}

	/*
	 * sleepRecord 와 one-to-many
	 */
	public function sleepRecords() {
		return $this->hasMany('MemberSleepRecord', 'avgSleepSeq' );
	}

}