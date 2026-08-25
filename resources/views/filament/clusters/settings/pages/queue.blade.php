<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <p class="text-yellow-800 dark:text-yellow-200 text-sm">
                {{ __('admin/setting.queue.messages.warning_message') }}
            </p>
        </div>

        {{-- 统计信息 --}}
        <div>
            {{ $this->jobsInfolist }}
        </div>

        {{-- Tab --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button 
                    wire:click="$set('activeTab', 'jobs')"
                    class="@if($activeTab === 'jobs') border-primary-500 text-primary-600 dark:text-primary-400 @else border-transparent hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                >
                    {{ __('admin/setting.queue.tabs.jobs') }}
                </button>
                <button 
                    wire:click="$set('activeTab', 'failed_jobs')"
                    class="@if($activeTab === 'failed_jobs') border-primary-500 text-primary-600 dark:text-primary-400 @else border-transparent hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                >
                    {{ __('admin/setting.queue.tabs.failed_jobs') }}
                </button>
                <button 
                    wire:click="$set('activeTab', 'job_batches')"
                    class="@if($activeTab === 'job_batches') border-primary-500 text-primary-600 dark:text-primary-400 @else border-transparent hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                >
                    {{ __('admin/setting.queue.tabs.job_batches') }}
                </button>
            </nav>
        </div>

        {{-- 表格 --}}
        <div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page> 