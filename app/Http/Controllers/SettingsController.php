<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        $userProfile = [
            'name' => $request->user()->name,
            'email' => $request->user()->email,
        ];

        return view('dashboard.settings', compact('userProfile'));
    }

    public function update(Request $request)
    {
        $business = $request->attributes->get('business');
        $user = $request->user();

        $businessName = trim((string) $request->input('business_name', ''));
        $name = trim((string) $request->input('name', ''));
        $notes = trim((string) $request->input('notes', ''));

        $currentPassword = (string) $request->input('current_password', '');
        $newPassword = (string) $request->input('new_password', '');
        $confirmPassword = (string) $request->input('confirm_password', '');

        if ($businessName === '' || $name === '') {
            return response()->json([
                'status' => 'error',
                'message' => t('business_name_user_required'),
            ]);
        }

        $updatePassword = false;

        if ($currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '') {
            if ($currentPassword === '') {
                return response()->json(['status' => 'error', 'message' => t('current_password_req')]);
            }

            if ($newPassword === '' || $confirmPassword === '') {
                return response()->json(['status' => 'error', 'message' => t('new_password_req')]);
            }

            if (mb_strlen($newPassword) !== 6) {
                return response()->json(['status' => 'error', 'message' => t('password_exact_6')]);
            }

            if ($newPassword !== $confirmPassword) {
                return response()->json(['status' => 'error', 'message' => t('password_mismatch')]);
            }

            if (! Hash::check($currentPassword, $user->password)) {
                return response()->json(['status' => 'error', 'message' => t('wrong_current_password')]);
            }

            $updatePassword = true;
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

            if ($file->getSize() > 2 * 1024 * 1024) {
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
            $user->update(['name' => $name]);

            $business->update([
                'business_name' => $businessName,
                'notes' => $notes,
            ]);

            if ($updatePassword) {
                $user->update(['password' => $newPassword]);
            }
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => t('settings_update_failed'),
            ]);
        }

        if ($file !== null && $photoExtension !== null) {
            $oldPhoto = $business->photo;
            $newFileName = 'logo_'.time().'.'.$photoExtension;

            $stored = Storage::disk('uploads')->putFileAs('businesses/'.$business->id, $file, $newFileName);
            if (! $stored) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('photo_save_failed'),
                ], 500);
            }

            $business->update(['photo' => $newFileName]);

            if (! empty($oldPhoto) && $oldPhoto !== $newFileName) {
                Storage::disk('uploads')->delete('businesses/'.$business->id.'/'.basename($oldPhoto));
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => t('settings_update_success'),
        ]);
    }
}
