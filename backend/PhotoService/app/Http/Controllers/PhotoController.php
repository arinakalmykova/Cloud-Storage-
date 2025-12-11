<?php

namespace App\Http\Controllers;
use App\Application\DTOs\CreatePhotoDTO;
use Illuminate\Http\Request;
use App\Application\Photo\PhotoService;
use Illuminate\Http\JsonResponse;

class PhotoController extends Controller
{
    public function __construct(
        private readonly PhotoService $photoService
    ) {}

    public function createUploadUrl(Request $request): JsonResponse
    {
        $userId = $request->user()->getId();

        $request->validate([
            'fileName' => 'sometimes|string|max:255',
            'mimeType' => 'sometimes|string|in:image/jpeg,image/jpg,image/png,image/gif,image/webp',
        ]);

        $fileName = $request->input('fileName', 'photo_' . now()->timestamp . '.jpg');
        $mimeType = $request->input('mimeType', 'image/jpeg');
        $dto= new CreatePhotoDTO($userId,$request->fileName, $request->mimeType);
        $photo = $this->photoService->createUploadIntent($dto);

        return response()->json([
            'photo_id'     => $photo->getId(),
            'upload_url'   => $photo->getPresignedUrl(),
            'expires_at'   => now()->addMinutes(15),
            'status'       => $photo->getStatus()->value(),
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $photo = $this->photoService->getById($id);

        if (!$photo || !$photo->isOwnedBy($request->user()->getId())) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json([
            'id'        => $photo->getId(),
            'status'    => $photo->getStatus()->value(),
            'url'       => $photo->getUrl(),
            'size'      => $photo->getSize(),
            'file_name' => $photo->getFileName(),
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
    }

    public function markUploaded(Request $request)
    {
        $request->validate([
            'photo_id' => 'required|string',
            'size' => 'sometimes|integer',
            'url' => 'required|string',
        ]);

        $photo = $this->photoService->getById($request->photo_id);

        if (!$photo || !$photo->isOwnedBy($request->user()->getId())) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $photo->markUploaded($request->url, $request->size ?? null);
        $this->photoService->save($photo);
        return response()->json(['status' => $photo->getStatus()->value()]);
    }

}