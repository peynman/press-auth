<?php

namespace Larapress\Auth\Repository;

use Carbon\Carbon;
use Larapress\Reports\Services\IReportsService;

class SignupReportRepository implements ISignupReportRepository {

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @return array
     */
    public function getSigninPerDomain($user, $from, $to) {
        /** @var IReportsService $service */
        $service = app(IReportsService::class);

        $filters = [];

        $fromC = null;
        $toC = null;

        return $service->queryMeasurement(
            'user.signin',
            $filters,
            ["domain"],
            ["domain", "_value"],
            $fromC,
            $toC,
            'count()'
        );
    }


    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @return array
     */
    public function getSignupPerDomain($user) {

    }
}
