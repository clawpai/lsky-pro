<div class="fixed inset-0 w-full h-full overflow-hidden py-4 px-4 md:py-20 md:px-0">
    <div class="flex flex-col container max-w-screen-md mx-auto h-full p-6 rounded-lg shadow-md overflow-x-hidden overflow-y-auto bg-white dark:bg-[rgba(var(--gray-800),var(--tw-bg-opacity))]">
        <h1 class="mt-6 mb-2 text-2xl">软件许可和使用协议</h1>
        <p class="text-sm text-gray-400 mb-8">请仔细阅读协议后，点击协议结尾的「同意此协议」按钮继续使用本软件。</p>
        <div class="markdown-body dark:bg-[rgba(var(--gray-800),var(--tw-bg-opacity))]">
            {!! $content !!}
        </div>
        <div class="mt-8 flex justify-end">
            <x-filament::button wire:click="agree()">同意此协议</x-filament::button>
        </div>
    </div>
</div>