<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

use Carbon\CarbonImmutable;
use IlTuoStudioDigitale\CoworkingConnector\Contract;
use IlTuoStudioDigitale\CoworkingConnector\Enums\TransactionActivityStatus;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * An activity of a transaction as the register returns it, with the total the platform computed (rule R15).
 */
final class RecordedTransactionActivityData extends Data
{
    /**
     * @param  array<string, mixed>  $compensation
     * @param  array<string, mixed>  $description
     */
    public function __construct(
        public string $reference,
        public array $compensation,
        public int $estimated_minutes,
        public ?int $minutes_worked,
        public TransactionActivityStatus $status,
        #[WithCast(DateTimeInterfaceCast::class, format: Contract::DATE_INPUT_FORMATS)]
        public ?CarbonImmutable $closed_at,
        public int $total_cents,
        public array $description,
    ) {}
}
