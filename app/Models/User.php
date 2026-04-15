<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment)
     * Tambahkan kolom krusial lo di sini!
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_code', // Wajib ada untuk NIK
        'division_id',   // Wajib ada untuk relasi departemen
        'role',          // Wajib ada untuk hak akses
        'is_active',     // Untuk status karyawan
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
    /* RELASI                                   */
    /* -------------------------------------------------------------------------- */

    /**
     * Relasi ke Transaksi
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'employee_id');
    }

    /**
     * Relasi ke Divisi
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /* -------------------------------------------------------------------------- */
    /* ACCESSOR                                  */
    /* -------------------------------------------------------------------------- */

    /**
     * Accessor Saldo: $user->balance
     */
    public function getBalanceAttribute()
    {
        // Pake optional chaining atau null coalescing biar aman kalau transaksi null
        return $this->transactions()
            ->where('status', 'POSTED')
            ->with('items')
            ->get()
            ->sum(fn($transaction) => $transaction->items->sum('subtotal'));
    }

    /* -------------------------------------------------------------------------- */
    /* HELPER ROLES                                */
    /* -------------------------------------------------------------------------- */

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isPetugas()
    {
        return $this->role === 'petugas';
    }

    public function isKaryawan()
    {
        return $this->role === 'karyawan';
    }
}
