<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_id',
        'environment',
        'token',
        'expires_in',
        'expires_at',
        'not-before-policy',
        'session_state',
        'status',
    ];

    /**
     * @return BelongsTo<BankAccount, AccessToken>
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}
