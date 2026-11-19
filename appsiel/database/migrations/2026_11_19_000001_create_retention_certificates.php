<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetentionCertificates extends Migration
{
    public function up()
    {
        Schema::create('contab_certificados_retenciones', function (Blueprint $t) {
            $t->increments('id');
            $t->string('token', 64)->unique();
            $t->unsignedInteger('core_empresa_id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('core_tercero_id');
            $t->string('impuesto', 10);
            $t->date('fecha_desde');
            $t->date('fecha_hasta');
            $t->longText('datos');
            $t->longText('html')->nullable();
            $t->string('pdf_path')->nullable();
            $t->string('pdf_sha256', 64)->nullable();
            $t->string('estado', 20)->default('Previsualizado');
            $t->dateTime('emitido_at')->nullable();
            $t->timestamps();
            $t->index(['core_empresa_id', 'user_id', 'created_at'], 'cert_ret_empresa_usuario_idx');
        });
        Schema::create('contab_certificados_envios', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('certificado_id');
            $t->string('destinatario');
            $t->string('estado', 20);
            $t->string('error')->nullable();
            $t->unsignedInteger('user_id');
            $t->timestamps();
            $t->index('certificado_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contab_certificados_envios');
        Schema::dropIfExists('contab_certificados_retenciones');
    }
}
