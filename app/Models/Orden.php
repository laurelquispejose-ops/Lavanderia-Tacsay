<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Orden extends Model
{
    use HasFactory;

    protected $table = 'ordenes'; // Nombre de la tabla en la BD

    protected $primaryKey = 'IdOrden'; // Clave primaria

    protected $fillable = [
        'IdCliente',
        'IdEmpleado',
        'FechaEntrada',
        'FechaEntrega',
        'FechaOrden',
        'Estado',
        'PrecioTotal',
        'ACuenta',
        'Saldo',
        'MetodoPago',
        'EstadoPago',
        'FechaPago'
    ];

    public $timestamps = true;

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'IdCliente');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'IdEmpleado');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleOrden::class, 'IdOrden', 'IdOrden');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'IdOrden', 'IdOrden');
    }
    
    public function pago()
    {
        return $this->hasOne(Pago::class, 'IdOrden', 'IdOrden');
    }
}
