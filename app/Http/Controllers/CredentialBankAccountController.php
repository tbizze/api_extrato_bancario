<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Carbon\Carbon;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Crypt, Storage};
use Illuminate\View\View;

class CredentialBankAccountController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankAccount $bankAccount): View
    {
        // Se Cliente ID e Client Secret no BD, então descriptografa.
        if (isset($bankAccount->client_id)) {
            $clientId               = Crypt::decryptString($bankAccount->client_id);
            $bankAccount->client_id = $clientId;
        }

        if (isset($bankAccount->client_secret)) {
            $clientSecret               = Crypt::decryptString($bankAccount->client_secret);
            $bankAccount->client_secret = $clientSecret;
        }

        return view('bank-accounts.credentials-create', compact('bankAccount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        // Validação de dados.
        $validated = $request->validate([
            'client_id'     => 'required|string',
            'client_secret' => 'required|string',
            'certificate'   => 'nullable|file',
            'key'           => 'nullable|file',
        ]);

        // Criptografar ClientId e ClientSecret.
        $encryptedClientId     = Crypt::encryptString($validated['client_id']);
        $encryptedClientSecret = Crypt::encryptString($validated['client_secret']);

        // Obter timestamp atual.
        $timestamp = Carbon::now()->format('Ymd-His');

        // Se submetido arquivo para certificate.
        if ($request->hasFile('certificate')) {
            $certificateName = 'certificate-' . $bankAccount->id . '_' . $timestamp . '.' . $request->file('certificate')->getClientOriginalExtension();
            $certificatePath = $request->file('certificate')->storeAs(
                '/certificates',
                $certificateName,
                'private'
            );
        }

        // Se submetido arquivo para key.
        if ($request->hasFile('key')) {
            $keyName = 'key-' . $bankAccount->id . '_' . $timestamp . '.' . $request->file('key')->getClientOriginalExtension();
            $keyPath = $request->file('key')->storeAs(
                '/keys',
                $keyName,
                'private'
            );
        }

        // Antes de salvar BD, coloca nome antigo dos arquivos em variável.
        $certificateOld = $bankAccount->certificate_path;
        $keyOld         = $bankAccount->key_path;

        // Salvar os dados no banco de dados.
        $bankAccount->update([
            'client_id'        => $encryptedClientId,
            'client_secret'    => $encryptedClientSecret,
            'certificate_path' => $certificateName ?? $bankAccount->certificate_path,
            'key_path'         => $keyName ?? $bankAccount->key_path,
        ]);

        // Excluir certificate antigo se existir, e se for submetido novo.
        if ($certificateOld != '' && isset($certificateName)) {
            Storage::disk('private')->delete('/certificates/' . $certificateOld);
        }

        // Excluir key antiga se existir, e se for submetido novo.
        if ($keyOld != '' && isset($keyName)) {
            Storage::disk('private')->delete('/keys/' . $keyOld);
        }

        return redirect()->route('bank-accounts.index')->with('success', 'Credenciais e arquivos salvos com sucesso!');
    }
}
