<?php

namespace App\Http\Middleware;

use App\Services\PlantContextService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureSecondProcessPlantAccess
{
    public function __construct(
        protected PlantContextService $plantContextService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // SUPER-ADMIN is unrestricted as-is (bypasses gating)
        if ($user->hasRole('SUPER-ADMIN') || $user->hasRoleAccess('SUPER-ADMIN')) {
            return $next($request);
        }

        // 1. Plant Context Check
        if (! $this->plantContextService->supportsSecondProcess($user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses Ditolak. Fitur Second Process tidak tersedia pada Plant ini.',
                ], 403);
            }

            abort(403, 'Akses Ditolak. Fitur Second Process tidak tersedia pada Plant ini.');
        }

        // 2. Route Gate Check (Reports vs Shop Floor Ops)
        $routeName = $request->route()?->getName() ?? '';
        $isReportRoute = str_starts_with($routeName, 'second-process-reports')
            || $routeName === 'second-process.report-analytics';

        $requiredGate = $isReportRoute ? 'view-second-process-reports' : 'view-second-process-ops';

        if (Gate::forUser($user)->denies($requiredGate)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses Ditolak. Anda tidak memiliki izin untuk mengakses modul ini.',
                ], 403);
            }

            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk mengakses modul ini.');
        }

        return $next($request);
    }
}
