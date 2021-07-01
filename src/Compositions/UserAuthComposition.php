<?php

namespace Larapress\Auth\Compositions;

use Larapress\Auth\Signin\SigninReport;
use Larapress\Auth\Signup\SignupReport;
use Larapress\CRUD\Services\CRUD\CRUDProviderComposition;

class UserAuthComposition extends CRUDProviderComposition
{
    /**
     * Undocumented function
     *
     * @return array
     */
    public function getReportSources(): array
    {
        return array_merge($this->sourceProvider->getReportSources(), [
            new SigninReport(),
            new SignupReport(),
        ]);
    }
}
