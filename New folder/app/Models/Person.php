<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $fillable = [
        'full_name',
        'national_code',
        'phone',
        'address',
        'description',
        'category',
        'photo_path',
        'face_embedding',
    ];

    protected $casts = [
        'face_embedding' => 'array',
    ];

    public function getPhotoUrlAttribute(): string
    {
        return $this->photo_path
            ? asset('storage/' . $this->photo_path)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&background=333&color=fff&size=128';
    }

    public function getCategoryBadgeClassAttribute(): string
    {
        return match ($this->category) {
            'vip' => 'bg-gradient-to-r from-yellow-400 to-amber-500 text-black font-bold',
            'employee' => 'bg-gradient-to-r from-emerald-500 to-green-600 text-white',
            'guest' => 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'vip' => 'VIP',
            'employee' => 'کارمند',
            'guest' => 'مهمان',
        };
    }
}
