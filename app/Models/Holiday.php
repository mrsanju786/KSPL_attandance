<?php

namespace App\Models;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
		'name',
		'date',
		'state',
		'state_id'
		
	];
}
