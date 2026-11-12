<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

use IlTuoStudioDigitale\CoworkingConnector\Validation\Rules\ItalianTaxCode;
use Spatie\LaravelData\Data;

/**
 * The final client of the principal for a process: the assigned firm works for it, without making it its own client.
 * Sent only after the signature of the documents of the coworking.
 */
final class FinalClientData extends Data
{
    public function __construct(
        public string $name,
        public ?string $tax_code,
    ) {}

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tax_code' => ['present', 'nullable', 'string', new ItalianTaxCode],
        ];
    }
}
