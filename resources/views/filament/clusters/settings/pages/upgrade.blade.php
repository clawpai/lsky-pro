<x-filament-panels::page>
    <x-filament::section>
        @if(in_array($status, [\App\UpgradeStatus::IDLE, \App\UpgradeStatus::CHECKING]))
            <div class="flex items-center justify-center">
                <x-filament::loading-indicator class="h-5 w-5 mr-1" /> {{ __('admin/setting.upgrade.status.checking') }}
            </div>
        @elseif($status === \App\UpgradeStatus::UP_TO_DATE)
            <div class="flex flex-col items-center justify-center">
                <p>{{ config('app.version') }}</p>
                <p class="text-gray-400 mt-1 text-sm">{{ __('admin/setting.upgrade.status.no_update') }}</p>
            </div>
        @elseif(in_array($status, [\App\UpgradeStatus::AVAILABLE, \App\UpgradeStatus::UPGRADING, \App\UpgradeStatus::COMPLETED, \App\UpgradeStatus::FAILED]))
            <div class="flex flex-row justify-between w-full">
                <div class="flex flex-row items-center">
                    @if($version['logo'])
                        <img class="w-10 h-10 mr-2" src="{{ $version['logo'] }}" alt="Lsky Pro+ - 2x.nz特供离线版 logo">
                    @endif
                    <div class="flex flex-col">
                        <p class="text-md font-bold">{{ $version['name'] }}</p>
                        <p class="text-sm">{{ $version['pushed_at'] }}</p>
                    </div>
                </div>

                @if($status === \App\UpgradeStatus::UPGRADING)
                    <x-filament::button disabled>
                        <div class="flex items-center justify-center">
                            <x-filament::loading-indicator class="h-5 w-5 mr-1" /> {{ __('admin/setting.upgrade.status.upgrading') }}
                        </div>
                    </x-filament::button>
                @elseif(in_array($status, [\App\UpgradeStatus::AVAILABLE, \App\UpgradeStatus::FAILED]))
                    <x-filament::button wire:click="upgrade()">{{ __('admin/setting.upgrade.btn') }}</x-filament::button>
                @endif
            </div>

            @if($status === \App\UpgradeStatus::UPGRADING)
                <div wire:poll.4s="updateProgress">
                    @if($message)
                        <div class="border-t mt-4 pt-4">
                            <div class="rounded p-2 bg-slate-100 dark:bg-slate-700 text-sm max-h-80 messages break-all overflow-y-auto">{!! $message !!}</div>
                        </div>
                    @endif
                </div>
            @endif

            @if($message && $status === \App\UpgradeStatus::FAILED)
                <div class="border-t mt-4 pt-4">
                    <div class="rounded p-2 bg-red-100 dark:bg-red-700 text-sm max-h-80 messages break-all overflow-y-auto">{!! $message !!}</div>
                </div>
            @endif

            @if($status === \App\UpgradeStatus::COMPLETED)
                @if($message)
                    <div class="border-t mt-4 pt-4">
                        <div class="rounded p-2 bg-slate-100 dark:bg-slate-700 text-sm max-h-80 messages break-all overflow-y-auto">{!! $message !!}</div>
                    </div>
                @endif

                <div class="border-t mt-4 pt-4">
                    <div class="rounded p-2 bg-green-100 dark:bg-green-500 text-sm">{{ __('admin/setting.upgrade.status.completed') }}</div>
                </div>
            @endif

            @if($version['changelog'])
                <div class="border-t mt-4 pt-4 p-4 markdown-body">{!! Str::markdown($version['changelog']) !!}</div>
            @endif
        @endif
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            {{ __('admin/setting.upgrade.changelog') }}
        </x-slot>

        <div class="markdown-body p-4">{!! Str::markdown($changelog) !!}</div>
    </x-filament::section>
</x-filament-panels::page>

@script
<script>
  $wire.check()
  
  function scrollMessagesToBottom() {
    setTimeout(() => {
      const messagesDiv = document.querySelector('.messages');
      if (messagesDiv) {
        messagesDiv.scrollTo({
          top: messagesDiv.scrollHeight,
          behavior: 'smooth'
        });
      }
    }, 50);
  }

  $wire.on('update-progress', () => {
    scrollMessagesToBottom();
  });

  $wire.on('upgrade-completed', () => {
    scrollMessagesToBottom();
  });
</script>
@endscript