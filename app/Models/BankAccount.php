<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_agency',
        'account_number',
        'bank_name',
        'company_id',
        'bank_id',
        'client_id',
        'client_secret',
        'certificate_path',
        'key_path',
    ];

    /**
     * @return BelongsTo<Company, BankAccount>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return HasMany<Transaction>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return BelongsTo<Bank, BankAccount>
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * @return HasMany<AccessToken>
     */
    public function accessTokens(): HasMany
    {
        return $this->hasMany(AccessToken::class);
    }
}
