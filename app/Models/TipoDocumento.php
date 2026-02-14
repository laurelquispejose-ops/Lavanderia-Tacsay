<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    protected $table = 'tipodocumento';
    protected $primaryKey = 'IdTipoDocumento';
    public $timestamps = true;

    protected $fillable = [
        'Nombre'
    ];

    // Relación con Pagos
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'IdTipoDocumento', 'IdTipoDocumento');
    }
}
