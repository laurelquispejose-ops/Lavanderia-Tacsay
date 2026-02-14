<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Cliente extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'clientes';

    protected $primaryKey = 'idCliente';

    protected $fillable = [
        'nombre',
        'apellido',
        'correoElectronico',
        'telefono',
        'direccion',
        'password'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getAuthIdentifierName()
    {
        return 'correoElectronico';
    }

    public function ordenes()
    {
        return $this->hasMany(\App\Models\Orden::class, 'IdCliente');
    }
}
