<?php

namespace App\Http\Controllers;
use App\Application\DTOs\CreatePhotoDTO;
use Illuminate\Http\Request;
use App\Application\Folder\FolderService;
use Illuminate\Http\JsonResponse;   

class FolderController extends Controller
{
    public function __construct(
        private readonly FolderService $folderService
    ) {}


    public function getFolders(Request $request): JsonResponse
    {
        $folders = $this->folderService->getFolders($request->user()->getId());
        return response()->json($folders);
    }

    public function createFolder(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255']);
        $userId = $request->user()->getId();
        $name = $request->string('name')->toString();

        $folder = $this->folderService->createFolder($userId, $name);

        return response()->json($folder);
    }

    public function deleteFolder(Request $request, string $id): JsonResponse
    {
        $this->folderService->deleteFolder($request->user()->getId(), $id);
        return response()->json(['status' => 'ok']);
    }

    public function renameFolder(Request $request, string $id): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255']);
        $userId = $request->user()->getId();
        $name = $request->string('name')->toString();

        $this->folderService->renameFolder($userId, $id, $name);

        return response()->json(['status' => 'ok']);
    }

}