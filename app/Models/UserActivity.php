<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $fillable = ['user_id', 'action', 'description', 'ip_address', 'user_agent'];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Fungsi Helper Global untuk mencatat aktivitas dengan mudah.
     */
    public static function log($action, $description)
    {
        if (auth()->check()) {
            self::create([
                'user_id'     => auth()->id(),
                'action'      => $action,
                'description' => $description,
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
            ]);
        }
    }

    /**
     * Boot method: Trigger otomatis yang berjalan di background
     */
    protected static function booted()
    {
        // Berjalan otomatis SETELAH aktivitas baru berhasil disimpan (created)
        static::created(function ($activity) {
            $maxRecords = 500;
            
            // Hitung total aktivitas user ini
            $count = static::where('user_id', $activity->user_id)->count();
            
            // Jika melebihi batas, hapus yang paling lama (First In First Out)
            if ($count > $maxRecords) {
                $oldestIds = static::where('user_id', $activity->user_id)
                    ->orderBy('created_at', 'asc') // Urutkan dari yang paling tua
                    ->limit($count - $maxRecords)
                    ->pluck('id');
                
                static::whereIn('id', $oldestIds)->delete();
            }
        });
    }
}