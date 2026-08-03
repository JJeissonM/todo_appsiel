<?php

namespace App\Hotel\Support;

use App\User;
use App\VentasPos\AperturaEncabezado;
use Illuminate\Support\Facades\Schema;

class HotelCreatorLabel
{
    public static function userLabel($user, $transactionAt = null, $pdvId = null)
    {
        return self::appendResponsible(self::userName($user), $transactionAt, $pdvId);
    }

    public static function appendResponsible($label, $transactionAt = null, $pdvId = null)
    {
        $label = trim((string)$label);
        if ($label == '') {
            $label = '--';
        }

        $responsible = self::responsibleFor($transactionAt, $pdvId);
        if ($responsible == '') {
            return $label;
        }

        if ($label == '--') {
            return $responsible;
        }

        return $label . ', ' . $responsible;
    }

    public static function responsibleFor($transactionAt = null, $pdvId = null)
    {
        if (empty($pdvId)) {
            return '';
        }

        if (!Schema::hasTable('vtas_pos_apertura_encabezados') || !Schema::hasColumn('vtas_pos_apertura_encabezados', 'responsable')) {
            return '';
        }

        $query = AperturaEncabezado::where('pdv_id', $pdvId);

        $transactionAt = self::normalizeDateTime($transactionAt);
        if ($transactionAt != '') {
            $query->where('created_at', '<=', $transactionAt);
        }

        $opening = $query->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();

        if (is_null($opening)) {
            return '';
        }

        if (is_null($opening->responsable)) {
            return '';
        }

        return trim((string)$opening->responsable);
    }

    protected static function userName($user)
    {
        if (is_null($user)) {
            return '--';
        }

        if (is_object($user)) {
            if (isset($user->name) && trim((string)$user->name) != '') {
                return $user->name;
            }

            if (isset($user->creado_por) && trim((string)$user->creado_por) != '') {
                return self::userName($user->creado_por);
            }
        }

        if (is_numeric($user)) {
            $model = User::find($user);
            return is_null($model) ? '--' : $model->name;
        }

        $value = trim((string)$user);
        if ($value == '') {
            return '--';
        }

        $model = User::where('email', $value)->first();
        return is_null($model) ? $value : $model->name;
    }

    protected static function normalizeDateTime($value)
    {
        if (empty($value)) {
            return '';
        }

        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        $value = trim((string)$value);
        if ($value == '' || strpos($value, '0000-00-00') === 0) {
            return '';
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return '';
        }

        return date('Y-m-d H:i:s', $timestamp);
    }
}
