<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Validation\ValidationException;
use IlTuoStudioDigitale\CoworkingConnector\ConnectorConfig;
use IlTuoStudioDigitale\CoworkingConnector\Data\TransactionData;
use IlTuoStudioDigitale\CoworkingConnector\Exceptions\PlatformRequestException;
use IlTuoStudioDigitale\CoworkingConnector\Jobs\Concerns\SendsOutboxRow;
use IlTuoStudioDigitale\CoworkingConnector\Models\PlatformTransactionOutbox;
use IlTuoStudioDigitale\CoworkingConnector\Outbox\EventOutbox;
use IlTuoStudioDigitale\CoworkingConnector\Outbox\ProfessionalOutbox;
use IlTuoStudioDigitale\CoworkingConnector\PlatformClient;

/**
 * Sends the last version of a transaction to the register. Network errors and a system not yet active are retried;
 * a contract violation, found by the platform or by the package before the request, or a conflict stop it, with the
 * error kept in the outbox. Once its first registration is confirmed, it sends again the declaration of the record
 * of the person waiting for it (rule R18).
 *
 * @internal
 */
final class SendPlatformTransaction implements ShouldQueue
{
    use Queueable;
    use SendsOutboxRow;

    public function __construct(public string $reference) {}

    public function handle(PlatformClient $client, ConnectorConfig $config, ProfessionalOutbox $professionals, EventOutbox $events): void
    {
        $row = PlatformTransactionOutbox::query()->where('reference', $this->reference)->first();

        if (!$row instanceof PlatformTransactionOutbox || $row->sent_revision === $row->revision || $this->waitsForConnection($config, $row))
        {
            return;
        }

        $revision = $row->revision;
        $isFirstRegistration = $row->sent_revision === null;
        $transaction = TransactionData::from($row->payload);
        $row->update(['attempts' => $row->attempts + 1]);

        try
        {
            $client->recordTransaction($this->reference, $row->transaction_id, $transaction, $revision);
        }
        catch (ValidationException $exception)
        {
            $this->refused($row, $exception, isFinal: true);

            return;
        }
        catch (PlatformRequestException $exception)
        {
            $this->refused($row, $exception, $exception->isContractViolation() || $exception->isConflict());

            return;
        }

        $this->confirmed($row, $revision);
        // The events between firms that follow this write in the register leave now (rule E13)
        $events->resume($row->transaction_id);

        // Only a first registration makes the platform know the person: a declaration of its record may wait for it
        $taxCode = $transaction->counterparty->tax_code;

        if ($isFirstRegistration && $taxCode !== null)
        {
            $professionals->resume($taxCode);
        }
    }
}
