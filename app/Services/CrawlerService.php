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

        $token = $this->encryptToken($this->getToken($response));

        $response = Http::withHeaders([
            'Referer' => $this->endpointURL,
        ])
        ->withCookies([
            'PHPSESSID' => $this->getCookieAttribute($response->cookies()->toArray(), 'Value'),
        ], $this->getCookieAttribute($response->cookies()->toArray(), 'Domain'))
        ->asForm()
        ->post($this->endpointURL, [
            'token' => implode('', $token)
        ]);

        return "RESPOSTA: {$this->getAnswer($response)}";
    }

    /**
     * Get Answer from Web Page
     * @param $response
     * @return string
     */
    public function getAnswer($response)
    {
        $DOM = $this->loadHTML($response->body());

        return $DOM->getElementById('answer')->textContent;
    }

    /**
     * Get Token from Web Page
     * @param $response
     * @return string
     */
    public function getToken($response)
    {
        $DOM = $this->loadHTML($response->body());

        $token = $DOM->getElementById('token')->getAttribute('value');

        return $token;
    }

    /**
     * Encrypt Web Page Token
     * @param $token
     * @return array
     */
    public function encryptToken($token)
    {
        $splitToken = str_split($token, 1);

        foreach($splitToken as $key => $character) {
            $splitToken[$key] = Arr::get($this->getReplacements(), $character, $character);
        }

        return $splitToken;
    }

    /**
     * Get Cookie Attribute
     * @param $cookie
     * @param $attribute
     * @return mixed
     */
    public function getCookieAttribute($cookie, $attribute)
    {
        return Arr::get(Arr::first($cookie), $attribute);
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
