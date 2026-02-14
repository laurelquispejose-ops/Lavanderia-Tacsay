<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Empleado extends Authenticatable
{
    use HasFactory;

    protected $table = 'empleados';

    protected $primaryKey = 'IdEmpleado';

    protected $fillable = [
        'Nombre',
        'CorreoElectronico',
        'Telefono',
        'Cargo',
        'Direccion',
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
        return 'CorreoElectronico';
    }
}
