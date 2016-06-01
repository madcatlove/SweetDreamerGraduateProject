<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

		// $this->call('UserTableSeeder');

		$this->call('MemberTableSeeder');
		$this->command->info(' Member Table is seeded!! ');
	}

}



// Member table seeder
/*
+---------------+-----------+------+-----+---------+----------------+
| Field         | Type      | Null | Key | Default | Extra          |
+---------------+-----------+------+-----+---------+----------------+
| seq           | int(11)   | NO   | PRI | NULL    | auto_increment |
| userid        | char(100) | NO   | UNI | NULL    |                |
| userpw        | char(200) | NO   |     | NULL    |                |
| bluetoothaddr | char(50)  | NO   |     | NULL    |                |
+---------------+-----------+------+-----+---------+----------------+
*/
class MemberTableSeeder extends Seeder {

	public function run() {
		DB::table('member')->delete(); // truncate

		$sql = 'INSERT INTO member (userid, userpw, bluetoothaddr) VALUES (?, ?, ?);';

		$data1 = array('madcat', '1234', '00:00:00:00:00');
		$data2 = array('madcat1', '1234', '00:00:00:00:01');
		$data3 = array('madcat2', '1234', '00:00:00:00:02');

		DB::insert( $sql, $data1);
		DB::insert( $sql, $data2);
		DB::insert( $sql, $data3);

	}

}
