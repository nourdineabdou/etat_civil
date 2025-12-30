<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RhtCongeAgent extends Model
{
    protected $table = 'rhtcongeagent';

    protected $primaryKey = 'id';

    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'CDOS', 'NMAT', 'DDCG', 'DFCG', 'NBJA', 'NBJC', 'CMCG', 'MTCG',
        'TYPA', 'CCLO', 'DCLO', 'DEFF', 'CUTICRE', 'DATECRE', 'CUTIMOD', 'DATEMOD',
        'TYPCG', 'REF'
    ];

    protected $casts = [
        'DDCG' => 'date',
        'DFCG' => 'date',
        'DCLO' => 'date',
        'DEFF' => 'date',
        'DATECRE' => 'date',
        'DATEMOD' => 'date',
        'NBJA' => 'decimal:2',
        'NBJC' => 'decimal:2',
        'MTCG' => 'decimal:3',
    ];
}
