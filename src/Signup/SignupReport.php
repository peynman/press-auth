<?php

namespace Larapress\Auth\Signup;

use Illuminate\Contracts\Queue\ShouldQueue;
use Larapress\CRUD\Services\CRUD\ICRUDReportSource;
use Larapress\Reports\Services\Reports\IReportsService;
use Larapress\Reports\Services\Reports\ReportSourceTrait;
use Larapress\Reports\Services\Reports\IMetricsService;
use Larapress\Reports\Services\Reports\MetricsSourceProperties;

class SignupReport implements ICRUDReportSource, ShouldQueue
{
    const MEASUREMENT_TYPE = 'signup';

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
            'metrics.total.signup' => function ($user, array $options = []) {
                $props = MetricsSourceProperties::fromReportSourceOptions($user, $options, $this->metricsDotGroups);
                return $this->metrics->queryMeasurement(
                    'users\.[0-9]*\.signup$',
                    self::MEASUREMENT_TYPE,
                    null,
                    $props->filters,
                    $props->groups,
                    $props->domains,
                    $props->from,
                    $props->to
                );
            },
            'metrics.windowed.signup' => function ($user, array $options = []) {
                $props = MetricsSourceProperties::fromReportSourceOptions($user, $options, $this->metricsDotGroups);
                return $this->metrics->aggregateMeasurement(
                    'users\.[0-9]*\.signup$',
                    self::MEASUREMENT_TYPE,
                    null,
                    $props->filters,
                    $props->groups,
                    $props->domains,
                    $props->from,
                    $props->to,
                    $props->window
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
        if (config('larapress.reports.reports.reports_service')) {
            $tags = [
                'domain' => $event->domainId,
                'user' => $event->userId,
            ];
            $this->reports->pushMeasurement('users.signup', 1, $tags, [], $event->timestamp);
        }

        if (config('larapress.reports.reports.metrics_table')) {
            $this->metrics->pushMeasurement(
                $event->domainId,
                self::MEASUREMENT_TYPE,
                'user:'.$event->userId,
                'users.'.$event->userId.'.signup',
                1,
                $event->timestamp
            );
        }
    }
}
