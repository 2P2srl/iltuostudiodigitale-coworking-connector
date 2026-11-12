<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use IlTuoStudioDigitale\CoworkingConnector\ConnectorConfig;
use IlTuoStudioDigitale\CoworkingConnector\Http\Controllers\WebhookController;
use IlTuoStudioDigitale\CoworkingConnector\Http\Middleware\VerifyPlatformSignature;

$path = resolve(ConnectorConfig::class)->webhookPath;

if ($path !== null)
{
    Route::post($path, WebhookController::class)
        ->middleware(VerifyPlatformSignature::class)
        ->name('platform.webhook');
}
