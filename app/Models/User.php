<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'role', 'email_verified_at', 'google_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
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
        ];
    }
        protected static function boot()
    {
        parent::boot();

        static::updating(function ($repairRequest) {
            // ตรวจสอบการเปลี่ยนแปลงสถานะ
            if ($repairRequest->isDirty('StatusID')) {
                // ส่งการแจ้งเตือนให้ผู้ที่สร้างคำร้อง
                $user = User::find($repairRequest->user_id);
                if ($user) {
                    $message = "สถานะของคำร้องซ่อม TicketID: " . $repairRequest->TicketNumber . " ได้เปลี่ยนแปลงแล้ว";
                    (new self)->sendLineNotify($message, $user->line_token); // ส่งข้อความไปยังผู้ใช้
                }
            }
        });
    }

    private function sendLineNotify($message, $token = null)
    {
        $token = $token ?? Auth::user()->line_token; // ใช้ token ของผู้ใช้ถ้าไม่ระบุ
        if (!$token) return;

        $response = Http::withHeaders(['Authorization' => 'Bearer ' . $token])
            ->asForm()
            ->post('https://notify-api.line.me/api/notify', ['message' => $message]);

        if ($response->successful()) {
            Log::info('LINE Notify sent successfully.');
        } else {
            Log::error('Error sending LINE Notify: ' . $response->body());
        }
    }

}
