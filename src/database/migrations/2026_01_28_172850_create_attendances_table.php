<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->date('attendance_date');
            $table->time('attendance_start_at');
            $table->time('attendance_finish_at')->nullable();
            $table->foreignId('attendance_status_id')->constrained();
            $table->text('remarks', 255)->nullable();
            $table->timestamps();

            // 同ユーザー＆同日付は1カラムのみ
            $table->unique(['user_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
