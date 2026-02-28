<?php

declare(strict_types=1);

namespace Aurix\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Aurix\Services\AppearanceSettingsService;

class AppearanceSettingsController extends Controller
{
    public function __construct(private readonly AppearanceSettingsService $appearance)
    {
    }

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->appearance->all(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $this->appearance->update((array) $request->all());

        return response()->json([
            'message' => 'Appearance settings updated.',
            'data' => $data,
        ]);
    }
}

