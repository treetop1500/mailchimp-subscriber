<?php

/**
 * ======================================================
 * MailchimpSubscriber Class
 * For use with the Mailchimp API v3.x
 * @author rwade@grayloon.com
 *
 * For installation / setup instructions see:
 * http://github.com/treetop1500/MailchimpSubscriber
 *
 * This Class/Service implements the Pacely MailChimp
 * Bundle at: https://github.com/pacely/mailchimp-api-v3
 * ======================================================
 */

namespace UtilBundle;

use Mailchimp\Mailchimp;


/**
 * Class SimpleSubscribeController
 * @package UtilBundle\Controller
 */
class MailchimpSubscriber
{

    protected $api_key;
    protected $list_id;


	/**
     * MailchimpSubscriber constructor.
     * @param $mailchimp_api_key
     * @param $mailchimp_list_id
     */
    public function __construct($mailchimp_api_key, $mailchimp_list_id) {
        $this->api_key = $mailchimp_api_key;
        $this->list_id = $mailchimp_list_id;
    }


    /**
     * @return array
     * A generic language exception that can be returned to users.
     */
    private function genericException() {
        return array(
          'status' => 'Error',
          'response' => "We're sorry, an error occurred. Please try again later."
        );
    }

    /**
     * @param $email
     * @return array
     * This is what's returned to users that are already in the list but do not have a status of 'subscribed'.
     * They'll be set to pending, and sent the email.
     */
    private function notSubscribed($email) {
        return array(
          'status' => 'Success!',
          'response' => "The address '" . $email . "' was previously unsubscribed. We have changed your status to
            pending, and sent an email to you which contains instruction on activating your account."
        );
    }

    /**
     * @param $email
     * @return array
     * Returned to users who try and subscribe but are already in the list with the 'subscribed' status.
     */
    private function alreadySubscribed($email) {
        return array(
          'status' => 'Good News!',
          'response' => "The address '" . $email . "' is already subscribed."
        );
    }

    /**
     * @param $email
     * @return array
     * Returned to users who were not in the list but are now.
     */
    private function isNowSubscribed($email) {
        return array(
          'status' => 'Success!',
          'response' => "The address '" . $email . "' is now subscribed. You will receive an email with further
            instruction on activating your subscription."
        );
    }


    /**
     * @param $email
     * @param null $groups array
     * @return array Loops through possible scenarios by first examining the status of the email in the list (if it's there)
     * Loops through possible scenarios by first examining the status of the email in the list (if it's there)
     * then handling the subscription items.
     * Ultimately returns an array contains a status (string) and response (string)
     */
    public function subscribe($email,$groups = null)
    {
        $mc = new MailChimp($this->api_key);

        try {

            // check first to see if the email address is already in the list.
            $getResult = $mc->get('lists/' . $this->list_id . '/members/' . md5($email));
            $status = $getResult['status'];

            if ($status == 'subscribed') {

                return $this->alreadySubscribed($email);
            } else {

                $data = array(
                  'status' => 'pending',
                  'status_if_new' => 'pending'
                );

                if ($groups) {
                    $data['interests'] = $groups;
                }

                // patch the user...
                $result = $mc->patch('lists/'.$this->list_id.'/members/' . md5($email), $data);
                // if all good...
                if ($result) {

                    return $this->notSubscribed($email);
                } else {

                    return $this->genericException();
                }
            }

        } catch (\Exception $e) {

            $mc_response = json_decode($e->getMessage());
            $status = $mc_response->status;

            // if not in the list already...
            if ($status == 404) {

                $data = array(
                  'email_address' => $email,
                  'status' => 'pending',
                  'status_if_new' => 'pending'
                );

                if ($groups) {
                    $data['interests'] = $groups;
                }

                // subscribe the user...
                $result = $mc->post('lists/'.$this->list_id.'/members', $data);
                // if all good...
                if ($result) {
                    return $this->isNowSubscribed($email);
                } else {

                    return $this->genericException();
                }
            } else {

                return $this->genericException();
            }
        }
    }
}

