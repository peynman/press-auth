<?php

namespace Larapress\Auth\Repository;

use Larapress\Profiles\IProfileUser;

interface ISignupReportRepository
{
    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @return array
     */
    public function getSigninPerDomain($user);

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @return array
     */
    public function getSignupPerDomain($user);
}
