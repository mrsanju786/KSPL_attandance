<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $fillable = [
		'title',
		'message',
		'created_by',
        'status'
		
	];
}
