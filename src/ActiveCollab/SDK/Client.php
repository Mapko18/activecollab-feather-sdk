<?php

  namespace ActiveCollab\SDK;

  use ActiveCollab\SDK\Exceptions\WrapperNotInitialized;
  use ActiveCollab\SDK\Exceptions\FileNotReadable;
  use ActiveCollab\SDK\Response;

  /**
   * activeCollab API client
   */
  final class Client {

    // API wrapper version
    const VERSION = '5.0.0';

    /**
     * Return user agent string
     *
     * @return string
     */
    static function getUserAgent() {
      return 'activeCollab API Wrapper; v' . self::VERSION;
    } // getUserAgent

    // ---------------------------------------------------
    //  Info
    // ---------------------------------------------------

    /**
     * Cached info response
     *
     * @var bool
     */
    private static $info_response = false;

    /**
     * Return info
     *
     * @param string|bool $property
     * @return bool|null|string
     */
    static function info($property = false) {
      if(self::$info_response === false) {
        self::$info_response = self::get('info')->getJson();
      } // if

      if($property) {
        return isset(self::$info_response[$property]) && self::$info_response[$property] ? self::$info_response[$property] : null;
      } else {
        return self::$info_response;
      } // if
    } // info

    // ---------------------------------------------------
    //  Make and process requests
    // ---------------------------------------------------

    /**
     * API URL
     *
     * @var string
     */
    static private $url;

    /**
     * Return API URL
     *
     * @return string
     */
    static function getUrl() {
      return self::$url;
    } // getUrl

    /**
     * Set API URL
     *
     * @param string $value
     */
    static function setUrl($value) {
      self::$url = $value;
    } // setUrl

    /**
     * API key
     *
     * @var string
     */
    static private $key;

    /**
     * Return API key
     *
     * @return string
     */
    static function getKey() {
      return self::$key;
    } // getKey

    /**
     * Set API key
     *
     * @param string $value
     */
    static function setKey($value) {
      self::$key = $value;
    } // setKey

    /**
     * Connector instance
     *
     * @var \ActiveCollab\SDK\Connector
     */
    static private $connector;

    /**
     * Return connector instance
     *
     * @return Connector
     */
    static function &getConnector() {
      if(empty(self::$connector)) {
        self::$connector = new Connector();
      } // if

      return self::$connector;
    } // getConnector

//    /**
//     * Prepare and execute API command
//     *
//     * @param string $command
//     * @param array $additional_command_params
//     * @param array $post_params
//     * @param array $attachments
//     * @return mixed
//     * @throws Exceptions\WrapperNotInitialized
//     * @throws Exceptions\FileNotReadable
//     */
//    static function call($command, $additional_command_params = null, $post_params = null, $attachments = null) {
//      if(empty(self::$url) || empty(self::$key) || empty(self::$connector)) {
//        throw new WrapperNotInitialized();
//      } // if
//
//      // ---------------------------------------------------
//      //  Prepare command URl
//      // ---------------------------------------------------
//
//      $data = is_array($additional_command_params) && count($additional_command_params) ? $additional_command_params : array();
//
//      $data['path_info'] = $command;
//      $data['auth_api_token'] = self::$key;
//      $data['format'] = 'json';
//      $command_url = self::$url . '?' . http_build_query($data);
//
//      // ---------------------------------------------------
//      //  Prepare attachments
//      // ---------------------------------------------------
//
//      if(empty($attachments)) {
//        $file_params = null;
//      } else {
//        $file_params = array();
//
//        $counter = 1;
//
//        foreach($attachments as $attachment) {
//          if(is_readable($attachment)) {
//            $file_params['attachment_' . $counter++] = $attachment;
//          } else {
//            throw new FileNotReadable($attachment);
//          } // if
//        } // foreach
//      } // if
//
//      // ---------------------------------------------------
//      //  Prepare POST variables
//      // ---------------------------------------------------
//
//      if(empty($post_params)) {
//        $post_params = array();
//      } else {
//        if(!isset($post_params['submitted']) || $post_params['submitted'] != 'submitted') {
//          $post_params['submitted'] = 'submitted';
//        } // if
//
//        foreach($post_params as $post_param => $post_param_value) {
//          if(is_array($post_param_value)) {
//            foreach($post_param_value as $k => $v) {
//              $post_params["{$post_param}[{$k}]"] = $v;
//            } // foreach
//
//            unset($post_params[$post_param]);
//          } // if
//        } // foreach
//      } // if
//
//      // ---------------------------------------------------
//      //  Make the call and decode the result
//      // ---------------------------------------------------
//
//      if(empty($post_params) && empty($file_params)) {
//        $response = self::$connector->get($command_url);
//      } else {
//        $response = self::$connector->post($command_url, $post_params, $file_params);
//      } // if
//
//      return json_decode($response, true);
//    } // call

    /**
     * Send a get request
     *
     * @param string $path
     * @return Response
     */
    static function get($path) {
      return self::getConnector()->get(self::prepareUrl($path), self::prepareHeaders());
    } // get

    /**
     * Send a POST request
     *
     * @param string $path
     * @param array|null $params
     * @param array|null $attachments
     * @return Response
     */
    static function post($path, $params = null, $attachments = null) {
      return self::getConnector()->post(self::prepareUrl($path), self::prepareHeaders(), self::prepareParams($params), self::prepareAttachments($attachments));
    } // post

    /**
     * Send a PUT request
     *
     * @param $path
     * @param array|null $params
     * @param array|null $attachments
     * @return Response
     */
    static function put($path, $params = null, $attachments = null) {
      return self::getConnector()->put(self::prepareUrl($path), self::prepareHeaders(), self::prepareParams($params), self::prepareAttachments($attachments));
    } // put

    /**
     * Send a delete command
     *
     * @param $path
     * @param array|null $params
     * @return Response
     */
    static function delete($path, $params = null) {
      return self::getConnector()->delete(self::prepareUrl($path), self::prepareHeaders(), self::prepareParams($params));
    } // delete

    /**
     * Prepare headers
     *
     * @return array
     */
    static private function prepareHeaders() {
      return [ 'X-Angie-AuthApiToken: ' . self::getKey() ];
    } // prepareHeaders

    /**
     * Prepare URL from the given path
     *
     * @param string $path
     * @return string
     */
    static private function prepareUrl($path) {
      $bits = parse_url($path);

      $path_info = isset($bits['path']) && $bits['path'] ? $bits['path'] : '/';
      $query = isset($bits['query']) && $bits['query'] ? '&' . $bits['query'] : '';

      return self::getUrl() . '?path_info=' . $path_info . $query;
    } // preparePath

    /**
     * Prepare params
     *
     * @param array|null $params
     * @return array
     */
    static private function prepareParams($params) {
      if(empty($params)) {
        $params = [];
      } else {
        foreach($params as $param => $param_value) {
          if(is_array($param_value)) {
            foreach($param_value as $k => $v) {
              $params["{$param}[{$k}]"] = $v;
            } // foreach

            unset($params[$param]);
          } // if
        } // foreach
      } // if

      return $params;
    } // prepareParams

    /**
     * Prepare attachments for request
     *
     * @param array|null $attachments
     * @return array|null
     * @throws Exceptions\FileNotReadable
     */
    static private function prepareAttachments($attachments = null) {
      $file_params = [];

      if($attachments) {
        $counter = 1;

        foreach($attachments as $attachment) {
          if(is_readable($attachment)) {
            $file_params['attachment_' . $counter++] = $attachment;
          } else {
            throw new FileNotReadable($attachment);
          } // if
        } // foreach
      } // if

      return $file_params;
    } // prepareAttachments

    /**
     * Return decoded response
     *
     * @param $response
     * @return mixed
     */
    static private function prepareResponse($response) {
      return json_decode($response);
    } // prepareResponse

  }