<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

use Spatie\LaravelData\Data;

/**
 * An option of the profile of the consultants: the code travels in the routes, the name is what to show (rule C6).
 */
final class ProfileOptionData extends Data
{
    public function __construct(
        public string $code,
        public string $name,
    ) {}
}
