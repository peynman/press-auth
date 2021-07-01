<?php

namespace Larapress\Auth\Signup;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Larapress\ECommerce\IECommerceUser;
use Larapress\Profiles\IProfileUser;

class SignupEvent implements ShouldQueue
{
    use Dispatchable, SerializesModels;

    /** @var int */
    public $userId;
    /** @var int */
    public $domainId;
    /** @var int */
    public $introducer;
    /** @var string */
    public $ip;
    /** @var int */
    public $timestamp;

    /**
     * Create a new event instance.
     *
     * @param $user
     * @param $domain
     * @param $ip
     * @param $timestamp
     */
    public function __construct(IECommerceUser $user, $domain, $introducer, $ip, $timestamp)
    {
        $this->userId = $user->id;
        $this->domainId = is_numeric($domain) || is_null($domain) ? $domain : $domain->id;
        $this->ip = $ip;
        $this->introducer = $introducer;
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

    /**
     * Undocumented function
     *
     * @return int
     */
    public function getIntroducerID() {
        return $this->introducer;
    }
}
