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
                        <h1 class="py-5 text-xl">Gerenciar Transações:
                            <span class=" text-md">
                                {{ $bankAccount->bank_name }}
                            </span>
                        </h1>
                        <a href="{{ route('bank-accounts.transactions.select', $bankAccount) }}"
                            class="rounded border-slate-700 bg-slate-700 py-3 px-4">Importar Transações
                        </a>
                    </div>

                    <table class="min-w-full w-full table-auto text-left">
                        <thead>
                            <tr class="bg-slate-600 ">
                                <th class="p-2 ">#</th>
                                <th class="p-2">Data</th>
                                <th>Histórico</th>
                                <th class=" text-right">Valor</th>
                                <th class=" pl-1">Tipo</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $item)
                                <tr class="text-sm border-b ">
                                    <td class="px-2 py-2 w-10">{{ $item->id }}</td>
                                    <td class="px-2 py-2">{{ $item->date }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td class=" text-right">{{ number_format($item->amount, 2, ',', '.') }}</td>
                                    <td class="pl-1 uppercase">{{ $item->type }}</td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
