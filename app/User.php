<?php

namespace App;

use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
    //Añadimos la clase JWTSubject
use Tymon\JWTAuth\Contracts\JWTSubject;

    //Añadimos la implementación de JWT en nuestro modelo
class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'name', 'username', 'password','persona_id','estado_registro'
        ];
        

        /**
         * The attributes that should be hidden for arrays.
         *
         * @var array
         */
        protected $hidden = [
            'password', 'remember_token','created_at', 'updated_at','deleted_at','email_verified_at'
        ];

        /*
            Añadiremos estos dos métodos
        */
        public function getJWTIdentifier()
        {
            return $this->getKey();
        }
        public function getJWTCustomClaims()
        {
            return [];
        }
        public function setPasswordAttribute($value){
            $this->attributes['password']=Hash::make($value);
        }
        public function persona(){
            return $this->belongsTo(Persona::class,'persona_id','id');
        }
}
