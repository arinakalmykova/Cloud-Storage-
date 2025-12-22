<?php
use App\Http\Controllers\PhotoController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use App\Jobs\ProcessMinioUploadedFile;

Route::middleware('jwt')->group(function () {
    Route::post('/photos/upload-url', [PhotoController::class, 'createUploadUrl']);
    Route::get('/photos/{id}', [PhotoController::class, 'show']);
    Route::post('/photos/mark-uploaded', [PhotoController::class, 'markUploaded']);
    Route::post('/photos/{id}/tags', [PhotoController::class, 'updateTags']);
});



