<?php

namespace Larapress\Auth\Services\Signin\Reports;

use Larapress\CRUD\ICRUDUser;
use Larapress\Reports\Services\Reports\ICRUDReportSource;
use Larapress\Reports\Services\Reports\IMetricsService;
use Larapress\Reports\Services\Reports\MetricsSourceHelper;
use Larapress\Reports\Services\Reports\ReportQueryRequest;

class SigninWindowedReport implements ICRUDReportSource
{
    use MetricsSourceHelper;

    const NAME = 'auth.signin.windowed';

    /**
     * Undocumented function
     *
     * @param IMetricsService $metrics
     */
    public function __construct(public IMetricsService $metrics)
    {
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function name(): string
    {
        return self::NAME;
    }

    /**
     * Undocumented function
     *
     * @param ICRUDUser $user
     * @param ReportQueryRequest $request
     * @return array
     */
    public function getReport(ICRUDUser $user, ReportQueryRequest $request): array
    {
        return $this->metrics->measurementQuery(
            $user,
            $request,
            config('larapress.auth.reports.group'),
            config('larapress.auth.reports.signin'),
            $request->getAggregateFunction(),
            $request->getAggregateWindow(),
        )
            ->get()
            ->toArray();
    }
}
