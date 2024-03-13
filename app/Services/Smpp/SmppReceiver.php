<?php

namespace App\Services\Smpp;
use SmppClient;
use SmppDeliveryReceipt;
use SocketTransport;
use Illuminate\Support\Facades\Log;

class SmppReceiver
{
    protected $transport, $client, $transmitter;

    public function start() {
        
        $this->connect();
        $this->readSms();
    }

    protected function connect() {
        
        try {
            $this->transport = new SocketTransport([config('smpp.smpp_service')], (int) config('smpp.smpp_port'));
            $this->transport->setRecvTimeout(30000);
            $this->transport->setSendTimeout(30000);

            $this->client = new SmppClient($this->transport);
            $this->client->debug = true;
            $this->transport->debug = true;

            $this->transport->open();

            $bindResult = $this->client->bindReceiver(config('smpp.smpp_receiver_id'), config('smpp.smpp_receiver_password'));
        } catch (\Exception $e) {
            Log::error('SMPP Connection Error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function disconnect() {
        if (isset($this->transport) && $this->transport->isOpen()) {
            if (isset($this->client)) {
                try {
                   $this->client->close();
                } catch (\Exception $e) {
                   $this->transport->close();
                }
            } else {
                $this->transport->close();
            }
        }
    }

    protected function keepAlive() {
        $this->client->enquireLink();
       $this->client->respondEnquireLink();
    }

    protected function readSms() {
        $time_start = microtime(true);
        $endtime = $time_start + 43200; 
        $lastTime = 0;
        
        do {
            $res = $this->client->readSMS();
            // dd($res);
            if ($res) {
                try {
                    if ($res instanceof SmppDeliveryReceipt) {
                    }
                    else {
                        $from = $res->source->value;     
                        $to = $res->destination->value;
                        $message = $res->message;
                        dd($from, $message, $to);
                    }
                } catch (\Exception $e) {
                    Log::error($e);
                }
                
                $lastTime = time();
            } else {
                $this->client->respondEnquireLink();
            }
        } while ($endtime > microtime(true));
    }
}