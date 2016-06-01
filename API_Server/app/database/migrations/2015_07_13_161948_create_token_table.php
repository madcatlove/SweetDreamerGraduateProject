<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokenTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('member_token', function($table) {
			$table->engine = 'InnoDB';
			$table->increments('seq');
			$table->integer('memberSeq')->unsigned();
			$table->string('token', 64);
			$table->timestamp('expiretime');

			$table->timestamps();

			// 외래키 추가
			$table->foreign('memberSeq')->references('seq')->on('member')->onDelete('cascade');
			$table->unique('memberSeq');
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
		Schema::drop('member_token');
	}

}
