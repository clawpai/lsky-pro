<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Facades\TicketService;
use App\Filament\Resources\TicketResource;
use App\Jobs\SendTicketReplyNotificationMailJob;
use App\Models\Group;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\TicketLevel;
use App\TicketStatus;
use Auth;
use Filament\Actions\Action;
use Filament\Forms\Components\Component as FormComponent;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Component as InfolistComponent;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Context;

class ViewTicket extends Page implements HasForms, HasInfolists
{
    use InteractsWithRecord, InteractsWithForms, InteractsWithInfolists;

    protected static string $resource = TicketResource::class;

    protected static string $view = 'filament.resources.ticket-resource.pages.view-ticket';

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('admin/ticket.view.navigation_label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin/ticket.view.title');
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        /** @var Ticket $ticket */
        $ticket = $this->record;
        $ticket->replies()->where('user_id', '<>', Auth::id())->update(['read_at' => now()]);
    }

    public function submit(): void
    {
        /** @var Ticket $ticket */
        $ticket = $this->getRecord();
        $ticket->replies()->create([
            'user_id' => Auth::id(),
            'content' => $this->getForm('form')->getState()['content'],
        ]);

        // 给该用户发送通知邮件
        /** @var TicketReply $lastReply */
        $lastReply = $ticket->replies()->where('user_id', '<>', Auth::id())->latest()->first();
        if ($ticket->user->email_verified_at && !is_null($lastReply) && $lastReply->is_notify) {
            /** @var Group $group */
            $group = Context::get('group');
            
            dispatch(new SendTicketReplyNotificationMailJob(
                groupId: $group->id,
                ticket: $ticket->withoutRelations(),
                emails: [$ticket->user->email],
            ));
        }

        $this->getForm('form')->fill(['content' => '']);

        Notification::make()->success()->title(__('admin/ticket.view.reply.submit.success'))->send();
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            $this->getContentFormComponent(),
        ]);
    }

    /**
     * 内容
     * @return FormComponent
     */
    protected function getContentFormComponent(): FormComponent
    {
        return Textarea::make('content')
            ->label(__('admin/ticket.view.reply.content.label'))
            ->placeholder(__('admin/ticket.view.reply.content.placeholder'))
            ->rows(1)
            ->autosize()
            ->maxLength(2000)
            ->required();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make(__('admin/ticket.actions.close.label'))
                ->requiresConfirmation()
                ->modalDescription(__('admin/ticket.actions.close.description'))
                ->color(Color::Red)
                ->action(function (Ticket $ticket) {
                    TicketService::close($ticket);
                    Notification::make()->success()->title(__('admin/ticket.actions.close.success'))->send();
                })
                ->visible(fn(Ticket $ticket): bool => $ticket->status === TicketStatus::InProgress),
        ];
    }

    protected function makeInfolist(): Infolist
    {
        return parent::makeInfolist()
            ->columns(1)
            ->record($this->getRecord())
            ->schema([
                Section::make(fn(Ticket $ticket): string => $ticket->title)->schema([
                    $this->getIssueNoEntryComponent(),
                    $this->getUserNameEntryComponent(),
                    $this->getUserEmailEntryComponent(),
                    $this->getUserPhoneEntryComponent(),
                    $this->getLevelEntryComponent(),
                    $this->getStatusEntryComponent(),
                    $this->getCreatedAtEntryComponent(),
                    $this->getReplyCreatedAtEntryComponent(),
                ])
                    ->columns(4)
                    ->collapsible()
                    ->collapsed(fn(Ticket $ticket): bool => $ticket->status === TicketStatus::Completed)
                    ->compact(),
            ]);
    }

    /**
     * 工单号
     * @return InfolistComponent
     */
    protected function getIssueNoEntryComponent(): InfolistComponent
    {
        return TextEntry::make('issue_no')->label(__('admin/ticket.columns.issue_no'));
    }

    /**
     * 用户名
     * @return InfolistComponent
     */
    protected function getUserNameEntryComponent(): InfolistComponent
    {
        return TextEntry::make('user.name')->label(__('admin/ticket.columns.user.name'));
    }

    /**
     * 用户邮箱
     * @return InfolistComponent
     */
    protected function getUserEmailEntryComponent(): InfolistComponent
    {
        return TextEntry::make('user.email')->label(__('admin/ticket.columns.user.email'));
    }

    /**
     * 用户手机号
     * @return InfolistComponent
     */
    protected function getUserPhoneEntryComponent(): InfolistComponent
    {
        return TextEntry::make('user.phone')->label(__('admin/ticket.columns.user.phone'));
    }

    /**
     * 工单等级
     * @return InfolistComponent
     */
    protected function getLevelEntryComponent(): InfolistComponent
    {
        return TextEntry::make('level')
            ->label(__('admin/ticket.columns.level'))
            ->badge()
            ->color(fn(TicketLevel $state) => match ($state) {
                TicketLevel::Medium => 'warning',
                TicketLevel::High => 'danger',
                default => 'info',
            })
            ->formatStateUsing(fn(TicketLevel $state) => __("admin.ticket_levels.{$state->value}"));
    }

    /**
     * 工单状态
     * @return InfolistComponent
     */
    protected function getStatusEntryComponent(): InfolistComponent
    {
        return TextEntry::make('status')
            ->label(__('admin/ticket.columns.status'))
            ->badge()
            ->color(fn(TicketStatus $state) => match ($state) {
                TicketStatus::InProgress => 'warning',
                default => 'success',
            })
            ->formatStateUsing(fn(TicketStatus $state) => __("admin.ticket_statuses.{$state->value}"));
    }

    /**
     * 工单创建时间
     * @return InfolistComponent
     */
    protected function getCreatedAtEntryComponent(): InfolistComponent
    {
        return TextEntry::make('created_at')
            ->label(__('admin/ticket.columns.created_at'))
            ->dateTime();
    }

    /**
     * 工单最后回复时间
     * @return InfolistComponent
     */
    protected function getReplyCreatedAtEntryComponent(): InfolistComponent
    {
        return TextEntry::make('reply.created_at')
            ->label(__('admin/ticket.columns.reply.created_at'))
            ->dateTime();
    }
}
