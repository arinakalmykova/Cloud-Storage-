<?php

namespace App\Http\Controllers;
use App\Application\DTOs\CreatePhotoDTO;
use Illuminate\Http\Request;
use App\Application\Photo\PhotoService;
use Illuminate\Http\JsonResponse;
use App\Application\Photo\MLServiceClient;
use App\Console\Commands\ConsumePhotoCompressed;

class PhotoController extends Controller
{
    public function __construct(
        private readonly PhotoService $photoService
    ) {}

    public function createUploadUrl(Request $request): JsonResponse
    {
        $userId = $request->user()->getId();

        $request->validate([
        'fileName' => 'required|string|max:255',
        'mimeType' => 'required|string',
        'description' => 'nullable|string|max:500'
        ]);


        $dto = new CreatePhotoDTO(
            userId: $userId,
            fileName: $request->fileName,
            description: $request->description ?? null,
            mimeType: $request->mimeType
        );

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
            'dominant_color' => $photo->getDominantColor(),
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
            'quality' => 'sometimes|integer',
            'format' => 'sometimes|string',
            'folder_id' => 'sometimes|string|nullable'
        ]);

        $photo = $this->photoService->getById($request->photo_id);

        if (!$photo || !$photo->isOwnedBy($request->user()->getId())) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $photo->markUploaded($request->url, $request->size,$request->quality,$request->format, $request->folder_id);
        $this->photoService->save($photo);
        $result = $this->photoService->processUploadedPhoto($photo);

        return response()->json(['status' => $photo->getStatus()->value()]);
    }

    public function recommend(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|image|mimes:jpg,jpeg,png,gif,webp,avif|max:10240',
        ]);

        $tmp = $request->file('file')->getPathname();

        $mlClient = new MLServiceClient();
        $result = $mlClient->classify($tmp);

        switch ($result['content_type']) {
            case 'photo':
                $mlFormat = 'webp';
                $mlQuality = 85;
                break;
            case 'text_graphics':
                $mlFormat = 'png';
                $mlQuality = 100;
                break;
            case 'illustration':
                $mlFormat = 'avif';
                $mlQuality = 80;
                break;
            case 'ui_screenshot':
                $mlFormat = 'png';
                $mlQuality = 95;
                break;
            case 'mixed':
            default:
                $mlFormat = 'webp';
                $mlQuality = 80;
        }

        return response()->json([
            'format' => $mlFormat,
            'quality' => $mlQuality,
            'content_type' => $result['content_type']
        ]);
    }

    public function updateTags(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'tags' => 'required|array',
            'tags.*' => 'string|max:50',
        ]);

        $this->photoService->updateTags(
            photoId: $id,
            userId: $request->user()->getId(),
            tagNames: $request->tags
        );

        return response()->json(['status' => 'ok']);
    }

    public function getRecentPhotos(Request $request): JsonResponse
    {
        
        $photos = $this->photoService->getRecentPhotos($request->user()->getId());
        return response()->json($photos);
    }

    public function movePhotoToFolder(Request $request): JsonResponse
    {
        $request->validate(['photo_id' => 'required|string']);
        $request->validate(['folder_id' => 'required|string|nullable']);
        $id = $request->string('photo_id')->toString();
        $userId = $request->user()->getId();
        $folderId = $request->string('folder_id')->toString();

        $this->photoService->movePhotoToFolder($userId, $id, $folderId);

        return response()->json(['status' => 'ok']);
    }

    public function deletePhoto(Request $request, string $id): JsonResponse
    {
        $this->photoService->deletePhoto($id, $request->user()->getId());
        return response()->json(['status' => 'ok']);
    }


    public function renamePhoto(Request $request, string $id): JsonResponse
    {
        $request->validate(['title' => 'required|string|max:255']);
        $userId = $request->user()->getId();
        $title = $request->string('title')->toString();

        $this->photoService->renamePhoto($userId, $id, $title);

        return response()->json(['status' => 'ok']);
    }
}