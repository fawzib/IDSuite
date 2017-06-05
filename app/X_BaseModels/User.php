<?php

namespace App;

use App\Contact;
use App\Model as Model;
use App\PersonName;
use Mockery\Exception;
use Illuminate\Support\Facades\Hash;

class User extends Model
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
        'remember_token', 'active'
    ];

    /**
     * Define table to be used with this model. It defaults and assumes table names will have an s added to the end.
     *for instance App\User table by default would be users
     */
    protected $table = "user";


    /**
     * relationships
     */
    public function contact(){
        return $this->hasOne('App\Contact', 'mrge_id');
    }

//    protected $guarded = [
//        'class_code'
//    ];

    /**
     * User constructor.
     * @param $contact /App/Contact
     * @param $password string
     * @param array $attributes
     */
    public function __construct(Contact $contact = null, $password, $attributes = array())  {
        parent::__construct($attributes); // Eloquent
        // Your construct code.

        $this->active = 1;

        if($contact !== null) {
            $this->contact = $contact->mrge_id;
        } else {
            $this->contact = null;
        }
        $this->setPassword($password);

        $this->save();

    }




    /**
     * @param $password string
     * set user password_hash
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

    /**
     * @param Email|string $email string
     * set user email
     */
    public function setEmail(Email $email){
        // TODO email validation
        try{
            $this->isActive();
            $this->email = $email;
            if($this->exists) {
                $this->save();
            }
        } catch(\Exception $e) {
            dump($e->getMessage());
        }

    }

    /**
     * @param PersonName $personName
     * @internal param string $first_name
     * @internal param string $last_name set user name* set user name
     */
    public function setPersonName(PersonName $personName){
        // TODO Name validation

        try{
            $this->isActive();
            $this->contact->personname = $personName;
            if($this->exists) {
                $this->save();
            }
        } catch (\Exception $e){
            dump($e->getMessage());
        }


    }

    /**
     * @return bool
     *
     * is Active
     * returns whether or not the user is active.
     */
    public function isActive(){
        if($this->active) {
            return true;
        } else {
            Throw new Exception('This user is not active. Therefore you cannot change the password', 409);
        }
    }


    /**
     * @return string
     *
     * getFullName
     * returns concatenated first and last name of user.
     */
    public function getFullName(){
        return $this->first_name . ' ' . $this->last_name;
    }

}
