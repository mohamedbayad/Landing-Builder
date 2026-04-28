<?php

namespace App\Events\Email;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FormSubmitted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public int $userId,
        public ?int $formId = null,
        public ?int $landingId = null,
        public ?int $formEndpointId = null,
        public ?int $preferredAutomationId = null,
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $phone = null,
        public array $data = []
    ) {
    }
}

