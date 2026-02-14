<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prenda extends Model
{
    protected $table = 'prendas';
    protected $primaryKey = 'IdPrenda';
    public $timestamps = true;

    protected $fillable = [
        'TipoPrenda',
        'ColorPrenda'
    ];

    // Relación con DetalleOrden
    public function detalles()
    {
        return $this->hasMany(DetalleOrden::class, 'IdPrenda', 'IdPrenda');
    }
}
