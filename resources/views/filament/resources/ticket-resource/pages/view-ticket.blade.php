<x-filament-panels::page>
    {{ $this->makeInfolist() }}

    <div class="flex flex-col">
        <div class="flex items-center border-b dark:border-b-gray-600 pb-2 space-x-1 w-full">
            <x-heroicon-o-chat-bubble-left class="text-amber-500 w-6 h-6" /> <span>{{ __('admin/ticket.view.session_label') }}</span>
        </div>

        <div class="flex flex-col space-y-6 mt-2 w-full">
            @foreach($this->getRecord()->replies()->with('user')->get() as $reply)
                @if($reply->user->id === auth()->id())
                    <div class="flex flex-row-reverse">
                        <img class="w-10 h-10 rounded-full border dark:border-gray-500" src="{{ app(\Filament\AvatarProviders\UiAvatarsProvider::class)->get(Auth::user()) }}" alt="avatar">
                        <div class="flex flex-col items-end space-y-1 mr-2 grow">
                            <p class="text-sm text-right">{{ $reply->user->name }} <small>{{ $reply->created_at->diffForHumans() }}</small></p>
                            <p class="flex-none w-fit inline-block bg-green-500 dark:bg-green-700 text-white overflow-hidden p-2 rounded-lg shadow md:max-w-[80%]">
                                {{ $reply->content }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="flex">
                        <img class="w-10 h-10 rounded-full border dark:border-gray-500" src="{{ app(\Filament\AvatarProviders\UiAvatarsProvider::class)->get(Auth::user()) }}" alt="avatar">
                        <div class="flex flex-col space-y-1 ml-2 grow">
                            <p class="text-sm">{{ $reply->user->name }} <small>{{ $reply->created_at->diffForHumans() }}</small></p>
                            <p class="flex-none w-fit inline-block bg-white dark:bg-white/20 overflow-hidden break-all p-2 rounded-lg shadow md:max-w-[80%]">
                                {{ $reply->content }}
                            </p>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- 关闭后工单不能继续回复 -->
    @if($this->getRecord()->status === \App\TicketStatus::Completed)
        <p class="flex items-center justify-center space-x-1 text-sm w-full text-amber-600">
            <x-heroicon-m-information-circle class="w-4 h-4"/>
            <span>{{ __('admin/ticket.view.close_tip') }}</span>
        </p>
    @else
        <form wire:submit="submit">
            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="submit" icon="heroicon-o-paper-airplane" class="mt-2">
                    {{ __('admin/ticket.view.reply.submit.label') }}
                </x-filament::button>
            </div>
        </form>
    @endif
</x-filament-panels::page>
