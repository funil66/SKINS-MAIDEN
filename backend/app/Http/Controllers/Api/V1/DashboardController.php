<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\KYCRequest;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard data for authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardData(Request $request)
    {
        $user = $request->user();
        
        // Get recent transactions (last 5 contracts)
        $recentTransactions = Contract::whereHas('parties', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['template', 'signatures'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        // Get KYC status
        $kycStatus = KYCRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Calculate statistics
        $totalContracts = Contract::whereHas('parties', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        $totalValue = Contract::whereHas('parties', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', 'completed')
        ->sum('value');

        $pendingContracts = Contract::whereHas('parties', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->whereIn('status', ['pending', 'signed'])
        ->count();

        // Monthly transaction trend (last 6 months)
        $monthlyTrend = Contract::whereHas('parties', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('created_at', '>=', now()->subMonths(6))
        ->select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(value) as total_value')
        )
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'steam_id' => $user->steam_id,
                'created_at' => $user->created_at,
            ],
            'kyc_status' => [
                'status' => $kycStatus ? $kycStatus->status : 'not_submitted',
                'submitted_at' => $kycStatus ? $kycStatus->created_at : null,
                'verified_at' => $kycStatus && $kycStatus->status === 'approved' ? $kycStatus->updated_at : null,
            ],
            'statistics' => [
                'total_contracts' => $totalContracts,
                'total_value' => number_format($totalValue, 2),
                'pending_contracts' => $pendingContracts,
                'completion_rate' => $totalContracts > 0 ? round((($totalContracts - $pendingContracts) / $totalContracts) * 100, 1) : 0,
            ],
            'recent_transactions' => $recentTransactions->map(function($contract) {
                return [
                    'id' => $contract->id,
                    'title' => $contract->title,
                    'value' => $contract->value,
                    'status' => $contract->status,
                    'created_at' => $contract->created_at,
                    'template_name' => $contract->template->name ?? 'N/A',
                    'signatures_count' => $contract->signatures->count(),
                ];
            }),
            'monthly_trend' => $monthlyTrend,
        ]);
    }
}
