<?php

namespace Yonidebleeker\UpStats\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Yonidebleeker\UpStats\Http\Models\Visitor;
use Yonidebleeker\UpStats\Http\Models\Pagevisit;
use Yonidebleeker\UpStats\Http\Models\Page;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpStatsController extends Controller
{
    /*
    * Fetch data for the dashboard
    */
    public function getDashboardData(Request $request)
    {
        // Retrieve start and end dates from the request
        $input_start_date = $request->start_date ?? null; //2024-05-15
        $input_end_date = $request->end_date ?? null;

        if ($input_start_date === null) {
            $start_date = today()->subMonth(1)->format('Y-m-d');
            $end_date = today()->addDay()->format('Y-m-d');
        } else {
            $start_date = Carbon::createFromFormat('Y-m-d', $input_start_date)->setTime(0, 0);
            $end_date = Carbon::createFromFormat('Y-m-d', $input_end_date)->addDay(1)->setTime(0, 0);
        }

        // Get data for the previous period
        $previousPeriod = $this->getPreviousPeriod($start_date, $end_date);

        // Fetch page views data
        $mostPageViews = $this->getPageViews('desc', $start_date, $end_date);
        $leastPageViews = $this->getPageViews('asc', $start_date, $end_date);

        // Fetch average visitor data
        $averageVisitorsEachDay = $this->getAverageVisitors($start_date, $end_date);
        $previousPeriodAverageVisitorsEachDay = $this->getAverageVisitors($previousPeriod['start_date'], $previousPeriod['end_date']);

        // Fetch bounce rate data
        $bounce_rate = $this->getBounceRate($start_date, $end_date);
        $previousPeriodBounceRate = $this->getBounceRate($previousPeriod['start_date'], $previousPeriod['end_date']);

        // Fetch average time data
        $average_time = $this->getAverageTime($start_date, $end_date);
        $previousPeriodAverageTime = $this->getAverageTime($previousPeriod['start_date'], $previousPeriod['end_date']);

        // Fetch desktop and mobile visitor percentage
        $desktop_and_mobile_visitors = $this->getPercentageDesktopMobile($start_date, $end_date);
        $previousPeriodDesktopAndMobileVisitors = $this->getPercentageDesktopMobile($previousPeriod['start_date'], $previousPeriod['end_date'], True);

        // Calculate and format previous period comparison metrics
        $previousPeriodComparison = [
            'averageVisitorsEachDay' => $previousPeriodAverageVisitorsEachDay != 0
                ? round((($averageVisitorsEachDay - $previousPeriodAverageVisitorsEachDay) / $previousPeriodAverageVisitorsEachDay) * 100, 2)
                : 0,
            'bounceRate' => $previousPeriodBounceRate != 0
                ? round(($bounce_rate - $previousPeriodBounceRate) / $previousPeriodBounceRate * 100, 2)
                : 0,
            'averageTime' => $previousPeriodAverageTime != 0
                ? round(($average_time - $previousPeriodAverageTime) / $previousPeriodAverageTime * 100, 2)
                : 0,
            'desktopAndMobileVisitors' => [
                'desktop' => $previousPeriodDesktopAndMobileVisitors['desktop'] !== null
                    ? ($desktop_and_mobile_visitors['desktop'] - $previousPeriodDesktopAndMobileVisitors['desktop'])
                    : 0,
                'mobile' => $previousPeriodDesktopAndMobileVisitors['mobile'] !== null
                    ? ($desktop_and_mobile_visitors['mobile'] - $previousPeriodDesktopAndMobileVisitors['mobile'])
                    : 0,
            ],
        ];

        // Fetch other relevant data
        $visitorsEachDay = $this->getVisitors($start_date, $end_date);
        $source = $this->getSource($start_date, $end_date);

        // Return the dashboard view with the fetched data if there is no start and end date in the request the data will be fetched for the last month
        return view('upstats::dashboard', [
            'mostPageViews' => $mostPageViews,
            'leastPageViews' => $leastPageViews,
            'averageVisitorsEachDay' => $averageVisitorsEachDay,
            'bounce_rate' => $bounce_rate,
            'average_time' => $average_time,
            'desktop_and_mobile_visitors' => $desktop_and_mobile_visitors,
            'previousPeriodComparison' => $previousPeriodComparison,
            'start_date' => $input_start_date,
            'end_date' => $input_end_date,
            'visitorsEachDay' => $visitorsEachDay,
            'source' => $source,
        ]);
    }


    /**
     * Get the top 5 pages with the most or least views
     */
    private function getPageViews($orderBy = 'desc', $start_date, $end_date)
    {
        $query = Pagevisit::query()->with('page')
            ->select('page_id', DB::raw('COUNT(*) as count'));

        if ($start_date && $end_date) {
             $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $query->groupBy('page_id')
            ->orderByRaw('COUNT(*) ' . strtoupper($orderBy))
            ->limit(6);

        $pageViews = $query->get();
        return $this->transformPageViews($pageViews);
    }

    /**
     * Transform the page views to a more readable format
     */
    private function transformPageViews($pageViews)
    {
        return $pageViews->map(function ($pageView) {
            return [
                'name' => $pageView->page->url,
                'count' => $pageView->count,
            ];
        });
    }

    /**
     * Get the average number of visitors in a specific time frame each day
     */
    private function getAverageVisitors($start_date, $end_date)
    {
        $query = Visitor::query()
        ->select(DB::raw('COUNT(*) as count'))
        ->groupBy(DB::raw('DATE(created_at)'));

        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $result = $query->first();

        return $result ? $result->count : 0;
    }

    /**
     * Get the percentage of desktop and mobile visitors
     */
    private function getVisitorsByDeviceType($deviceType, $start_date, $end_date)
    {
        $query = Visitor::query()
            ->where('device_type', $deviceType);

        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        return $query->count();
    }

    /**
     * Get the percentage of desktop and mobile visitors
     */
    private function getPercentageDesktopMobile($start_date, $end_date, $previousPeriod = False)
    {
        $desktopVisitors = $this->getVisitorsByDeviceType('desktop', $start_date, $end_date);
        $mobileVisitors = $this->getVisitorsByDeviceType('mobile', $start_date, $end_date);

        $totalVisitors = $desktopVisitors + $mobileVisitors;

        // Check if the previous period is set and the total visitors are zero
        if ($previousPeriod && $totalVisitors == 0) {
            return [
                'desktop' => null,
                'mobile' => null,
            ];
        }

        // Check if total visitors is zero to avoid division by zero
        if ($totalVisitors == 0) {
            return ['desktop' => 0, 'mobile' => 0];
        }

        return [
            'desktop' => round(($desktopVisitors / $totalVisitors) * 100, 2),
            'mobile' => round(($mobileVisitors / $totalVisitors) * 100, 2),
        ];
    }

    /**
     * Get the average visit time of the website
     */
    private function getAverageTime($start_date, $end_date)
    {
        // Define the base query
        $query = Pagevisit::query()
            ->select('visitor_id', 'created_at')
            ->orderBy('visitor_id')
            ->orderBy('created_at');

        // Apply date range if specified
        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        // Fetch the results
        $pageVisits = $query->get();

        $visitorVisitTimes = [];

        // Group visits by visitor_id
        $pageVisitsGrouped = $pageVisits->groupBy('visitor_id');

        foreach ($pageVisitsGrouped as $visitorId => $visits) {
            if ($visits->count() > 1) {
                $firstVisit = $visits->first();
                $lastVisit = $visits->last();
                $visitorVisitTimes[] = $firstVisit->created_at->diffInMinutes($lastVisit->created_at);
            }
        }

        // Calculate the average time
        $totalVisitors = count($visitorVisitTimes);
        $averageTime = $totalVisitors > 0 ? array_sum($visitorVisitTimes) / $totalVisitors : 0;

        return round($averageTime, 2);
    }



    private function getBounceRate($start_date, $end_date)
    {
        // Define the base query
        $query = Pagevisit::query()
            ->selectRaw('visitor_id, COUNT(*) as visit_count')
            ->groupBy('visitor_id');

        // Apply date range if specified
        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        // Get total number of unique visitors
        $totalVisitors = $query->get()->count();

        // Get the number of visitors with only one page visit
        $singlePageVisits = $query->having('visit_count', '=', 1)->get()->count();

        // Ensure to handle the case where totalVisitors is 0 to avoid division by zero
        $bounceRate = ($totalVisitors > 0) ? ($singlePageVisits / $totalVisitors) : 0;
        $bounceRate = round($bounceRate * 100, 2);
        return $bounceRate;
    }

    /**
     * Get the number of visitors each day
     */
    private function getVisitors($start_date, $end_date)
    {
        $query = Visitor::query()->selectRaw('DATE(created_at) as date, COUNT(*) as visitor_count');

        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $visitors = $query->groupBy('date')->get();

        return $visitors;
    }

    /**
     * Get the source of the visitors
     */
    private function getSource($start_date, $end_date)
    {
        $query = Visitor::query()->select('source', DB::raw('COUNT(*) as count'));

        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $query->groupBy('source');

        return $query->take(5)->get();
    }

    /**
     * Get the start and end dates of the period before the current period
     */
    private function getPreviousPeriod($start_date, $end_date)
    {
        if ($start_date && $end_date) {
            $diff = strtotime($end_date) - strtotime($start_date);

            $diff_days = round($diff / (60 * 60 * 24));

            // Getting the start and end dates of the period before the current period
            $start_date_before = date('Y-m-d', strtotime($start_date . ' -' . $diff_days . ' days'));
            $end_date_before = date('Y-m-d', strtotime($end_date . ' -' . $diff_days . ' days'));
        } else {
            $start_date_before = today()->subMonth(2);
            $end_date_before = today()->subMonth(1);
        }

        return [
            'start_date' => $start_date_before,
            'end_date' => $end_date_before
        ];
    }
}
