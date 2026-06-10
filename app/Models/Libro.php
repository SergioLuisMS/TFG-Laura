<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Libro extends Model
{
    use HasFactory;

    // La tabla se llama 'books' aunque el modelo se llame 'Libro'
    protected $table = 'books';

    protected $fillable = ['title', 'author', 'genre', 'cover_url', 'user_id'];
    
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'book_user', 'book_id', 'user_id')
            ->withPivot('estado', 'puntuacion')
            ->withTimestamps();
    }
}
