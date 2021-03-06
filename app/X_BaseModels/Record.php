<?php

namespace App;

use App\Model as Model;
use App\Enums\EnumDataSourceType;

/**
 * App\Record
 *
 * @property string $id
 * @property string $class_code
 * @property string|null $endpoint_id
 * @property string|null $type
 * @property string|null $timeperiod_id
 * @property string|null $remote_location_id
 * @property string|null $local_id
 * @property string|null $conf_id
 * @property string|null $local_name
 * @property string|null $local_number
 * @property string|null $remote_name
 * @property string|null $remote_number
 * @property string|null $dialed_digits
 * @property string|null $direction
 * @property string|null $protocol
 * @property int|null $active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Endpoint $endpoint
 * @property-read \App\Endpoint $entity
 * @property-read \App\Location $remote_location
 * @property-read \App\TimePeriod $timeperiod
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereClassCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereConfId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereDialedDigits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereEndpointId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereLocalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereLocalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereLocalNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereProtocol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereRemoteLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereRemoteName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereRemoteNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereTimeperiodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $entity_id
 * @property string|null $conference_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereConferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Record whereEntityId($value)
 */
class Record extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'row_hash',  'endpoint_id', 'entity_id', 'timeperiod_id', 'local_id', 'type', 'conference_id', 'remote_location_id', 'local_name', 'local_number', 'remote_name', 'remote_number', 'dialed_digits', 'direction', 'protocol'
    ];

    protected $guarded = [
        'created_at', 'updated_at'
    ];

    /**
     * Define table to be used with this model. It defaults and assumes table names will have an s added to the end.
     *for instance App\User table by default would be users
     */
    protected $table = "record";

    public $incrementing = false;

    protected $keyType = 'uuid';

    /**
     * relationships
     */
    public function endpoint(Endpoint $e = null)
    {
        if ($e !== null) {
            $this->endpoint_id = $e->id;
        }
        return $this->hasOne(Endpoint::class, 'id', 'endpoint_id');
    }


    public function entity(Entity $e = null)
    {

        if ($e !== null) {
            $this->entity_id = $e->id;
        }

        return $this->hasOne(Entity::class, 'id', 'entity_id');
    }

    public function timeperiod(TimePeriod $t = null)
    {
        if ($t !== null) {
            $this->timeperiod_id = $t->id;
        }
        return $this->hasOne(TimePeriod::class, 'id', 'timeperiod_id');
    }

    public function remote_location(Location $l = null)
    {
        if ($l !== null) {
            $this->remote_location_id = $l->id;
        }
        return $this->hasOne(Location::class, 'id', 'remote_location_id');
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


    /**
     *
     * constructor
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes); // Eloquent
        // Your construct code.

        return $this;

    }


    /**
     *  process
     */
    public function process()
    {
        $analytics = $this->endpoint->analytics;

        foreach ($analytics as $analytic) {

            $property_relationship = $analytic->analytic_object_relationship;
            $property_name = $analytic->analytic_object_property;

            switch ($analytic->analytic_type) {
                case 0:
                    if (is_null($property_relationship)) {
                        if (!is_null($property_name)) {
                            $analytic->value++;
                            $analytic->save();
                        }
                    } else {
                        if (!is_null($this->$property_relationship->$property_name)) {
                            $analytic->value++;
                            $analytic->save();
                        }
                    }
                    break;
                case 1:
                    if (is_null($property_relationship)) {
                        if (!is_null($property_name)) {
                            $analytic->value = $analytic->value + $this->$property_name;
                            $analytic->save();
                        }
                    } else {
                        if (!is_null($this->$property_relationship->$property_name)) {
                            $analytic->value = $analytic->value + $this->$property_relationship->$property_name;
                            $analytic->save();
                        }
                    }
                    break;
                case 2:

                    break;
                case 3:

                    break;
                case 4:

                    break;
                case 5:
                    if (!is_null($analytic->numerator) && !is_null($analytic->denominator)) {
                        $analytic->value = round($analytic->numerator->value / $analytic->denominator->value, 8);
                        $analytic->save();
                    }
                    break;
                case 6:
                    if (!is_null($property_name)) {
                        $stringvalsarray = array();

                        foreach ($this->endpoint->records as $record) {
                            $stringvalsarray[] = $record->$property_name;
                        }

                        $count = array_count_values($stringvalsarray);

                        arsort($count);

                        $keys = array_keys($count);

                        $analytic->stringvalue = $keys[0];
                        $analytic->value = $count[$keys[0]];
                        $analytic->save();
                    }
            }
        }
    }


    public static function recomputeAnalytics()
    {

        ini_set('memory_limit', '-1');

        Analytic::resetAnalytics();

        $records = Record::all();

        $records_length = count($records);

        unset($records);

        for ($count = 0; $count < $records_length; $count++) {
            $records = Record::all();

            $record = $records[0];

            if ($record->timeperiod->duration < 0) {
                continue;
            }

            $record->process();

            unset($records);
            unset($record);

        }
    }


    public static function getRecordCount()
    {
        $result = \DB::select("select COUNT(*) FROM record");

        $prop = 'COUNT(*)';

        return $result[0]->$prop;

    }

    public static function getEntityCountWithRecords()
    {
        $result = \DB::select("SELECT count(*) as count, record.entity_id FROM `record`  GROUP BY record.entity_id");

        $total_count = 0;

        foreach($result as $group){
            if($group->entity_id !== null){
                $total_count++;
            } else {
                // Log something here //
            }
        }

        return $total_count;

    }

    /**
     *
     * getDuration
     *
     * return record duration
     *
     * @return int|null
     */
    public function getDuration()
    {
        return $this->timeperiod->duration;
    }
}
