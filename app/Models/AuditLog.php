<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, string $description, ?string $entityType = null, ?int $entityId = null): void
    {
        try {
            static::create([
                'user_id' => session('admin_user_id') ?? auth()->id(),
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
                'ip_address' => request()?->ip(),
            ]);
        } catch (\Throwable $e) {
            // Denetim günlüğü hatası ana işlemi kesintiye uğratmasın
        }
    }
}
