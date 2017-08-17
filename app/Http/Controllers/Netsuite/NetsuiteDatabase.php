<?php

namespace App\Http\Controllers\Netsuite;

use App\DynamicEnumValue;
use App\Enums\EnumDataSourceType;
use App\Http\Controllers\Helper\AddressExtractor;
use App\Http\Controllers\Helper\Database;
use App\Http\Controllers\Helper\Funcs;
use Illuminate\Support\Facades\Log;

use App\Analytic;
use App\Http\Controllers\Helper\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NetSuite\NetSuiteService;

/*
 * Netsuite operators: contains, doesNotContain, doesNotStartWith, empty, hasKeywords, is, isNot, notEmpty, within, noneOf
 * */
class NetsuiteDatabase extends \App\Http\Controllers\Controller
{


    /* grab all sm sites via saved search id */
    public static function AddUpdateSMSites() {



        $service = new NetsuiteController();
        $result = $service->savedSearch(1422, 100);


        echo ".";
        $total_pages = $result->totalPages;
        $all_records = $result->searchRowList->searchRow;
        $search_id =    $result->searchId;

        // if we have more than one page
          goto skip_pages;
        for($page_counter = 0; $page_counter < $total_pages; $page_counter++) {

            $next_page_to_fetch = $page_counter+2;

            $search_result = [];
            $retry = 10;
            do{
                try{
                    $search_result = $service->getPage($search_id, $next_page_to_fetch);
                    $retry = 0; // it was able to continue
                }catch (\Exception $e) {
                    echo "%"; // retry
                    sleep(2); // give me a second will you
                    $retry--;
                }

            }while($retry != 0);

            if($search_result && is_array($search_result->searchRowList->searchRow)){
                $all_records = array_merge($all_records, $search_result->searchRowList->searchRow);
                echo "."; // merged
            }
            else
                echo "_"; // skipped
            //
            //   dd($all_records);
        }
           skip_pages:

        $insert_counter = 0;

        // grab customer internal ids
        $local_netsuite_employee = Database::getAllCustomers();
        $db_customers_internal_ids = array_column($local_netsuite_employee, 'value');
        // grab sm site internal ids
        $local_sm_records= Database::getAllSMSites();
        $db_sm_internal_ids = array_column($local_sm_records, 'value');

     //   dump("db_customers_internal_ids", $db_customers_internal_ids);
      //  dump("db_sm_internal_ids", $db_sm_internal_ids);





        foreach ($all_records as $record) {
            $insert_counter++;
                echo $insert_counter . "/" . count($all_records) . "...";


                // note to self: avoid saved searches
                $sm_internal_id = $record->basic->id[0]->searchValue;



            $name = $record->basic->name[0]->searchValue;

                $city = null; $address = null;$zip_code= null;  $full_address = null;$customer_internal_id = null;
                foreach ($record->basic->customFieldList->customField as $address_fields) {

                    if($address_fields->scriptId == 'custrecord_esm_site_city')
                        $city = $address_fields->searchValue;
                    elseif($address_fields->scriptId == 'custrecord_esm_site_addr1')
                        $address = $address_fields->searchValue;
                    elseif($address_fields->scriptId == 'custrecord_esm_site_zip')
                        $zip_code = $address_fields->searchValue;
                    elseif($address_fields->scriptId == 'custrecord_esm_site_address_text')
                        $full_address = $address_fields->searchValue;
                    elseif($address_fields->scriptId == 'custrecord_esm_site_parent')
                        $customer_internal_id = $address_fields->searchValue->internalId;
                }

            if($customer_internal_id == null){
                echo '!';
                continue; // something messed up, skip this record

            }


                // $full_address = trim(preg_replace('/\s\s+/', ', ', $full_address));
            $full_address = str_replace(["(",")","[","]",":",";","{","}"], " ", $full_address);

            $full_address_extracted = new AddressExtractor($full_address);
                $full_address_extracted_output = $full_address_extracted->getOutput();
                $country = \Jasny\ISO\Countries::getCode($full_address_extracted_output['country']);
                if(!$country) $country = null;
                $state = $full_address_extracted_output['state'];
                if(!$state) $state = null;

                /*now that out of the way lets start the process */

                // make sure that customer exist and sm site record does not

                if(array_search($customer_internal_id, $db_customers_internal_ids) !== false
                    && array_search($sm_internal_id, $db_sm_internal_ids) === false) {

                    //add to db

                    $dev = DynamicEnumValue::getByValue($customer_internal_id, EnumDataSourceType::getKeyByValue("netsuite"));
                    //dd($dev);
                    //dd("customer_internal_id ",$customer_internal_id,' ', "sm_internal_id ", $sm_internal_id);


                    if($dev) {

                        $location = Process::processLocation($address, $city, $state, $zip_code, $country); // will create coordiante object as well
                        $name = Process::processEntityName($name);
                        $phones = Process::processPhone();
                        $email = Process::processEmail();
                        $entity_contact = Process::processEntityContact($location, $name, $email, $phones);

                        $entity = $dev->referable(\App\Entity::class);
                        $entity->sites()->save($entity_contact);
                        $entity->save();
                        echo "+";
                    }else {
                        echo "?";

                        // couldn't find entity for customer id
                    }



                }else {
                    // todo: update record
                    echo "-";


                }

                $insert_counter++;



        }
        echo "**done**";
    }

