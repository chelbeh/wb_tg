<?php

namespace App\Http\Controllers;


use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class WbOrdersController extends Controller
{
    public function find($dateFrom): PromiseInterface|Response
    {
       return Http::retry(3, 5000)->withHeaders(['Authorization' => config('services.wb-bot-api.token')])
            ->throw()->get("https://statistics-api.wildberries.ru/api/v1/supplier/orders?dateFrom=$dateFrom");
    }
}
