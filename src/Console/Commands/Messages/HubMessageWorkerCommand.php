<?php

namespace BildVitta\SpHub\Console\Commands\Messages;

use BildVitta\SpHub\Console\Commands\Messages\Resources\MessageProcessor;
use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPExceptionInterface;

class HubMessageWorkerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmqworker:hub';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets and processes messages';

    /**
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

    private MessageProcessor $messageProcessor;

    public function __construct(MessageProcessor $messageProcessor)
    {
        parent::__construct();
        $this->messageProcessor = $messageProcessor;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        while (true) {
            try {
                $this->process();
            } catch (AMQPExceptionInterface $exception) {
                $this->closeChannel();
                $this->closeConnection();
                sleep(5);
            }
        }

        return 0;
    }

    private function process(): void
    {
        $this->connect();
        $this->channel = $this->connection->channel();

        $queueName = config('sp-hub.rabbitmq.queue.hub');
        $callback = [$this->messageProcessor, 'process'];

        $qos = config('sp-hub.rabbitmq.qos', false);
        if ($qos) {
            $qos = (int) $qos;
            $this->channel->basic_qos(null, $qos, null);
        }

        $this->channel->basic_consume(
            queue: $queueName,
            callback: $callback
        );

        $this->channel->consume();

        $this->closeChannel();
        $this->closeConnection();
    }

    private function closeChannel(): void
    {
        try {
            if ($this->channel) {
                $this->channel->close();
            }
        } catch (Exception $exception) {
        }
    }

    private function closeConnection(): void
    {
        try {
            if ($this->connection) {
                $this->connection->close();
            }
        } catch (Exception $exception) {
        }
    }

    private function connect(): void
    {
        $host = config('sp-hub.rabbitmq.host');
        $port = config('sp-hub.rabbitmq.port');
        $user = config('sp-hub.rabbitmq.user');
        $password = config('sp-hub.rabbitmq.password');
        $virtualhost = config('sp-hub.rabbitmq.virtualhost');
        $heartbeat = (int) config('sp-hub.rabbitmq.heartbeat', 60);
        $sslOptions = [
            'verify_peer' => false,
        ];
        $options = [
            'heartbeat' => $heartbeat,
        ];

        $useSsl = config('sp-hub.rabbitmq.use_ssl', true);
        if (app()->isLocal()) {
            $useSsl = false;
        }

        if ($useSsl) {
            $this->connection = new AMQPSSLConnection(
                host: $host,
                port: $port,
                user: $user,
                password: $password,
                vhost: $virtualhost,
                ssl_options: $sslOptions,
                options: $options
            );

            return;
        }

        $this->connection = new AMQPStreamConnection(
            host: $host,
            port: $port,
            user: $user,
            password: $password,
            vhost: $virtualhost,
            heartbeat: $heartbeat
        );
    }
}
