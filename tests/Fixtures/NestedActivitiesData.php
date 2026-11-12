<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Tests\Fixtures;

use IlTuoStudioDigitale\CoworkingConnector\Data\TransactionActivityData;
use IlTuoStudioDigitale\CoworkingConnector\Data\TransactionData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

/**
 * The activities of a transaction validated as Spatie validates a collection of DTOs with rules(): with one
 * NestedRules on activities.*. The reference of the equivalence test of TransactionData, which writes the same
 * rules under the key of each activity.
 */
final class NestedActivitiesData extends Data
{
    /**
     * @param  list<TransactionActivityData>  $activities
     */
    public function __construct(
        #[DataCollectionOf(TransactionActivityData::class)]
        public array $activities,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return ['activities' => ['required', 'array', 'list', 'max:'.TransactionData::MAX_ACTIVITIES]];
    }
}
