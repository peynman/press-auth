<?php

namespace Larapress\Auth\Services\Signup\Reports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Larapress\Auth\Signup\SignupEvent;
use Larapress\Reports\Services\Reports\IMetricsService;

class SignupListener implements ShouldQueue
{
    const KEY = 'auth.signup';

    public function __construct(public IMetricsService $metrics)
    {
    }

    /**
     * push the event to timeserieas database
     *
     * @param SignupEvent $event
     *
     * @return void
     */
    public function handle(SignupEvent $event)
    {
        if (config('larapress.auth.reports.signup')) {
            $this->metrics->pushMeasurement(
                $event->domainId,
                $event->userId,
                null,
                $event->getUser()->getMembershipGroupIds(),
                config('larapress.auth.reports.signup'),
                config('larapress.auth.reports.group'),
                self::KEY,
                1,
                null,
                $event->timestamp

            );
        }
    }
}
