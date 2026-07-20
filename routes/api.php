<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CallController;
use App\Http\Controllers\Api\V1\DirectMessageController;
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\MobileController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PublicChatController;
use App\Http\Controllers\Api\V1\PublicChurchController;
use App\Http\Controllers\Api\V1\PublicContentController;
use App\Http\Controllers\Api\V1\RealtimeController;
use App\Http\Controllers\Api\V1\UnreadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('mobile/bootstrap', [MobileController::class, 'bootstrap']);

    Route::prefix('auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('refresh', [AuthController::class, 'refresh']);

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('device-token', [AuthController::class, 'deviceToken']);
            Route::delete('device-token', [AuthController::class, 'deleteDeviceToken']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('media/uploads', [MediaController::class, 'index']);
        Route::post('media/uploads', [MediaController::class, 'store']);
        Route::delete('media/uploads/{mediaUpload}', [MediaController::class, 'destroy']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead']);

        Route::get('realtime/config', [RealtimeController::class, 'config']);
        Route::post('realtime/calls/{call}/token', [RealtimeController::class, 'callToken']);
    });

    Route::prefix('public')->group(function (): void {
        Route::get('sermons', [PublicContentController::class, 'sermons']);
        Route::get('sermons/{sermon:slug}', [PublicContentController::class, 'sermon']);
        Route::get('radio', [PublicContentController::class, 'radio']);
        Route::get('live', [PublicContentController::class, 'live']);

        Route::get('info', [PublicChurchController::class, 'info']);
        Route::get('beliefs', [PublicChurchController::class, 'beliefs']);
        Route::get('jurisdictions', [PublicChurchController::class, 'jurisdictions']);
        Route::get('committee', [PublicChurchController::class, 'committee']);
        Route::get('members', [PublicChurchController::class, 'members']);
        Route::get('members/stats', [PublicChurchController::class, 'memberStats']);
        Route::get('photos', [PublicChurchController::class, 'photos']);
        Route::get('testimonials', [PublicChurchController::class, 'testimonials']);
        Route::get('programs', [PublicChurchController::class, 'programs']);
        Route::get('quote', [PublicChurchController::class, 'quote']);
        Route::get('quotes', [PublicChurchController::class, 'quotes']);
        Route::get('prayer-rooms', [PublicChurchController::class, 'prayerRooms']);
        Route::post('prayer-rooms/launch', [PublicChurchController::class, 'launchPrayerRoom'])->middleware('auth:sanctum');
        Route::get('pastor-calendar', [PublicChurchController::class, 'pastorCalendar']);

        Route::get('chat', [PublicChatController::class, 'index']);
        Route::post('chat', [PublicChatController::class, 'store'])->middleware('auth:sanctum');
        Route::post('chat/seen', [PublicChatController::class, 'markSeen'])->middleware('auth:sanctum');

        Route::get('groups', [GroupController::class, 'index']);
        Route::post('groups', [GroupController::class, 'store'])->middleware('auth:sanctum');
        Route::get('groups/mine', [GroupController::class, 'mine'])->middleware('auth:sanctum');
        Route::post('groups/{group}/join', [GroupController::class, 'join'])->middleware('auth:sanctum');
        Route::get('groups/{group}/chat', [GroupController::class, 'messages']);
        Route::post('groups/{group}/chat', [GroupController::class, 'storeMessage'])->middleware('auth:sanctum');

        Route::get('dm/calls', [CallController::class, 'dmIndex'])->middleware('auth:sanctum');
        Route::post('dm/calls', [CallController::class, 'dmStore'])->middleware('auth:sanctum');
        Route::post('dm/calls/seen', [CallController::class, 'markDmSeen'])->middleware('auth:sanctum');
        Route::get('dm', [DirectMessageController::class, 'index'])->middleware('auth:sanctum');
        Route::post('dm', [DirectMessageController::class, 'store'])->middleware('auth:sanctum');
        Route::get('dm/{conversation}', [DirectMessageController::class, 'show'])->middleware('auth:sanctum');
        Route::post('dm/{conversation}', [DirectMessageController::class, 'storeInThread'])->middleware('auth:sanctum');

        Route::get('calls', [CallController::class, 'publicIndex']);
        Route::post('calls', [CallController::class, 'publicStore'])->middleware('auth:sanctum');
        Route::get('social/unread', [UnreadController::class, 'social'])->middleware('auth:sanctum');
    });

    // Compatibility aliases kept for the APK-era social contract.
    Route::get('chat', [PublicChatController::class, 'index']);
    Route::post('chat', [PublicChatController::class, 'store'])->middleware('auth:sanctum');
    Route::get('messages', [PublicChatController::class, 'index']);
    Route::get('groups', [GroupController::class, 'index']);
    Route::get('calls', [CallController::class, 'index'])->middleware('auth:sanctum');
    Route::post('call', [CallController::class, 'store'])->middleware('auth:sanctum');
    Route::post('call/{call}/token', [RealtimeController::class, 'callToken'])->middleware('auth:sanctum');
    Route::post('call/signal', [CallController::class, 'storeSignal'])->middleware('auth:sanctum');
    Route::post('call/state', [CallController::class, 'updateState'])->middleware('auth:sanctum');
});
