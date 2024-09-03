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
                    dump($dados);
                @endphp

                <form method="GET" action="{{ route('extrato') }}" class="p-4 flex gap-5 bg-gray-100">

                    <div class="">
                        <label for="date" class="mr-2">Filter by Date:</label>
                        <input type="date" name="date" id="date" value="{{ $date }}"
                            class="border border-gray-300 p-2 rounded">
                    </div>
                    <div class="">
                        <label for="type" class="mr-2">Filter by Type:</label>
                        <select name="type" id="type" class="border border-gray-300 p-2 rounded w-44">
                            <option value="">Select Type</option>
                            @foreach ($tipos as $item)
                                <option value="{{ $item['id'] }}" {{ $item['id'] == $tipo ? 'selected' : '' }}>
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
                            <th class="py-2 px-4 border-b">Valor bruto</th>
                            <th class="py-2 px-4 border-b">Taxa</th>
                            <th class="py-2 px-4 border-b">Tarifa</th>
                            <th class="py-2 px-4 border-b">Valor líquido</th>
                            <th class="py-2 px-4 border-b">Status</th>
                            <th class="py-2 px-4 border-b">Cód. PIX</th>
                            <th class="py-2 px-4 border-b">NSU</th>
                            <th class="py-2 px-4 border-b">Nº Maquininha</th>
                            <th class="py-2 px-4 border-b">Financeira</th>
                            <th class="py-2 px-4 border-b">Canal</th>
                            <th class="py-2 px-4 border-b">Plano / Parcela</th>
                            <th class="py-2 px-4 border-b">Qde. Parcela</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dados['detalhes'] as $item)
                            <tr class=" text-sm text-center border-b">
                                <td class="py-2 px-4  text-sm">
                                    {{ $item['data_inicial_transacao'] }} {{ $item['hora_inicial_transacao'] }}
                                </td>
                                <td class="py-2 px-4 ">
                                    {{ number_format($item['valor_total_transacao'], 2, ',', '.') }}
                                </td>
                                <td class="py-2 px-4 ">
                                    {{ number_format($item['taxa_intermediacao'], 2, ',', '.') }}
                                </td>
                                <td class="py-2 px-4 ">
                                    {{ number_format($item['tarifa_intermediacao'], 2, ',', '.') }}
                                </td>
                                <td class="py-2 px-4 ">
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
                                <td class="py-2 px-4 ">
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
</x-app-layout>
{{-- 
      "movimento_api_codigo" => "B2C9B5133D90CC02254F0648B9265C38"
      "tipo_registro" => "1"
      "estabelecimento" => "111107154"
      "data_inicial_transacao" => "2024-09-01"
      "hora_inicial_transacao" => "07:47:52"
      "data_venda_ajuste" => "2024-09-02"
      "hora_venda_ajuste" => "08:06:02"
      "tipo_evento" => "1"
      "tipo_transacao" => "1"
      "codigo_transacao" => "336F66463AAF4F298EE79E78B4F7CE6F"
      "codigo_venda" => "753300"
      "valor_total_transacao" => 30.0
      "valor_parcela" => 29.28
      "pagamento_prazo" => "U"
      "plano" => "1 "
      "parcela" => "1"
      "quantidade_parcela" => "0"
      "data_movimentacao" => "2024-09-02"
      "taxa_parcela_comprador" => null
      "tarifa_boleto_compra" => null
      "valor_original_transacao" => 30.0
      "taxa_parcela_vendedor" => null
      "taxa_intermediacao" => 0.72
      "tarifa_intermediacao" => 0.0
      "tarifa_boleto_vendedor" => null
      "taxa_rep_aplicacao" => null
      "valor_liquido_transacao" => 29.28
      "taxa_antecipacao" => 0.0
      "valor_liquido_antecipacao" => 0.0
      "status_pagamento" => "3"
      "identificador_revenda" => null
      "meio_pagamento" => "8"
      "instituicao_financeira" => "VISA ELECTRON"
      "canal_entrada" => "ME"
      "leitor" => "12"
      "meio_captura" => "1"
      "cod_banco" => ""
      "banco_agencia" => ""
      "conta_banco" => ""
      "num_logico" => ""
      "nsu" => "424510753300"
      "cartao_bin" => "498407"
      "cartao_holder" => "8281"
      "codigo_autorizacao" => "228072"
      "codigo_cv" => "753300"
      "numero_serie_leitor" => "542-310-478"
      "tx_id" => null
      "taxa_emissor" => null
      "taxa_adquirente" => null
       --}}
