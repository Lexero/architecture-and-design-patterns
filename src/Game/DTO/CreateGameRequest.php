<?php

declare(strict_types=1);

namespace App\Game\DTO;

final readonly class CreateGameRequest
{
    public function __construct(
        public string $organizerId,
        public array $participants,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['organizerId'],
            $data['participants'],
        );
    }
}
