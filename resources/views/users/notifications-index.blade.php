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
                        <h1 class="py-5 text-xl">Gerenciar Usuários</h1>
                        <a href="{{ route('users.create') }}"
                            class="rounded border-slate-700 bg-slate-700 py-3 px-4">Adicionar
                            Usuário</a>
                    </div>
                    <table class="min-w-full w-full table-auto text-left">
                        <thead>
                            <tr class="bg-slate-600 ">
                                <th class="py-2 pl-2">Data</th>
                                <th class="py-2 pl-2">Título</th>
                                <th>Mensagem</th>
                                <th>Lido</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notifications as $notification)
                                <tr class="text-sm border-b ">
                                    <td class="px-2 py-2">{{ $notification->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ $notification->data['title'] ?? '' }}</td>
                                    <td>{{ $notification->data['message'] }}</td>
                                    <td>
                                        @if ($notification->read_at)
                                            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24"
                                                height="24" viewBox="0,0,256,256">
                                                <g fill="#4b5563" fill-rule="nonzero" stroke="none" stroke-width="1"
                                                    stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10"
                                                    stroke-dasharray="" stroke-dashoffset="0" font-family="none"
                                                    font-weight="none" font-size="none" text-anchor="none"
                                                    style="mix-blend-mode: normal">
                                                    <g transform="scale(10.66667,10.66667)">
                                                        <path
                                                            d="M19.98047,5.99023c-0.2598,0.00774 -0.50638,0.11632 -0.6875,0.30273l-10.29297,10.29297l-3.29297,-3.29297c-0.25082,-0.26124 -0.62327,-0.36647 -0.97371,-0.27511c-0.35044,0.09136 -0.62411,0.36503 -0.71547,0.71547c-0.09136,0.35044 0.01388,0.72289 0.27511,0.97371l4,4c0.39053,0.39037 1.02353,0.39037 1.41406,0l11,-11c0.29576,-0.28749 0.38469,-0.72707 0.22393,-1.10691c-0.16075,-0.37985 -0.53821,-0.62204 -0.9505,-0.60988z">
                                                        </path>
                                                    </g>
                                                </g>
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24"
                                                height="24" viewBox="0,0,256,256">
                                                <g fill="#4b5563" fill-rule="nonzero" stroke="none" stroke-width="1"
                                                    stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10"
                                                    stroke-dasharray="" stroke-dashoffset="0" font-family="none"
                                                    font-weight="none" font-size="none" text-anchor="none"
                                                    style="mix-blend-mode: normal">
                                                    <g transform="scale(10.66667,10.66667)">
                                                        <path
                                                            d="M4.99023,3.99023c-0.40692,0.00011 -0.77321,0.24676 -0.92633,0.62377c-0.15312,0.37701 -0.06255,0.80921 0.22907,1.09303l6.29297,6.29297l-6.29297,6.29297c-0.26124,0.25082 -0.36647,0.62327 -0.27511,0.97371c0.09136,0.35044 0.36503,0.62411 0.71547,0.71547c0.35044,0.09136 0.72289,-0.01388 0.97371,-0.27511l6.29297,-6.29297l6.29297,6.29297c0.25082,0.26124 0.62327,0.36648 0.97371,0.27512c0.35044,-0.09136 0.62411,-0.36503 0.71547,-0.71547c0.09136,-0.35044 -0.01388,-0.72289 -0.27512,-0.97371l-6.29297,-6.29297l6.29297,-6.29297c0.29576,-0.28749 0.38469,-0.72707 0.22393,-1.10691c-0.16075,-0.37985 -0.53821,-0.62204 -0.9505,-0.60988c-0.2598,0.00774 -0.50638,0.11632 -0.6875,0.30273l-6.29297,6.29297l-6.29297,-6.29297c-0.18827,-0.19353 -0.4468,-0.30272 -0.7168,-0.30273z">
                                                        </path>
                                                    </g>
                                                </g>
                                            </svg>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            {{-- <a href="{{ route('users.edit', $notification) }}"
                                                class="border border-slate-700 px-2 rounded-md bg-slate-600">Editar</a> --}}
                                            <form action="{{ route('notifications.destroy', $notification) }}"
                                                method="POST" class="d-inline">
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
