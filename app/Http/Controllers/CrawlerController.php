<?php

namespace App\Http\Controllers;

use App\Services\CrawlerService;

class CrawlerController extends Controller
{
    /**
     * @var CrawlerService
     */
    private $crawlerService;

    /**
     * CrawlerController constructor.
     * @param CrawlerService $crawlerService
     */
    public function __construct(CrawlerService $crawlerService)
    {
        $this->crawlerService = $crawlerService;
    }

    public function index()
    {
        return $this->crawlerService->initCrawler();
    }
}

