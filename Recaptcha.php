<?php
/*
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          https://developers.google.com/recaptcha/docs/
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *
 * Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net
 * AUTHORS:
 *   Mike Crawford
 *   Ben Maurer
 *   Pavlo Sadovyi (made this wrapper for Phalcon Framework)
 * CONTRIBUTORS:
 *   Loïc Rodriguez (adapt and simplify code to work with ReCAPTCHA v2 and Phalcon 3)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class Recaptcha extends \Phalcon\DI\Injectable
{
    /**
     * Save public and secret key here.
     * You can have your API keys from https://www.google.com/recaptcha/admin/create
     */
    const API_PUBLIC_KEY = 's3cr3t';
    const API_PRIVATE_KEY = 'pr1v4t3-s3cr3t';

    public static $error = 'incorrect-captcha-sol';
    public static $is_valid = false;

    /**
     * Gets the challenge HTML (javascript and non-javascript version).
     * This is called from the browser, and the resulting reCAPTCHA HTML widget
     * is embedded within the HTML form it was called from.
     *
     * @param array params An array containing tag attributes: https://developers.google.com/recaptcha/docs/display (section "Configuration")
     * @return string - The HTML to be embedded in the user's form.
     */
    public static function get($params = array())
    {
        // Generate HTML
        $html = '<div class="g-recaptcha" data-sitekey="'.self::API_PUBLIC_KEY.'"';
        if (isset($params['theme'])) {
            $html .= ' data-theme="'.$params['theme'].'"';
        }
        if (isset($params['type'])) {
            $html .= ' data-type="'.$params['type'].'"';
        }
        if (isset($params['size'])) {
            $html .= ' data-size="'.$params['size'].'"';
        }
        if (isset($params['tabindex'])) {
            $html .= ' data-tabindex="'.$params['tabindex'].'"';
        }
        if (isset($params['callback'])) {
            $html .= ' data-callback="'.$params['callback'].'"';
        }
        if (isset($params['expired-callback'])) {
            $html .= ' data-expired-callback="'.$params['expired-callback'].'"';
        }
        $html .= '></div>';

        // Return HTML
        return $html;
    }

    /**
     * Calls an HTTP POST function to verify if the user's guess was correct
     *
     * @param string $challenge
     * @param string $response
     * @param array $extra_params An array of extra variables to post to the server
     * @return boolean $this->is_valid property
     */
    public static function check($challenge, $response, $extra_params = array())
    {
        // Discard spam submissions
        if (!$challenge or !$response) {
            return self::$is_valid;
        }

        // Get response from API
        $response = self::httpPost('www.google.com', "/recaptcha/api/verify", array(
            'privatekey' => self::API_PRIVATE_KEY,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
            'challenge' => $challenge,
            'response' => $response
        ) + $extra_params);
        $answers = explode("\n", $response[1]);

        if (trim($answers[0]) == 'true') self::$is_valid = true;
        else self::$error = $answers[1];
        return self::$is_valid;
    }

    /**
     * Submits an HTTP POST to a reCAPTCHA server
     *
     * @param string $host
     * @param string $path
     * @param array $data
     * @param int port
     * @return array response
     */
    private static function httpPost($host, $path, $data, $port = 80)
    {
        $req = self::qsEncode($data);
        $http_request  = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: ".strlen($req)."\r\n";
        $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;
        $response = '';
        if (!($fs = @fsockopen($host, $port, $errno, $errstr, 10))) {
            die('Could not open socket');
        }
        fwrite($fs, $http_request);
        while (!feof($fs)) $response .= fgets($fs, 1160); // One TCP-IP packet
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);
        return $response;
    }
    /**
     * Encodes the given data into a query string format
     *
     * @param array $data Array of string elements to be encoded
     * @return string $req Encoded request
     */
    private static function qsEncode($data)
    {
        $req = '';
        foreach ($data as $key => $value)
            $req .= $key.'='.urlencode(stripslashes($value)).'&';
        // Cut the last '&'
        $req = substr($req, 0, strlen($req) - 1);
        return $req;
    }
}
