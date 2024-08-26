<?php

namespace App\Http\Controllers;

use App\Services\SantanderService;
use Illuminate\Http\{JsonResponse};

class SantanderController extends Controller
{
    protected mixed $santanderService;

    public function __construct(SantanderService $santanderService)
    {
        $this->santanderService = $santanderService;
    }

    // Método para obter TOKEN
    public function getToken(): JsonResponse
    {
        $token = $this->santanderService->getAccessToken();

        return response()->json(['access_token' => $token]);
    }
}
