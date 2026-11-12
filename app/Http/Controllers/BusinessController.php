<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $businesses = $request->user()
            ->businesses()
            ->orderByDesc('id')
            ->get();

        return view('businesses', compact('businesses'));
    }

    public function create()
    {
        return view('businesses.create');
    }

    public function store(Request $request)
    {
        $businessName = trim((string) $request->input('business_name', ''));
        $currency = trim((string) $request->input('currency', 'IDR'));
        $notes = trim((string) $request->input('notes', ''));

        if ($businessName === '') {
            return response()->json([
                'status' => 'error',
                'message' => t('business_name_user_required'),
            ], 400);
        }

        $file = $request->file('photo');
        $photoExtension = null;

        if ($file !== null) {
            if (! $file->isValid()) {
                $isSizeError = in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);

                return response()->json([
                    'status' => 'error',
                    'message' => $isSizeError ? t('photo_max_size') : t('photo_upload_failed'),
                ], 400);
            }

            if ($file->getSize() > 5 * 1024 * 1024) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('photo_max_size'),
                ], 400);
            }

            $imageInfo = @getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('invalid_image_file'),
                ], 400);
            }

            $mimeTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ];

            $mime = $imageInfo['mime'] ?? '';
            if (! isset($mimeTypes[$mime])) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('invalid_image_format'),
                ], 400);
            }

            $photoExtension = $mimeTypes[$mime];
        }

        try {
            $business = $request->user()->businesses()->create([
                'business_name' => $businessName,
                'currency' => $currency !== '' ? $currency : 'IDR',
                'notes' => $notes,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => t('business_create_failed'),
            ], 500);
        }

        if ($file !== null && $photoExtension !== null) {
            $photoName = time().'.'.$photoExtension;

            $stored = Storage::disk('uploads')->putFileAs(
                'businesses/'.$business->id,
                $file,
                $photoName
            );

            if ($stored) {
                $business->update(['photo' => $photoName]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => t('business_create_success'),
        ]);
    }

    public function destroy(Request $request)
    {
        $businessId = (int) $request->input('business_id', 0);

        if ($businessId <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => t('invalid_business'),
            ]);
        }

        try {
            $deleted = $request->user()
                ->businesses()
                ->where('id', $businessId)
                ->delete();
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => t('business_delete_failed'),
            ]);
        }

        if ($deleted > 0) {
            Storage::disk('uploads')->deleteDirectory('businesses/'.$businessId);

            return response()->json([
                'status' => 'success',
                'message' => t('business_delete_success'),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => t('business_unauthorized'),
        ]);
    }
}
