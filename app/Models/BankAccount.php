<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = ['account_agency', 'account_number', 'bank_name', 'company_id'];

    public function company(): mixed
    {
        return $this->belongsTo(Company::class);
    }
}
