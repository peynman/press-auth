<?php

namespace Larapress\Auth\Signin;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Larapress\ECommerce\IECommerceUser;
use Larapress\Profiles\IProfileUser;
use Carbon\Carbon;

class SigninEvent implements ShouldQueue
{
    use Dispatchable, SerializesModels;

    /** @var int */
    public $userId;
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
        $this->domainId = is_numeric($domain) || is_null($domain) ? $domain : $domain->id;
        $this->ip = $ip;
        $this->timestamp = is_numeric($timestamp) ? $timestamp : $timestamp->getTimestamp();
    }

    /**
     * Undocumented function
     *
     * @return IProfileUser
     */
    public function getUser(): IProfileUser
    {
        return call_user_func([config('larapress.crud.user.model'), "find"], $this->userId);
    }
}
