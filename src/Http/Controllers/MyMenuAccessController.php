<?php

declare(strict_types=1);

namespace Aurix\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Aurix\Services\MenuAccessService;

class MyMenuAccessController extends Controller
{
    public function __construct(private readonly MenuAccessService $menuAccess)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        return response()->json([
            'data' => $this->menuAccess->forUser($user)->values(),
        ]);
    }
}
