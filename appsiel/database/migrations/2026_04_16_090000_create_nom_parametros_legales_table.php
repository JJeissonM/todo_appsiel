<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNomParametrosLegalesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nom_parametros_legales')) {
            Schema::create('nom_parametros_legales', function (Blueprint $table) {
                $table->increments('id');
                $table->date('fecha_inicio');
                $table->date('fecha_fin')->nullable();
                $table->decimal('smmlv', 14, 2);
                $table->decimal('auxilio_transporte', 14, 2)->default(0);
                $table->decimal('uvt', 14, 2)->default(0);
                $table->decimal('horas_laborales', 8, 3)->default(220);
                $table->decimal('horas_dia_laboral', 8, 3)->default(7.333);
                $table->string('normatividad', 255)->nullable();
                $table->string('estado')->default('Activo');
                $table->timestamps();

                $table->index(['fecha_inicio', 'fecha_fin']);
            });
        }

        if (!Schema::hasTable('nom_pila_planillas_generadas')) {
            return;
        }

        Schema::table('nom_pila_planillas_generadas', function (Blueprint $table) {
            if (!Schema::hasColumn('nom_pila_planillas_generadas', 'parametro_legal_id')) {
                $table->integer('parametro_legal_id')->nullable()->after('fecha_final_mes');
            }

            if (!Schema::hasColumn('nom_pila_planillas_generadas', 'smmlv')) {
                $table->decimal('smmlv', 14, 2)->nullable()->after('parametro_legal_id');
            }

            if (!Schema::hasColumn('nom_pila_planillas_generadas', 'uvt')) {
                $table->decimal('uvt', 14, 2)->nullable()->after('smmlv');
            }

            if (!Schema::hasColumn('nom_pila_planillas_generadas', 'horas_laborales')) {
                $table->decimal('horas_laborales', 8, 3)->nullable()->after('uvt');
            }

            if (!Schema::hasColumn('nom_pila_planillas_generadas', 'horas_dia_laboral')) {
                $table->decimal('horas_dia_laboral', 8, 3)->nullable()->after('horas_laborales');
            }

            if (!Schema::hasColumn('nom_pila_planillas_generadas', 'normatividad')) {
                $table->string('normatividad', 255)->nullable()->after('horas_dia_laboral');
            }
        });
    }

    public function down()
    {
        if (Schema::hasTable('nom_pila_planillas_generadas')) {
            Schema::table('nom_pila_planillas_generadas', function (Blueprint $table) {
                $columns = [
                    'parametro_legal_id',
                    'smmlv',
                    'uvt',
                    'horas_laborales',
                    'horas_dia_laboral',
                    'normatividad',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('nom_pila_planillas_generadas', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('nom_parametros_legales')) {
            Schema::drop('nom_parametros_legales');
        }
    }
}
