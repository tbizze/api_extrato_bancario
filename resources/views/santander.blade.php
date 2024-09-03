<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                @php
                    dump($transactions);
                @endphp

                <form method="GET" action="{{ route('santander.extrato') }}" class="p-4 flex gap-5 bg-gray-100">

                    <div class="">
                        <label for="initial_date" class="mr-2">Data início:</label>
                        <input type="date" name="initial_date" id="initial_date" value="{{ $initial_date }}"
                            class="border border-gray-300 p-2 rounded">
                    </div>
                    <div class="">
                        <label for="finalDate" class="mr-2">data fim:</label>
                        <input type="date" name="finalDate" id="finalDate" value="{{ $finalDate }}"
                            class="border border-gray-300 p-2 rounded">
                    </div>
                    <div class="">
                        <label for="page" class="mr-2">Filter by Type:</label>
                        <select name="page" id="page" class="border border-gray-300 p-2 rounded w-44">
                            <option value="">Selecione a página</option>
                            @foreach ($pages as $item)
                                <option value="{{ $item['id'] }}" {{ $item['id'] == $page ? 'selected' : '' }}>
                                    {{ $item['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-8 py-2 rounded">Buscar</button>
                </form>

                <table class="min-w-full bg-white">
                    <thead>
                        <tr class=" bg-slate-600 text-white">
                            <th class="py-2 px-4 border-b ">Data</th>
                            <th class="py-2 px-4 border-b text-right">Valor</th>
                            <th class="py-2 px-4 border-b text-left">Tipo</th>
                            <th class="py-2 px-4 border-b text-left">Histórico</th>
                            <th class="py-2 px-4 border-b text-left">Complemento</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions['_content'] as $item)
                            <tr class=" text-sm border-b">
                                <td class="py-2 px-4 text-center text-sm">
                                    {{ $item['transactionDate'] }}
                                </td>
                                <td class="py-2 px-4 text-sm text-right">
                                    {{ $item['amount'] }}
                                </td>
                                <td class="py-2 px-4 text-sm">
                                    {{ $item['creditDebitType'] }}
                                </td>
                                <td class="py-2 px-4 text-sm">
                                    {{ $item['transactionName'] }}
                                </td>
                                <td class="py-2 px-4 text-sm">
                                    {{ $item['historicComplement'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</x-app-layout>
