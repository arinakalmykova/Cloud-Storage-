<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Application\Photo\PhotoService;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function __construct(private PhotoService $photoService) {}

   public function stats(Request $request)
    {
        $userId = $request->user()->getId();
        return response()->json($this->photoService->getStorageStats($userId));
    }
}