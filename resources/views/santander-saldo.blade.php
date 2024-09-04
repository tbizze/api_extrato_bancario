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
                    dump($saldo);
                @endphp

                <div
                    class="relative flex flex-col w-full h-full overflow-auto text-gray-700 bg-white shadow-md bg-clip-border">
                    <table class="min-w-full w-full table-auto bg-white">
                        <thead>
                            <tr class=" bg-slate-600 text-white text-center">
                                <th class="py-2 px-4 border-b">Saldo disponível</th>
                                <th class="py-2 px-4 border-b">Saldo bloqueado</th>
                                <th class="py-2 px-4 border-b">Saldo disponível + estimado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class=" text-sm border-b text-center">
                                <td class="py-2 px-4  text-sm">
                                    {{ number_format($saldo['availableAmount'], 2, ',', '.') }}
                                </td>
                                <td class="py-2 px-4 text-sm ">
                                    {{ number_format($saldo['blockedAmount'], 2, ',', '.') }}
                                </td>
                                <td class="py-2 px-4 text-sm">
                                    {{ number_format($saldo['automaticallyInvestedAmount'], 2, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-sm m-3 ">
                    <div class="">Saldo disponível para uso imediato.</div>
                    <div class="">Saldo bloqueado, não disponível para uso imediato.</div>
                    <div class="">Saldo disponível, incluindo Valor de Resgate Automático.</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
