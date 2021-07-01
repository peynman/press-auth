<?php

namespace Larapress\Auth\Signin;

use Illuminate\Contracts\Queue\ShouldQueue;
use Larapress\CRUD\Services\CRUD\ICRUDReportSource;
use Larapress\Reports\Services\Reports\ReportSourceTrait;
use Larapress\Reports\Services\Reports\IMetricsService;
use Larapress\Reports\Services\Reports\IReportsService;
use Larapress\Reports\Services\Reports\MetricsSourceProperties;

class SigninReport implements ICRUDReportSource, ShouldQueue
{
    const MEASUREMENT_TYPE = 'signin';

    use ReportSourceTrait;

    /** @var IReportsService */
    private $reports;

    /** @var IMetricsService */
    private $metrics;

    /** @var array */
    private $avReports;

    // start dot groups from 1 position_1.position_2.position_3...
    private $metricsDotGroups = [
        'user' => 2,
        'domain' => 'domain_id',
    ];

    public function __construct()
    {
        /** @var IReportsService */
        $this->reports = app(IReportsService::class);
        /** @var IMetricsService */
        $this->metrics = app(IMetricsService::class);

        $this->avReports = [
            'metrics.total.signin' => function ($user, array $options) {
                $props = MetricsSourceProperties::fromReportSourceOptions($user, $options, $this->metricsDotGroups);
                return $this->metrics->queryMeasurement(
                    'users\.[0-9]*\.signin$',
                    self::MEASUREMENT_TYPE,
                    null,
                    $props->filters,
                    $props->groups,
                    $props->domains,
                    $props->from,
                    $props->to
                );
            },
            'metrics.windowed.signin' => function ($user, array $options) {
                $props = MetricsSourceProperties::fromReportSourceOptions($user, $options, $this->metricsDotGroups);
                return $this->metrics->aggregateMeasurement(
                    'users\.[0-9]*\.signin$',
                    self::MEASUREMENT_TYPE,
                    null,
                    $props->filters,
                    $props->groups,
                    $props->domains,
                    $props->from,
                    $props->to,
                    $props->window
                );
            },
        ];
    }

    public function handle(SigninEvent $event)
    {

        if (config('larapress.reports.reports.reports_service')) {
            $tags = [
                'domain' => $event->domainId,
                'user' => $event->userId,
            ];
            $this->reports->pushMeasurement('users.signin', 1, $tags, [
                'user_history' => $event->userDaysAfterSignup,
            ], $event->timestamp);
        }

        if (config('larapress.reports.reports.metrics_table')) {
            $this->metrics->pushMeasurement(
                $event->domainId,
                self::MEASUREMENT_TYPE,
                'user:'.$event->userId,
                'users.'.$event->userId.'.signin',
                1,
                $event->timestamp
            );
        }
    }
}
