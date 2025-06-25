<?php

namespace App\Http\Controllers\Helpers;

use App\Models\Comment;
use App\Models\CommentFile;
use App\Models\File;
use App\Models\Post;
use App\Models\PostFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileHelpers
{
    public const uploadPath = 'uploads\\';

    public static function savePostFile(UploadedFile $file, Post $post)
    {
        $dbFile = self::saveToDb($file);

        $postFile = (new PostFile);
        $postFile->file()->associate($dbFile);
        $postFile->post()->associate($post);
        $postFile->save();

        self::saveFile($file, $dbFile->saved_name);

        return $dbFile;
    }

    public static function saveCommentFile(UploadedFile $file, Comment $comment)
    {
        $dbFile = self::saveToDb($file);

        $commentFile = (new CommentFile);
        $commentFile->file()->associate($dbFile);
        $commentFile->comment()->associate($comment);
        $commentFile->save();

        self::saveFile($file, $dbFile->saved_name);

        return $dbFile;
    }

    private static function saveToDb(UploadedFile $file): File
    {
        $dbFile = (new File)->fill([
            'name' => $file->getClientOriginalName(),
            'saved_name' => 'tmp',
            'file_size' => $file->getSize(),
        ]);

        $dbFile->save();
        $dbFile = $dbFile->refresh();

        $dbFile->saved_name = $dbFile->uuid.'.dat';
        $dbFile->save();

        return $dbFile;
    }

    private static function saveFile(UploadedFile $file, string $name)
    {
        $fileContent = $file->get();
        $encryptedContent = encrypt($fileContent);

        Storage::put(self::uploadPath.$name, $encryptedContent);
    }
}
