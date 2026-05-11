<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncArea extends Model
{
    use HasFactory;

    protected $table = 'enc_area_general';
    public $timestamps = false;

    protected $fillable = ['cod_area','nom_area'];
}

