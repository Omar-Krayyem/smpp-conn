<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StartSmpp extends Command
{
    protected $signature = 'smpp:start-receiver';
    protected $description = 'Start SMPP Receiver';
    
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $receiver = new \App\Services\Smpp\SmppReceiver();
        $receiver->start();
    }
}
