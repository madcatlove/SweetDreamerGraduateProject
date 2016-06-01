<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberRawdata extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		Schema::create('member_rawdata', function($table) {
			$table->engine = 'InnoDB';
			$table->increments('seq');
			$table->datetime('regdate');
			$table->integer('rawdata');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
		Schema::drop('member_rawdata');
	}

}
