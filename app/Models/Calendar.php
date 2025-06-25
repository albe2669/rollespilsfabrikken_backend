<?php

namespace App\Models;

use Dyrynda\Database\Support\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

class Calendar extends Model
{
    use GeneratesUuid;
    use HasFactory;
    use Searchable;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'title',
        'description',
        'colour',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        $array = Arr::only($array, [
            'id',
            'title',
            'description',
        ]);

        return $array;
    }

    public function obj()
    {
        return $this->belongsTo(Obj::class);
    }

    public function permissions()
    {
        return $this->obj()->first()->permissions;
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    public function canUseRooms()
    {
        if ($this->allowed_resource == 'all' || $this->allowed_resource == 'rooms') {
            return true;
        }

        return false;
    }

    public function canUseEquipment()
    {
        if ($this->allowed_resource == 'all' || $this->allowed_resource == 'equipment') {
            return true;
        }

        return false;
    }

    public function setAllowedResources(bool $rooms, bool $equipment)
    {
        if ($rooms && $equipment) {
            $this->allowed_resource = 'all';
        } elseif ($rooms) {
            $this->allowed_resource = 'rooms';
        } elseif ($equipment) {
            $this->allowed_resource = 'equipment';
        } else {
            $this->allowed_resource = 'none';
        }
    }
}
