<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TAlumnos extends Model
{
    use HasFactory;
    protected $table = 't_alumnos';
    protected $fillable = array(
                            'COD_ALUMNO',
                            'NOM_ALUMNO',
                        );
    //protected $primaryKey = 'ID';
    // protected $hidden = [
    //     'created_at', 'updated_at','deleted_at'
    // ];
}
