<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Enums;

/**
 * The order of the consultants GET /professionals lists (rule N1).
 */
enum ProfessionalSort: string
{
    /**
     * By last name, first name and id: the order of the platform when the search names none.
     */
    case Name = 'name';

    /**
     * By years of experience, the most first and those who did not state them last, then by name.
     */
    case Experience = 'experience';

    /**
     * The profiles completed most recently first, then by id.
     */
    case Recent = 'recent';
}
