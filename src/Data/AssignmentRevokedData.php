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
 * The payload of coworking.assignment_revoked v1: the principal revokes the assignment, before or after the answer of
 * the firm, after registering it revoked. revoked_at is the closed_at of the transaction.
 */
final class AssignmentRevokedData extends Data
{
    use IsEventBetweenFirms;

    public const string TYPE = Contract::ASSIGNMENT_REVOKED;

    public function __construct(
        public TransactionIdData $transaction,
        #[WithCast(DateTimeInterfaceCast::class, format: Contract::DATE_INPUT_FORMATS)]
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: Contract::DATE_FORMAT)]
        public CarbonImmutable $revoked_at,
    ) {}
}
