<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

/**
 * The options of the profile of the consultants, as GET /profile-options lists them in the order of the platform
 * (rule C6). Read the labels from here: a new option arrives without a new release of the package.
 */
final class ProfileOptionsData extends Data
{
    /**
     * @param  list<ProfileOptionData>  $legal_natures
     * @param  list<ProfileOptionData>  $availabilities
     * @param  list<ProfileOptionData>  $work_modes
     * @param  list<ProfileOptionData>  $qualifications
     * @param  list<CompetenceCentreData>  $competence_centres
     */
    public function __construct(
        #[DataCollectionOf(ProfileOptionData::class)]
        public array $legal_natures,
        #[DataCollectionOf(ProfileOptionData::class)]
        public array $availabilities,
        #[DataCollectionOf(ProfileOptionData::class)]
        public array $work_modes,
        #[DataCollectionOf(ProfileOptionData::class)]
        public array $qualifications,
        #[DataCollectionOf(CompetenceCentreData::class)]
        public array $competence_centres,
    ) {}

    /**
     * The name of a code among the options, null for a code the platform does not list.
     *
     * @param  list<ProfileOptionData>  $options
     */
    public static function nameOf(array $options, string $code): ?string
    {
        return array_find($options, fn (ProfileOptionData $option): bool => $option->code === $code)?->name;
    }

    /**
     * Every competence of every centre, code => name, in the order of the catalogue.
     *
     * @return array<string, string>
     */
    public function competences(): array
    {
        $competences = [];

        foreach ($this->competence_centres as $centre)
        {
            foreach ($centre->competences as $competence)
            {
                $competences[$competence->code] = $competence->name;
            }
        }

        return $competences;
    }
}
