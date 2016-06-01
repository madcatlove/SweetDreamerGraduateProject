<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MemberSleepRecord extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		Schema::create('member_sleepRecord', function($table) {
			$table->engine = 'InnoDB';
			$table->increments('seq');
			$table->integer('memberSeq')->unsigned();
			$table->dateTime('regtime');
			$table->integer('regIdx');
			$table->integer('sleepStep');
			$table->integer('reghour');
			$table->timestamps();
			$table->string('noise', 200)->nullable()->default('');
			$table->integer('avgSleepSeq')->unsigned();

			// 외래키 추가.
			$table->foreign( 'avgSleepSeq' )->references( 'seq' )->on('member_avgSleep')->onDelete('cascade');
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
		Schema::drop('member_sleepRecord');
	}

}
