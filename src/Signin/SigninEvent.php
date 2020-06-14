<?php

namespace Larapress\Auth\Signin;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SigninEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var \Larapress\Profiles\IProfileUser */
    public $user;
    /** @var \Larapress\Profiles\Models\Domain */
    public $domain;
    /** @var string */
    public $ip;

    /**
     * Create a new event instance.
     *
     * @param $user
     * @param $domain
     * @param $ip
     */
    public function __construct($user, $domain, $ip)
    {
        $this->user = $user;
        $this->domain = $domain;
        $this->ip = $ip;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel(config('larapress.crud.events.channel'));
    }
}
