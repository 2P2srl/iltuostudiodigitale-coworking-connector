<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use IlTuoStudioDigitale\CoworkingConnector\Contract;
use IlTuoStudioDigitale\CoworkingConnector\Data\ListedProfessionalData;
use IlTuoStudioDigitale\CoworkingConnector\Data\ProfileOptionsData;
use IlTuoStudioDigitale\CoworkingConnector\Data\TransactionData;
use IlTuoStudioDigitale\CoworkingConnector\Enums\ProfessionalSort;
use IlTuoStudioDigitale\CoworkingConnector\Exceptions\PlatformRequestException;
use IlTuoStudioDigitale\CoworkingConnector\Exceptions\ProfessionalNotFoundException;
use IlTuoStudioDigitale\CoworkingConnector\Facades\Platform;

const CONSULTANT = '0199c2b0-0000-7000-8000-000000000001';

beforeEach(function (): void {
    Http::preventStrayRequests();
});

/**
 * A consultant as GET /professionals lists it.
 *
 * @return array<string, mixed>
 */
function listedConsultant(array $overrides = []): array
{
    return [
        'id' => CONSULTANT,
        'first_name' => 'Mario',
        'last_name' => 'Rossi',
        'municipality' => 'Bari',
        'province' => 'BA',
        'typologies' => ['commercialisti'],
        'tax_code_verified' => false,
        'legal_nature' => 'corporation',
        'availability' => 'collaboration_or_employment',
        'work_mode' => 'remote',
        'years_of_experience' => 6,
        'qualifications' => ['chartered_accountant'],
        'competences' => ['ordinary_accounting', 'payroll'],
        'presentation' => 'Seguo la contabilità di piccole imprese.',
        'has_curriculum' => true,
        'has_photo' => true,
        'photo_updated_at' => '2026-11-13T09:30:00Z',
        'profile_completed_at' => '2026-11-12T18:04:00Z',
        'engagements' => ['occasional', 'employment'],
        'qualification_years' => [['qualification' => 'chartered_accountant', 'years' => 6]],
        'on_site_share' => null,
        ...$overrides,
    ];
}

/**
 * A consultant as GET /professionals/{id} shows it.
 *
 * @return array<string, mixed>
 */
function consultantDetails(array $overrides = []): array
{
    return listedConsultant([
        'tax_code' => 'RSSMRA80A01H501U',
        'email' => 'mario.rossi@example.com',
        'mobile' => '+393331234567',
        'vat_number' => null,
        'linkedin_url' => 'https://www.linkedin.com/in/mario-rossi',
        'billing_entity' => [
            'name' => 'Rossi Consulenze S.r.l.',
            'vat_number' => '01234567897',
            'tax_code' => '01234567897',
            'address' => 'Via Sparano 10',
            'municipality' => 'Bari',
            'province' => 'BA',
            'role' => 'Amministratore unico',
        ],
        'curriculum' => ['name' => 'cv-mario-rossi.pdf', 'size' => 182044, 'uploaded_at' => '2026-11-12T10:15:00Z'],
        ...$overrides,
    ]);
}

/**
 * An empty page of GET /professionals, with its numbers.
 */
