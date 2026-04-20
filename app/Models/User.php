<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Kolom yang dapat diisi secara massal (Mass Assignment).
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_code', // Wajib untuk NIK
        'division_id',   // Wajib untuk relasi departemen
        'role',          // Wajib untuk hak akses
        'is_active',     // Status aktif karyawan
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /* -------------------------------------------------------------------------- */
    /* RELASI */
    /* -------------------------------------------------------------------------- */

    /**
     * Relasi ke Transaksi.
     * Satu user bisa memiliki banyak transaksi.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'employee_id');
    }

    /**
     * Relasi ke Divisi.
     * Mengambil data divisi dari user ini.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /* -------------------------------------------------------------------------- */
    /* ACCESSOR */
    /* -------------------------------------------------------------------------- */

    /**
     * Accessor Saldo: $user->balance
     * Menghitung total saldo dari transaksi yang sudah di-post.
     */
    public function getBalanceAttribute()
    {
        return $this->transactions()
            ->where('status', \App\Enums\TransactionStatus::POSTED->value)
            ->with('items')
            ->get()
            ->sum(fn ($transaction) => $transaction->items->sum('subtotal'));
    }

    /* -------------------------------------------------------------------------- */
    /* HELPER ROLES */
    /* -------------------------------------------------------------------------- */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    public function isKaryawan(): bool
    {
        return $this->role === 'karyawan';
    }
}
