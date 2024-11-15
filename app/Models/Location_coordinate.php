<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location_coordinate extends Model
{
    protected $fillable = [
		'lat',
		'long',
		'radius',
        'area_id'
	];
}