    /* grab all customers from netsuite and add or update current customer in database */
    public static function AddUpdateAllCustomers() {

        // grab db customers (entities)


        /*
         * todo: create entity_user table connection to connect multiple of entities to multiple user(s)
         *
         * */

        $query = "SELECT * FROM entity WHERE entity.active =1";


        dd("all local customers fetched");

        $service = new NetsuiteController();
        $result = $service->getAllCustomers(100);
        $total_pages = $result->totalPages;
        $all_records = $result->recordList->record;
        $search_id =    $result->searchId;

        // if we have more than one page
        for($page_counter = 0; $page_counter < $total_pages; $page_counter++) {
            $next_page_to_fetch = $page_counter+2;
            $search_result = $service->getPage($search_id, $next_page_to_fetch);
            $all_records = array_merge($all_records, $search_result->recordList->record);



         //   dd($all_records);
        }


        // now we have all customer records, lets start processing them
        foreach ($all_records as $record) {
            echo $insert_counter."/".count($all_records)."...";
            $insert_counter ++;
            // match customers by internal id
            if(array_search($record->internalId, $internal_ids) === false) {

                // add customer
                $phone_numbers = array(
                    'default' => $record->phone,
                    'fax' => $record->fax

                );

                $location = Process::processLocation(); // will create coordiante object as well
                $name = Process::processEntityName($record->companyName);
                $phones = Process::processPhone($phone_numbers); // returns an array of PhoneNumber class
                $email = Process::processEmail($record->email);
                $entity_contact = Process::processEntityContact($location, $name, $email, $phones);

                $note = Process::processNote($record->comments);
                $dynamic_enum = \App\DynamicEnum::getByName('reference_key'); // grabbing the dynamic enum object that has name reference_key
                $dynamic_enum_value = Process::processDev($record->internalId, 'netsuite', $dynamic_enum);
                $entity = Process::processEntity($entity_contact, $dynamic_enum_value, $note);
                echo "+";

                // fix parent
                if($record->parent !== null) {
                    $parent_netsuite_id = $record->parent->internalId;
                    $dev = DynamicEnumValue::getByValue($parent_netsuite_id, EnumDataSourceType::getKeyByValue("netsuite"));

                    if(!$dev) {
                        $parent_entity = $dev->referable(\App\Entity::class);
                        $parent_entity->children($entity)->save($entity);
                        $parent_entity->save();
                    }
                }


                // add person contacts
                $contactRolesList = $record->contactRolesList;
                if($contactRolesList !== null) {
                    //add contacts

                    $contactRoles = $contactRolesList->contactRoles;
                    foreach ($contactRoles as $contactRole) {

                        $internal_id = $contactRole->contact->internalId;
                        $name = $contactRole->contact->name;
                        $name = Funcs::split_name($name);
                        $name = Process::processName($name[0],null, $name[1], null);

                        $location = Process::processLocation();
                        $email = Process::processEmail($contactRole->email);

                        Process::processContact( $location, $name, $email,  [], $entity);
                        echo "+";
                    }
                }
                // end of adding contacts to customer






    }

    /* grab all employees from netsuite and add or update current employee list in database in one go */
    public static function AddUpdateAllEmployees() {

        // grab local employee list
        $query = "select user.id as user_id ,  dynamic_enum_value.id as dynamic_enum_value_id, dynamic_enum_value.value, dynamic_enum_value.value_type
        from user
        LEFT  JOIN object_dev ON user.id = object_dev.object_id
        LEFT  JOIN dynamic_enum_value ON object_dev.dynamic_enum_value_id = dynamic_enum_value.id
        WHERE dynamic_enum_value.value_type = 0 AND dynamic_enum_value.value IS NOT NULL";

        $local_netsuite_employee = DB::select($query);


        // get netsuite employee list
        $service = new NetsuiteController();
        $netsuite_employee_list = $service->getEmployeeList();

        $records = $netsuite_employee_list->recordList->record;


        $internal_ids = array_column($local_netsuite_employee, 'value');


        $users_created = array();
        foreach ($records as $record) {


            if(array_search($record->internalId, $internal_ids) === false) {
                //insert to database if employee not found


                $user_email = \App\User::getUserByEmail($record->email);

                // just an extra check for the email
                if (!is_object($user_email)) {

                    $email = Process::processEmail($record->email);

                    if($email) {
                    $name = Process::processName($record->firstName,$record->middleName,$record->lastName, $record->title);

                    $phone_numbers = array(
                        'default' => $record->phone,
                        'fax' => $record->fax,
                        'officePhone' => $record->officePhone,
                        'homePhone' => $record->homePhone,

                    );

                    $location = Process::processLocation(); // will create coordiante object as well



                    $phones = Process::processPhone($phone_numbers); // returns an array of PhoneNumber class


                    $dynamic_enum = \App\DynamicEnum::getByName('reference_key'); // grabbing the dynamic enum object that has name reference_key

                    $dynamic_enum_value = Process::processDev($record->internalId, 'netsuite', $dynamic_enum);

                    $person_contact = Process::processContact($location, $name, $email, $phones);

                    $user = Process::processUser($person_contact, $dynamic_enum_value);


                    $users_created[] = $user;
                    }else{
                        // invalid email! what netsuite. seriously?
                        Log::error("Invalid email", array("record" =>$record));

                    }
                    /*
                     *
                     *  $note = Process::processNote();

                    $company_name = $c[0];

                    $iscolon_index = strpos($company_name, ':');

                    if(!$iscolon_index){
                        $entity = Process::processEntity($c, $entity_contact, $note);
                    } else {

                        $entity = Process::processEntity($c, $entity_contact, $note);

                        $company_name = substr($company_name, 0, $iscolon_index);

                        $parent_entity = \App\Entity::getByName($company_name);

                        if($parent_entity === null){
                        } else {

                            $parent_entity->children($entity)->save($entity);

                            $parent_entity->save();
                        }

                    }
    */


                    //end of insert


                }else{
                    // ns record exist and email also exist.. update proccess

                    echo 'ns record exist and email also exist.. update proccess';
                }


            }else {
                // ns record found in database
                echo 'ns record found in database';
            }



        }
        echo "users created";
        dd($users_created);

    }

    /* grab customers in db and select last x not modified and grab and update sales team */
    public static function AddUpdateSalesTeam($limit = 100) {


        $dev = new DynamicEnumValue();

        $dev->value = "SC_12312";

        $de = DynamicEnum::getByName('reference_key');

        $dev->value_type = \App\Enums\EnumDataSourceType::getKeyByValue('netsuite');

        $query = "SELECT ";

        $query_result = DB::select($query);

        $service = new NetsuiteController();

        $service->getCustomer();
    }





}