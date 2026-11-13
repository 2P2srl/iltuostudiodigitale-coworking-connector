<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

/**
 * A page of GET /professionals, with the total (rule N1): pass the next page to the same search to read on. A page past
 * the last one is empty.
 */
final readonly class ProfessionalPage
{
    /**
     * @param  list<ListedProfessionalData>  $professionals
     */
    public function __construct(
        public array $professionals,
        public int $currentPage,
        public int $lastPage,
        public int $perPage,
        public int $total,
    ) {}

    public function hasMore(): bool
    {
        return $this->currentPage < $this->lastPage;
    }
}
