<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// USER TABLE 명세서
/**
 * +---------------+-----------+------+-----+---------+----------------+
 *| Field         | Type      | Null | Key | Default | Extra          |
 *+---------------+-----------+------+-----+---------+----------------+
 *| seq           | int(11)   | NO   | PRI | NULL    | auto_increment |
 *| userid        | char(100) | NO   | UNI | NULL    |                |
 *| userpw        | char(200) | NO   |     | NULL    |                |
 *| bluetoothaddr | char(50)  | NO   |     | NULL    |                |
 *+---------------+-----------+------+-----+---------+----------------+
*/

class CreateUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('member', function($table) {
			$table->engine = 'InnoDB';
			$table->increments('seq'); // auto primary key
			$table->string('userid', 100);
			$table->string('userpw', 200);
			$table->string('bluetoothaddr', 50)->nullable();
			$table->timestamps();

			// key
			$table->unique('userid');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('member');
	}

}
