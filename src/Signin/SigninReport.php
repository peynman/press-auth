<?php

namespace Larapress\Auth\Signin;

use Illuminate\Support\Facades\Log;
use Larapress\CRUD\Services\IReportSource;
use Larapress\Reports\Services\BaseReportSource;
use Larapress\Reports\Services\IReportsService;

class SigninReport implements IReportSource
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
            'domain' => $event->domain->id,
        ];
        $this->reports->pushMeasurement('user.signin', 1, $tags, [], $event->timestamp);
    }
}
