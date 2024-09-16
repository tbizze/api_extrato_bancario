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
                    {{-- Chama compomente para exibir flesh message --}}
                    <x-flash-message />

                    <div class="flex justify-between items-center">
                        <h1 class="py-5 text-xl">Nova Conta Bancária</h1>
                    </div>

                    <form action="{{ route('bank-accounts.store') }}" method="POST" class="w-full max-w-lg">
                        @csrf
                        {{-- Linha 1 --}}
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-400 text-xs font-bold mb-2"
                                    for="bank_id">
                                    Banco
                                </label>
                                <div class="relative">
                                    <select
                                        class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                        id="bank_id" name="bank_id">
                                        <option selected value="">Selecione banco</option>
                                        @foreach ($banks as $item)
                                            <option value="{{ $item['id'] }}">{{ $item['bank_name'] }}</option>
                                        @endforeach
                                    </select>
                                    <div
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                                        </svg>
                                    </div>
                                </div>
                                @error('bank_id')
                                    <div class="text-xs text-red-600">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>
                        {{-- Linha 2 --}}
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-400 text-xs font-bold mb-2"
                                    for="bank_name">
                                    Descrição
                                </label>
                                <input
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    type="text" id="bank_name" name="bank_name" value="{{ old('bank_name') }}"
                                    placeholder="Registre uma descrição">
                                @error('bank_name')
                                    <div class="text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        {{-- Linha 3 --}}
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
                        {{-- Botões --}}
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
