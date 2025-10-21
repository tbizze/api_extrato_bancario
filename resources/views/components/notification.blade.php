{{-- Fonte: https://www.creative-tim.com/twcomponents/component/free-tailwind-css-notification-component --}}

<div class="w-full h-full bg-gray-800 bg-opacity-90 top-0 overflow-y-auto overflow-x-hidden fixed sticky-0 z-50 hidden"
    id="chec-div">
    <div class="w-full absolute z-10 right-0 h-full overflow-x-hidden transform translate-x-0 transition ease-in-out duration-700"
        id="notificationsModal">
        <div class="2xl:w-3/12 bg-gray-100 dark:bg-gray-900 h-screen overflow-y-auto p-8 absolute right-0">

            {{-- Header das notifications --}}
            <div class="flex items-center justify-between">
                <div class=" flex items-end relative">
                    <p tabindex="0"
                        class="focus:outline-none text-2xl font-semibold leading-6 text-gray-800 dark:text-gray-200">
                        Notificações
                        @if (auth()->user()->unreadNotifications->count() > 0)
                            <div
                                class="absolute -top-1 -right-4 flex rounded-full w-4 h-4 text-xs items-center justify-center text-white bg-red-500">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </div>
                        @endif
                    </p>
                </div>

                <div class="flex gap-2 text-gray-600">
                    @if (auth()->user()->unreadNotifications->count() > 0)
                        <button type="button" onclick="markAllAsRead()" class="rounded-md hover:bg-gray-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24"
                                viewBox="0,0,256,256">
                                <g fill="#4b5563" fill-rule="nonzero" stroke="none" stroke-width="1"
                                    stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10"
                                    stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none"
                                    font-size="none" text-anchor="none" style="mix-blend-mode: normal">
                                    <g transform="scale(10.66667,10.66667)">
                                        <path
                                            d="M19.98047,5.99023c-0.2598,0.00774 -0.50638,0.11632 -0.6875,0.30273l-10.29297,10.29297l-3.29297,-3.29297c-0.25082,-0.26124 -0.62327,-0.36647 -0.97371,-0.27511c-0.35044,0.09136 -0.62411,0.36503 -0.71547,0.71547c-0.09136,0.35044 0.01388,0.72289 0.27511,0.97371l4,4c0.39053,0.39037 1.02353,0.39037 1.41406,0l11,-11c0.29576,-0.28749 0.38469,-0.72707 0.22393,-1.10691c-0.16075,-0.37985 -0.53821,-0.62204 -0.9505,-0.60988z">
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                    @endif
                    <button role="button" aria-label="close modal"
                        class="focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 rounded-md cursor-pointer hover:bg-gray-500/20"
                        onclick="notificationHandler(false)">
                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24"
                            viewBox="0,0,256,256">
                            <g fill="#4b5563" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt"
                                stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0"
                                font-family="none" font-weight="none" font-size="none" text-anchor="none"
                                style="mix-blend-mode: normal">
                                <g transform="scale(10.66667,10.66667)">
                                    <path
                                        d="M4.99023,3.99023c-0.40692,0.00011 -0.77321,0.24676 -0.92633,0.62377c-0.15312,0.37701 -0.06255,0.80921 0.22907,1.09303l6.29297,6.29297l-6.29297,6.29297c-0.26124,0.25082 -0.36647,0.62327 -0.27511,0.97371c0.09136,0.35044 0.36503,0.62411 0.71547,0.71547c0.35044,0.09136 0.72289,-0.01388 0.97371,-0.27511l6.29297,-6.29297l6.29297,6.29297c0.25082,0.26124 0.62327,0.36648 0.97371,0.27512c0.35044,-0.09136 0.62411,-0.36503 0.71547,-0.71547c0.09136,-0.35044 -0.01388,-0.72289 -0.27512,-0.97371l-6.29297,-6.29297l6.29297,-6.29297c0.29576,-0.28749 0.38469,-0.72707 0.22393,-1.10691c-0.16075,-0.37985 -0.53821,-0.62204 -0.9505,-0.60988c-0.2598,0.00774 -0.50638,0.11632 -0.6875,0.30273l-6.29297,6.29297l-6.29297,-6.29297c-0.18827,-0.19353 -0.4468,-0.30272 -0.7168,-0.30273z">
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </button>
                </div>

            </div>

            <!-- Notifications List -->
            @if (Auth::check())
                @forelse (auth()->user()->unreadNotifications as $notification)
                    <div class="w-full p-3 mt-4 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded shadow flex flex-shrink-0"
                        data-notification-id="{{ $notification->id }}">
                        <div tabindex="0" aria-label="group icon" role="img"
                            class="focus:outline-none w-8 h-8 border rounded-full border-gray-200 dark:border-gray-700 flex flex-shrink-0 items-center justify-center">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M4.30325 12.6667L1.33325 15V2.66667C1.33325 2.48986 1.40349 2.32029 1.52851 2.19526C1.65354 2.07024 1.82311 2 1.99992 2H13.9999C14.1767 2 14.3463 2.07024 14.4713 2.19526C14.5963 2.32029 14.6666 2.48986 14.6666 2.66667V12C14.6666 12.1768 14.5963 12.3464 14.4713 12.4714C14.3463 12.5964 14.1767 12.6667 13.9999 12.6667H4.30325ZM5.33325 6.66667V8H10.6666V6.66667H5.33325Z"
                                    fill="#4338CA" />
                            </svg>
                        </div>
                        <div class="pl-3 w-full text-gray-800 dark:text-gray-300">
                            <div class="flex items-center justify-between w-full">
                                <p tabindex="0" class="focus:outline-none text-sm leading-none">
                                    {{ $notification->data['message'] }}</p>
                                <div tabindex="0" aria-label="close icon" role="button"
                                    onclick="markAsRead('{{ $notification->id }}')"
                                    class="focus:outline-none cursor-pointer rounded-md p-1 hover:bg-gray-500/20">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10.5 3.5L3.5 10.5" stroke="#4B5563" stroke-width="1.25"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M3.5 3.5L10.5 10.5" stroke="#4B5563" stroke-width="1.25"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                            </div>
                            <p tabindex="0" class="focus:outline-none text-xs leading-3 pt-1 text-gray-500">
                                {{-- {{ $notification->created_at->format('d/m/Y H:i:s') }} --}}
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2 text-gray-500">Nenhuma notificação não lida</p>
                    </div>
                @endforelse
            @endif

            <!-- Footer -->
            <div class="flex justify-end mt-10 pt-4 border-t border-gray-200 dark:border-gray-800">
                <a href="{{ route('notifications.index') }}"
                    class="px-4 py-2 text-sm font-bold text-gray-600  hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-500 transition-colors">
                    Ver todas as notificações
                </a>
            </div>

        </div>
    </div>
</div>
<script>
    let notification = document.getElementById("notificationsModal");
    let checdiv = document.getElementById("chec-div");
    let flag3 = true;
    const notificationHandler = () => {
        if (!flag3) {
            // Fecha modal
            notification.classList.add("translate-x-full");
            notification.classList.remove("translate-x-0");
            setTimeout(function() {
                checdiv.classList.add("hidden");
            }, 300);
            flag3 = true;
        } else {
            // Abre modal
            setTimeout(function() {
                notification.classList.remove("translate-x-full");
                notification.classList.add("translate-x-0");
            }, 100);
            checdiv.classList.remove("hidden");
            flag3 = false;
        }
    };

    // Funções AJAX
    async function markAsRead(notificationId) {
        try {
            const response = await fetch(`/notifications/${notificationId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            });

            const data = await response.json();

            if (data.success) {
                // Remover a notificação da lista
                const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.remove();
                }

                // Atualizar contador
                updateNotificationCount();

                // Se não houver mais notificações, mostrar mensagem
                if (document.querySelectorAll('[data-notification-id]').length === 0) {
                    location.reload(); // Ou atualizar a lista dinamicamente
                }
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao marcar notificação como lida');
        }
    }

    async function markAllAsRead() {
        try {
            const response = await fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            });

            const data = await response.json();

            if (data.success) {
                // Fechar modal e recarregar
                notificationHandler(false);
                location.reload();
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao marcar notificações como lidas');
        }
    }

    function updateNotificationCount() {
        // Atualizar o badge de contagem (implementação básica)
        const badge = document.querySelector('.bg-red-500');
        if (badge) {
            const currentCount = parseInt(badge.textContent);
            if (currentCount > 1) {
                badge.textContent = currentCount - 1;
            } else {
                badge.remove();
            }
        }
        const label = document.querySelector('.text-red-500');
        if (label) {
            const currentCount = parseInt(label.textContent);
            if (currentCount > 1) {
                label.textContent = currentCount - 1;
            } else {
                label.remove();
            }
        }
    }
</script>
