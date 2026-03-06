<?php

namespace App\Service\Export;

use App\Exceptions\Api\ApiException;

class ExportException extends ApiException
{
    public const string KEY = 'export';
}
