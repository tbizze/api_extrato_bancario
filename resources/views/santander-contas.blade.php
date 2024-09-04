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
                    dump($contas);
                @endphp

                <form method="GET" action="{{ route('santander.contas') }}" class="p-4 flex gap-5 bg-gray-100">
                    <div class="">
                        <label for="page" class="mr-2">Página:</label>
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
                <div
                    class="relative flex flex-col w-full h-full overflow-auto text-gray-700 bg-white shadow-md bg-clip-border">
                    <table class="min-w-full w-full table-auto bg-white">
                        <thead>
                            <tr class=" bg-slate-600 text-white text-center">
                                <th class="py-2 px-4 border-b">Banco</th>
                                <th class="py-2 px-4 border-b">Agência</th>
                                <th class="py-2 px-4 border-b">Conta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contas['_content'] as $item)
                                <tr class=" text-sm border-b text-center">
                                    <td class="py-2 px-4  text-sm">
                                        {{ $item['compeCode'] }}
                                    </td>
                                    <td class="py-2 px-4 text-sm ">
                                        {{ $item['branchCode'] }}
                                    </td>
                                    <td class="py-2 px-4 text-sm">
                                        {{ $item['number'] }}
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
