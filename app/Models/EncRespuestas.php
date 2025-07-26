<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncRespuestas extends Model
{
    use HasFactory;

    protected $table = 'enc_respuestas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'tipo','semestre','cod_alu','cod_cur','cod_pro','turno',
        'cod_pre','cod_alt','fecha_reg'
    ];

    public function pregunta()
    {
        return $this->belongsTo(EncPregunta::class, 'cod_pre', 'cod_pre');
    }
}

