<?php

namespace App\Exceptions\Api;

class PersonalAccessClientIsNotConfiguredException extends ApiException
{
    public const string KEY = 'personal_access_client_is_not_configured';
}
