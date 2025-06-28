<?php

namespace App\Http\Controllers\Gateway\PipraPay;

use App\Models\Deposit;
use App\Models\GatewayCurrency;
use App\Http\Controllers\Gateway\PaymentController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class ProcessController extends Controller
{
    public static function process($deposit)
    {
        $gateway_currency = $deposit->gatewayCurrency();
        $piprapayParams = json_decode($gateway_currency->gateway_parameter);

        $requestData = [
            'full_name'     => optional($deposit->user)->username ?? "Alis Danyal",
            'email_mobile'  => optional($deposit->user)->email ?? "alis@example.com",
            'amount'        => round($deposit->final_amo, 2),
            'currency'      => $piprapayParams->currency,
            'metadata'      => [
                'invoiceid' => $deposit->trx,
            ],
            'redirect_url'  => route(gatewayRedirectUrl()),
            'return_type'   => 'GET',
            'cancel_url'    => route(gatewayRedirectUrl()),
            'webhook_url'   => route('ipn.'.$deposit->gateway->alias),
        ];

        try {
            $redirect_url = self::initPayment($requestData, $piprapayParams);
            $send['redirect'] = true;
            $send['redirect_url'] = $redirect_url;
        } catch (Exception $e) {
            $send['error'] = true;
            $send['message'] = $e->getMessage();
        }

        return json_encode($send);
    }

    public function ipn(Request $request)
    {
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);

        // Log IPN if needed
        // file_put_contents(storage_path('logs/piprapay-ipn.log'), $raw.PHP_EOL, FILE_APPEND);

        $upAcc = GatewayCurrency::where('gateway_alias', 'PipraPay')->orderBy('id', 'desc')->first();
        $piprapayParams = json_decode($upAcc->gateway_parameter);

        if (!isset($data['pp_id']) || empty($data['pp_id'])) {
            return response('Invalid callback', 400);
        }

        try {
            $verify = self::verifyPayment($data['pp_id'], $piprapayParams);

            if (isset($verify['status']) && strtolower($verify['status']) === 'completed') {
                $deposit = Deposit::where('trx', $verify['metadata']['invoiceid'])->first();
                if ($deposit && $deposit->status == 0) {
                    PaymentController::userDataUpdate($deposit);
                }
            }
        } catch (Exception $e) {
            // Log or handle the exception
        }

        return response('OK', 200);
    }

    public static function initPayment($requestData, $piprapayParams)
    {
        $url = rtrim($piprapayParams->api_url, '/') . '/api/create-charge';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($requestData),
            CURLOPT_HTTPHEADER => [
                "mh-piprapay-api-key: " . $piprapayParams->api_key,
                "accept: application/json",
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new Exception("cURL Error #:" . $err);
        }

        $result = json_decode($response, true);
        if (isset($result['status']) && isset($result['pp_url'])) {
            return $result['pp_url'];
        }

        throw new Exception($result['message'] ?? 'Unexpected error from PipraPay');
    }

    public static function verifyPayment($pp_id, $piprapayParams)
    {
        $url = rtrim($piprapayParams->api_url, '/') . '/api/verify-payments';

        $payload = ['pp_id' => $pp_id];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "mh-piprapay-api-key: " . $piprapayParams->api_key,
                "accept: application/json",
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new Exception("cURL Error #:" . $err);
        }

        return json_decode($response, true);
    }
}