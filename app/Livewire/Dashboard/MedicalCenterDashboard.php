<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MedicalCenterDashboard extends Component
{
    public $stats = [];
    public $recentContracts = [];
    public $expiringContracts = [];
    public $chartData = [];
    public $statusDistribution = [];
    public $loading = true;

    protected $listeners = ['refreshDashboard' => '$refresh'];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $medicalCenterId = auth()->user()->medical_center_id;
        
        // Load statistics
        $this->stats = [
            'total_contracts' => Contract::where('medical_center_id', $medicalCenterId)->count(),
            'active_contracts' => Contract::where('medical_center_id', $medicalCenterId)
                ->where('status', 'active')->count(),
            'pending_approval' => Contract::where('medical_center_id', $medicalCenterId)
                ->where('status', 'uploaded')->count(),
            'expiring_soon' => Contract::where('medical_center_id', $medicalCenterId)
                ->where('end_date', '<=', Carbon::now()->addDays(30))
                ->where('status', 'active')->count(),
            'total_value' => Contract::where('medical_center_id', $medicalCenterId)
                ->where('status', 'active')
                ->sum('contract_value')
        ];

        // Load recent contracts
        $this->recentContracts = Contract::where('medical_center_id', $medicalCenterId)
            ->with(['createdBy', 'approvedBy'])
            ->latest()
            ->take(5)
            ->get();

        // Load expiring contracts
        $this->expiringContracts = Contract::where('medical_center_id', $medicalCenterId)
            ->where('end_date', '>=', now())
            ->where('end_date', '<=', now()->addDays(30))
            ->where('status', 'active')
            ->orderBy('end_date')
            ->take(5)
            ->get();

        // Status distribution for chart
        $this->statusDistribution = Contract::where('medical_center_id', $medicalCenterId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            })->toArray();

        // Prepare chart data
        $this->prepareChartData();
        
        $this->loading = false;
    }

    protected function prepareChartData()
    {
        $labels = [];
        $data = [];
        $backgroundColors = [
            'pending' => 'rgba(245, 158, 11, 0.8)',    // yellow
            'uploaded' => 'rgba(59, 130, 246, 0.8)',   // blue
            'approved' => 'rgba(16, 185, 129, 0.8)',   // green
            'active' => 'rgba(16, 185, 129, 0.8)',     // green
            'expired' => 'rgba(239, 68, 68, 0.8)',     // red
            'terminated' => 'rgba(239, 68, 68, 0.8)',  // red
        ];

        $statusLabels = [
            'pending' => 'Pending',
            'uploaded' => 'Uploaded',
            'approved' => 'Approved',
            'active' => 'Active',
            'expired' => 'Expired',
            'terminated' => 'Terminated',
        ];

        foreach ($statusLabels as $status => $label) {
            if (isset($this->statusDistribution[$status])) {
                $labels[] = $label;
                $data[] = $this->statusDistribution[$status];
            }
        }

        $this->chartData = [
            'labels' => $labels,
            'datasets' => [[
                'data' => $data,
                'backgroundColor' => array_map(function($status) use ($backgroundColors) {
                    return $backgroundColors[$status] ?? 'rgba(156, 163, 175, 0.8)';
                }, array_keys($statusLabels)),
            ]],
        ];
    }

    public function refreshDashboard()
    {
        $this->loading = true;
        $this->loadDashboardData();
    }

    public function render()
    {
        return view('livewire.dashboard.medical-center-dashboard');
    }
}
