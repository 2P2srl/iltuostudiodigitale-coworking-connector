<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

use Carbon\CarbonImmutable;
use IlTuoStudioDigitale\CoworkingConnector\Contract;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * The curriculum a consultant uploaded, as GET /professionals/{id} describes it: the file comes with
 * PlatformClient::curriculum() (rule N7).
 */
final class CurriculumData extends Data
{
    /**
     * @param  int  $size  In bytes.
     */
    public function __construct(
        public string $name,
        public int $size,
        #[WithCast(DateTimeInterfaceCast::class, format: Contract::DATE_INPUT_FORMATS)]
        public ?CarbonImmutable $uploaded_at,
    ) {}
}
