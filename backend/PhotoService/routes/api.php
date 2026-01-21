<?php
use App\Http\Controllers\PhotoController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use App\Jobs\ProcessMinioUploadedFile;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt')->group(function () {
    Route::post('/photos/upload-url', [PhotoController::class, 'createUploadUrl']);
    Route::get('/photos/{id}', [PhotoController::class, 'show']);
    Route::post('/photos/mark-uploaded', [PhotoController::class, 'markUploaded']);
    Route::post('/photos/{id}/tags', [PhotoController::class, 'updateTags']);
    Route::post('/photos/recommend',[PhotoController::class, 'recommend']);
Route::post('/broadcasting/auth', function (Request $request) {
    $logFile = storage_path('logs/broadcast_auth.log');

    $data = [
        'timestamp' => now()->toDateTimeString(),
        'user' => $request->user() ? $request->user()->getId(): null,
        'bearer_token' => $request->bearerToken(),
        'ip' => $request->ip(),
        'headers' => $request->headers->all(),
        'input' => $request->all(),
    ];

    // Преобразуем массив в строку для записи
    $content = json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

    // Записываем в файл, добавляем в конец (флаг FILE_APPEND)
    file_put_contents($logFile, $content, FILE_APPEND);

    // Продолжаем стандартную авторизацию
    return Broadcast::auth($request);
});
});



