<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Empresa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center">
                        <h1 class="py-5 text-xl">Editar Conta bancária</h1>

                    </div>

                    <form action="{{ route('bank-accounts.update', $bankAccount) }}" method="POST"
                        class="w-full max-w-lg">
                        @csrf
                        @method('PUT')

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
