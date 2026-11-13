<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Data;

/**
 * The photo of a consultant downloaded with GET /professionals/{id}/photo: a square of at most 400 pixels (rule N8).
 * Keep it in cache until photo_updated_at changes, and drop it when the consultant leaves the list or has_photo turns
 * false.
 */
final readonly class PhotoFile
{
    public function __construct(
        public string $content,
        public string $mimeType,
    ) {}
}
