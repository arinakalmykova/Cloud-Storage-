<?php
use App\Http\Controllers\PhotoController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use App\Jobs\ProcessMinioUploadedFile;

Route::middleware('jwt')->group(function () {
    Route::post('/photos/upload-url', [PhotoController::class, 'createUploadUrl']);
    Route::get('/photos/{id}', [PhotoController::class, 'show']);
});


Route::post('/minio/webhook', function (Request $request) {
    ProcessMinioUploadedFile::dispatch($request->all())
        ->onConnection('rabbitmq')
        ->onQueue('photo');

    return "OK";
});


Route::post('/events/user-created', [UserEventsController::class, 'handleUserCreated']);
