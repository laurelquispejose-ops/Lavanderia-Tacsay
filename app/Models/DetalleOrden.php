<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleOrden extends Model
{
    protected $table = 'detalle_orden';
    protected $primaryKey = 'IdDetalle';
    public $timestamps = true;

    protected $fillable = [
        'IdOrden',
        'IdPrenda',
        'IdServicioLavado',
        'IdServicioPlanchado',
        'Cantidad',
        'Peso'
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'IdOrden', 'IdOrden');
    }

    public function prenda()
    {
        return $this->belongsTo(Prenda::class, 'IdPrenda', 'IdPrenda');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'IdServicio');
    }


    public function servicioLavado()
    {
        return $this->belongsTo(Servicio::class, 'IdServicioLavado');
    }

    public function servicioPlanchado()
    {
        return $this->belongsTo(Servicio::class, 'IdServicioPlanchado');
    }
}
