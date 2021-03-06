<?php

namespace App;

use App\Enums\EnumDataSourceType;
use App\Enums\EnumStatusType;
use Carbon\Carbon;
use Exception;

use App\Model as Model;

/**
 * App\Endpoint
 *
 * @property string $id
 * @property string|null $entity_id
 * @property string|null $model_id
 * @property string|null $proxy_id
 * @property string|null $location_id
 * @property int|null $type
 * @property string $class_code
 * @property string|null $name
 * @property string|null $ipaddress
 * @property string|null $sync_time
 * @property string|null $reboot_time
 * @property string|null $last_reboot
 * @property string|null $status_at
 * @property int|null $status
 * @property int|null $active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Analytic[] $analytics
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\DynamicEnumValue[] $devs
 * @property-read \App\EndpointModel $endpointmodel
 * @property-read \App\Entity $entity
 * @property-read \App\Location $location
 * @property-read \App\Proxy $proxy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Record[] $records
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereClassCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereIpaddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereLastReboot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereProxyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereRebootTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereStatusAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $e_many
 * @property string|null $links
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereEMany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Endpoint whereLinks($value)
 */
class Endpoint extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entity_id', 'proxy_id', 'location_id', 'type', 'e_many', 'ipaddress'
    ];


    protected $guarded = [
        'updated_at', 'created_at'
    ];

    /**
     * Define table to be used with this model. It defaults and assumes table names will have an s added to the end.
     *for instance App\User table by default would be users
     */
    protected $table = "endpoint";

    public $incrementing = false;

    protected $keyType = 'uuid';

    /**
     *
     * relationships
     *
     */
    public function endpointmodel(EndpointModel $e = null)
    {

        if ($e !== null) {
            $this->model_id = $e->id;
        }

        return $this->hasOne(EndpointModel::class, 'id', 'model_id');
    }

    public function proxy(Proxy $p = null)
    {

        if ($p !== null) {
            $this->proxy_id = $p->id;
        }

        return $this->hasOne(Proxy::class, 'id', 'proxy_id');
    }

    public function location(Location $l = null)
    {
        if ($l !== null) {
            $this->location_id = $l->id;
        }

        return $this->hasOne(Location::class, 'id', 'location_id');
    }

    public function entity(Entity $e = null)
    {

        if ($e !== null) {
            $this->entity_id = $e->id;
        }

        return $this->hasOne(Entity::class, 'id', 'entity_id');
    }

    public function records()
    {
        return $this->hasMany(Record::class, 'endpoint_id', 'id');
    }

    public function analytics()
    {
        return $this->hasMany(Analytic::class, 'endpoint_id', 'id');
    }

    public function references(DynamicEnumValue $dynamic_enum_value = null)
    {

        $references = $this->morphToMany(DynamicEnumValue::class, 'object', 'object_dev')->withTimestamps();

        if ($dynamic_enum_value !== null) {
            $references->attach($dynamic_enum_value, ['dynamic_enum_id' => $dynamic_enum_value->definition->id, 'value_type' => $dynamic_enum_value->value_type]);
        }

        $ref_array = array();

        foreach ($references->get() as $reference) {
            $ref_array[EnumDataSourceType::getValueByKey($reference->value_type)] = $reference->value;
        }

        return $ref_array;
    }

    public function devs(DynamicEnumValue $dynamic_enum_value = null, $getAllDev = false)
    {
        return $this->morphToMany(DynamicEnumValue::class, 'object', 'object_dev')->withTimestamps();
    }

    /**
     * Endpoint constructor.
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes); // Eloquent
        // Your construct code.

        return $this;
    }


    /**
     *
     * is Active
     * returns whether or not the user is active.
     * @throws Exception
     *  @return bool is Active
     */
    public function isActive()
    {
        if ($this->active) {
            return true;
        } else {
            Throw new Exception('This user is not active. Therefore you cannot change the password', 409);
        }
    }


    /**
     *
     * getByName
     *
     * @param $name
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public static function getByName($name)
    {
        return (new Endpoint)->where('name', $name)->first();
    }

    /**
     *
     * getByCol
     *
     * @param $col
     * @param $value
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public static function getByCol($col, $value)
    {
        return (new Endpoint)->where($col, $value)->first();
    }

    /**
     *
     * hasReference
     *
     * @param $reference_key
     * @return bool
     */
    public function hasReference($reference_key)
    {
        $result = array_key_exists($reference_key, $this->references());
        return $result;
    }

    /**
     *
     * updateDev
     *
     * @param $key
     * @param $value
     * @param $de
     * @return bool
     */
    public function updateDev($key, $value, $de)
    {

        if ($this->hasReference($key)) {
            foreach ($this->devs as $dev) {
                if ($dev->dynamicenum_id === $de->id) {
                    if ($de->values[$dev->value_type] === $key) {
                        if ($dev->value === $value) {
                            return $dev;
                        } else {
                            $dev->value = $value;
                            $dev->save();
                            return $dev;
                        }
                    } else {
                        continue;
                    }
                }
            }
        }
        return false;
    }


    /**
     *
     * getType
     *
     * return the value type of endpoint->type
     *
     * @return mixed
     */
    public function getType()
    {

        return EnumDataSourceType::getValueByKey($this->type);

    }


    /**
     *
     * getCallCount
     *
     * return the count of call records associated to this endpoint
     *
     * @return int
     */
    public function getCallCount()
    {
        $count = 'count(*)';
        return \DB::select("select count(*) from record where record.endpoint_id = '$this->id'")[0]->$count;
    }


    /**
     *
     * getAverageCallDuration
     *
     * return the count of call records associated to this endpoint
     *
     * @return array
     */
    public function getAverageCallDuration()
    {
        return \DB::select("select AVG(timeperiod.duration) as timeperiod_duration from record LEFT join timeperiod on record.timeperiod_id=timeperiod.id where record.endpoint_id = '$this->id'")[0]->timeperiod_duration;
    }


    /**
     *
     * getEntityName
     *
     * return Endpoint Entity's name
     *
     * @return null|string
     */
    public function getEntityName()
    {

        return $this->entity->contact->name->name;

    }


    /**
     *
     * getProxyName
     *
     * return Endpoint Entity's name
     *
     * @return null|string
     */
    public function getProxyName()
    {
        if(isset($this->proxy->name)){
            return $this->proxy->name;
        } else {
            return false;
        }

    }

    /**
     *
     * getStatus
     *
     * get the status value from key
     *
     * @return mixed
     */
    public function getStatus()
    {
        return EnumStatusType::getValueByKey($this->status);
    }


    /**
     * @param Carbon $start_date
     * @return mixed
     */
    public function getRecordsByDate(Carbon $start_date){

        $start_date = $start_date->toDateString();

        /**
         * @var Carbon $start_date
         */
        return \DB::select("select record.*, timeperiod.start as timeperiod_start, timeperiod.end as timeperiod_end, timeperiod.duration as timeperiod_duration from record LEFT join timeperiod on record.timeperiod_id=timeperiod.id where timeperiod.start > '$start_date' AND record.endpoint_id = '$this->id'");

    }


    public function getRawRecords(){

        return \DB::select("select record.*, timeperiod.start as timeperiod_start, timeperiod.end as timeperiod_end, timeperiod.duration as timeperiod_duration from record LEFT join timeperiod on record.timeperiod_id=timeperiod.id where record.endpoint_id = '$this->id'");

    }



    public function getMostRecentRecordDate(){
        $record = \DB::select("
                  SELECT record.*, timeperiod.start as timeperiod_start 
                  FROM record 
                  LEFT Join timeperiod on record.timeperiod_id = timeperiod.id
                  WHERE record.endpoint_id = '$this->id'
                  ORDER BY timeperiod_start DESC
                  LIMIT 1
                  ");

        if(count($record) === 0){
            return 0;
        } else {
            return $record[0]->timeperiod_start;
        }
    }


}
