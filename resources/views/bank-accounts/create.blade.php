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

                    <form action="{{ route('bank-accounts.store') }}" method="POST" class="w-full max-w-lg">
                        @csrf
                        {{-- @method('POST') --}}

                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-400 text-xs font-bold mb-2"
                                    for="bank_name">
                                    Nome do Banco
                                </label>
                                <input
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    type="text" id="bank_name" name="bank_name" value="{{ old('bank_name') }}"
                                    placeholder="Banco do Brasil">
                                @error('bank_name')
                                    <div class="text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                                <label class="block uppercase tracking-wide text-gray-400 text-xs font-bold mb-2"
                                    for="account_agency">
                                    Número da agência
                                </label>
                                <input
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                                    type="text" id="account_agency" name="account_agency"
                                    value="{{ old('account_agency') }}" placeholder="2194">
                                @error('account_agency')
                                    <div class="text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="w-full md:w-1/2 px-3">
                                <label class="block uppercase tracking-wide text-gray-400 text-xs font-bold mb-2"
                                    for="account_number">
                                    Número da conta
                                </label>
                                <input
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    type="text" id="account_number" name="account_number"
                                    value="{{ old('account_number') }}" placeholder="130014564">
                                @error('account_number')
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
