<?php

namespace Digitalup\UpStats;

use Detection\MobileDetect;
use Illuminate\Http\Request;
use Digitalup\UpStats\Http\Models\Page;
use Digitalup\UpStats\Http\Models\Pagevisit;
use Digitalup\UpStats\Http\Models\Visitor;

class UpStats
{
    protected $detect;

    public function __construct()
    {
        $this->detect = new MobileDetect();
    }

    public function store()
    {
        $request = request();
        $visitor = Visitor::firstOrCreate([
            'cookie' => $this->substractCookie($request->headers->get('cookie')),
            'source' => $this->categorizeSource($request->headers->get('referer')),
            'device_type' => $this->isMobile() ? 'mobile' : 'desktop',
        ]);

        $page = Page::firstOrCreate([
            'url' => $request->url(),
        ]);

        Pagevisit::create([
            'page_id' => $page->id,
            'visitor_id' => $visitor->id,
        ]);

        return response()->json(['message' => 'Page visit stored']);
    }


    /**
     * Get if the visitor is on a mobile device
     */
    private function isMobile()
    {
        try {
            $isMobile = $this->detect->isMobile();
            return $isMobile;
        } catch (\Detection\Exception\MobileDetectException $e) {
            return null;
        }
    }

    /**
     * Substract the cookie from the request headers
     */
    private function substractCookie($cookie)
    {
        $cookies = explode(';', $cookie);
        $cookies = array_map('trim', $cookies);

        // Filter the cookies to find the one with the name 'visitor_id'
        $visitorIdCookie = array_filter($cookies, function ($cookie) {
            return strpos($cookie, 'upstats_user_cookie') !== false;
        });

        // If the 'visitor_id' cookie is found, return it; otherwise, return null
        return $visitorIdCookie ? reset($visitorIdCookie) : null;
    }

    /**
     * Get all page visits with their related page and visitor
     */
    public function getAll()
    {
        $pagevisits = Pagevisit::with('page', 'visitor')->get();
        return response()->json($pagevisits);
    }

    /**
     * Categorize the source of the visitor
     */
    private function categorizeSource($source)
    {
        if (!$source) {
            return 'Direct';
        }

        $categories = [
            'google' => 'Google',
            'facebook|twitter|linkedin|x\.com|instagram' => 'Social Media'
        ];

        // Check if the source matches any of the categories
        foreach ($categories as $pattern => $category) {
            if (preg_match('/' . $pattern . '/i', $source)) {
                return $category;
            }
        }

        return 'Other';
    }



}
