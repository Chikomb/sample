<?php

namespace App\Http\Controllers;

use App\Models\DataCategory;
use App\Models\DataSurvey;
use App\Models\Language;
use App\Models\SmsSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SmsSessionController extends Controller
{
    function generateSessionId()
    {
        $prefix = 'SMS'; // Prefix for the account number
        $suffix = time(); // Suffix for the account number (UNIX timestamp)

        // Generate a random number between 1000 and 9999
        $random = rand(100000000000, 999999999999);

        // Combine the prefix, random number, and suffix to form the account number
        $payment_reference_number = $prefix . $random;

        return $payment_reference_number;
    }

    function get_telecom_operator_name($phone_number)
    {
        $mappings = [
            '096' => 'MTN',
            '26096' => 'MTN',
            '076' => 'MTN',
            '26076' => 'MTN',
            '+26076' => 'MTN',
            '+26096' => 'MTN',
            '095' => 'Zamtel',
            '26095' => 'Zamtel',
            '075' => 'Zamtel',
            '26075' => 'Zamtel',
            '+26075' => 'Zamtel',
            '+26095' => 'Zamtel',
            '097' => 'Airtel',
            '26097' => 'Airtel',
            '077' => 'Airtel',
            '26077' => 'Airtel',
            '+26077' => 'Airtel',
            '+26097' => 'Airtel',
        ];

        foreach ($mappings as $prefix => $operator) {
            if (str_starts_with($phone_number, $prefix)) {
                return $operator;
            }
        }

        return "Unknown";
    }


    public function Sms_Bot(Request $request)
    {
        if ($request) {

            $language = 1;
            $case_no = 1;
            $step_no = 0;
            $message_string = "";
            $user_message = $request->text;
            $phone_number = $request->phone;
            $session_id = $this->generateSessionId();
            $error_message_string = "";

            $session_id = $this->generateSessionId();
            $data_category = DataCategory::where('is_active', 1)->first()->name;

            $telecom_operator = $this->get_telecom_operator_name($phone_number);



            /*if(SmsSession::where('phone_number', $phone_number)->where('status', 1)->count() > 0)
            {
                $case_no = 14;
                $step_no = 1;
            }else{

            }*/

            //getting last session info
            $getLastSessionInfor = SmsSession::where('phone_number', $phone_number)->where('status', 0)->orderBy('id', 'DESC')->first();

            //checking if there is an active session or not
            if (!empty($getLastSessionInfor)) {
                $case_no = $getLastSessionInfor->case_no;
                $step_no = $getLastSessionInfor->step_no;
                $session_id = $getLastSessionInfor->session_id;
                $language = $getLastSessionInfor->language_id;

                if ($case_no == 1 && $step_no == 1 && !empty($user_message)) {
                    $language = $user_message;
                    //update the session details
                    $update_session = SmsSession::where('session_id', $session_id)->update([
                        "language_id" => $user_message
                    ]);

                }

            } else {
                //save new session record
                $new_session = SmsSession::create([
                    "phone_number" => $phone_number,
                    "case_no" => 1,
                    "step_no" => 0,
                    "session_id" => $session_id,
                    "language_id" => $language
                ]);
                $new_session->save();
            }

            switch ($case_no) {
                case '1':
                    if ($case_no == 1 && $step_no == 0) {
                        $geLanguages = Language::where('is_active', 1)->get();

                        $language_menu = "MOH & Akros are conducting a survey. Choose language\n\n";

                        $lists = $geLanguages;
                        $counter = 1;

                        foreach ($lists as $list) {
                            $language_menu = $language_menu . "\n" . $counter . ". " . $list->name;
                            $product_list[$counter] = $list->id;
                            $counter = 1 + $counter;
                        }

                        $message_string = $language_menu;

                        $imageURL = "https://res.cloudinary.com/kwachapay/image/upload/v1685364563/Akros_web-dark_tso3mu.png";

                        $update_session = SmsSession::where('session_id', $session_id)->update([
                            "case_no" => 1,
                            "step_no" => 1
                        ]);

                        return $this->sendMessage($message_string, $phone_number);

                    } elseif ($case_no == 1 && $step_no == 1 && !empty($user_message)) {

                        if (is_numeric($user_message) && $user_message >= 1 && $user_message <= 7) {
                            $chosen_language = "English";

                            if ($language == 1) //english
                            {
                                $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                $chosen_language = "Nyanja";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                $chosen_language = "Bemba";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                $chosen_language = "Tonga";
                            } elseif ($language == 5) //kaonde
                            {
                                $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                $chosen_language = "Kaonde";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                $chosen_language = "Lunda";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                $chosen_language = "Luvale";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 1,
                                "step_no" => 2
                            ]);

                            $ticked_language = "LANGUAGE: " . $chosen_language;
                            $selected_language = $this->sendMessage($ticked_language, $phone_number, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);
                        } else {
                            $error_message_string = "You have entered an invalid input!";

                            $geLanguages = Language::where('is_active', 1)->get();

                            $language_menu = "MOH & Akros are conducting a survey. Choose language\n\n";

                            $lists = $geLanguages;
                            $counter = 1;

                            foreach ($lists as $list) {
                                $language_menu = $language_menu . "\n" . $counter . ". " . $list->name;
                                $product_list[$counter] = $list->id;
                                $counter = 1 + $counter;
                            }

                            $message_string = $language_menu;

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 1,
                                "step_no" => 1
                            ]);

                            $error_response = $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);
                        }

                    } elseif ($case_no == 1 && $step_no == 2 && !empty($user_message)) {
                        if (is_numeric($user_message) && $user_message >= 1 && $user_message <= 2) {
                            if ($user_message == 1)// register account
                            {
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "1",
                                    "question" => "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?",
                                    "answer" => "1",
                                    "answer_value" => "Yes",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "What is your age? (Enter in years)";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "What is your age? (Enter in years)";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "What is your age? (Enter in years)";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "What is your age? (Enter in years)";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "Munani lilimo zekai (mun’ole lilimo) \n\n";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "What is your age? (Enter in years)";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "What is your age? (Enter in years)";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 2,
                                    "step_no" => 1
                                ]);

                                return $this->sendMessage($message_string, $phone_number);

                            } elseif ($user_message == 2) //Learn More
                            {

                                if ($language == 1) //english
                                {
                                    $message_string = "Thank you for your input, have a nice day";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "Thank you for your input, have a nice day";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "Thank you for your input, have a nice day";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "Thank you for your input, have a nice day";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "Thank you for your input, have a nice day";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "Thank you for your input, have a nice day";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "Thank you for your input, have a nice day";
                                }

                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "1",
                                    "question" => "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?",
                                    "answer" => "2",
                                    "answer_value" => "No",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 2,
                                    "step_no" => 1,
                                    "status" => 1 //terminate the session
                                ]);

                                return $this->sendMessage($message_string, $phone_number);

                            } else {
                                if ($language == 1) //english
                                {
                                    $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                    $error_message_string = "You have entered an invalid input!";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 1,
                                    "step_no" => 1
                                ]);

                                $error_response = $this->sendMessage($error_message_string, $phone_number);
                                return $this->sendMessage($message_string, $phone_number);

                            }
                        } else {
                            if ($language == 1) //english
                            {
                                $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?. \n\n1. Yes \n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 1,
                                "step_no" => 1
                            ]);

                            $error_response = $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);
                        }
                    }
                    break;
                case '2':
                    if ($case_no == 2 && $step_no == 1 && !empty($user_message)) {
                        if (is_numeric($user_message)) {
                            //validate the age entered
                            if ($user_message >= 18) {
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "2",
                                    "question" => "What is your age? (Enter in years)",
                                    "answer" => $user_message,
                                    "answer_value" => $user_message,
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 2,
                                    "step_no" => 2
                                ]);

                                return $this->sendMessage($message_string, $phone_number);
                            } else {
                                //if entered age is less than 18 years old
                                if ($language == 1) //english
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above. Thank you.";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                    $error_message_string = "You have entered an invalid input!";
                                }

                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "1",
                                    "question" => "Akros and Ministry of health are conducting a survey(if there’s need to specify the reason, it shall be done here). If you are 18 years or older and wish to proceed, press 1. if not press 2.",
                                    "answer" => "2",
                                    "answer_value" => $user_message,
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 2,
                                    "step_no" => 1,
                                    "status" => 1 //terminate session
                                ]);

                                $error_response = $this->sendMessage($error_message_string, $phone_number);
                                return $this->sendMessage($message_string, $phone_number);

                            }
                        } else {
                            if ($language == 1) //english
                            {
                                $message_string = "What is your age? (Enter in years)";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "What is your age? (Enter in years)";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "What is your age? (Enter in years)";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "What is your age? (Enter in years)";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Munani lilimo zekai (mun’ole lilimo) \n\n";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "What is your age? (Enter in years)";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "What is your age? (Enter in years)";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "What is your age? (Enter in years)";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 1
                            ]);

                            $error_response = $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);

                        }

                    } elseif ($case_no == 2 && $step_no == 2 && !empty($user_message)) {
                        if (is_numeric($user_message) && $user_message >= 1 && $user_message <= 4) {

                            $gender = "invalid input";

                            if ($user_message == 1){
                                $gender = "Male";
                            }else if ($user_message == 2) {
                                $gender = "Female";
                            } elseif ($user_message == 3) {
                                $gender = "Other";
                            } elseif ($user_message == 4) {
                                $gender = "Prefer not to say";
                            }

                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "3",
                                "question" => "What is your gender?",
                                "answer" => $user_message,
                                "answer_value" => $gender,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 3
                            ]);

                            return $this->sendMessage($message_string, $phone_number);
                        } else {
                            if ($language == 1) //english
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 2
                            ]);

                            $error_response = $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);
                        }

                    } elseif ($case_no == 2 && $step_no == 3 && !empty($user_message)) {
                        //Save selected District and Ask for respective constituency
                        if (is_numeric($user_message) && $user_message >= 1 && $user_message <= 3) {
                            if ($user_message == 1) {
                                //save the Lusaka district
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "4a",
                                    "question" => "In which District do you live?",
                                    "answer" => "1",
                                    "answer_value" => "Lusaka District",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 1 //save Constituency in Lusaka
                                ]);

                                return $this->sendMessage($message_string, $phone_number);

                            } elseif ($user_message == 2) {
                                //Kalomo District
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "4a",
                                    "question" => "In which District do you live?",
                                    "answer" => $user_message,
                                    "answer_value" => "Kalomo District",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 4,
                                    "step_no" => 1 //save Kalomo District Constituency and go ask about wards
                                ]);

                                return $this->sendMessage($message_string, $phone_number);

                            } elseif ($user_message == 3) {
                                //chavuma District
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "4a",
                                    "question" => "In which District do you live?",
                                    "answer" => $user_message,
                                    "answer_value" => "Chavuma District",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 5,
                                    "step_no" => 1
                                ]);

                                return $this->sendMessage($message_string, $phone_number);

                            } else {
                                //craft the error message and display the previous question

                                if ($language == 1) //english
                                {
                                    $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                    $error_message_string = "You have entered an invalid input!";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                    $error_message_string = "You have entered an invalid input!";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 2,
                                    "step_no" => 3
                                ]);

                                $error_response = $this->sendMessage($error_message_string, $phone_number);
                                return $this->sendMessage($message_string, $phone_number);
                            }
                        } else {
                            if ($language == 1) //english
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "What is your gender? \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 2
                            ]);

                            $error_response = $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);
                        }
                    }
                    break;
                case '3':
                    if($case_no == 3 && $step_no == 1 && !empty($user_message)){
                        //Lusaka District
                        if (is_numeric($user_message) && $user_message >= 1 && $user_message <= 7) {
                            if ($user_message == 1) {
                                //Chawama Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Chawama Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 2 // save Chawama Constituency ward, Ask about Covid then go to Case 6 to proceed
                                ]);

                                return $this->sendMessage($message_string, $phone_number);

                            } elseif ($user_message == 2) {
                                //Kabwata Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Kabwata Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 3 //save Kabwata ward, Ask about Covid then go to Case 6 to proceed
                                ]);

                                return $this->sendMessage($message_string, $phone_number);

                            } elseif ($user_message == 3) {
                                //Kanyama Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Kanyama Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 4 //save Kanyama ward, Ask about Covid then go to Case 6 to proceed
                                ]);

                                return $this->sendMessage($message_string, $phone_number);

                            } elseif ($user_message == 4) {
                                //Lusaka Central Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "4b",
                                    "question" => "Which Constituency do you live in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Lusaka Central Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 5 //save Lusaka Central ward, ask about Covid and go to Case 6
                                ]);

                                return $this->sendMessage($message_string, $phone_number);

                            } elseif ($user_message == 5) {
                                //Mandevu Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Mandevu Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 6 //save Mandevu ward, ask about Covid and go to Case 6
                                ]);

                                return $this->sendMessage($message_string, $phone_number);

                            } elseif ($user_message == 6) {
                                //Matero Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Matero Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 7 //save Matero Ward, ask about Covid and go to case 6
                                ]);

                                return $this->sendMessage($message_string, $phone_number);

                            } elseif ($user_message == 7) {
                                //Munali Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Munali Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 8 //save Munali Ward, ask about Covid and go to Case 6
                                ]);

                                return $this->sendMessage($message_string, $phone_number);

                            }
                        } else {
                            $error_message_string = "You have entered an invalid input!";

                            if ($language == 1) //english
                            {
                                $message_string = "Which constituency do you live in? \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Which constituency do you live in? \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Which constituency do you live in? \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Which constituency do you live in? \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Which constituency do you live in? \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Which constituency do you live in? \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Which constituency do you live in? \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Which constituency do you live in? \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 3
                            ]);

                            $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);
                        }
                    }elseif ($case_no == 3 && $step_no == 2 && !empty($user_message)){
                        //save Chawama Ward
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 4){
                            $ward = "Nkoloma";

                            if ($user_message == 2){
                                $ward = "Chawama";
                            }elseif ($user_message == 3)
                            {
                                $ward = "John Howard";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Lilayi";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number);


                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3. John Howard\n5. Lilayi";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 2 //Ask about Covid then go to Case 6 to proceed
                            ]);

                            return $this->sendMessage($message_string, $phone_number);
                        }
                    }elseif ($case_no == 3 && $step_no == 3 && !empty($user_message)){
                        //save Kanyama Ward
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 6){
                            $ward = "Kamwala";

                            if($user_message == 2)
                            {
                                $ward = "Kabwata";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Libala";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Chilenje";
                            }elseif ($user_message == 5)
                            {
                                $ward = "Kamulanga";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number);


                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 3 //save Kabwata ward, Ask about Covid then go to Case 6 to proceed
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }elseif ($case_no == 3 && $step_no == 4 && !empty($user_message)){
                        //save Lusaka Central Ward
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 3){

                            $ward = "Kanyama";
                            if($user_message == 2)
                            {
                                $ward = "Harry Mwanga Nkumbula";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Munkolo";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number);


                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 4 //save Kanyama ward, Ask about Covid then go to Case 6 to proceed
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }elseif ($case_no == 3 && $step_no == 5 && !empty($user_message)){
                        //save Lusaka Central Ward
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <=4){
                            $ward = "Silwizya";

                            if($user_message == 2)
                            {
                                $ward = "Independence";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Lubwa";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Kabulonga";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 5 //save Lusaka Central ward, ask about Covid and go to Case 6
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }elseif ($case_no == 3 && $step_no == 6 && !empty($user_message)){
                        //save Mandevu Ward
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 7){
                            $ward = "Roma";

                            if($user_message == 2)
                            {
                                $ward = "Mulungushi";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Ngwerere";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Chaisa";
                            }elseif ($user_message == 5)
                            {
                                $ward = "Justine Kabwe";
                            }elseif ($user_message == 6)
                            {
                                $ward = "Raphael Chota";
                            }elseif ($user_message == 7)
                            {
                                $ward = "Mpulungu";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 6 //save Mandevu ward, ask about Covid and go to Case 6
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }elseif ($case_no == 3 && $step_no == 7 && !empty($user_message)){
                        //save Matero Ward
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 5){
                            $ward = "Muchinga";

                            if($user_message == 2)
                            {
                                $ward = "Kapwepwe";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Lima";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Mwembeshi";
                            }elseif ($user_message == 5)
                            {
                                $ward = "Matero";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 7 //save Matero Ward, ask about Covid and go to case 6
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }elseif ($case_no == 3 && $step_no == 8 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 7)
                        {
                            $ward = "Chainda";

                            if($user_message == 2)
                            {
                                $ward = "Mtendere";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Kalingalinga";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Chakunkula";
                            }elseif ($user_message == 5)
                            {
                                $ward = "Munali";
                            }elseif ($user_message == 6)
                            {
                                $ward = "Chelstone";
                            }elseif ($user_message == 7)
                            {
                                $ward = "Avondale";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }else{

                            if ($language == 1) //english
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 8 //save Munali Ward, ask about Covid and go to Case 6
                            ]);

                            $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }
                    break;
                case '4': //show Kalomo District Wards
                    if ($case_no == 4 && $step_no == 1 && !empty($user_message)) {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 2)
                        {
                            if($user_message == 1)
                            {
                                //Dundumwezi Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Dundumwezi Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                //ask about Dundumwezi wards
                                if ($language == 1) //english
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 4,
                                    "step_no" => 2 // save Dundumwezi Constituency ward, Ask about Covid then go to Case 6 to proceed
                                ]);

                                return $this->sendMessage($message_string, $phone_number);


                            }elseif ($user_message == 2)
                            {
                                //Kalomo Central Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Kalomo Central Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                //ask about Kalomo Central Wards
                                if ($language == 1) //english
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 4,
                                    "step_no" => 3 // save Kalomo Central Constituency ward, Ask about Covid then go to Case 6 to proceed
                                ]);

                                return $this->sendMessage($message_string, $phone_number);


                            }

                        }else{
                            //repeat constituency question
                            if ($language == 1) //english
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 4,
                                "step_no" => 1 //save Kalomo District Constituency and go ask about wards
                            ]);

                            $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }elseif ($case_no == 4 && $step_no == 2 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 6)
                        {
                            $ward = "Chikanta";

                            if($user_message == 2)
                            {
                                $ward = "Chamuka";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Kasukwe";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Omba";
                            }elseif ($user_message == 5)
                            {
                                $ward = "Bbili";
                            }elseif ($user_message == 6)
                            {
                                $ward = "Naluja";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 4,
                                "step_no" => 2 // save Dundumwezi Constituency ward, Ask about Covid then go to Case 6 to proceed
                            ]);

                            $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }elseif ($case_no == 4 && $step_no == 3 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 9)
                        {
                            $ward = "Siachitema";

                            if($user_message == 2)
                            {
                                $ward = "Kalonda";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Choonga";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Mayoba";
                            }elseif ($user_message == 5)
                            {
                                $ward = "Namwianga";
                            }elseif ($user_message == 6)
                            {
                                $ward = "Simayakwe";
                            }elseif ($user_message == 7)
                            {
                                $ward = "Chawila";
                            }elseif ($user_message == 8)
                            {
                                $ward = "Sipatunyana";
                            }elseif ($user_message == 9)
                            {
                                $ward = "Nachikungu";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 4,
                                "step_no" => 3 // save Kalomo Central Constituency ward, Ask about Covid then go to Case 6 to proceed
                            ]);

                            $this->sendMessage($error_message_string,$phone_number, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }
                case '5'://Chavuma District Wards
                    if ($case_no == 5 && $step_no == 1 && !empty($user_message)) {
                        if(is_numeric($user_message) && $user_message == 1)
                        {
                            //save data
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "4b",
                                "question" => "In which Constituency do you stay in?",
                                "answer" => $user_message,
                                "answer_value" => "Chavuma Constituency",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            //ask which ward do they live in
                            if ($language == 1) //english
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 5,
                                "step_no" => 2 // save Chavuma Constituency ward, Ask about Covid then go to Case 6 to proceed
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }else{
                            //repeat constituency question
                            if ($language == 1) //english
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Which constituency do you live in? \n\n1. Chavuma";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 5,
                                "step_no" => 1 //save Kalomo District Constituency and go ask about wards
                            ]);

                            $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }elseif ($case_no == 5 && $step_no == 2 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 13)
                        {
                            $ward = "Chambi Mandalo";

                            if($user_message == 2)
                            {
                                $ward = "Sewe";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Lingelengenda";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Chiyeke";
                            }elseif ($user_message == 5)
                            {
                                $ward = "KalomboKamisamba";
                            }elseif ($user_message == 6)
                            {
                                $ward = "Chivombo Mbelango";
                            }elseif ($user_message == 7)
                            {
                                $ward = "Chavuma central";
                            }elseif ($user_message == 8)
                            {
                                $ward = "Sanjongo";
                            }elseif ($user_message == 9)
                            {
                                $ward = "Lingundu";
                            }elseif ($user_message == 10)
                            {
                                $ward = "Lukolwe Musumba";
                            }elseif ($user_message == 11)
                            {
                                $ward = "Kambuya Mukelengombe";
                            }elseif ($user_message == 12)
                            {
                                $ward = "Nyalanda Nyambingala";
                            }elseif ($user_message == 13)
                            {
                                $ward = "Nguvu";
                            }

                            //save the ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "In which Ward do you stay in? \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 5,
                                "step_no" => 2 // save Chavuma Constituency ward, Ask about Covid then go to Case 6 to proceed
                            ]);

                            $this->sendMessage($error_message_string,$phone_number, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }
                case '6'://Save Question 5
                    if ($case_no == 6 && $step_no == 1 && !empty($user_message)) {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 2)
                        {
                            if($user_message == 1)
                            {
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "5",
                                    "question" => "Have you received a COVID-19 vaccine?",
                                    "answer" => $user_message,
                                    "answer_value" => "Yes",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 7,
                                    "step_no" => 1 //save
                                ]);

                                return $this->sendMessage($message_string, $phone_number);

                            }elseif ($user_message == 2)
                            {
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $phone_number,
                                    "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "SMS",
                                    "question_number" => "5",
                                    "question" => "Have you received a COVID-19 vaccine?",
                                    "answer" => $user_message,
                                    "answer_value" => "No",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                }

                                $update_session = SmsSession::where('session_id', $session_id)->update([
                                    "case_no" => 7,
                                    "step_no" => 2 //save
                                ]);

                                return $this->sendMessage($message_string, $phone_number);

                            }
                            //save data
                        }else{
                            //repeat wards question
                            if ($language == 1) //english
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Have you received a COVID-19 vaccine?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }

                    break;
                case '7':
                    if($case_no == 7 && $step_no == 1 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 4)
                        {
                            $answer = "Very Concerned";

                            if($user_message == 2)
                            {
                                $answer = "Somewhat concerned";
                            }elseif ($user_message == 3)
                            {
                                $answer = "A little concerned";
                            }elseif ($user_message == 4)
                            {
                                $answer = "Not at all concerned";
                            }

                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "7",
                                "question" => "How concerned are you about getting COVID-19?",
                                "answer" => $user_message,
                                "answer_value" => $answer,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 8,
                                "step_no" => 1 //save
                            ]);

                            return $this->sendMessage($message_string, $phone_number);


                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "How concerned are you about getting COVID-19?\n\n1. Very Concerned\n2. Somewhat concerned\n3. A little concerned\n4. Not at all concerned";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 7,
                                "step_no" => 1 //save
                            ]);

                            $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }elseif ($case_no == 7 && $step_no == 2 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 3)
                        {
                            $answer = "Yes, I do want to";

                            if($user_message == 2) {
                                $answer = "No, you do not want to";
                            }elseif ($user_message == 3)
                            {
                                $answer = "Not sure";
                            }

                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "6",
                                "question" => "Do you want to get a COVID-19 vaccine?",
                                "answer" => $user_message,
                                "answer_value" => $answer,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 8,
                                "step_no" => 1 //save
                            ]);

                            return $this->sendMessage($message_string, $phone_number);
                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Do you want to get a COVID-19 vaccine?\n\n1. Yes, I do want to\n2. No, you do not want to\n3. Not sure";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 7,
                                "step_no" => 2 //save
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }
                    break;
                case '8':
                    if($case_no == 8 && $step_no == 1 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 4)
                        {
                            $answer = "Very important";

                            if($user_message == 2)
                            {
                                $answer = "Somewhat important";
                            }elseif ($user_message == 3)
                            {
                                $answer = "A little important";
                            }elseif ($user_message == 4)
                            {
                                $answer = "Not at all important";
                            }

                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "8",
                                "question" => "How important is getting a COVID-19 vaccine for your health?",
                                "answer" => $user_message,
                                "answer_value" => $answer,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 9,
                                "step_no" => 1 //save
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "How important is getting a COVID-19 vaccine for your health?\n\n1. Very important\n2. Somewhat important\n3. A little important\n4. Not at all important";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 8,
                                "step_no" => 1 //save
                            ]);

                            $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }
                    break;
                case '9':
                    if($case_no == 9 && $step_no == 1 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 3)
                        {
                            $answer = "Yes";

                            if($user_message == 2)
                            {
                                $answer = "No";
                            }elseif ($user_message == 3)
                            {
                                $answer = "I don’t know";
                            }

                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "9",
                                "question" => "Have most of your close family and friends received the COVID-19 vaccine?",
                                "answer" => $user_message,
                                "answer_value" => $answer,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 10,
                                "step_no" => 1 //save
                            ]);

                            return $this->sendMessage($message_string, $phone_number);


                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Have most of your close family and friends received the COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 9,
                                "step_no" => 1 //save
                            ]);

                            $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }
                    break;
                case '10':
                    if($case_no == 10 && $step_no == 1 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 3)
                        {
                            $answer = "Yes";

                            if($user_message == 2)
                            {
                                $answer = "No";
                            }elseif ($user_message == 3)
                            {
                                $answer = "I don’t know";
                            }

                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "10",
                                "question" => "Do you think most of your close family and friends want you to get a COVID-19 vaccine?",
                                "answer" => $user_message,
                                "answer_value" => $answer,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 11,
                                "step_no" => 1 //save
                            ]);

                            return $this->sendMessage($message_string, $phone_number);


                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Do you think most of your close family and friends want you to get a COVID-19 vaccine?\n\n1. Yes\n2. No\n3. I don’t know";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 10,
                                "step_no" => 1 //save
                            ]);

                            $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);


                        }
                    }
                    break;
                case '11':
                    if($case_no == 11 && $step_no == 1 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 2)
                        {
                            $answer = "Yes";

                            if($user_message == 2)
                            {
                                $answer = "No";
                            }

                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "11",
                                "question" => "Do you know where to get a COVID-19 vaccine for yourself?",
                                "answer" => $user_message,
                                "answer_value" => $answer,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 12,
                                "step_no" => 1 //save
                            ]);

                            return $this->sendMessage($message_string, $phone_number);


                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Do you know where to get a COVID-19 vaccine for yourself?\n\n1. Yes\n2. No";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 11,
                                "step_no" => 1 //save
                            ]);

                            $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);


                        }
                    }
                    break;
                case '12':
                    if($case_no == 12 && $step_no == 1 && !empty($user_message)){
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 4)
                        {
                            $answer = "Very costly";

                            if($user_message == 2)
                            {
                                $answer = "Somewhat costly";
                            }elseif ($user_message == 3)
                            {
                                $answer = "A little costly";
                            }elseif($user_message == 4)
                            {
                                $answer = "Not at all costly";
                            }

                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "12",
                                "question" => "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work?",
                                "answer" => $user_message,
                                "answer_value" => $answer,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 13,
                                "step_no" => 1 //save
                            ]);

                            return $this->sendMessage($message_string, $phone_number);


                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Do you find it costly to get a vaccine? Consider clinic costs, transport, or missed work.\n\n1. Very costly\n2. Somewhat costly\n3. A little costly\n4. Not at all costly";
                                $error_message_string = "You have entered an invalid input!";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 12,
                                "step_no" => 1 //save
                            ]);
                            $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);


                        }
                    }
                    break;
                case '13':
                    if($case_no == 13 && $step_no == 1 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 4)
                        {
                            $answer = "Yes, I have already received a COVID-19 booster";

                            if($user_message == 2)
                            {
                                $answer = "Yes, I do want to";
                            }elseif ($user_message == 3)
                            {
                                $answer = "Not sure";
                            }elseif ($user_message == 4)
                            {
                                $answer = "No, I do not want to";
                            }

                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $phone_number,
                                "language_id" => SmsSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "SMS",
                                "question_number" => "13",
                                "question" => "Do you want to get a COVID-19 booster vaccine?",
                                "answer" => $user_message,
                                "answer_value" => $answer,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "     ~ END ~     \n_Thank you for participating in the survey_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "     ~ END ~     \n_Thank you for participating in the survey_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "     ~ END ~     \n_Thank you for participating in the survey_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "     ~ END ~     \n_Thank you for participating in the survey_";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "     ~ END ~     \n_Thank you for participating in the survey_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "     ~ END ~     \n_Thank you for participating in the survey_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "     ~ END ~     \n_Thank you for participating in the survey_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "     ~ END ~     \n_Thank you for participating in the survey_";
                            }

                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 13,
                                "step_no" => 1,
                                "status" => 1 //terminating session
                            ]);

                            return $this->sendMessage($message_string, $phone_number);

                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                                $error_message_string = "You have entered an invalid input!";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Do you want to get a COVID-19 booster vaccine?\n\n1. Yes, I have already received a COVID-19 booster\n2. Yes, I do want to\n3. Not sure\n4. No, I do not want to";
                                $error_message_string = "You have entered an invalid input!";
                            }


                            $update_session = SmsSession::where('session_id', $session_id)->update([
                                "case_no" => 13,
                                "step_no" => 1 //save
                            ]);

                            $this->sendMessage($error_message_string, $phone_number);
                            return $this->sendMessage($message_string, $phone_number);

                        }
                    }
                    break;

            }
        } else {
            Log::info('SMS Error', ['no message' => json_encode($request)]);
        }

    }

    function sendMessage($message_string, $phone_number)
    {
        $sender_number = env('SMS_SENDER_NUMBER');

        //Next auto response
        $url_encoded_message = urlencode($message_string);

        //Next auto response
        $sendSenderSMS = Http::withoutVerifying()
            ->post('http://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '.+&shortcode=2343&sender_id='.$sender_number.'&phone=' . $phone_number . '&api_key=121231313213123123');

        return $sendSenderSMS->body();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SmsSession  $smsSession
     * @return \Illuminate\Http\Response
     */
    public function show(SmsSession $smsSession)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SmsSession  $smsSession
     * @return \Illuminate\Http\Response
     */
    public function edit(SmsSession $smsSession)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SmsSession  $smsSession
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SmsSession $smsSession)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SmsSession  $smsSession
     * @return \Illuminate\Http\Response
     */
    public function destroy(SmsSession $smsSession)
    {
        //
    }
}
