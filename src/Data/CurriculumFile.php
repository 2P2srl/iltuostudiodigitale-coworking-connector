<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

/**
 * The curriculum of a consultant downloaded with GET /professionals/{id}/curriculum: a PDF of at most 5 MB, in clear
 * (rule N7). Personal data: show it to the firm, never keep it outside the record of the assignment.
 */
final readonly class CurriculumFile
{
    public function __construct(
        public string $name,
        public string $content,
    ) {}
}
