<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

use IlTuoStudioDigitale\CoworkingConnector\Contract;
use IlTuoStudioDigitale\CoworkingConnector\Data\Concerns\IsEventBetweenFirms;
use Spatie\LaravelData\Data;

/**
 * The payload of coworking.assignment_offered v1: the principal offers to a firm of another system the assignment it
 * registered invited. The terms are those of the register: the package of the firm reads the transaction and hands it
 * to the listeners in PlatformEventReceived::$transaction.
 */
final class AssignmentOfferedData extends Data
{
    use IsEventBetweenFirms;

    public const string TYPE = Contract::ASSIGNMENT_OFFERED;

    public function __construct(
        public TransactionIdData $transaction,
    ) {}
}
