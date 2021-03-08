<?php

namespace Larapress\Auth\Signin;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Larapress\ECommerce\IECommerceUser;
use Larapress\Profiles\IProfileUser;
use Larapress\Profiles\Models\Domain;
use Carbon\Carbon;

class SigninEvent implements ShouldQueue
{
    use Dispatchable, SerializesModels;

    /** @var int */
    public $userId;
    /** @var int */
    public $supportId;
    /** @var int */
    public $domainId;
    /** @var string */
    public $ip;
    /** @var int */
    public $timestamp;
    /** @var int */
    public $userDaysAfterSignup;

    /**
     * Create a new event instance.
     *
     * @param $user
     * @param $domain
     * @param $ip
     * @param $timestamp
     */
    public function __construct(IECommerceUser $user, $domain, $ip, $timestamp)
    {
        $this->userId = $user->id;
        $this->userDaysAfterSignup = Carbon::now()->diffInDays($user->created_at);
        $this->supportId = $user->getSupportUserId();
        $this->domainId = is_numeric($domain) || is_null($domain) ? $domain : $domain->id;
        $this->ip = $ip;
        $this->timestamp = $timestamp;
    }


    /**
     * @return Domain
     */
    public function getDomain(): Domain
    {
        return is_null($this->domainId) ? null : Domain::find($this->domainId);
    }

    public function getUser(): IProfileUser
    {
        return call_user_func([config('larapress.crud.user.class'), "find"], $this->userId);
    }
}
