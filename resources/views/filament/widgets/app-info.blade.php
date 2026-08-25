<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ __('admin/dashboard.app_info.heading') }}</x-slot>

        <div class="relative rounded-md bg-white overflow-hidden dark:bg-gray-800 dark:ring-white/10">
            <dl>
                <div class="bg-gray-50 dark:bg-gray-900 dark:ring-white/10 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">{{ __('admin/dashboard.app_info.version') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 sm:mt-0 sm:col-span-2">{{ config('app.version') }}</dd>
                </div>
                <div class="bg-white dark:bg-gray-800 dark:ring-white/10 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">{{ __('admin/dashboard.app_info.website') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 sm:mt-0 sm:col-span-2">
                        <a target="_blank" class="hover:text-blue-500" href="https://www.lsky.pro">https://www.lsky.pro</a>
                    </dd>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 dark:ring-white/10 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">{{ __('admin/dashboard.app_info.docs') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 sm:mt-0 sm:col-span-2">
                        <a target="_blank" class="hover:text-blue-500" href="https://docs.lsky.pro">https://docs.lsky.pro</a>
                    </dd>
                </div>
                <div class="bg-white dark:bg-gray-800 dark:ring-white/10 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">{{ __('admin/dashboard.app_info.community') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 sm:mt-0 sm:col-span-2">
                        <a target="_blank" class="hover:text-blue-500" href="https://bbs.lskypro.com">https://bbs.lskypro.com</a>
                    </dd>
                </div>
            </dl>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
