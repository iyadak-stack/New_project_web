<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\User; 

class Contact extends Model
{
    use HasFactory;

    protected $table = 'contacts';
    
    protected $primaryKey = 'contact_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'contact_id',
        'line_id',
        'discord_id',
        'google_meet_link',
        'zoom_link',
        'Users_user_id',
    ];

    /**
     * ความสัมพันธ์ BelongTo กับ User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'Users_user_id', 'user_id');
    }
}