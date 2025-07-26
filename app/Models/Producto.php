<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table = 'producto';
    protected $fillable = array(
                            'nom_producto',
                            'cod_producto',
                            'color_id',
                            'lote',
                            'largo',
                            //'origen',
                            'espesor',
                            'estado_registro',
                        );
    protected $primaryKey = 'id';
    protected $hidden = [
        'created_at', 'updated_at','deleted_at'
    ];
    public function color(){
        return $this->belongsTo(Color::class,'color_id','id');
    }
    public function registro_entreda_detalle(){
        return $this->hasMany(RegistroEntradaDetalle::class,'producto_id','id');
    }
    public function registro_salida_detalle(){
        return $this->hasMany(RegistroSalidaDetalle::class,'producto_id','id');
    }
}
