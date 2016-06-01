<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlramTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		
		// 알람 테이블 생성.
		Schema::create('member_alarm', function($table) {
			$table->engine = 'InnoDB';
			$table->increments('seq');
			$table->integer('memberSeq')->unsigned(); // 회원 외래키
			$table->integer('repeat_day')->nullable()->default(0)->unsigned(); // 반복일
			$table->boolean('isrepeat'); // 반복설정

			$table->dateTime('waketime'); // 꺠울 시간 (시작)
			$table->dateTime('waketime2'); // 꺠울 시간 ( 끝 )

			// 마그레이션을 위한 필드.
			$table->timestamps();
			$table->softDeletes();

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
		Schema::drop('member_alarm');
	}

}
