<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavMenuItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function url(): string
    {
        return $this->link_type === 'anchor' ? '#' . $this->target : $this->target;
    }
}
