<?php
namespace App\Events\Platform\Printer;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * 设备绑定事件
 */
class BindEvent
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $outbound_id = null;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct($outbound_id)
  {
    $this->outbound_id = $outbound_id;
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return \Illuminate\Broadcasting\Channel|array
   */
  public function broadcastOn()
  {
    return new PrivateChannel('channel-name');
  }
}
