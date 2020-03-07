<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// define('WOO_ALL_IN_ONE_TURBOSMS_LIVE', true);

function wooaio_turbosms_order_created($order_id, $data, $order) {
    $turbosms_settings = Woo_All_In_One_Turbosms_Helpers::get_settings();

    if (!empty($turbosms_settings['text_new_order'])) {
        $order_phone = $order->get_billing_phone();
        $order_number = $order->get_order_number();
        $order_date = wc_format_datetime( $order->get_date_created() );
        $order_total = $order->get_formatted_order_total();
        $order_status = wc_get_order_status_name( $order->get_status() );
    
        $text = str_replace("{{order_number}}", $order_number, $text);
        $text = str_replace("{{order_date}}", $order_date, $text);
        $text = str_replace("{{order_total}}", $order_total, $text);
        $text = str_replace("{{order_status}}", $order_status, $text);

        $text = $turbosms_settings['text_new_order'];
        wooaio_turbosms_send_sms($order_phone, $text, $order);
    }
}

function wooaio_turbosms_order_status_changed($order_id, $from, $to, $order) {
    $turbosms_settings = Woo_All_In_One_Turbosms_Helpers::get_settings();

    if (empty($turbosms_settings['send_order_status_' . $to]) || 'yes' !== $turbosms_settings['send_order_status_' . $to] || empty($turbosms_settings['text_order_status_' . $to])) {
        return;
    }

    $order_phone = $order->get_billing_phone();
    $order_number = $order->get_order_number();
    $order_date = wc_format_datetime( $order->get_date_created() );
    $order_total = $order->get_formatted_order_total();
    $order_status = wc_get_order_status_name( $order->get_status() );
    $from_order_status = wc_get_order_status_name( $from );

    $text = $turbosms_settings['text_order_status_' . $to];

    $text = str_replace("{{order_number}}", $order_number, $text);
    $text = str_replace("{{order_date}}", $order_date, $text);
    $text = str_replace("{{order_total}}", $order_total, $text);
    $text = str_replace("{{order_status}}", $order_status, $text);
    $text = str_replace("{{old_order_status}}", $from_order_status, $text);

    wooaio_turbosms_send_sms($order_phone, $text, $order);
}

function wooaio_turbosms_send_sms($phone, $text, $order) {
    $turbosms_settings = Woo_All_In_One_Turbosms_Helpers::get_settings();
    $is_valid_phone = preg_match('/^\+380\d{9}$/', $phone);
    $encoding =  mb_detect_encoding($text, "auto");

    if (empty($turbosms_settings['login']) || empty($turbosms_settings['password']) || empty($turbosms_settings['sender'])) {
        $notice_text = __("SMS can't be send. Sender, Login or Password is empty", 'woo-all-in-one-turbosms');
    } elseif( 1 !== $is_valid_phone ) {
        $error = __("Wrong phone format", 'woo-all-in-one-turbosms');
        $notice_text = sprintf( __("SMS can't be send. %s.", 'woo-all-in-one-turbosms'), $error );
    } elseif( 'UTF-8' !== $encoding ) {
        $error = __("Wrong text encoding", 'woo-all-in-one-turbosms');
        $notice_text = sprintf( __("SMS can't be send. %s.", 'woo-all-in-one-turbosms'), $error );
    } else {
        $client = new Woo_All_In_One_Turbosms_API;
        $auths_status = $client->getAuthStatus();

        if ('Вы успешно авторизировались' !== $auths_status->AuthResult) { // Authorization failed
            $error = $auths_status->AuthResult;
            $notice_text = sprintf( __("SMS can't be send. %s.", 'woo-all-in-one-turbosms'), $error );
        } else {
            $client = new Woo_All_In_One_Turbosms_API;
            $auths_status = $client->getAuthStatus();
            $sender = $turbosms_settings['sender'];
            
            if ( defined( 'WOO_ALL_IN_ONE_TURBOSMS_LIVE' ) && WOO_ALL_IN_ONE_TURBOSMS_LIVE ) {
                // $notice_text = sprintf( __('SMS sent to client (debug mode live). Phone number %s. SMS Text: "%s"', 'woo-all-in-one-turbosms'), $phone, $text );

                // ob_start();
                // $sms = $client->sendSMS($sender, $phone, $text);
                // var_dump($sms->SendSMSResult->ResultArray);

                // $error = ob_get_clean();
                // $error = __("Connection error", 'woo-all-in-one-turbosms');
                // $notice_text = sprintf( __("SMS can't be send. %s.", 'woo-all-in-one-turbosms'), $error );

                if (true) {
                    $sms = $client->sendSMS($sender, $phone, $text);
    
                    if (!$sms) {
                        $error = __("Connection error", 'woo-all-in-one-turbosms');
                        $notice_text = sprintf( __("SMS can't be send. %s.", 'woo-all-in-one-turbosms'), $error );
                    } else {
                        $result = $sms->SendSMSResult->ResultArray;

                        if (!is_array($result) && is_string($result)) {
                            $error = $result;
                            $notice_text = sprintf( __("SMS can't be send. %s.", 'woo-all-in-one-turbosms'), $error );
                        } elseif (is_array($result)) {
                            $sms_result = !empty($result[0]) ? $result[0] : false;
                            $sms_id = !empty($result[1]) ? $result[1] : false;

                            if (!$sms_result) {
                                $error = 'Unknown error';
                                $notice_text = sprintf( __("SMS can't be send. %s.", 'woo-all-in-one-turbosms'), $error );
                            }elseif (false === strpos ($sms_result, 'Сообщения успешно отправлены')) {
                                $error = $sms_result;
                                $notice_text = sprintf( __("SMS can't be send. %s.", 'woo-all-in-one-turbosms'), $error );
                            } else {
                                $notice_text = sprintf( __('SMS sent to client. Phone number %s. SMS Text: "%s"', 'woo-all-in-one-turbosms'), $phone, $text );
                            }
                        } else {
                            $error = 'Unknown error';
                            $notice_text = sprintf( __("SMS can't be send. %s.", 'woo-all-in-one-turbosms'), $error );
                        }
                    }
                }
            } else {
                $sms = $client->getBalance();
                $notice_text = sprintf( __('SMS sent to client (debug mode). Phone number %s. SMS Text: "%s"', 'woo-all-in-one-turbosms'), $phone, $text );
            }
        }
    }

    $order->add_order_note( $notice_text );
}