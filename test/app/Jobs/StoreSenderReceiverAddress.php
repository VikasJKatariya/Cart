<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\AddressBook;

class StoreSenderReceiverAddress implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $input;
    protected $userid;
    public function __construct($userid, $input)
    {
        $this->input = $input;
        $this->userid = $userid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
            $book = AddressBook::where([['created_by',$this->userid],['type','Sender'],['address1',$this->input['address1_s']],['name',$this->input['first_name_s'].' '.$this->input['last_name_s']]])->first();
            if(!empty($book)){
                $addresssender = AddressBook::find($book->id);
                Log::info('AddressBook data update');
            }else{
                $addresssender =  new AddressBook;
                Log::info('AddressBook data add');
            }
            $addresssender->created_by =  $this->userid;
            $addresssender->type =  'Sender';
            $addresssender->name =  $this->input['first_name_s'].' '.$this->input['last_name_s'];
            $addresssender->email =  $this->input['email_s'];
            $addresssender->address1 =  $this->input['address1_s'];
            $addresssender->address2 =  $this->input['address2_s'];
            $addresssender->address3 =  $this->input['address3_s'];
            $addresssender->country_id =  $this->input['coutry_s'];
            $addresssender->city =  $this->input['city_s'];
            $addresssender->state =  $this->input['state_s'];
            $addresssender->postalcode =  $this->input['postal_code_s'];
            $addresssender->phone_number =  $this->input['phone_s'];
            $addresssender->company =  $this->input['company_s'];
            $addresssender->save();


            $bookr = AddressBook::where([['created_by',$this->userid],['type','Receiver'],['company',$this->input['company_r']],['address1',$this->input['address1_r']],['name',$this->input['full_name_r']]])->first();
            if(!empty($bookr)){
                $addressreceiver = AddressBook::find($bookr->id);
                Log::info('AddressBook data receiver update');
            }else{
                $addressreceiver =  new AddressBook;
                Log::info('AddressBook data receiver add');
            }

            $addressreceiver->created_by =  $this->userid;
            $addressreceiver->type =  'Receiver';
            $addressreceiver->name =  $this->input['full_name_r'];
            $addressreceiver->email =  $this->input['email_r'];
            $addressreceiver->address1 =  $this->input['address1_r'];
            $addressreceiver->address2 =  $this->input['address2_r'];
            $addressreceiver->address3 =  $this->input['address3_r'];
            $addressreceiver->country_id =  $this->input['country_r'];
            $addressreceiver->city =  $this->input['city_r'];
            $addressreceiver->state =  $this->input['state_r'];
            $addressreceiver->postalcode =  $this->input['postal_code_r'];
            $addressreceiver->phone_number =  $this->input['phone_r'];
            $addressreceiver->company =  $this->input['company_r'];
            $addressreceiver->save();



    }
}
