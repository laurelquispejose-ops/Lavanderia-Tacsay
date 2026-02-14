<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';
    public $timestamps = true;

    protected $fillable = [
        'IdOrden',
        'IdTipoDocumento',
        'NumDocumento',
        'FechaPago',
        'MontoPagado',
        'MetodoPago'
    ];

    // Relación: este pago pertenece a una orden
    public function orden()
    {
        return $this->belongsTo(Orden::class, 'IdOrden', 'IdOrden');
    }

    // Si tienes una tabla `TipoDocumento` para esto:
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'IdTipoDocumento', 'IdTipoDocumento');
    }
}
