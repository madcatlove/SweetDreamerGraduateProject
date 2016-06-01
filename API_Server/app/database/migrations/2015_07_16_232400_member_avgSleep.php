<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MemberAvgSleep extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		Schema::create('member_avgSleep', function($table) {

			$table->engine = 'InnoDB';
			$table->increments('seq');
			$table->integer('memberSeq')->unsigned();
			$table->integer('totalRecord')->nullable()->default(0);
			$table->integer('deepRecord')->nullable()->default(0);
			$table->integer('regIdx');
			$table->timestamps();

			// unique key 추가
			$table->unique( array('memberSeq', 'regIdx') );

			// 외래키 추가
			$table->foreign('memberSeq')->references('seq')->on('member')->onDelete('cascade');




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
		Schema::drop('member_avgSleep');
	}

}
