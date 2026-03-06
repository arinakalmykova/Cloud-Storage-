<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Application\Photo\PhotoService;

class StorageController extends Controller
{
    public function __construct(private PhotoService $photoService) {}

    public function stats()
    {
        return response()->json($this->photoService->getStorageStats());
    }
}