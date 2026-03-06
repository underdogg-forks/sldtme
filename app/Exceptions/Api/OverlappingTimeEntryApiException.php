<?php

namespace App\Exceptions\Api;

class OverlappingTimeEntryApiException extends ApiException
{
    public const string KEY = 'overlapping_time_entry';
}
