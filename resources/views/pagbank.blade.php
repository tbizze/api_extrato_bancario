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

                <form method="GET" action="{{ route('extrato') }}" class="p-4 flex gap-5 bg-gray-100">

                    <div class="">
                        <label for="data_movimento" class="mr-2">Defina a data:</label>
                        <input type="date" name="data_movimento" id="data_movimento" value="{{ $data_movimento }}"
                            class="border border-gray-300 p-2 rounded">
                    </div>
                    <div class="">
                        <label for="type" class="mr-2">Defina o tipo:</label>
                        <select name="type" id="type" class="border border-gray-300 p-2 rounded w-44">
                            <option value="">Select Type</option>
                            @foreach ($types as $item)
                                <option value="{{ $item['id'] }}" {{ $item['id'] == $type ? 'selected' : '' }}>
                                    {{ $item['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="">
                        <label for="page" class="mr-2">Defina a página</label>
                        <select name="page" id="page" class="border border-gray-300 p-2 rounded w-44">
                            <option value="">Selecione página</option>
                            @foreach ($pages as $item)
                                <option value="{{ $item['id'] }}" {{ $item['id'] == $page ? 'selected' : '' }}>
                                    {{ $item['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-8 py-2 rounded">Buscar</button>
                </form>

                <div
                    class="relative flex flex-col w-full h-full overflow-scroll text-gray-700 bg-white shadow-md bg-clip-border">
                    <table class="w-full text-center table-auto min-w-max bg-white ">
                        <thead>
                            <tr class=" bg-slate-600 text-white">
                                <th class="py-2 px-4">Data</th>
                                <th class="py-2 px-4">Valor <br>bruto</th>
                                <th class="py-2 px-4">Taxa</th>
                                <th class="py-2 px-4">Tarifa</th>
                                <th class="py-2 px-4">Valor <br>líquido</th>
                                <th class="py-2 px-4">Status</th>
                                <th class="py-2 px-4">Cód. PIX</th>
                                <th class="py-2 px-4">NSU</th>
                                <th class="py-2 px-4">Nº <br>Maquininha</th>
                                <th class="py-2 px-4">Financeira</th>
                                <th class="py-2 px-4">Canal</th>
                                <th class="py-2 px-4">Plano / <br>Parcela</th>
                                <th class="py-2 px-4">Qde. <br>Parcela</th>
                            </tr>
                        </thead>
                        <tbody class="">
                            @foreach ($transactions['detalhes'] as $item)
                                <tr class=" text-sm border-b">
                                    <td class="py-2 px-4 text-sm">
                                        {{ $item['data_venda_ajuste'] }} {{ $item['hora_venda_ajuste'] }}
                                    </td>
                                    <td class="py-2 px-4 text-right">
                                        {{ number_format($item['valor_total_transacao'], 2, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-4 text-right">
                                        {{ number_format($item['taxa_intermediacao'], 2, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-4 text-right">
                                        {{ number_format($item['tarifa_intermediacao'], 2, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-4 text-right">
                                        {{ number_format($item['valor_liquido_transacao'], 2, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-4 ">
                                        {{ $item['status_pagamento'] }}
                                    </td>
                                    <td class="py-2 px-4 ">
                                        {{ $item['tx_id'] }}
                                    </td>
                                    <td class="py-2 px-4 ">
                                        {{ $item['nsu'] }}
                                    </td>
                                    <td class="py-2 px-4 text-right">
                                        {{ $item['numero_serie_leitor'] }}
                                    </td>
                                    <td class="py-2 px-4 ">
                                        {{ $item['instituicao_financeira'] }}
                                    </td>
                                    <td class="py-2 px-4 ">
                                        {{ $item['canal_entrada'] }}
                                    </td>
                                    <td class="py-2 px-4 ">
                                        {{ $item['plano'] }} / {{ $item['parcela'] }}
                                    </td>
                                    <td class="py-2 px-4 ">
                                        {{ $item['quantidade_parcela'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
