<?php

namespace Faanigee\Wallet\Helpers;


class Helper
{
  public static function sendRequest(
    $url,
    $endpoint = null,
    $method = 'GET',
    $param = [],
    $headers = [],
    $isRawFieldType = false
  ) {
    if ($method == 'GET') {
      if ($endpoint !== null) {
        $curlopt_url = $url . $endpoint . (!empty($param) ? '?' . http_build_query($param) : '');
      } else {
        $curlopt_url = $url . (!empty($param) ? '?' . http_build_query($param) : '');
      }

      $curl = curl_init();
      curl_setopt_array($curl, [
        CURLOPT_URL => $curlopt_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
      ]);

      $response = curl_exec($curl);

      curl_close($curl);

      return response()->json(json_decode($response, true));
    }
    if ($method == 'POST') {
      $curl = curl_init();

      $curlopt_url = $url . $endpoint;

      $param = json_encode($param);

      curl_setopt_array($curl, [
        CURLOPT_URL => $curlopt_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $param,
        CURLOPT_HTTPHEADER => $headers,
      ]);

      $response = curl_exec($curl);

      curl_close($curl);

      return response()->json(json_decode($response, true));
    }
  }

  public static function ajaxResponse($data, $code = 302, $message = null)
  {
    $res = [];
    switch ($code) {
      case 200:
        $res = [
          'success' => true,
          'code' => $code,
          'status' => $code,
          'data' => $data,
          'message' => $message ?? 'Congratulation query successfully executed...',
        ];
        break;
      case 302:
        $res = [
          'success' => false,
          'code' => $code,
          'status' => 210,
          'data' => $data ?? null,
          'message' => $message ?? 'Oppps Query Failed...',
        ];
    }

    return $res;
  }

}
