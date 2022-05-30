<?php

namespace App\Helpers;
use App\Models\Order;

trait CrmHelper {

    public function getSystemOrderNumber() {
        $order = Order::latest()->first();
        $length = 8;
        if ($order) {
            $orderLength = $order->id+1;
            $length = strlen($orderLength) < 8 ? 8 - strlen($orderLength) : 0;
        }
        if ($length > 0) {
            return "BAS-".str_pad($order->id+1, $length, 0, STR_PAD_LEFT);
        } else {
            return "BAS-".$order->id+1;
        }
    }

    protected function getOrderError($get, $type) {
        $error = false;
        $errorMessage = "";

        if ($type == "borrower") {
            $array_filter2 = [
                "borrower_name",
                "co_borrower_name"
            ];    
            foreach ($array_filter2 as $item) {
                if ($get->{$item} == null) {
                    $error = true;
                    array_push($errorMessage, $this->getTitleReplace($item)." is missing");
                }
            }
            if ($get->borrower_contact == false) {
                $error = true;
                array_push($errorMessage, $this->getTitleReplace('borrower_contact')." is missing");
            }
            if ($get->borrower_email == false) {
                $error = true;
                array_push($errorMessage, $this->getTitleReplace('borrower_email')." is missing");
            }
        } elseif ($type == "contactInfo") {
            $array_filter3 = [
                "contact_info",
                "contact_number",
                "email_address"
            ];
            if ($get->contact_info == null) {
                $error = true;
                array_push($errorMessage, "Enter contact Info");
            }
            if ($get->contact_number == false) {
                $error = true;
                array_push($errorMessage, "Choose contact number");
            }
            if ($get->email_address == false) {
                $error = true;
                array_push($errorMessage, "Choose email address");
            }
        } elseif ($type == "providerService") {
            if ($get->note ==  null ) {
                $error = true;
                array_push($errorMessage, "Enter provider service fee");
            }
            if (count($get->data) == 0) {
                $error = true;
                array_push($errorMessage, "Please add some fee");
            }
        }

        return [
            "error" => $error,
            "message" => $errorMessage,
        ];
    }

    protected function getTitleReplace($item) {
        return ucwords(str_replace("_", " ", $item));
    }
}