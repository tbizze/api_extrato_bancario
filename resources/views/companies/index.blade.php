<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Empresas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center">
                        <h1 class="py-5 text-xl">Gerenciar Empresas</h1>
                        <a href="{{ route('companies.create') }}"
                            class="rounded border-slate-700 bg-slate-700 py-3 px-4">Adicionar
                            Empresa</a>
                    </div>
                    <table class="min-w-full w-full table-auto text-left">
                        <thead>
                            <tr class="bg-slate-600 ">
                                <th class="py-2">Nome</th>
                                <th>CNPJ</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($companies as $item)
                                <tr class="text-sm border-b ">
                                    <td class="px-2 py-2">{{ $item->name }}</td>
                                    <td>{{ $item->cnpj }}</td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="{{ route('companies.edit', $item) }}"
                                                class="border border-slate-700 px-2 rounded-md bg-slate-600">Editar</a>
                                            <form action="{{ route('companies.destroy', $item) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    class="border border-slate-700 px-2 rounded-md bg-slate-600">Deletar</button>
                                            </form>
                                        </div>
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
