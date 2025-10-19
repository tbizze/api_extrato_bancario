<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagbankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_id',
        'cod_transacao',
        'dt_transacao',
        'dt_pgto_prevista',
        'dt_pgto_efetiva',
        'valor_transacao',
        'valor_tarifas',
        'qde_parcelas',
        'description',
        'id_leitor',
        'tx_id',
        'tipo',
        'status',
        'notes',
    ];
}
