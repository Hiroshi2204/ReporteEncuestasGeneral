<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Persona extends Model
{
    protected $table = 'persona';
    protected $fillable = array(
                            'numero_documento',
                            'tipo_documento_id',
                            'nombres',
                            'apellido_paterno',
                            'apellido_materno',
                            'celular',
                            'correo',
                            'distrito_id',
                            'direccion'
                        );
    protected $primaryKey = 'id';
    protected $hidden = [
        'created_at', 'updated_at','deleted_at'
    ];
    public function tipo_documento(){
        return $this->belongsTo(TipoDocumento::class,'tipo_documento_id','id');
    }
    public function distrito(){
        return $this->belongsTo(Distritos::class,'distrito_id','id');
    }
    public function rol(){
        return $this->belongsTo(Rol::class,'rol_id','id');
    }
}
