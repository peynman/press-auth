<?php

namespace Larapress\Auth\Compositions;

use Larapress\Auth\Signin\Reports\SigninWindowedReport;
use Larapress\Auth\Signup\Reports\SignupWindowedReport;
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
            SigninWindowedReport::NAME => SigninWindowedReport::class,
            SignupWindowedReport::NAME => SignupWindowedReport::class,
        ]);
    }
}
