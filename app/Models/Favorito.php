<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorito extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = ['user_id', 'favorito_type', 'favorito_id'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function favorito(): MorphTo
    {
        return $this->morphTo();
    }
}
