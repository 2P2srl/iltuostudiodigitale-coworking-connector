<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Enums;

enum OutboxStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';

    /**
     * Closed without error: a declaration of a professional the system never assigned (rule R18).
     */
    case Discarded = 'discarded';
}
