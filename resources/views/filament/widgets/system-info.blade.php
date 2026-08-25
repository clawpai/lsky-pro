<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ __('admin/dashboard.system_info.heading') }}</x-slot>

        <div class="relative rounded-md bg-white overflow-hidden dark:bg-gray-800 dark:ring-white/10">
            <dl>
                <div class="bg-gray-50 dark:bg-gray-900 dark:ring-white/10 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">{{ __('admin/dashboard.system_info.os') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 sm:mt-0 sm:col-span-2">
                        {{ php_uname() }}
                    </dd>
                </div>
                <div class="bg-white dark:bg-gray-800 dark:ring-white/10 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">{{ __('admin/dashboard.system_info.server') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 sm:mt-0 sm:col-span-2">
                        {{ request()->server('SERVER_SOFTWARE') }}
                    </dd>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 dark:ring-white/10 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">{{ __('admin/dashboard.system_info.php_version') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 sm:mt-0 sm:col-span-2">
                        {{ phpversion() }}
                    </dd>
                </div>
                <div class="bg-white dark:bg-gray-800 dark:ring-white/10 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">{{ __('admin/dashboard.system_info.memory_limit') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 sm:mt-0 sm:col-span-2">
                        {{ ini_get("memory_limit") }}
                    </dd>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 dark:ring-white/10 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">{{ __('admin/dashboard.system_info.upload_max_filesize') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 sm:mt-0 sm:col-span-2">
                        {{ ini_get("upload_max_filesize") }}
                    </dd>
                </div>
                <div class="bg-white dark:bg-gray-800 dark:ring-white/10 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">{{ __('admin/dashboard.system_info.post_max_size') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 sm:mt-0 sm:col-span-2">
                        {{ ini_get('post_max_size') }}
                    </dd>
                </div>
            </dl>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
