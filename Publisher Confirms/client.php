<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->confirm_select();

$channel->set_ack_handler(
    function (AMQPMessage $message) {
        echo " [✔] Message is confirmed!";
    }
);

$channel->set_nack_handler(
    function (AMQPMessage $message) {
        echo " [❌] Message is nack-ed!";
    }
);

$channel->queue_declare('hello', false, true, false, false);

$data = implode(' ', array_slice($argv, 1));
if (empty($data)) {
    $data = "Hello World!";
}
$msg = new AMQPMessage(
    $data,
    array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
);

$channel->basic_publish($msg, '', 'hello');

echo ' [✔] Sent ', $data, "\n";

$channel->wait_for_pending_acks(5.000);

$channel->close();
$connection->close();