function consultantPage(array $meta = []): PromiseInterface
{
    return Http::response(['data' => [], 'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 25, 'total' => 0, ...$meta]]);
}

it('N1 and N3: searches the consultants with filters, order and page, without contacts', function (): void {
    withToken(['platform.test/api/v1/professionals*' => Http::response([
        'data' => [listedConsultant()],
        'links' => [
            'first' => 'https://platform.test/api/v1/professionals?sort=experience&page=1',
            'last' => 'https://platform.test/api/v1/professionals?sort=experience&page=3',
            'prev' => 'https://platform.test/api/v1/professionals?sort=experience&page=1',
            'next' => 'https://platform.test/api/v1/professionals?sort=experience&page=3',
        ],
        'meta' => ['current_page' => 2, 'last_page' => 3, 'per_page' => 10, 'total' => 23],
    ])]);

    $page = Platform::searchProfessionals(typology: 'commercialisti', province: 'BA', search: 'rossi', perPage: 10, page: 2, sort: ProfessionalSort::Experience);

    expect($page->professionals[0])
        ->id->toBe(CONSULTANT)
        ->last_name->toBe('Rossi')
        ->typologies->toBe(['commercialisti'])
        ->tax_code_verified->toBeFalse()
        ->legal_nature->toBe('corporation')
        ->availability->toBe('collaboration_or_employment')
        ->work_mode->toBe('remote')
        ->years_of_experience->toBe(6)
        ->qualifications->toBe(['chartered_accountant'])
        ->competences->toBe(['ordinary_accounting', 'payroll'])
        ->presentation->toBe('Seguo la contabilità di piccole imprese.')
        ->has_curriculum->toBeTrue()
        ->has_photo->toBeTrue()
        ->photo_updated_at->toEqual(CarbonImmutable::parse('2026-11-13T09:30:00Z'))
        ->profile_completed_at->toEqual(CarbonImmutable::parse('2026-11-12T18:04:00Z'))
        ->engagements->toBe(['occasional', 'employment'])
        ->on_site_share->toBeNull()
        ->and($page->professionals[0]->qualification_years[0])
        ->qualification->toBe('chartered_accountant')
        ->years->toBe(6)
        ->and(property_exists($page->professionals[0], 'email'))->toBeFalse()
        ->and($page)
        ->currentPage->toBe(2)
        ->lastPage->toBe(3)
        ->perPage->toBe(10)
        ->total->toBe(23)
        ->and($page->hasMore())->toBeTrue();
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://platform.test/api/v1/professionals?typology=commercialisti&province=BA&search=rossi&per_page=10&page=2&sort=experience');
});

it('N1: sends every order of the contract', function (ProfessionalSort $sort, string $value): void {
    withToken(['platform.test/api/v1/professionals*' => consultantPage()]);

    Platform::searchProfessionals(sort: $sort);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://platform.test/api/v1/professionals?sort='.$value);
})->with([
    'by name' => [ProfessionalSort::Name, 'name'],
    'by experience' => [ProfessionalSort::Experience, 'experience'],
    'the most recent' => [ProfessionalSort::Recent, 'recent'],
]);

it('N4 and N8: reads a consultant listed before the photo and the date of the profile, with no defaults lost', function (): void {
    $consultant = ListedProfessionalData::from(Arr::except(listedConsultant(), ['presentation', 'has_photo', 'photo_updated_at', 'profile_completed_at', 'engagements', 'qualification_years', 'on_site_share']));

    expect($consultant)
        ->engagements->toBe([])
        ->qualification_years->toBe([])
        ->on_site_share->toBeNull()
        ->presentation->toBeNull()
        ->has_photo->toBeFalse()
        ->photo_updated_at->toBeNull()
        ->profile_completed_at->toBeNull();
});

it('N1 and N6: searches the consultants by their profile', function (): void {
    withToken(['platform.test/api/v1/professionals*' => consultantPage()]);

    Platform::searchProfessionals(centre: 'labour', competences: ['payroll', 'contractors'], availability: 'employment', workMode: 'remote', qualification: 'labour_consultant', engagement: 'occasional');

    Http::assertSent(fn (Request $request): bool => urldecode($request->url()) === 'https://platform.test/api/v1/professionals?centre=labour&competences[0]=payroll&competences[1]=contractors&availability=employment&work_mode=remote&qualification=labour_consultant&engagement=occasional');
});

it('N1: refuses more competences than a search may ask for, before any request', function (): void {
    expect(fn () => Platform::searchProfessionals(competences: array_fill(0, Contract::MAX_COMPETENCE_FILTERS + 1, 'payroll')))
        ->toThrow(ValidationException::class, 'Al massimo 20 competenze in una sola ricerca.');

    Http::assertNothingSent();
});

it('N1: reads a page past the last one of the consultants', function (): void {
    withToken(['platform.test/api/v1/professionals*' => consultantPage(['current_page' => 4, 'last_page' => 3, 'total' => 61])]);

    $page = Platform::searchProfessionals();

    expect($page->professionals)->toBe([])
        ->and($page)
        ->currentPage->toBe(4)
        ->lastPage->toBe(3)
        ->total->toBe(61)
        ->and($page->hasMore())->toBeFalse();
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://platform.test/api/v1/professionals');
});

it('N4 and R3: reads a consultant with the data to assign, ready as the person counterparty', function (): void {
    withToken(['platform.test/api/v1/professionals/*' => Http::response(['data' => consultantDetails([
        'work_mode' => 'remote_or_on_site',
        'engagements' => ['continuous'],
        'on_site_share' => ['unit' => 'percent', 'value' => 40],
    ])])]);

    $consultant = Platform::professional(CONSULTANT);

    expect($consultant)
        ->tax_code->toBe('RSSMRA80A01H501U')
        ->mobile->toBe('+393331234567')
        ->vat_number->toBeNull()
        ->competences->toBe(['ordinary_accounting', 'payroll'])
        ->presentation->toBe('Seguo la contabilità di piccole imprese.')
        ->has_photo->toBeTrue()
        ->photo_updated_at->toEqual(CarbonImmutable::parse('2026-11-13T09:30:00Z'))
        ->profile_completed_at->toEqual(CarbonImmutable::parse('2026-11-12T18:04:00Z'))
        ->linkedin_url->toBe('https://www.linkedin.com/in/mario-rossi')
        ->engagements->toBe(['continuous'])
        ->and($consultant->qualification_years[0]->years)->toBe(6)
        ->and($consultant->on_site_share)
        ->unit->toBe('percent')
        ->value->toBe(40)
        ->and($consultant->billing_entity)
        ->name->toBe('Rossi Consulenze S.r.l.')
        ->role->toBe('Amministratore unico')
        ->and($consultant->curriculum)
        ->name->toBe('cv-mario-rossi.pdf')
        ->size->toBe(182044)
        ->uploaded_at->toEqual(CarbonImmutable::parse('2026-11-12T10:15:00Z'))
        ->and($consultant->toCounterparty()->toWire())->toBe(person())
        ->and(TransactionData::validateAndCreate(transaction(['counterparty' => $consultant->toCounterparty()->toWire()]))->counterparty->toWire())->toBe(person());
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://platform.test/api/v1/professionals/'.CONSULTANT);
});

it('N4: reads a consultant who bills in their own name and has no curriculum', function (): void {
    withToken(['platform.test/api/v1/professionals/*' => Http::response(['data' => consultantDetails(['legal_nature' => 'individual', 'billing_entity' => null, 'curriculum' => null, 'has_curriculum' => false])])]);

    expect(Platform::professional(CONSULTANT))
        ->billing_entity->toBeNull()
        ->curriculum->toBeNull();
});

it('N7: downloads the curriculum of a consultant, with the name of the file', function (string $disposition, string $name): void {
    withToken(['platform.test/api/v1/professionals/*/curriculum' => Http::response("%PDF-1.4\nMario Rossi\n%%EOF\n", 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => $disposition,
    ])]);

    $curriculum = Platform::curriculum(CONSULTANT);

    expect($curriculum)
        ->name->toBe($name)
        ->content->toBe("%PDF-1.4\nMario Rossi\n%%EOF\n");
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://platform.test/api/v1/professionals/'.CONSULTANT.'/curriculum');
})->with([
    'plain' => ['attachment; filename="cv-mario-rossi.pdf"', 'cv-mario-rossi.pdf'],
    'with an UTF-8 name' => ["attachment; filename=\"Curriculum Nicolo.pdf\"; filename*=utf-8''Curriculum%20Nicol%C3%B2.pdf", 'Curriculum Nicolò.pdf'],
    'without quotes' => ['attachment; filename=cv.pdf', 'cv.pdf'],
    'without a name' => ['attachment', 'curriculum.pdf'],
]);

it('N8: downloads the photo of a consultant, with its type', function (array $headers, string $mimeType): void {
    withToken(['platform.test/api/v1/professionals/*/photo' => Http::response("\xFF\xD8\xFF\xE0photo", 200, $headers)]);

    expect(Platform::photo(CONSULTANT))
        ->content->toBe("\xFF\xD8\xFF\xE0photo")
        ->mimeType->toBe($mimeType);
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://platform.test/api/v1/professionals/'.CONSULTANT.'/photo');
})->with([
    'a JPEG' => [['Content-Type' => 'image/jpeg'], 'image/jpeg'],
    'with parameters' => [['Content-Type' => 'image/png; charset=binary'], 'image/png'],
    'without a type' => [['Content-Type' => ''], 'image/jpeg'],
]);

it('C6: reads the options of the profile with their labels', function (): void {
    withToken(['platform.test/api/v1/profile-options' => Http::response(['data' => [
        'legal_natures' => [['code' => 'freelancer', 'name' => 'Libera professione con partita IVA']],
        'availabilities' => [['code' => 'collaboration', 'name' => 'Collaborazione']],
        'work_modes' => [['code' => 'remote', 'name' => 'Da remoto']],
        'qualifications' => [['code' => 'chartered_accountant', 'name' => 'Commercialista'], ['code' => 'tax_consultant', 'name' => 'Tributarista'], ['code' => 'not_registered', 'name' => 'Non iscritto ad albi']],
        'competence_centres' => [
            ['code' => 'accounting', 'name' => 'Contabilità e bilancio', 'competences' => [['code' => 'ordinary_accounting', 'name' => 'Contabilità ordinaria']]],
            ['code' => 'labour', 'name' => 'Lavoro', 'competences' => [['code' => 'payroll', 'name' => 'Paghe e contributi']]],
        ],
        'engagements' => [['code' => 'occasional', 'name' => 'Collaborazione occasionale'], ['code' => 'continuous', 'name' => 'Collaborazione continuativa'], ['code' => 'employment', 'name' => 'Lavoro dipendente']],
    ]])]);

    $options = Platform::profileOptions();

    expect($options->legal_natures[0])->code->toBe('freelancer')->name->toBe('Libera professione con partita IVA')
        ->and($options->competence_centres[1]->competences[0]->name)->toBe('Paghe e contributi')
        ->and($options->competences())->toBe(['ordinary_accounting' => 'Contabilità ordinaria', 'payroll' => 'Paghe e contributi'])
        ->and(ProfileOptionsData::nameOf($options->qualifications, 'chartered_accountant'))->toBe('Commercialista')
        ->and(ProfileOptionsData::nameOf($options->qualifications, 'astronaut'))->toBeNull()
        ->and(ProfileOptionsData::nameOf($options->qualifications, 'not_registered'))->toBe('Non iscritto ad albi')
        ->and(ProfileOptionsData::nameOf($options->engagements, 'continuous'))->toBe('Collaborazione continuativa');
});

it('C6: reads the options of a portal that lists no engagements yet', function (): void {
    expect(ProfileOptionsData::from([
        'legal_natures' => [],
        'availabilities' => [],
        'work_modes' => [],
        'qualifications' => [],
        'competence_centres' => [],
    ])->engagements)->toBe([]);
});

it('N4, N7 and N8: tells a consultant not to find, or without the file, from the other errors', function (string $path, Closure $read): void {
    withToken(['platform.test/api/v1/professionals/'.$path => Http::response(['message' => 'Non trovato.'], 404)]);

    expect($read)->toThrow(fn (ProfessionalNotFoundException $exception) => expect($exception->id)->toBe(CONSULTANT));
})->with('consultant readings');

it('N4, N7 and N8: throws the other refusals of the platform as they are', function (string $path, Closure $read): void {
    withToken(['platform.test/api/v1/professionals/'.$path => Http::response(['message' => 'Sospeso.', 'reason' => 'suspended'], 403)]);

    expect($read)->toThrow(fn (PlatformRequestException $exception) => expect($exception->status)->toBe(403));
})->with('consultant readings');

dataset('consultant readings', [
    'the details' => ['*', fn () => Platform::professional(CONSULTANT)],
    'the curriculum' => ['*/curriculum', fn () => Platform::curriculum(CONSULTANT)],
    'the photo' => ['*/photo', fn () => Platform::photo(CONSULTANT)],
]);
