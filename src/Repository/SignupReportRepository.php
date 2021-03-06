<?php

namespace Larapress\Auth\Repository;

use Larapress\Reports\Services\Reports\IReportsService;

class SignupReportRepository implements ISignupReportRepository
{

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @return array
     */
    public function getSigninPerDomain($user)
    {
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
    public function getSignupPerDomain($user)
    {
    }
}
