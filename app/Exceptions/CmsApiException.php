<?php

namespace App\Exceptions;

use RuntimeException;

class CmsApiException extends RuntimeException
{
    public function __construct(public readonly int $upstreamStatus = 503)
    {
        parent::__construct('Layanan CMS belum dapat menyediakan data.');
    }
}
