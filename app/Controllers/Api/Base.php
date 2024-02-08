<?php

namespace App\Controllers\Api;

use App\Models\Api\DeviceModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\OAuth2;
use Psr\Log\LoggerInterface;
use Firebase\JWT\JWT;
use CodeIgniter\RESTful\ResourceController;


/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class Base extends ResourceController
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    protected function sendIosPush($token, $message = '', $title = APP_NAME, $subtitle = '')
    {
        $privateKeyPath = __DIR__ . '/AuthKey_B9GG868T6P.p8';
        $p8key = file_get_contents($privateKeyPath);

        // $url = "https://api.sandbox.push.apple.com:443";
        // Swap this out when deploying.
        $url = "https://api.push.apple.com:443";


        $payload = [
            "iss" => "6N52UUJQBG",
            "iat" => time()
        ];



        $headers = array(
            'kid' => "B9GG868T6P"
        );
        $jwt = JWT::encode($payload, $p8key, 'ES256', null, $headers);

        $msg = array
        (
            'body' => $message,
            'title' => $title,
            'subtitle' => $subtitle,
            'badge' => "1",
            'sound' => 'default' /*Default sound*/
        );

        $fields = ["aps" => ["alert" => $msg, "mutable-content" => "1"]];
        $apnsUrl = $url . '/3/device/' . $token;
        $headers = array(
            "apns-push-type: alert",
            "Authorization: bearer $jwt",
            'Content-Type: application/json',
            "apns-topic: com.miromie.ios.miromie"
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $apnsUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);

        curl_exec($ch);

        curl_close($ch);
    }

    protected function sendAndroidPush($token, $message = '', $title = APP_NAME)
    {
        $privateKeyPath = __DIR__ . '/miromie-firebase-service-account.json';
        $credentials = new ServiceAccountCredentials(
            ["https://www.googleapis.com/auth/firebase.messaging"],
            $privateKeyPath
        );
        $token = $credentials->fetchAuthToken();
        $jwt = $token['access_token'];

        $headers = array(
            "Authorization: bearer $jwt",
            'Content-Type: application/json',
        );
        $url = "https://fcm.googleapis.com/v1/projects/myproject-b5ae1/messages:send";

        $msg = array
        (
            'body' => $message,
            'title' => $title,
            'subtitle' => $message,
        );

        $fields = ["message" => ["notification" => $msg, "token" => $token]];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);

        curl_exec($ch);

        curl_close($ch);
    }

    public function sendNotification($targetId, $message = '', $title = APP_NAME, $subtitle = '')
    {
        $deviceModel = new DeviceModel();
        $pushInfo = $deviceModel->getPushInfo($targetId);

        if ($pushInfo == "" || $pushInfo == null || !is_array($pushInfo)) {
            return;
        }

        $platform = $pushInfo['platform'];
        $token = $pushInfo['device_push_token'];
        if ($platform == "ios") {
            $this->sendIosPush($token, $message, $title, $subtitle);
        }
        if ($platform == "android") {
            $this->sendAndroidPush($token, $message, $title, );
        }



    }
}
