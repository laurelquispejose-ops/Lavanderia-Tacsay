<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table = 'servicios';
    protected $primaryKey = 'IdServicio';
    public $timestamps = true;

    protected $fillable = [
        'Nombre',
        'TipoServicio',
        'Precio'
    ];

    // Relación con DetalleOrden
    public function detalles()
    {
        return $this->hasMany(DetalleOrden::class, 'IdServicio', 'IdServicio');
    }
}
