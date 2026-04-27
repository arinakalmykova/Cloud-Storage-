<?php

namespace App\Http\Controllers;

use App\Application\Color\ColorService;
use App\Application\DTOs\CreatePhotoDTO;
use App\Application\Photo\CompressionRecommendationBroker;
use App\Application\Photo\MLServiceClient;
use App\Application\Photo\PhotoService;
use App\Application\Tag\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function __construct(
        private readonly PhotoService $photoService,
        private readonly ColorService $colorService,
        private readonly TagService $tagService,
        private readonly CompressionRecommendationBroker $compressionRecommendationBroker
    ) {
    }

    public function createUploadUrl(Request $request): JsonResponse
    {
        $userId = $request->user()->getId();

        $request->validate([
            'fileName' => 'required|string|max:255',
            'mimeType' => 'required|string',
            'description' => 'nullable|string|max:500',
        ]);

        $dto = new CreatePhotoDTO(
            userId: $userId,
            fileName: $request->fileName,
            description: $request->description ?? null,
            mimeType: $request->mimeType
        );

        $photo = $this->photoService->createUploadIntent($dto);

        return response()->json([
            'photo_id' => $photo->getId(),
            'upload_url' => $photo->getPresignedUrl(),
            'expires_at' => now()->addMinutes(15),
            'status' => $photo->getStatus()->value(),
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $photo = $this->photoService->getById($id);

        if (!$photo || !$photo->isOwnedBy($request->user()->getId())) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json([
            'id' => $photo->getId(),
            'status' => $photo->getStatus()->value(),
            'url' => $photo->getUrl(),
            'size' => $photo->getSize(),
            'file_name' => $photo->getFileName(),
        ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function markUploaded(Request $request): JsonResponse
    {
        $request->validate([
            'photo_id' => 'required|string',
            'size' => 'sometimes|integer',
            'url' => 'required|string',
            'quality' => 'sometimes|integer',
            'format' => 'sometimes|string',
            'folder_id' => 'sometimes|string|nullable',
            'content_type' => 'sometimes|string|nullable',
        ]);

        try {
            $photo = $this->photoService->getById($request->input('photo_id'));

            if (!$photo || !$photo->isOwnedBy($request->user()->getId())) {
                return response()->json([
                    'status' => 'failed',
                    'error' => 'Photo not found or not owned by user',
                ], 404);
            }

            $photo->markUploaded(
                $request->input('url'),
                (int) $request->input('size', 0),
                $request->filled('quality') ? (int) $request->input('quality') : null,
                $request->input('format'),
                $request->input('folder_id'),
                $request->input('content_type')
            );

            $this->photoService->save($photo);
            $this->photoService->processUploadedPhoto($photo);

            return response()->json(['status' => $photo->getStatus()->value()]);
        } catch (\Exception $e) {
            \Log::error('Mark uploaded failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function recommend(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200',
            'content_type' => 'sometimes|string',
        ]);

        $file = $request->file('file');

        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/avif',
        ];

        if (!in_array($file->getMimeType(), $allowedMimes, true)) {
            return response()->json([
                'error' => 'Invalid file type',
                'messages' => [
                    'file' => ['The file must be an image of type: jpg, jpeg, png, webp, avif.'],
                ],
            ], 422);
        }

        $tmp = $file->getPathname();
        $contentType = $request->input('content_type');

        if (!$contentType) {
            $mlClient = new MLServiceClient();
            $classification = $mlClient->classify($tmp);
            $contentType = $classification['content_type'];
        }

        $recommendation = $this->compressionRecommendationBroker->requestRecommendation(
            $tmp,
            $file->getMimeType(),
            $contentType
        );

        return response()->json([
            'format' => $recommendation['format'],
            'quality' => $recommendation['quality'],
            'content_type' => $contentType,
            'estimated_size' => $recommendation['estimated_size'] ?? null,
            'saved_bytes' => $recommendation['saved_bytes'] ?? null,
            'saved_percent' => $recommendation['saved_percent'] ?? null,
        ]);
    }

    public function estimateCompression(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200',
            'format' => 'required|string|in:jpeg,png,webp,avif',
            'quality' => 'required|integer|min:0|max:100',
            'content_type' => 'required|string',
        ]);

        $file = $request->file('file');

        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/avif',
        ];

        if (!in_array($file->getMimeType(), $allowedMimes, true)) {
            return response()->json([
                'error' => 'Invalid file type',
                'messages' => [
                    'file' => ['The file must be an image of type: jpg, jpeg, png, webp, avif.'],
                ],
            ], 422);
        }

        $estimate = $this->compressionRecommendationBroker->requestEstimate(
            $file->getPathname(),
            $file->getMimeType(),
            $request->string('content_type')->toString(),
            $request->string('format')->toString(),
            (int) $request->input('quality')
        );

        return response()->json([
            'format' => $estimate['format'],
            'quality' => $estimate['quality'],
            'content_type' => $request->string('content_type')->toString(),
            'estimated_size' => $estimate['estimated_size'] ?? null,
            'saved_bytes' => $estimate['saved_bytes'] ?? null,
            'saved_percent' => $estimate['saved_percent'] ?? null,
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

    public function search(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $filters = $request->only([
            'file_name',
            'tags',
            'dominant_color',
            'description',
            'dateFrom',
            'dateTo',
            'format',
            'content_type',
        ]);

        $photos = $this->photoService->searchPhotos($request->user()->getId(), $query, $filters);

        return response()->json($photos);
    }

    public function getFilters(Request $request): JsonResponse
    {
        $userId = $request->user()->getId();

        $tags = $this->tagService->getTags($userId);
        $colors = $this->colorService->getColors($userId);

        return response()->json([
            'tags' => $tags,
            'colors' => $colors,
            'content_types' => $this->photoService->getContentTypes($userId),
        ]);
    }
}
