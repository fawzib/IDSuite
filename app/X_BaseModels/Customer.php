<?php
/**
 * Created by PhpStorm.
 * User: amac
 * Date: 6/5/17
 * Time: 2:54 AM
 */

namespace App;

use Illuminate\Support\Facades\Hash;

use App\Model as Model;

class Customer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contact'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token', 'password_hash', 'active'
    ];

    protected $guarded = [
        'updated_at', 'created_at'
    ];


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer';

    public $incrementing = false;

    /**
     * relationships
     */
    public function contact(){
        return $this->hasOne('App\Contact', 'mrge_id', 'contact_id');
    }


    /**
     * Customer constructor.
     * @param array $attributes
     */
    public function __construct($attributes = array())  {
        parent::__construct($attributes); // Eloquent
        // Your construct code.

        $this->active = 1;

        $this->save();

        return $this;
    }


    /**
     * @param $password string
     * set customer password_hash
     */
    public function setPassword($password){
        // TODO Password Validation
        try{
            $this->isActive();
            $this->password_hash = Hash::make($password);
            if($this->exists) {
                $this->save();
            }
        } catch(\Exception $e) {
            dump($e->getMessage());
        }
    }

}