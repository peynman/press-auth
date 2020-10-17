<?php

namespace Larapress\Auth\Signup;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SignupEvent implements ShouldQueue
{
    use Dispatchable, SerializesModels;

    /** @var \Larapress\Profiles\IProfileUser */
    public $user;
    /** @var \Larapress\Profiles\Models\Domain */
    public $domain;
    /** @var string */
    public $ip;
    /** @var int */
    public $timestamp;
    /** @var int */
    public $introducer;

    /**
     * Create a new event instance.
     *
     * @param $user
     * @param $domain
     * @param $ip
     * @param $timestamp
     */
    public function __construct($user, $domain, $introducer, $ip, $timestamp)
    {
        $this->user = $user;
        $this->domain = $domain;
        $this->ip = $ip;
        $this->timestamp = $timestamp;
        $this->introducer = $introducer;
    }
}
