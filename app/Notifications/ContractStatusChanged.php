<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public $contract;
    public $previousStatus;
    public $newStatus;
    public $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Contract $contract, string $previousStatus, string $newStatus, ?string $comment = null)
    {
        $this->contract = $contract;
        $this->previousStatus = $previousStatus;
        $this->newStatus = $newStatus;
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = route('contracts.show', $this->contract);
        
        return (new MailMessage)
            ->subject("Contract Status Updated: {$this->contract->title}")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The status of the contract has been updated:')
            ->line("**Contract:** {$this->contract->title}")
            ->line("**From:** " . ucfirst($this->previousStatus))
            ->line("**To:** " . ucfirst($this->newStatus))
            ->when($this->comment, function ($message) {
                $message->line('')
                       ->line('**Comments:**')
                       ->line($this->comment);
            })
            ->action('View Contract', $url)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'comment' => $this->comment,
            'url' => route('contracts.show', $this->contract),
        ];
    }
}
