<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    private function walk($array, Collection $org)
    {
        foreach ($array as $object) {
            $object->children = $this->walk($org->where('parent_id', '=', $object->id), $org);
        }

        return $array;
    }

    public function comments()
    {
        $comments = DB::table('comments')
            ->select('post_id', 'id', 'parent_id')
            ->where('post_id', '=', 2)
            ->get();

        $return = $this->walk($comments->where('parent_id', '=', null), $comments);

        return response()->json([
            'data' => $return,
        ], 200);
    }
}
