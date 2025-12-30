<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NniSearch extends Model
{
    protected $table = 'nni_searches';

    protected $fillable = [
        'nni',
        'nom_fr',
        'prenom_fr',
        'date_naissance',
        'lieu_naissance_fr',
        'ip',
        'user_id',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];
}
