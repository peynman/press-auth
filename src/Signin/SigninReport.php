<?php

namespace Larapress\Auth\Signin;

use Illuminate\Contracts\Queue\ShouldQueue;
use Larapress\CRUD\Services\IReportSource;
use Larapress\Reports\Services\BaseReportSource;
use Larapress\Reports\Services\IReportsService;

class SigninReport implements IReportSource, ShouldQueue
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
            'users.signin.total' => function ($user, array $options = []) {
                [$filters, $fromC, $toC, $groups] = $this->getCommonReportProps($user, $options);
                return $this->reports->queryMeasurement(
                    'user.signin',
                    $filters,
                    $groups,
                    array_merge(["_value"], $groups),
                    $fromC,
                    $toC,
                    'count()'
                );
            },
            'users.signin.windowed' => function ($user, array $options = []) {
                [$filters, $fromC, $toC, $groups] = $this->getCommonReportProps($user, $options);
                $window = isset($options['window']) ? $options['window'] : '1h';
                return $this->reports->queryMeasurement(
                    'user.signin',
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

    public function handle(SigninEvent $event)
    {
        $tags = [
            'domain' => $event->domainId,
            'support' => $event->supportId,
            'user' => $event->userId,
            'user_history' => $event->userDaysAfterSignup,
        ];
        $this->reports->pushMeasurement('user.signin', 1, $tags, [], $event->timestamp);
    }
}
