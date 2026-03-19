<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApmPrintJobsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('apm_print_jobs')) {
            return;
        }

        Schema::create('apm_print_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('core_empresa_id')->default(1);
            $table->unsignedInteger('core_tipo_transaccion_id');
            $table->unsignedInteger('core_tipo_doc_app_id');
            $table->unsignedInteger('consecutivo');
            $table->unsignedInteger('apm_print_status_id');
            $table->string('document_type', 50);
            $table->string('document_label', 120)->nullable();
            $table->unsignedInteger('copy_number')->default(1);
            $table->string('copy_label', 40);
            $table->string('printer_id', 120)->nullable();
            $table->string('station_id', 120)->nullable();
            $table->longText('payload_json');
            $table->unsignedInteger('attempts_count')->default(0);
            $table->text('last_error')->nullable();
            $table->string('queued_by', 120)->nullable();
            $table->string('printed_by', 120)->nullable();
            $table->dateTime('queued_at');
            $table->dateTime('last_attempt_at')->nullable();
            $table->dateTime('printed_at')->nullable();
            $table->timestamps();

            $table->unique(['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'document_type', 'copy_number'], 'uniq_apm_print_doc_copy');
            $table->index(['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo'], 'idx_apm_print_doc');
            $table->index(['apm_print_status_id', 'queued_at'], 'idx_apm_print_status_queue');
            $table->foreign('apm_print_status_id')->references('id')->on('apm_print_statuses');
        });
    }

    public function down()
    {
        Schema::dropIfExists('apm_print_jobs');
    }
}
