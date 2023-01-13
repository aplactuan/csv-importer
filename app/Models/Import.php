<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Import extends Model
{
    use HasFactory;

    protected $fillable= [
        'model',
        'file_path',
        'file_name',
        'total_rows',
        'processed_rows',
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime'
    ];

    public function scopeNotCompleted(Builder $query)
    {
        $query->whereNull('completed_at');
    }

    public function scopeForModel(Builder $query, string $model)
    {
        $query->where('model', $model);
    }

    public function percentCompleted(): int
    {
        return floor(($this->processed_rows / $this->total_rows) * 100);
    }
}
