<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'base' => 'required|string',
            'currency' => 'required|string',
        ]);

        $client = new Client();
        $response = $client->get("http://api.exchangeratesapi.io/latest");
        $responseBody = json_decode($response->getBody(), true);

        $responseCurrencies = $responseBody['rates'];
        $responseCurrencies[$responseBody['base']] = 1;

        $currencies = explode(',', $request->currency);
        $base = $request->base;

        if (!array_key_exists($base, $responseCurrencies)) {
            return response()->json(['message' => 'the base currency does not exist'], 422);
        }

        //return values
        $returnCurrencies = array();

        foreach ($currencies as $currency) {
            if (!array_key_exists($currency, $responseCurrencies) || $currency == $base) {
                continue;
            }
            $returnCurrencies[$currency] = $responseCurrencies[$currency] / $responseCurrencies[$base];
        }

        $returnValue = [
            'results' => [
                'base' => $base,
                'date' => Carbon::now()->format('Y-m-d'),
                'rates' => $returnCurrencies,
            ],

        ];

        return response()->json($returnValue, 200);
    } //end method index
} //end class HomeController
