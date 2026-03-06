<?php

namespace App\Exceptions\Api;

class UserIsAlreadyMemberOfOrganizationApiException extends ApiException
{
    public const string KEY = 'user_is_already_member_of_organization';
}
