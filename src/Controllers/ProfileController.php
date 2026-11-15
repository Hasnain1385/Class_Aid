<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\User;
use App\Models\Subscription;
use App\Models\SchoolClass;
use App\Models\Student;

class ProfileController
{
    public function show(Request $request): void
    {
        $userId = (int) Session::userId();
        $user = User::findById($userId);

        if (!$user) {
            Session::flash('error', 'User account not found.');
            Response::redirect('/login');
            return;
        }

        $subscription = Subscription::getActiveForUser($userId);
        $classes = SchoolClass::getAllByTeacher($userId);
        $studentsCount = count(Student::getAll($userId));

        $layout = Session::isSuperAdmin() ? 'admin' : 'main';

        View::render('profile.index', [
            'title' => 'My Profile & Account Settings',
            'user' => $user,
            'subscription' => $subscription,
            'classesCount' => count($classes),
            'studentsCount' => $studentsCount,
        ], $layout);
    }

    public function update(Request $request): void
    {
        $userId = (int) Session::userId();
        $user = User::findById($userId);

        if (!$user) {
            Session::flash('error', 'User account not found.');
            Response::redirect('/login');
            return;
        }

        $name = trim((string) $request->input('name'));
        $email = strtolower(trim((string) $request->input('email')));
        $schoolName = trim((string) $request->input('school_name', ''));
        $phone = trim((string) $request->input('phone', ''));

        if (empty($name) || empty($email)) {
            Session::flash('error', 'Name and Email are required fields.');
            Response::redirect('/profile');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid email address.');
            Response::redirect('/profile');
            return;
        }

        // Check if email is already taken by another account
        $existing = User::findByEmail($email);
        if ($existing && (int) $existing['id'] !== $userId) {
            Session::flash('error', "The email '{$email}' is already registered to another user.");
            Response::redirect('/profile');
            return;
        }

        $updateData = [
            'name' => $name,
            'email' => $email,
            'school_name' => $schoolName,
            'phone' => !empty($phone) ? $phone : null,
        ];

        // Process profile avatar upload if present
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $uploadResult = self::processAvatarUpload($file, $userId, $user['avatar'] ?? null);
            if ($uploadResult['success']) {
                $updateData['avatar'] = $uploadResult['path'];
            } else {
                Session::flash('error', $uploadResult['error']);
                Response::redirect('/profile');
                return;
            }
        }

        User::update($userId, $updateData);
        Session::refreshAuthUser();

        Session::flash('success', 'Your profile details have been updated successfully.');
        Response::redirect('/profile');
    }

    public function removeAvatar(Request $request): void
    {
        $userId = (int) Session::userId();
        $user = User::findById($userId);

        if ($user && !empty($user['avatar'])) {
            $oldFilePath = dirname(__DIR__, 2) . '/public' . $user['avatar'];
            if (file_exists($oldFilePath)) {
                @unlink($oldFilePath);
            }
            User::update($userId, ['avatar' => null]);
            Session::refreshAuthUser();
            Session::flash('success', 'Profile photo removed.');
        }

        Response::redirect('/profile');
    }

    public function updatePassword(Request $request): void
    {
        $userId = (int) Session::userId();
        $currentPassword = (string) $request->input('current_password');
        $newPassword = (string) $request->input('new_password');
        $confirmPassword = (string) $request->input('new_password_confirmation');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            Session::flash('error', 'Please complete all password fields.');
            Response::redirect('/profile');
            return;
        }

        $currentHash = User::getPasswordHash($userId);
        if (!$currentHash || !User::verifyPassword($currentPassword, $currentHash)) {
            Session::flash('error', 'Current password is incorrect. Please try again.');
            Response::redirect('/profile');
            return;
        }

        if (strlen($newPassword) < 6) {
            Session::flash('error', 'New password must be at least 6 characters long.');
            Response::redirect('/profile');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'New password and confirmation do not match.');
            Response::redirect('/profile');
            return;
        }

        User::update($userId, ['password' => $newPassword]);
        Session::flash('success', 'Password updated successfully.');
        Response::redirect('/profile');
    }

    private static function processAvatarUpload(array $file, int $userId, ?string $currentAvatar): array
    {
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Photo size must be less than 5MB.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowedMimes, true)) {
            return ['success' => false, 'error' => 'Only JPG, PNG, WEBP, or GIF image files are allowed.'];
        }

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/avatars';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Delete old avatar if present
        if ($currentAvatar) {
            $oldPath = dirname(__DIR__, 2) . '/public' . $currentAvatar;
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $filename = 'avatar_' . $userId . '_' . time() . '.png';
        $destinationPath = $uploadDir . '/' . $filename;

        // Resize and optimize avatar to 400x400 square using GD
        try {
            $sourceImage = match ($mime) {
                'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
                'image/png' => @imagecreatefrompng($file['tmp_name']),
                'image/webp' => @imagecreatefromwebp($file['tmp_name']),
                'image/gif' => @imagecreatefromgif($file['tmp_name']),
                default => null,
            };

            if ($sourceImage) {
                $origWidth = imagesx($sourceImage);
                $origHeight = imagesy($sourceImage);

                // Center crop square
                $cropSize = min($origWidth, $origHeight);
                $srcX = (int) (($origWidth - $cropSize) / 2);
                $srcY = (int) (($origHeight - $cropSize) / 2);

                $targetSize = 400;
                $targetImage = imagecreatetruecolor($targetSize, $targetSize);

                // Preserve transparency
                imagealphablending($targetImage, false);
                imagesavealpha($targetImage, true);
                $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
                imagefilledrectangle($targetImage, 0, 0, $targetSize, $targetSize, $transparent);

                imagecopyresampled($targetImage, $sourceImage, 0, 0, $srcX, $srcY, $targetSize, $targetSize, $cropSize, $cropSize);

                imagepng($targetImage, $destinationPath, 8);
                imagedestroy($sourceImage);
                imagedestroy($targetImage);
            } else {
                move_uploaded_file($file['tmp_name'], $destinationPath);
            }

            return [
                'success' => true,
                'path' => '/uploads/avatars/' . $filename,
            ];
        } catch (\Throwable $e) {
            // Fallback to simple move
            move_uploaded_file($file['tmp_name'], $destinationPath);
            return [
                'success' => true,
                'path' => '/uploads/avatars/' . $filename,
            ];
        }
    }
}
