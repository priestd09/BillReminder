<?php

namespace App\Helpers;

use Auth;

class GoogleCalendarHelper
{
    private static $google_api = 'https://www.googleapis.com/calendar/v3';
    private static $url        = '';
    private static $request    = [];
    public  static $errors     = false;
    private static $curl_opts  = [];

    /* 
      command functions
    */

    /**
     * Return json list of calendars
     *
     * @return object()
     */
    public static function getCalendars()
    {
      self::setVar('url', '/users/me/calendarList');
      return self::get();
    }

    /**
     * Return json details of a calendar
     *
     * @return object()
     */
    public static function getCalendar($calendar)
    {
      self::setVar('url', '/calendars/'.$calendar);
      return self::get();
    }

    /**
     * Return json details of a calendar
     *
     * @return object()
     */
    public static function deleteCalendar($calendar)
    {
      self::setVar('url', '/calendars/'.$calendar);
      return self::remove();
    }

    /**
     * Return json of events for a calendar
     *
     * @return object()
     */
    public static function getEvents($calendar='primary')
    {
      self::setVar('url', '/calendars/'.$calendar.'/events');
      return self::get();
    }


    /**
     * Return json of events for a calendar
     *
     * @return object()
     */
    public static function postEvents($calendar='primary', $post=[])
    {
      self::setVar('url', '/calendars/'.$calendar.'/events');
      return self::post($post);
    }


    /*
      backend functions
    */


    /**
     * Return json from google api or false
     *
     * @return object()
     */
    public static function get()
    {
     self::setVar('curl_opts', false);
     return self::curl();
    }

    /**
     * Return json from google api or false
     *
     * @return object()
     */
    public static function remove()
    {
      self::setVar('curl_opts', 'DELETE');
      return self::curl();
    }

    /**
     * Return json from google api or false
     *
     * @return object()
     */
    public static function post($post=[])
    {
      self::setVar('curl_opts', json_encode($post));
      return self::curl();
    }

    /**
     * Return array of curl headers with authorization token
     *
     * @return array()
     */
    private static function curl_headers()
    {
      return [
         'Content-type: application/json',
         'Authorization: Bearer ' . Auth::user()->token
        ];
    }

    /**
     * Return url for get/post call, has request vars + api key
     *
     * @return string
     */
    private static function get_url()
    {
      return self::$google_api.self::$url.self::build_request();
    }

    /**
     * Return result of curl get/post
     *
     * @return curl_exec()
     */
    private static function curl()
    {
      $ch      = self::curl_open();
      $results = json_decode(curl_exec($ch));
      curl_close ($ch);
      return is_object($results) ? self::check_errors($results) : false;
    }

    /**
     * Return 
     *
     * @return 
     */
    private static function curl_open()
    {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, self::get_url());
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, self::curl_headers());
      if(self::$curl_opts == 'DELETE'){
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
      } elseif(self::$curl_opts){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, self::$curl_opts); 
      }
      return $ch;
    }

    /**
     * Return boolean if error exists on curl result
     *
     * @return boolean
     */
    private static function check_errors($results)
    {
      if(isset($results->error->errors)){
        self::setVar('errors', $results->error->errors);
        return false;
      } else return $results;
    }

    
    /**
     * Return request query
     *
     * @return http_build_query()
     */
    private static function build_request()
    {
      return '?'.http_build_query(array_merge(self::$request, ['key' => env('GOOGLE_API_KEY')]));
    }

    /**
     * Set class variables
     *
     * @return 
     */
    public static function setVar($var, $val)
    {
      self::$$var = $val;
    }




}