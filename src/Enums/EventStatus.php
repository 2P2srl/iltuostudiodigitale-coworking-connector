<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Enums;

enum EventStatus: string
{
    case Queued = 'queued';
    case Delivered = 'delivered';
    case Exhausted = 'exhausted';
    case Cancelled = 'cancelled';
}
