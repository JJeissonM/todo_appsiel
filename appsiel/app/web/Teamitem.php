<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Teamitem extends Model
{
    protected $table = 'pw_teamitems';
    protected $fillable = ['id', 'title', 'description', 'more_details', 'text_color', 'title_color', 'background_color', 'imagen', 'team_id', 'created_at', 'updated_at'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
