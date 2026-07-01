<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        // Permitir la asignación masiva de la suscripción a la newsletter.
        'newsletter',
    ];
    
    /**
     * Accesor rápido para obtener la dirección principal
     */
    public function primaryAddress()
    {
        return $this->hasOne(UserAddress::class)->where('es_principal', true);
    }

    /**
     * Relación con todas las direcciones del usuario
     */
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // Castear el campo de la newsletter como booleano de forma automática.
            'newsletter' => 'boolean',
        ];
    }

    /**
     * Relación con CartItem
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Relación con Order
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relación con Favoritos
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}
