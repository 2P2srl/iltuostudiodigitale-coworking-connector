<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

use Carbon\CarbonImmutable;
use IlTuoStudioDigitale\CoworkingConnector\Contract;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * The outcome of the last verification of the webhook.
 */
final class VerificationData extends Data
{
    public function __construct(
        #[WithCast(DateTimeInterfaceCast::class, format: Contract::DATE_INPUT_FORMATS)]
        public CarbonImmutable $attempted_at,
        public bool $succeeded,
        public ?string $error,
    ) {}
}
