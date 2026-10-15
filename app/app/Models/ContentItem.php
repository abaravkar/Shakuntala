<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['title' => 'array', 'body' => 'array'];
    }

    public function translated(string $field, string $locale): string
    {
        $value = $this->$field;

        return $value[$locale] ?? $value['en'] ?? '';
    }
}
