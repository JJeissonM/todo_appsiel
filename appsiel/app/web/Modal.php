<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Modal extends Model
{
    protected $table = 'pw_modal';
    protected $fillable = ['id', 'title', 'body', 'enlace', 'tipo_recurso', 'path', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

}

/*
		SQL para crear tabla

CREATE TABLE `pw_modal` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `enlace` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tipo_recurso` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `widget_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
--
-- Indices de la tabla `pw_modal`
--
ALTER TABLE `pw_modal`
  ADD PRIMARY KEY (`id`);
  
  --
-- AUTO_INCREMENT de la tabla `pw_modal`
--
ALTER TABLE `pw_modal`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  */
