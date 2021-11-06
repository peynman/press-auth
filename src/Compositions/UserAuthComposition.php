<?php

namespace Larapress\Auth\Compositions;

use Larapress\Auth\Services\Signin\Reports\SigninReport;
use Larapress\Auth\Services\Signup\Reports\SignupReport;
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
            SigninReport::NAME => SigninReport::class,
            SignupReport::NAME => SignupReport::class,
        ]);
    }
}
