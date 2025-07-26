<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncPregunta extends Model
{
    use HasFactory;

    protected $table = 'enc_pregunta';
    protected $primaryKey = 'cod_pre';
    public $timestamps = false;

    protected $fillable = ['nom_pre','orden','cod_area','cod_encuesta'];

    public function area()
    {
        return $this->belongsTo(EncArea::class, 'cod_area', 'cod_area');
    }
}

