<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CorrectionRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_name',
        'email',
        'document_type',
        'description',
        'attachment_path',
        'status',
        'notes',
        'price',
        'is_paid',
        'completed_at',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'completed_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in-progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function markAsInProgress()
    {
        $this->update(['status' => 'in-progress']);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsPaid()
    {
        $this->update(['is_paid' => true]);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isInProgress()
    {
        return $this->status === 'in-progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }
}
