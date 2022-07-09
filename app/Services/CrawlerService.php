<?php

namespace App\Services;

use App\Traits\CrawlerHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class CrawlerService
{
    use CrawlerHelper;

    /**
     * Endpoint URL
     * @var string
     */
    private $endpointURL = 'http://applicant-test.us-east-1.elasticbeanstalk.com/';

    /**
     * Init Crawler
     * @return string
     */
    public function initCrawler()
    {
        $response = Http::get($this->endpointURL);

        $cookie = Arr::first($response->cookies()->toArray());
        $DOM = $this->loadHTML($response->body());

        $token = $DOM->getElementById('token')->getAttribute('value');
        $splitToken = str_split($token, 1);

        foreach($splitToken as $key => $character) {
            $splitToken[$key] = Arr::get($this->getReplacements(), $character, $character);
        }

        $response = Http::withHeaders([
            'Referer' => $this->endpointURL,
        ])
        ->withCookies([
            'PHPSESSID' => $cookie['Value'],
        ], $cookie['Domain'])
        ->asForm()
        ->post($this->endpointURL, [
            'token' => implode('', $splitToken)
        ]);

        $DOM = $this->loadHTML($response->body());

        $value = $DOM->getElementById('answer')->textContent;

        return "RESPOSTA: {$value}";
    }

    /**
     * @param $html
     * @return \DOMDocument
     */
    public function loadHTML($html)
    {
        $DOM = new \DOMDocument;
        $DOM->loadHTML($html);

        return $DOM;
    }
}
