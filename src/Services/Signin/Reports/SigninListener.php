<?php

namespace Larapress\Auth\Services\Signin\Reports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Larapress\Auth\Services\Signin\SigninEvent;
use Larapress\Reports\Services\Reports\IMetricsService;

class SigninListener implements ShouldQueue
{
    const KEY = 'auth.signin';

    public function __construct(public IMetricsService $metrics)
    {
    }

    /**
     * Undocumented function
     *
     * @param SigninEvent $event
     *
     * @return void
     */
    public function handle(SigninEvent $event)
    {
        if (config('larapress.auth.reports.signin')) {
            $this->metrics->pushMeasurement(
                $event->domainId,
                $event->userId,
                null,
                $event->getUser()->getMembershipGroupIds(),
                config('larapress.auth.reports.signin'),
                config('larapress.auth.reports.group'),
                self::KEY,
                1,
                null,
                $event->timestamp
            );
        }
    }
}
