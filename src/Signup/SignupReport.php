<?php

namespace Larapress\Auth\Signup;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Larapress\CRUD\Services\IReportSource;
use Larapress\Reports\Services\IReportsService;
use Larapress\Reports\Services\BaseReportSource;

class SignupReport implements IReportSource, ShouldQueue
{
    use BaseReportSource;

    /** @var IReportsService */
    private $reports;

    /** @var array */
    private $avReports;

    public function __construct(IReportsService $reports)
    {
        $this->reports = $reports;
        $this->avReports = [
            'users.signup.total' => function ($user, array $options = []) {
                [$filters, $fromC, $toC, $groups] = $this->getCommonReportProps($user, $options);
                return $this->reports->queryMeasurement(
                    'user.signup',
                    $filters,
                    $groups,
                    array_merge(["_value"], $groups),
                    $fromC,
                    $toC,
                    'count()'
                );
            },
            'users.signup.windowed' => function ($user, array $options = []) {
                [$filters, $fromC, $toC, $groups] = $this->getCommonReportProps($user, $options);
                $window = isset($options['window']) ? $options['window'] : '1h';
                return $this->reports->queryMeasurement(
                    'user.signup',
                    $filters,
                    $groups,
                    array_merge(["_value", "_time"], $groups),
                    $fromC,
                    $toC,
                    'aggregateWindow(every: '.$window.', fn: sum)'
                );
            }
        ];
    }

    /**
     * push the event to timeserieas database
     *
     * @param SignupEvent $event
     * @return void
     */
    public function handle(SignupEvent $event)
    {
        $tags = [
            'domain' => $event->domainId,
            'support' => $event->supportId,
            'user' => $event->userId,
        ];
        $this->reports->pushMeasurement('user.signup', 1, $tags, [], $event->timestamp);
    }
}
