<?php

namespace Larapress\Auth\Signin;

use Larapress\Reports\Services\IReportsService;

class SigninReport {
    /** @var IReportsService */
    private $reports;
    public function __construct(IReportsService $reports)
    {
        $this->reports = $reports;
    }

    public function handle(SigninEvent $event)
    {
        $tags = [
            'user' => $event->user->id,
            'domain' => $event->domain->id,
            'ip' => $event->ip,
        ];
        $this->reports->pushMeasurement('user.signin', 1, $tags, [], time());
    }
}
