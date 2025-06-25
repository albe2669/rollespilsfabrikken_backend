<?php

namespace App\Http\Controllers\Helpers;

use App\Models\User;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidDateException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class Helpers
{
    public function convertDate($date)
    {
        $date = Str::replaceFirst('\r\n', '', $date);
        if ($date == null || $date == '' || $date == false) {
            return false;
        }
        $approvedFormats = [
            'Y-m-d H:i:s',
            'd-m-Y H:i:s',
            'y-m-d H:i:s',
            'd-m-y H:i:s',
            'd M Y H:i:s',
            'Y-m-d H:i',
            'd-m-Y H:i',
            'y-m-d H:i',
            'd-m-y H:i',
            'd M Y H:i',
            'Y-m-d',
            'd-m-Y',
            'y-m-d',
            'd-m-y',
            'd M Y',
        ];

        foreach ($approvedFormats as $format) {
            try {
                if ($carbon = Carbon::createFromFormat($format, $date)) {
                    return $carbon->format('Y-m-d H:i:s');
                }
            } catch (InvalidDateException|\InvalidArgumentException $e) {

            }

        }

        return false;
    }

    public function convertDateToCarbon($date)
    {
        $date = Str::replaceFirst('\r\n', '', $date);
        if ($date == null || $date == '' || $date == false) {
            return false;
        }
        $approvedFormats = [
            'Y-m-d H:i:s',
            'd-m-Y H:i:s',
            'y-m-d H:i:s',
            'd-m-y H:i:s',
            'd M Y H:i:s',
            'Y-m-d H:i',
            'd-m-Y H:i',
            'y-m-d H:i',
            'd-m-y H:i',
            'd M Y H:i',
            'Y-m-d',
            'd-m-Y',
            'y-m-d',
            'd-m-y',
            'd M Y',
        ];

        foreach ($approvedFormats as $format) {
            try {
                if ($carbon = Carbon::createFromFormat($format, $date)) {
                    return $carbon;
                }
            } catch (InvalidDateException|\InvalidArgumentException $e) {

            }

        }

        return false;
    }

    public function filterItems(FormRequest $request, $models)
    {
        $items = $request->query('items') ?? 5;
        $items = (int) $items;

        $order = 'desc';
        $current_page = $request->query('page') ?? 1;

        if ($request->query('sort') && $request->query('sort') == 'relevance') {
            $retrieved = $models->get()->sort(function ($a, $b) {
                if ($a->relevance == 0) {
                    return 1;
                }

                if ($a->relevance == $b->relevance) {
                    return 0;
                }

                return ($a->relevance < $b->relevance) ? -1 : 1;
            })->values();

            $sliced = $retrieved->slice(($current_page * $items) - $items, $items)->values();

            return new LengthAwarePaginator($sliced, count($retrieved), $items, $current_page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        if ($models == null || ! $models->first()) {
            return $models->paginate();
        }

        if ($request->query('before') && $date = $this->convertDate($request->query('before'))) {
            $models->where('created_at', '<', $date);
        }

        if ($request->query('after') && $date = $this->convertDate($request->query('after'))) {
            $models->where('created_at', '>', $date);
        }

        if ($request->query('user')) {
            $user = (new User)->whereUuid($request->query('user'))->firstOrFail();
            $models->where('user_id', '=', $user['id']);
        }

        if ($request->query('direction') && in_array($request->query('direction'), ['asc', 'desc'])) {
            $order = $request->query('direction');
        }

        if ($request->query('sort') && in_array($request->query('sort'), $models->first()->getTableColumns())) {
            $models->orderBy($request->query('sort'), $order);
        }

        $models = $models->paginate($items);
        $models->appends($request->except('page'))->links();

        return $models;
    }

    public function searchItems(FormRequest $request, $model, $wheres)
    {
        $sortBy = 'created_at';
        $order = 'asc';
        $items = 5;

        $models = $model::search($request->query('search'));

        if ($models == null || ! $models->first()) {
            return $models->paginate();
        }

        if ($request->query('items')) {
            $items = $request->query('items');
        }

        if ($request->query('direction') && in_array($request->query('direction'), ['asc', 'desc'])) {
            $order = $request->query('direction');
        }

        if ($request->query('sort') && in_array($request->query('sort'), $models->first()->getTableColumns())) {
            $models->orderBy($request->query('sort'), $order);
        }

        foreach ($wheres as $where) {
            $models->where($where['key'], $where['value']);
        }

        $models = $models->paginate($items);
        $models->appends($request->except('page'))->links();

        return $models;
    }
}
