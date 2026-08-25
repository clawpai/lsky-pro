<div class="space-y-6">
    {{-- {{ __('admin/setting.queue.messages.basic_info') }} --}}
    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('admin/setting.queue.messages.basic_info') }}</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('admin/setting.queue.fields.queue') }}:</span>
                <span class="ml-2 px-2 py-1 bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200 rounded text-xs">
                    {{ $record->queue }}
                </span>
            </div>
            <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('admin/setting.queue.fields.connection') }}:</span>
                <span class="ml-2 text-gray-600 dark:text-gray-400">{{ $record->connection }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('admin/setting.queue.fields.failed_at') }}:</span>
                <span class="ml-2 text-gray-600 dark:text-gray-400">{{ $record->failed_at->format('Y-m-d H:i:s') }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('admin/setting.queue.messages.task_type') }}:</span>
                <span class="ml-2 px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200 rounded text-xs">
                    @if(isset($record->payload['displayName']))
                        {{ \Illuminate\Support\Str::afterLast($record->payload['displayName'], '\\') }}
                    @elseif(isset($record->payload['job']))
                        {{ \Illuminate\Support\Str::afterLast($record->payload['job'], '\\') }}
                    @else
                        {{ __('admin/setting.queue.messages.unknown_task') }}
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- {{ __('admin/setting.queue.messages.payload_info') }} --}}
    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('admin/setting.queue.messages.payload_info') }}</h3>
        <div class="bg-gray-100 dark:bg-gray-900 rounded p-3 overflow-auto">
            <pre class="text-xs text-gray-700 dark:text-gray-300">{{ is_string($record->payload) ? $record->payload : json_encode($record->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>

    {{-- {{ __('admin/setting.queue.messages.exception_details') }} --}}
    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-red-900 dark:text-red-200 mb-3 flex items-center">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 mr-2" />
            {{ __('admin/setting.queue.messages.exception_details') }}
        </h3>
        <div class="bg-red-100 dark:bg-red-900/30 rounded p-3 overflow-auto max-h-96">
            <pre class="text-xs text-red-800 dark:text-red-200">{{ $record->exception }}</pre>
        </div>
    </div>
</div> 