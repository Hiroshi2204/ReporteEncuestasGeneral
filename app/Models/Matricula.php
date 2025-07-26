<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    use HasFactory;

    protected $table = 'matricula';
    protected $primaryKey = 'ID';
    public $timestamps = false; // 👈 evita que busque created_at/updated_at

    protected $fillable = [
        'COD_ESCUELA','NOM_ESCUELA','COD_ALUMNO','NOM_ALUMNO',
        'COD_CURSO','DES_CURSO','COD_TURNO','COD_PRO','PROFESOR',
        'GRUPO','ENCUESTADO','IP','FECHA_REG','SEDE','TIPO'
    ];

    public function respuestas()
    {
        return $this->hasMany(EncRespuestas::class, 'cod_alu', 'COD_ALUMNO')
            ->whereColumn('enc_respuestas.cod_cur', 'matricula.COD_CURSO')
            ->whereColumn('enc_respuestas.cod_pro', 'matricula.COD_PRO')
            ->whereRaw('TRIM(enc_respuestas.turno) = TRIM(matricula.COD_TURNO)');
    }
}
