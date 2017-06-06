<?php

namespace App;

use App\Model as Model;

class Location extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'coordinates', 'address', 'city', 'state', 'zipcode'
    ];


    protected $guarded = [
        'created_at', 'updated_at'
    ];

    /**
     * Define table to be used with this model. It defaults and assumes table names will have an s added to the end.
     *for instance App\User table by default would be users
     */
    protected $table = "location";

    public $incrementing = false;

    /**
     * relationships
     */
    public function coordinate(){
        return $this->hasOne('App\Coordinate', 'mrge_id', 'coordinate_id');
    }



    /**
     * Location constructor.
     * @param array $attributes
     */
    public function __construct($attributes = array())  {
        parent::__construct($attributes); // Eloquent
        // Your construct code.

        $this->save();

        return $this;

    }


}
