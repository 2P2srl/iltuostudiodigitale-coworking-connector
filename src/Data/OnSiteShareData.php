<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

use Spatie\LaravelData\Data;

/**
 * How much of the work a consultant who works both remotely and on site does on site: a `percent` of the time or the
 * `weekly_hours`. Only with the work mode remote_or_on_site.
 */
final class OnSiteShareData extends Data
{
    /**
     * @param  string  $unit  percent or weekly_hours.
     */
    public function __construct(
        public string $unit,
        public int $value,
    ) {}
}
