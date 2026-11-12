<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

use Spatie\LaravelData\Data;

/**
 * A consultant registered on the platform who agreed to be found by the firms, as GET /professionals lists it, with the
 * profile they wrote: no contacts, no tax code, no VAT number (rules N1–N3). tax_code_verified false means a tax code
 * declared at the registration, checked only in its form. The codes of the profile are those of
 * PlatformClient::profileOptions(), with the labels to show.
 */
final class ListedProfessionalData extends Data
{
    /**
     * @param  list<string>  $typologies
     * @param  list<string>  $qualifications  In the order of the catalogue, maybe none.
     * @param  list<string>  $competences  In the order of the catalogue.
     */
    public function __construct(
        public string $id,
        public string $first_name,
        public string $last_name,
        public string $municipality,
        public string $province,
        public array $typologies,
        public bool $tax_code_verified,
        public ?string $legal_nature = null,
        public ?string $availability = null,
        public ?string $work_mode = null,
        public ?int $years_of_experience = null,
        public array $qualifications = [],
        public array $competences = [],
        public bool $has_curriculum = false,
    ) {}
}
