<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalCase extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cases';

    protected $fillable = [
        'case_number',
        'client_id',
        'case_type_id',
        'case_status_id',
        'title',
        'description',
        'lawyer_id',
        'assigned_date',
        'closed_date',
        'court_name',
        'judge_name',
        'opponent_name',
        'notes',
    ];

    protected $casts = [
        'assigned_date' => 'datetime',
        'closed_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
