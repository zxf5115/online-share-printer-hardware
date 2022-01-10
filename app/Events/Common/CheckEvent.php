<?php
namespace App\Events\Common;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * 打印机校验事件
 */
class CheckEvent
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $printer_id = null;

  public $client_id = null;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct($printer_id, $client_id)
  {
    $this->printer_id = $printer_id;

    $this->client_id = $client_id;
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
