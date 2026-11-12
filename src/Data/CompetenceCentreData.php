<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

/**
 * An area of the catalogue of competences, with its competences in order (rule C6).
 */
final class CompetenceCentreData extends Data
{
    /**
     * @param  list<ProfileOptionData>  $competences
     */
    public function __construct(
        public string $code,
        public string $name,
        #[DataCollectionOf(ProfileOptionData::class)]
        public array $competences,
    ) {}
}
