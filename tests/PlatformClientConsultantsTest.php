<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use IlTuoStudioDigitale\CoworkingConnector\Contract;
use IlTuoStudioDigitale\CoworkingConnector\Data\ProfileOptionsData;
use IlTuoStudioDigitale\CoworkingConnector\Data\TransactionData;
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
        'has_curriculum' => true,
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
        'presentation' => 'Seguo la contabilità di piccole imprese.',
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

it('N1 and N3: searches the consultants with filters and cursor, without contacts', function (): void {
    withToken(['platform.test/api/v1/professionals*' => Http::response([
        'data' => [listedConsultant()],
        'links' => ['next' => 'https://platform.test/api/v1/professionals?cursor=abc', 'prev' => null],
        'meta' => ['per_page' => 25, 'next_cursor' => 'abc', 'prev_cursor' => null],
    ])]);

    $page = Platform::searchProfessionals(typology: 'commercialisti', province: 'BA', search: 'rossi', perPage: 10, cursor: 'start');

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
        ->has_curriculum->toBeTrue()
        ->and(property_exists($page->professionals[0], 'email'))->toBeFalse()
        ->and($page->hasMore())->toBeTrue()
        ->and($page->previousCursor)->toBeNull();
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://platform.test/api/v1/professionals?typology=commercialisti&province=BA&search=rossi&per_page=10&cursor=start');
});

it('N1 and N6: searches the consultants by their profile', function (): void {
    withToken(['platform.test/api/v1/professionals*' => Http::response(['data' => [], 'meta' => ['next_cursor' => null, 'prev_cursor' => null]])]);

    Platform::searchProfessionals(centre: 'labour', competences: ['payroll', 'contractors'], availability: 'employment', workMode: 'remote', qualification: 'labour_consultant');

    Http::assertSent(fn (Request $request): bool => urldecode($request->url()) === 'https://platform.test/api/v1/professionals?centre=labour&competences[0]=payroll&competences[1]=contractors&availability=employment&work_mode=remote&qualification=labour_consultant');
});

it('N1: refuses more competences than a search may ask for, before any request', function (): void {
    expect(fn () => Platform::searchProfessionals(competences: array_fill(0, Contract::MAX_COMPETENCE_FILTERS + 1, 'payroll')))
        ->toThrow(ValidationException::class, 'Al massimo 20 competenze in una sola ricerca.');

    Http::assertNothingSent();
});

it('N1: reads the last page of the consultants', function (): void {
    withToken(['platform.test/api/v1/professionals*' => Http::response(['data' => [], 'meta' => ['next_cursor' => null, 'prev_cursor' => 'abc']])]);

    $page = Platform::searchProfessionals();

    expect($page->professionals)->toBe([])
        ->and($page->hasMore())->toBeFalse()
        ->and($page->previousCursor)->toBe('abc');
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://platform.test/api/v1/professionals');
});

it('N4 and R3: reads a consultant with the data to assign, ready as the person counterparty', function (): void {
    withToken(['platform.test/api/v1/professionals/*' => Http::response(['data' => consultantDetails()])]);

    $consultant = Platform::professional(CONSULTANT);

    expect($consultant)
        ->tax_code->toBe('RSSMRA80A01H501U')
        ->mobile->toBe('+393331234567')
        ->vat_number->toBeNull()
        ->competences->toBe(['ordinary_accounting', 'payroll'])
        ->presentation->toBe('Seguo la contabilità di piccole imprese.')
        ->linkedin_url->toBe('https://www.linkedin.com/in/mario-rossi')
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

it('N7: tells a consultant without a curriculum or not to find from the other errors', function (): void {
    withToken(['platform.test/api/v1/professionals/*/curriculum' => Http::response(['message' => 'Non trovato.'], 404)]);

    expect(fn () => Platform::curriculum(CONSULTANT))
        ->toThrow(fn (ProfessionalNotFoundException $exception) => expect($exception->id)->toBe(CONSULTANT));
});

it('N7: throws the other refusals of the curriculum as they are', function (): void {
    withToken(['platform.test/api/v1/professionals/*/curriculum' => Http::response(['message' => 'Sospeso.', 'reason' => 'suspended'], 403)]);

    expect(fn () => Platform::curriculum(CONSULTANT))
        ->toThrow(fn (PlatformRequestException $exception) => expect($exception->status)->toBe(403));
});

it('C6: reads the options of the profile with their labels', function (): void {
    withToken(['platform.test/api/v1/profile-options' => Http::response(['data' => [
        'legal_natures' => [['code' => 'freelancer', 'name' => 'Libera professione con partita IVA']],
        'availabilities' => [['code' => 'collaboration', 'name' => 'Collaborazione']],
        'work_modes' => [['code' => 'remote', 'name' => 'Da remoto']],
        'qualifications' => [['code' => 'chartered_accountant', 'name' => 'Commercialista']],
        'competence_centres' => [
            ['code' => 'accounting', 'name' => 'Contabilità e bilancio', 'competences' => [['code' => 'ordinary_accounting', 'name' => 'Contabilità ordinaria']]],
            ['code' => 'labour', 'name' => 'Lavoro', 'competences' => [['code' => 'payroll', 'name' => 'Paghe e contributi']]],
        ],
    ]])]);

    $options = Platform::profileOptions();

    expect($options->legal_natures[0])->code->toBe('freelancer')->name->toBe('Libera professione con partita IVA')
        ->and($options->competence_centres[1]->competences[0]->name)->toBe('Paghe e contributi')
        ->and($options->competences())->toBe(['ordinary_accounting' => 'Contabilità ordinaria', 'payroll' => 'Paghe e contributi'])
        ->and(ProfileOptionsData::nameOf($options->qualifications, 'chartered_accountant'))->toBe('Commercialista')
        ->and(ProfileOptionsData::nameOf($options->qualifications, 'astronaut'))->toBeNull();
});

it('N4: tells a consultant that is not among those to find from the other errors', function (): void {
    withToken(['platform.test/api/v1/professionals/*' => Http::response(['message' => 'Non trovato.'], 404)]);

    expect(fn () => Platform::professional(CONSULTANT))
        ->toThrow(fn (ProfessionalNotFoundException $exception) => expect($exception->id)->toBe(CONSULTANT));
});

it('N4: throws the other refusals of the platform as they are', function (): void {
    withToken(['platform.test/api/v1/professionals/*' => Http::response(['message' => 'Sospeso.', 'reason' => 'suspended'], 403)]);

    expect(fn () => Platform::professional(CONSULTANT))
        ->toThrow(fn (PlatformRequestException $exception) => expect($exception->status)->toBe(403));
});
