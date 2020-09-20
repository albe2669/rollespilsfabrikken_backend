<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Post\File as AddFile;
use App\Http\Requests\API\Post\DownloadFile;
use App\Models\Comment;
use App\Models\File;
use App\Models\Forum;
use App\Models\Post;
use finfo;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

use App\Http\Resources\Comment\Comment as CommentResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Helpers\FileHelpers;

class CommentFileController extends Controller
{
    public function file(AddFile $request, Forum $forum, Post $post, Comment $comment) {
        if ($request->hasFile(('files'))) {
            foreach ($request->file('files') as $file) {
                $existingFile = $comment->files()->where('name', '=', $file->getClientOriginalName())->first();

                if ($existingFile) {
                    Storage::delete(FileHelpers::uploadPath .$existingFile->saved_name);
                    $existingFile->delete();
                }

                FileHelpers::saveCommentFile($file, $comment);
            }
        }

        if ($request->exists('file_deletions')) {
            foreach ($request->validated()['file_deletions'] as $fileDeletion) {
                $file = (new File)->whereUuid($fileDeletion)->first();

                if (!$file) {
                    continue;
                }

                Storage::delete(FileHelpers::uploadPath . $file->saved_name);
                $file->delete();
            }
        }

        return response()->json([
            'message' => 'success',
            'post' => new CommentResource($comment)
        ], 200);
    }

    public function getFile(DownloadFile $request, Forum $forum, Post $post, Comment $comment, File $file) {
        try {
            $contents = Storage::get(FileHelpers::uploadPath . $file->saved_name);
        } catch (FileNotFoundException $e) {
            return response()->json([
                'message' => 'Could not find file, it might have been deleted'
            ], 404);
        } catch (\Exception $e) {
            Log::error($e);

            return response()->json([
                'message' => 'Something went wrong. Check with the administrator'
            ], 500);
        }

        $decrypted = decrypt($contents);

        return response()->make($decrypted, 200, array(
            'Content-Type' => (new finfo(FILEINFO_MIME))->buffer($decrypted),
            'Content-Disposition' => 'attachment; filename="' . pathinfo($file->name, PATHINFO_BASENAME) . '"'
        ));
    }
}
