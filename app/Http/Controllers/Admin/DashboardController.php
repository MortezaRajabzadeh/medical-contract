<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MedicalCenter;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * نمایش داشبورد مدیریت
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // جمع‌آوری اطلاعات آماری برای داشبورد
            $stats = [
                'medicalCenters' => MedicalCenter::count(),
                'contracts' => [
                    'total' => Contract::count(),
                    'active' => Contract::where('status', 'active')->count(),
                    'pending' => Contract::where('status', 'pending')->count(),
                    'expired' => Contract::where('status', 'expired')->count(),
                ],
                'users' => User::count(),
                'recentContracts' => Contract::with('medicalCenter')
                                        ->latest()
                                        ->take(5)
                                        ->get(),
            ];
            
            Log::info('داشبورد مدیریت با موفقیت بارگذاری شد', [
                'user_id' => auth()->id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return view('admin.dashboard', compact('stats'));
        } catch (\Exception $e) {
            Log::error('خطا در بارگذاری داشبورد مدیریت', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            // در صورت خطا، یک داشبورد خالی نمایش می‌دهیم
            return view('admin.dashboard', ['stats' => []]);
        }
    }
}
