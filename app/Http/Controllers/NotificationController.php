<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Exibir todas as notificações
     */
    public function index(): View
    {
        /** @var LengthAwarePaginator<DatabaseNotification> $notifications */
        $notifications = auth()->user()->notifications()->paginate(20);

        return view('users.notifications-index', compact('notifications'));
    }

    /**
     * Marcar uma notificação como lida (via AJAX)
     */
    public function markAsRead(Request $request, DatabaseNotification $notification): JsonResponse
    {
        if ($notification->notifiable_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notificação marcada como lida',
        ]);
    }

    /**
     * Marcar todas as notificações como lidas (via AJAX)
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        // auth()->user()->unreadNotifications->markAsRead();
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Todas as notificações marcadas como lidas',
        ]);
    }

    /**
     * Excluir uma notificação
     */
    public function destroy(DatabaseNotification $notification): RedirectResponse
    {
        if ($notification->notifiable_id !== auth()->id()) {
            // return response()->json(['error' => 'Unauthorized'], 403);
            return redirect()->route('notifications.index')->with('error', 'Não foi possível remover, não autorizado');
        }

        $notification->delete();

        return redirect()->route('notifications.index')->with('success', 'Notificação removida com sucesso');
    }
}
