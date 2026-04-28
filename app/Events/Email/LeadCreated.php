<?php

namespace App\Events\Email;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public int $userId,
        public int $leadId,
        public ?int $landingId = null,
        public ?int $landingPageId = null,
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $phone = null,
        public array $data = []
    ) {
    }
}

