<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Facades;

use Illuminate\Support\Facades\Facade;
use IlTuoStudioDigitale\CoworkingConnector\Data\AcceptedEventData;
use IlTuoStudioDigitale\CoworkingConnector\Data\CurriculumFile;
use IlTuoStudioDigitale\CoworkingConnector\Data\EnvelopeData;
use IlTuoStudioDigitale\CoworkingConnector\Data\EventTypeData;
use IlTuoStudioDigitale\CoworkingConnector\Data\FileData;
use IlTuoStudioDigitale\CoworkingConnector\Data\FileReferenceData;
use IlTuoStudioDigitale\CoworkingConnector\Data\ListedMemberData;
use IlTuoStudioDigitale\CoworkingConnector\Data\MemberData;
use IlTuoStudioDigitale\CoworkingConnector\Data\MemberPage;
use IlTuoStudioDigitale\CoworkingConnector\Data\ProfessionalData;
use IlTuoStudioDigitale\CoworkingConnector\Data\ProfessionalPage;
use IlTuoStudioDigitale\CoworkingConnector\Data\ProfessionalRecordData;
use IlTuoStudioDigitale\CoworkingConnector\Data\ProfileOptionsData;
use IlTuoStudioDigitale\CoworkingConnector\Data\RecordedTransactionData;
use IlTuoStudioDigitale\CoworkingConnector\Data\RegisteredMemberData;
use IlTuoStudioDigitale\CoworkingConnector\Data\SystemData;
use IlTuoStudioDigitale\CoworkingConnector\Data\TypologyData;
use IlTuoStudioDigitale\CoworkingConnector\PlatformClient;

/**
 * @method static SystemData system()
 * @method static SystemData present(string $webhookUrl, list<int> $contractVersions = [1])
 * @method static list<TypologyData> typologies()
 * @method static list<EventTypeData> eventTypes()
 * @method static list<RegisteredMemberData> syncMembers(list<MemberData> $members)
 * @method static MemberPage searchMembers(?string $typology = null, ?string $search = null, ?int $perPage = null, ?string $cursor = null)
 * @method static ListedMemberData member(string $memberId)
 * @method static ProfileOptionsData profileOptions()
 * @method static ProfessionalPage searchProfessionals(?string $typology = null, ?string $province = null, ?string $search = null, ?int $perPage = null, ?string $cursor = null, ?string $centre = null, list<string> $competences = [], ?string $availability = null, ?string $workMode = null, ?string $qualification = null)
 * @method static ProfessionalData professional(string $professionalId)
 * @method static CurriculumFile curriculum(string $professionalId)
 * @method static AcceptedEventData send(EnvelopeData $envelope)
 * @method static RecordedTransactionData transaction(string $id)
 * @method static void declareProfessional(string $taxCode, ProfessionalRecordData $record)
 * @method static FileData uploadFile(string $uploadId, string $sender, string $recipient, string $disk, string $path, string $name, string $mediaType)
 * @method static FileData fileStatus(string $fileId)
 * @method static void downloadFile(FileReferenceData $file, string $disk, string $path)
 * @method static FileData confirmFile(string $fileId, string $sha256)
 *
 * @see PlatformClient
 */
final class Platform extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PlatformClient::class;
    }
}
