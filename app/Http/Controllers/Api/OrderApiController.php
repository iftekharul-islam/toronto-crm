<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Helpers\CrmHelper;
use App\Models\ContactInfo;
use App\Models\BorrowerInfo;
use App\Models\PropertyInfo;
use Illuminate\Http\Request;
use App\Models\AppraisalDetail;
use App\Models\ProvidedService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderApiController extends Controller
{
    use CrmHelper;
    
    public function store(Request $get) {
        $step = $get->step1;
        $step2 = $get->step2;
        $company = $get->company;

        $providedData = $get->providedData;

        $errorChecking = $this->getErrorMessage($get);
        $error = $errorChecking['error'];
        $errorMessage = $errorChecking['message'];
        if ($error == true) {
            return response()->json(['error' => $error, 'messages' => $errorMessage]);
        }
        
        $orderProccess = DB::transaction( function() use ($step, $step2, $company, $get) {
            $amcClient = $step['amcClient'];
            $appraiserName = $step['appraiserName'];
            
            try {
                $dueDate = Carbon::parse($step['dueDate'])->format('Y-m-d');
                $receiveDate = Carbon::parse($step['receiveDate'])->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json(['error' => false, 'messages' => ['Please check received & due dates']]);
            }
            
            $systemOrder = $step['systemOrder'];
            $clientOrderNo = $step['clientOrderNo'];
            $lender = $step['lender'];


            $user = User::find($get->user_id);
            $submitType = $get->type;
            $orderId = null;
            if ($submitType) {
                $orderId = $get->order['id'];
            }
            
            if ($orderId == null) {
                $order = new Order;
                $order->created_at = Carbon::now();
                $order->status = 1;
            } else {    
                $order = Order::find($orderId);
                if (!$order) {
                    return response()->json(['error' => true, 'messages' => ['Order information not found']]);
                }
                $order->updated_at = Carbon::now();
            }

            // creating orders
            $order->amc_id = $amcClient;
            $order->lender_id = $lender;
            $order->company_id = $company['id'];
            $order->created_by = $user->id;
            $order->received_date = $receiveDate;
            $order->due_date = $dueDate;
            $order->client_order_no = $clientOrderNo;
            $order->system_order_no = $systemOrder ?? $this->getSystemOrderNumber();
            $order->save();


            $fhaCaseNo = $step['fhaCaseNo'];
            $loanNo = $step['loanNo'];
            $technologyFee = $step['technologyFee'];
            $loanType = $step['loanType'];

            // Create appraisal details

            if ($orderId == null) {
                $apprlDetails = new AppraisalDetail;
                $apprlDetails->created_at = Carbon::now();
            } else {    
                $apprlDetails = AppraisalDetail::where('order_id', $order->id)->first();
                if (!$apprlDetails) {
                    return response()->json(['error' => true, 'messages' => ['Order information not found']]);
                }
                $apprlDetails->updated_at = Carbon::now();
            }

            $apprlDetails->appraiser_id = $appraiserName;
            $apprlDetails->order_id = $order->id;
            $apprlDetails->loan_no = $loanNo;
            $apprlDetails->loan_type = $loanType;
            $apprlDetails->technology_fee = $technologyFee;
            $apprlDetails->fha_case_no = $fhaCaseNo;
            $apprlDetails->save();

            // Create Provider Types
            $fee = $get->providedData['extra'];
            $note = $step['note'];


            if ($orderId == null) {
                $providerType = new ProvidedService;
                $providerType->created_at = Carbon::now();
            } else {    
                $providerType = ProvidedService::where('order_id', $order->id)->first();
                if (!$providerType) {
                    return response()->json(['error' => true, 'messages' => ['Order information not found']]);
                }
                $providerType->updated_at = Carbon::now();
            }
            
            $providerType->order_id = $order->id;
            $providerType->appraiser_type_fee = json_encode($fee);
            $providerType->note = $note;
            $providerType->total_fee = collect($fee)->sum('fee');
            $providerType->save();

            $searchAddress = $step['searchAddress'];
            $state = $step['state'];
            $street = $step['street'];
            $unitNo = $step['unitNo'];
            $zipcode = $step['zipcode'];
            $city = $step['city'];
            $country = $step['country'];

            // create property info

            if ($orderId == null) {
                $propertyInfo = new PropertyInfo;
                $propertyInfo->created_at = Carbon::now();
            } else {    
                $propertyInfo = PropertyInfo::where('order_id', $order->id)->first();
                if (!$propertyInfo) {
                    return response()->json(['error' => true, 'messages' => ['Order information not found']]);
                }
                $propertyInfo->updated_at = Carbon::now();
            }

            $propertyInfo->order_id = $order->id;
            $propertyInfo->search_address = $searchAddress;
            $propertyInfo->street_name = $street;
            $propertyInfo->city_name = $city;
            $propertyInfo->state_name = $state;
            $propertyInfo->zip = $zipcode;
            $propertyInfo->country = $country;
            $propertyInfo->unit_no = $unitNo;
            $propertyInfo->latitude = "1";
            $propertyInfo->longitude = "1";
            $propertyInfo->save();


            $borrower_contact = $step2['borrower_contact'];
            $borrower_contact_s = $step2['borrower_contact_s'];
            $borrower_email = $step2['borrower_email'];
            $borrower_email_s = $step2['borrower_email_s'];
            $borrower_name = $step2['borrower_name'];
            $co_borrower_name = $step2['co_borrower_name'];


            // create borrower type
            if ($orderId == null) {
                $borrowerType = new BorrowerInfo;
                $borrowerType->created_at = Carbon::now();
            } else {    
                $borrowerType = BorrowerInfo::where('order_id', $order->id)->first();
                if (!$borrowerType) {
                    return response()->json(['error' => true, 'messages' => ['Order information not found']]);
                }
                $borrowerType->updated_at = Carbon::now();
            }
            $borrowerType->order_id = $order->id; 
            $borrowerType->borrower_name = $borrower_name;
            $borrowerType->co_borrower_name = $co_borrower_name;
            $borrowerType->contact_email = json_encode([
                'email' => $borrower_email_s,
                'phone' => $borrower_contact_s
            ]);
            $borrowerType->save();


            // Contact Info Saved
            $contactSame = $step2['contactSame'];
            $contact_info = $step2['contact_info'];
            $contact_number = $step2['contact_number'];
            $contact_number_s = $step2['contact_number_s'];
            $email_address = $step2['email_address'];
            $email_address_s = $step2['email_address_s'];

            if ($orderId == null) {
                $contactInfo = new ContactInfo;
                $contactInfo->created_at = Carbon::now();
            } else {    
                $contactInfo = ContactInfo::where('order_id', $order->id)->first();
                if (!$contactInfo) {
                    return response()->json(['error' => true, 'messages' => ['Order information not found']]);
                }
                $contactInfo->updated_at = Carbon::now();
            }

            $contactInfo->order_id = $order->id;
            $contactInfo->is_borrower = $contactSame == 1 ? 1 : 0;
            $contactInfo->contact = $contact_info;
            $contactInfo->contact_email = json_encode([
                'email' => $email_address_s,
                'phone' => $contact_number_s
            ]);
            $contactInfo->save();

            return response()->json([
                "error" => false,
                "message" => "New order has been stored"
            ]);
        });
        return $orderProccess;
    }

    protected function getErrorMessage($get) {
        $step = $get->step1;
        $step2 = $get->step2;
        $company = $get->company;
        $providedData = $get->providedData;

        $error = false;
        $errorMessage = [
            'step1' => [],
            'step2' => []
        ];

        if (!isset($company['id'])) {
            $error = true;
            array_push($errorMessage['step1'], 'Company Information Missing');
        }
        
        if (!isset($providedData['extra']) && count($providedData['extra']) == 0) {
            $error = true;
            array_push($errorMessage['step1'], 'Please add provider services data');
        }

        $array_filter = [
            "clientOrderNo",
            "loanNo",
            "loanType",
            "receiveDate",
            "fhaCaseNo",
            "appraiserName",
            "dueDate",
            "amcClient",
            "lender",
            "note",
            "searchAddress",
            "state",
            "city",
            "street",
            "zipcode",
            "country",
        ];

        foreach ($array_filter as $item) {
            if ($step[$item] == null) {
                $error = true;
                array_push($errorMessage['step1'], $this->recTitle($item)." is missing");
            }
        }

        $array_filter2 = [
            "borrower_name",
            "co_borrower_name"
        ];

        foreach ($array_filter2 as $item) {
            if ($step2[$item] == null) {
                $error = true;
                array_push($errorMessage['step2'], $this->recTitle($item)." is missing");
            }
        }
        if ($step2['borrower_contact'] == false) {
            $error = true;
            array_push($errorMessage['step2'], $this->recTitle('borrower_contact')." is missing");
        }
        if ($step2['borrower_email'] == false) {
            $error = true;
            array_push($errorMessage['step2'], $this->recTitle('borrower_email')." is missing");
        }


        if ($step2['contactSame'] == 1) {
            $array_filter3 = [
                "contact_info",
                "contact_number",
                "email_address"
            ];
            foreach ($array_filter3 as $item) {
                if ($step2[$item] == null) {
                    $error = true;
                    array_push($errorMessage['step2'], $this->recTitle($item)." is missing");
                }
            }
        }

        return [
            "error" => $error,
            "message" => $errorMessage,
        ];
    }

    protected function recTitle($item) {
        return ucwords(str_replace("_", " ", $item));
    }

}
