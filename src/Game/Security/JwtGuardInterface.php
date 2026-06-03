<?php

declare(strict_types=1);

namespace App\Game\Security;

use App\Auth\DTO\JwtPayload;
use Symfony\Component\HttpFoundation\Request;

interface JwtGuardInterface
{
    public function extractAndValidate(Request $request, string $expectedGameId): JwtPayload;
}
