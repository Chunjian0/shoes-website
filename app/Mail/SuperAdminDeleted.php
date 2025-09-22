<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SuperAdminDeleted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $superAdmin
    ) {}

    public function build()
    {
        return $this->markdown('emails.super-admin-deleted')
                   ->subject('Super Administrator Account has been deleted')
                   ->with([
                       'superAdmin' => $this->superAdmin,
                       'deletedBy' => auth()->user()
                   ]);
    }
} 