<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Transações') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Chama compomente para exibir flesh message --}}
                    <x-flash-message />

                    <div class="flex justify-between items-center">
                        <h1 class="py-5 text-xl">Importar Transações</h1>

                    </div>

                    <form action="{{ route('bank-accounts.transactions.import', $bankAccount) }}" method="POST"
                        class="w-full max-w-lg">
                        @csrf

                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-400 text-xs font-bold mb-2"
                                    for="bank_account">
                                    Banco e conta
                                </label>
                                <input
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    type="text" id="bank_account" name="bank_account" readonly
                                    value="{{ $bankAccount->bank->bank_name }} - {{ $bankAccount->account_agency }} / {{ $bankAccount->account_number }}"
                                    placeholder="Empresa de Exemplo">
                            </div>

                        </div>
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                                <label class="block uppercase tracking-wide text-gray-400 text-xs font-bold mb-2"
                                    for="initial_date">
                                    Data inicial
                                </label>
                                <input
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                                    type="date" id="initial_date" name="initial_date"
                                    value="{{ old('initial_date') }}" placeholder="02455784000132">
                                @error('initial_date')
                                    <div class="text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                                <label class="block uppercase tracking-wide text-gray-400 text-xs font-bold mb-2"
                                    for="final_date">
                                    Data final
                                </label>
                                <input
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                                    type="date" id="final_date" name="final_date" value="{{ old('final_date') }}"
                                    placeholder="02455784000132">
                                @error('final_date')
                                    <div class="text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <button type="submit" class="rounded border-slate-700 bg-slate-700 py-3 px-5">Processar
                                importação</button>
                            <a href="{{ route('bank-accounts.transactions.index', $bankAccount) }}"
                                class="rounded border-slate-700 bg-slate-700 py-3 px-5">Cancelar</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
