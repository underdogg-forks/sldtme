<?php

namespace App\Service\IpLookup;

interface IpLookupServiceContract
{
    public function lookup(string $ip): ?IpLookupResponseDto;
}
