<?php

namespace App\Livewire\Contracts;

use Livewire\Component;
use App\Models\Contract;
use App\Notifications\ContractStatusChanged;

class StatusManager extends Component
{
    public Contract $contract;
    public $currentStatus;
    public $statusComment = '';
    
    public $statusOptions = [
        'pending' => 'Pending',
        'uploaded' => 'Uploaded',
        'approved' => 'Approved',
        'active' => 'Active',
        'expired' => 'Expired',
        'terminated' => 'Terminated'
    ];

    protected $rules = [
        'currentStatus' => 'required|in:pending,uploaded,approved,active,expired,terminated',
        'statusComment' => 'nullable|string|max:500'
    ];

    public function mount(Contract $contract)
    {
        $this->contract = $contract;
        $this->currentStatus = $contract->status;
    }

    public function updateStatus()
    {
        $this->authorize('update-status', $this->contract);
        
        $this->validate();
        
        if ($this->currentStatus === $this->contract->status) {
            $this->dispatch('notify', 'No changes detected.', 'info');
            return;
        }

        $previousStatus = $this->contract->status;
        
        $this->contract->update([
            'status' => $this->currentStatus,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Log status change
        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->contract)
            ->withProperties([
                'old_status' => $previousStatus,
                'new_status' => $this->currentStatus,
                'comment' => $this->statusComment
            ])
            ->log('Contract status updated');

        // Send notification to contract creator
        if ($this->contract->created_by !== auth()->id()) {
            $this->contract->createdBy->notify(
                new ContractStatusChanged(
                    $this->contract, 
                    $previousStatus, 
                    $this->currentStatus,
                    $this->statusComment
                )
            );
        }

        // Reset the comment field
        $this->reset('statusComment');
        
        // Dispatch events
        $this->dispatch('contract-status-updated', $this->contract->id);
        $this->dispatch('notify', 'Contract status updated successfully!');
    }

    public function render()
    {
        return view('livewire.contracts.status-manager');
    }
}
