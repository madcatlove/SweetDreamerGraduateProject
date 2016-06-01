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

class MemberRawData extends Eloquent  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'member_rawdata';

	/**
	 * Priamry Key 
	 */
	protected $primaryKey = 'seq';


}