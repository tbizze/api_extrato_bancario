<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Usuários') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center">
                        <h1 class="py-5 text-xl">Novo Usuários</h1>
                        <a href="{{ route('users.create') }}"
                            class="rounded border-slate-700 bg-slate-700 py-3 px-4">Adicionar
                            Usuário</a>
                    </div>


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
