<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexMediaRequest;
use App\Http\Requests\StoreMediaRequest;
use App\Services\MediaService;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function __construct(
        private MediaService $mediaService
    ) {}

    public function index(IndexMediaRequest $request): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->getPerPage();
        $result = $this->mediaService->getUserMedia($request->user(), $perPage);
        
        return response()->json($result);
    }

    public function store(StoreMediaRequest $request): \Illuminate\Http\JsonResponse
    {
        $file = $request->file('file');
        $collection = $request->getCollection();

        $media = $this->mediaService->uploadMedia($request->user(), $file, $collection);
        $mediaData = $this->mediaService->transformMediaItem($media);

        return response()->json($mediaData, 201);
    }

    public function destroy(Media $media): \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
    {
        $deleted = $this->mediaService->deleteMedia($media, Auth::user());

        if (!$deleted) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->noContent();
    }
}
