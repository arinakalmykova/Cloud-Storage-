<?php
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\FolderController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use App\Jobs\ProcessMinioUploadedFile;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt')->group(function () {
    Route::post('/photos/upload-url', [PhotoController::class, 'createUploadUrl']);    
    Route::get('/photos/recent', [PhotoController::class, 'getRecentPhotos']);
    Route::get('/photos/{id}', [PhotoController::class, 'show']);
    Route::post('/photos/mark-uploaded', [PhotoController::class, 'markUploaded']);
    Route::post('/photos/{id}/tags', [PhotoController::class, 'updateTags']);
    Route::post('/photos/recommend',[PhotoController::class, 'recommend']);
    Route::get('/folders',[FolderController::class, 'getFolders']);
    Route::post('/folders',[FolderController::class, 'createFolder']);
    Route::delete('/photos/{id}', [PhotoController::class, 'deletePhoto']);
    Route::put('/photos/{id}', [PhotoController::class, 'renamePhoto']);
    Route::put('/folders/{id}',[FolderController::class, 'renameFolder']);
    Route::delete('/folders/{id}',[FolderController::class, 'deleteFolder']);
    Route::post('/folders/move-photo',[PhotoController::class, 'movePhotoToFolder']);
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
        $content = json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
        file_put_contents($logFile, $content, FILE_APPEND);
        return Broadcast::auth($request);
    });
});



