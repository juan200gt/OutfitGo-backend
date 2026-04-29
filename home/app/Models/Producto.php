<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    /** @use HasFactory<\Database\Factories\ProductoFactory> */
    use HasFactory;

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function tallas()
    {
        return $this->belongsToMany(Talla::class, 'producto_talla');
    }

    public function colores()
    {
        return $this->belongsToMany(Color::class, 'producto_color');
    }

    public function imagenes()
    {
        return $this->hasMany(ImagenProducto::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function scopeAdulto($query)
    {
        return $query->where('publico', 'adulto');
    }

    public function scopeInfantil($query)
    {
        return $query->where('publico', 'infantil');
    }

    public function scopeUnisex($query)
    {
        return $query->where('publico', 'unisex');
    }

    public function scopePublico($query, $tipo)
    {
        if (in_array($tipo, ['adulto', 'infantil', 'unisex'])) {
            return $query->where('publico', $tipo);
        }
        return $query;
    }

    public function variantes()
    {
        return $this->hasMany(ProductoVariante::class);
    }

    protected $fillable = [
    'nombre', 'slug', 'descripcion', 'publico', 
    'url_imagen_principal', 'precio', 'stock', 
    'marca_id', 'categoria_id'
];

    public function historialPrecios()
    {
        return $this->hasMany(HistorialPrecio::class);
    }

    protected $casts = [
    'galeria' => 'array',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

}