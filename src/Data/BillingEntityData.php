<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

use Spatie\LaravelData\Data;

/**
 * The entity a sole proprietorship or a company bills through, as the consultant declared it in their profile, with
 * the role of the person in it (rule N4).
 */
final class BillingEntityData extends Data
{
    public function __construct(
        public ?string $name,
        public ?string $vat_number,
        public ?string $tax_code,
        public ?string $address,
        public ?string $municipality,
        public ?string $province,
        public ?string $role,
    ) {}
}
