<?php
/*
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          https://developers.google.com/recaptcha/docs/intro
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *
 * This library is highly inspired from https://github.com/pavlosadovyi/phalcon-recaptcha
 *
 * AUTHORS::
 *   Loïc Rodriguez
 */
class Recaptcha extends \Phalcon\DI\Injectable
{
    /**
     * Save public and secret key here.
     * You can have your API keys from https://www.google.com/recaptcha/admin/create
     */
    const API_PUBLIC_KEY = 's3cr3t';
    const API_PRIVATE_KEY = 'pr1v4t3-s3cr3t';

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
     * @param string $response
     * @param array $extra_params An array of extra variables to post to the server
     * @return boolean $this->is_valid property
     */
    public static function check($gResponse)
    {
        // Discard spam submissions
        if (!$gResponse) {
            return false;
        }

        // Get response from API
        $response = self::httpPost($gResponse);

        return (boolean) $response->success;
    }

    /**
     * Submits an HTTP POST to a reCAPTCHA server
     *
     * @param array $gResponse
     * @return array $response
     */
    private static function httpPost($gResponse)
    {
        $data = array(
            'secret' => self::API_PRIVATE_KEY,
            'response' => $gResponse
        );

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, $context);

        return json_decode($result);
    }
}
