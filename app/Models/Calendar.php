<?php

namespace App\Models;

use Dyrynda\Database\Support\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;
use Dyrynda\Database\Support\GeneratesUuid;

/**
 * Class Calendar
 *
 * @property int $id
 * @property string $uuid
 * @property int $obj_id
 * @property string $title
 * @property string $colour
 * @property string $description
 * @property string $allowed_resource
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class Calendar extends Model
{
    use Searchable, GeneratesUuid, HasFactory;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'title',
        'description',
        'colour'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function toSearchableArray() {
        $array = $this->toArray();

        $array = Arr::only($array, [
            'id',
            'title',
            'description'
        ]);

        return $array;
    }

    public function obj() {
        return $this->belongsTo('App\Models\Obj');
    }

    public function permissions() {
        return $this->obj()->first()->permissions;
    }

    public function events() {
        return $this->hasMany('App\Models\Event');
    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    public function canUseRooms() {
        if ($this->allowed_resource == 'all' || $this->allowed_resource == 'rooms') {
            return true;
        }

        return false;
    }

    public function canUseEquipment() {
        if ($this->allowed_resource == 'all' || $this->allowed_resource == 'equipment') {
            return true;
        }

        return false;
    }

    public function setAllowedResources(bool $rooms, bool $equipment) {
        if ($rooms && $equipment) {
            $this->allowed_resource = 'all';
        } else if ($rooms) {
            $this->allowed_resource = 'rooms';
        } else if ($equipment) {
            $this->allowed_resource = 'equipment';
        } else {
            $this->allowed_resource = 'none';
        }
    }
}
