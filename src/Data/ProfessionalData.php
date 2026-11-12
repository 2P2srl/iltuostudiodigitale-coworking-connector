<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

use Spatie\LaravelData\Data;

/**
 * A consultant as GET /professionals/{id} shows it, with the data to offer an assignment and the whole profile (rule
 * N4). The platform keeps every reading (rule N5). Contacts, profile and curriculum are personal data: never cache them,
 * log them or keep them outside the record the system creates for the assignment.
 */
final class ProfessionalData extends Data
{
    /**
     * @param  list<string>  $typologies
     * @param  string|null  $mobile  In E.164, or null.
     * @param  list<string>  $qualifications  In the order of the catalogue, maybe none.
     * @param  list<string>  $competences  In the order of the catalogue.
     * @param  BillingEntityData|null  $billing_entity  Only for a sole proprietorship or a company.
     * @param  CurriculumData|null  $curriculum  The file comes with PlatformClient::curriculum().
     */
    public function __construct(
        public string $id,
        public string $first_name,
        public string $last_name,
        public string $municipality,
        public string $province,
        public array $typologies,
        public bool $tax_code_verified,
        public string $tax_code,
        public string $email,
        public ?string $mobile,
        public ?string $vat_number,
        public ?string $legal_nature = null,
        public ?string $availability = null,
        public ?string $work_mode = null,
        public ?int $years_of_experience = null,
        public array $qualifications = [],
        public array $competences = [],
        public bool $has_curriculum = false,
        public ?string $presentation = null,
        public ?string $linkedin_url = null,
        public ?BillingEntityData $billing_entity = null,
        public ?CurriculumData $curriculum = null,
    ) {}

    /**
     * The consultant as the person counterparty of a transaction, with its record as the platform shows it: locked
     * for a registered consultant (rules R3 and R20). The mobile does not go in the register.
     */
    public function toCounterparty(): CounterpartyData
    {
        return CounterpartyData::person(
            taxCode: $this->tax_code,
            firstName: $this->first_name,
            lastName: $this->last_name,
            email: $this->email,
            vatNumber: $this->vat_number,
            municipality: $this->municipality,
            province: $this->province,
        );
    }
}
