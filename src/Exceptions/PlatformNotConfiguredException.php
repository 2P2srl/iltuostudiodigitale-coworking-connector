<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Exceptions;

final class PlatformNotConfiguredException extends PlatformException
{
    public function __construct()
    {
        parent::__construct('Il sistema non è collegato al portale: mancano PLATFORM_URL, PLATFORM_CLIENT_ID o PLATFORM_CLIENT_SECRET.');
    }
}
