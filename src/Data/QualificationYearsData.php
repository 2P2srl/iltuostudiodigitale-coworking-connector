<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

use Spatie\LaravelData\Data;

/**
 * The years of experience a consultant declared for one of their qualifications, a code of
 * GET /profile-options (rule C6).
 */
final class QualificationYearsData extends Data
{
    public function __construct(
        public string $qualification,
        public int $years,
    ) {}
}
