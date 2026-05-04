<?php

namespace App\Livewire\Master;

use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ActivityLogIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterAction = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterAction(): void { $this->resetPage(); }

    public function render()
    {
        $logs = ActivityLog::with('actor')
            ->when($this->search, function ($q) {
                $q->whereHas('actor', fn ($a) => $a->where('name', 'like', "%{$this->search}%"))
                  ->orWhere('description', 'like', "%{$this->search}%");
            })
            ->when($this->filterAction, fn ($q) => $q->where('action', $this->filterAction))
            ->latest('created_at')
            ->paginate(20);

        $actionTypes = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('livewire.master.activity-log-index', compact('logs', 'actionTypes'));
    }
}
