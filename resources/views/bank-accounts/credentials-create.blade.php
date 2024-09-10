<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Contas Bancárias') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center">
                        <h1 class="py-5 text-xl">Nova Conta Bancária</h1>
                    </div>

                    <form action="{{ route('bank-accounts.credentials.update', $bankAccount) }}" method="POST"
                        enctype="multipart/form-data" class="w-full max-w-lg">
                        @csrf
                        @method('PUT')
                        {{-- @method('POST') --}}

                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-400 text-xs font-bold mb-2"
                                    for="bank_account">
                                    Banco / Agência / conta
                                </label>
                                <input
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    type="text" id="bank_account" name="bank_account" readonly
                                    value="{{ $bankAccount->bank->bank_name }} / {{ $bankAccount->account_agency }} / {{ $bankAccount->account_number }}">
                            </div>
                        </div>

                        {{-- Primeira linha --}}
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                                <label class="block uppercase tracking-wide text-gray-400 text-xs font-bold mb-2"
                                    for="client_id">
                                    User / Client Id
                                </label>
                                <input
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                                    type="text" id="client_id" name="client_id"
                                    value="{{ old('client_id', $bankAccount->client_id) }}">
                                @error('client_id')
                                    <div class="text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="w-full md:w-1/2 px-3">
                                <label class="block uppercase tracking-wide text-gray-400 text-xs font-bold mb-2"
                                    for="client_secret">
                                    Password / Client Secret
                                </label>
                                <input
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    type="text" id="client_secret" name="client_secret"
                                    value="{{ old('client_secret', $bankAccount->client_secret) }}">
                                @error('client_secret')
                                    <div class="text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Segunda linha --}}
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-400 text-xs font-bold mb-2"
                                    for="certificate">
                                    Certificado digital
                                </label>
                                @if ($bankAccount->certificate_path)
                                    <div class="text-sm text-blue-400 pb-2 font-bold">
                                        {{ asset($bankAccount->certificate_path) }}
                                    </div>
                                @endif
                                <input
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    type="file" id="certificate" name="certificate" value="{{ old('certificate') }}"
                                    placeholder="Banco do Brasil">
                                @error('certificate')
                                    <div class="text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        {{-- Terceira linha --}}
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-400 text-xs font-bold mb-2"
                                    for="key">
                                    Chave Key
                                </label>
                                @if ($bankAccount->key_path)
                                    <div class="text-sm text-blue-400 pb-2 font-bold">
                                        {{ asset($bankAccount->key_path) }}
                                    </div>
                                @endif
                                <input
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    type="file" id="key" name="key" value="{{ old('key') }}"
                                    placeholder="Banco do Brasil">
                                @error('key')
                                    <div class="text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit"
                                class="rounded border-slate-700 bg-slate-700 py-3 px-5">Salvar</button>
                            <a href="{{ route('bank-accounts.index') }}"
                                class="rounded border-slate-700 bg-slate-700 py-3 px-5">Cancelar</a>
                        </div>


                    </form>


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
