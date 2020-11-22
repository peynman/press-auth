<?php

namespace Larapress\Auth\Signup;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Larapress\ECommerce\IECommerceUser;
use Larapress\Profiles\IProfileUser;
use Larapress\Profiles\Models\Domain;

class SignupEvent implements ShouldQueue
{
    use Dispatchable, SerializesModels;

    /** @var int */
    public $userId;
    /** @var int */
    public $domainId;
    /** @var int */
    public $supportId;
    /** @var string */
    public $ip;
    /** @var int */
    public $timestamp;
    /** @var int */
    public $introducerId;

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
        $this->supportId = $user->getSupportUserId();
        $this->introducerId = is_numeric($introducer) || is_null($introducer) ? $introducer : $introducer->id;
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

    /**
     * Undocumented function
     *
     * @return IProfileUser
     */
    public function getUser(): IProfileUser {
        return call_user_func([config('larapress.crud.user.class'), "find"], $this->userId);
    }


    /**
     * Undocumented function
     *
     * @return IProfileUser
     */
    public function getIntroducer() {
        if (is_null($this->introducerId)) {
            return null;
        }
        return call_user_func([config('larapress.crud.user.class'), "find"], $this->introducerId);
    }
}
