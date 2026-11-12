<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

use Carbon\CarbonImmutable;
use IlTuoStudioDigitale\CoworkingConnector\Contract;
use IlTuoStudioDigitale\CoworkingConnector\Data\Concerns\IsEventBetweenFirms;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

/**
 * The payload of coworking.assignment_declined v1: the firm declines the assignment offered, once, with a reason or
 * none. The principal then registers the transaction declined, with responded_at equal to answered_at (rule R24).
 */
final class AssignmentDeclinedData extends Data
{
    use IsEventBetweenFirms;

    public const string TYPE = Contract::ASSIGNMENT_DECLINED;

    /**
     * @param  string|null  $reason  The reason for the principal, up to 1000 characters, or null.
     */
    public function __construct(
        public TransactionIdData $transaction,
        #[WithCast(DateTimeInterfaceCast::class, format: Contract::DATE_INPUT_FORMATS)]
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: Contract::DATE_FORMAT)]
        public CarbonImmutable $answered_at,
        public ?string $reason,
    ) {}
}
