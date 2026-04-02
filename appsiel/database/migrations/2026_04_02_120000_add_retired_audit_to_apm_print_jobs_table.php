<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRetiredAuditToApmPrintJobsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('apm_print_jobs')) {
            return;
        }

        Schema::table('apm_print_jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('apm_print_jobs', 'retired_by')) {
                $table->string('retired_by', 120)->nullable()->after('printed_by');
            }

            if (!Schema::hasColumn('apm_print_jobs', 'retired_at')) {
                $table->dateTime('retired_at')->nullable()->after('printed_at');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('apm_print_jobs')) {
            return;
        }

        Schema::table('apm_print_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('apm_print_jobs', 'retired_by')) {
                $table->dropColumn('retired_by');
            }

            if (Schema::hasColumn('apm_print_jobs', 'retired_at')) {
                $table->dropColumn('retired_at');
            }
        });
    }
}
