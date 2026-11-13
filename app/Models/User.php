<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model User mewakili data akun pengguna dalam sistem StockIn.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Atribut yang harus disembunyikan saat serialisasi (misal toArray / JSON).
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Konversi tipe data otomatis (casting) untuk atribut tertentu.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Memastikan password selalu di-hash secara aman
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke entitas Bisnis: Satu pengguna dapat memiliki banyak bisnis.
     */
    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }
}
