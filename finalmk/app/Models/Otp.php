<?php

namespace App\Models;

use App\Mail\OtpCodeMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Otp extends Model
{
    public const MAX_ATTEMPTS = 5;

    public const COOLDOWN_SECONDS = 60;

    public const TTL_MINUTES = 10;

    public const PURPOSE_REGISTRATION = 'registration';

    public const PURPOSE_PASSWORD_RESET = 'password_reset';

    protected $fillable = ['email', 'purpose', 'code_hash', 'attempts', 'expires_at', 'last_sent_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    /** Plain code for the OTP issued in this request only — never persisted. */
    public ?string $plainCode = null;

    /**
     * Issue a fresh code and email it. Returns false when delivery fails.
     */
    public static function send(string $email, string $purpose, ?string $name = null): bool
    {
        $otp = static::issue($email, $purpose);

        try {
            Mail::to($email)->send(new OtpCodeMail($otp->plainCode, $purpose, $name ?? ''));
        } catch (\Throwable $e) {
            Log::error('OTP email failed to send', [
                'email' => $email,
                'purpose' => $purpose,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        return true;
    }

    public static function issue(string $email, string $purpose): self
    {
        static::query()->where('expires_at', '<', now()->subDay())->delete();

        $code = (string) random_int(100000, 999999);

        $otp = static::query()->updateOrCreate(
            ['email' => $email, 'purpose' => $purpose],
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::TTL_MINUTES),
                'last_sent_at' => now(),
            ]
        );

        $otp->plainCode = $code;

        return $otp;
    }

    public static function active(string $email, string $purpose): ?self
    {
        return static::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->first();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isLockedOut(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }

    public function attemptsLeft(): int
    {
        return max(0, self::MAX_ATTEMPTS - $this->attempts);
    }

    public function secondsUntilResend(): int
    {
        if (! $this->last_sent_at) {
            return 0;
        }

        $elapsed = (int) $this->last_sent_at->diffInSeconds(now());

        return max(0, self::COOLDOWN_SECONDS - $elapsed);
    }

    public function matches(string $code): bool
    {
        return Hash::check($code, $this->code_hash);
    }

    public function registerFailedAttempt(): void
    {
        $this->increment('attempts');
        $this->refresh();
    }

    public function consume(): void
    {
        $this->delete();
    }
}
