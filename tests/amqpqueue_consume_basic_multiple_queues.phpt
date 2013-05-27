--TEST--
AMQPQueue::consume with return false on multiple queues.
--SKIPIF--
<?php if (!extension_loaded("amqp")) print "skip"; ?>
--FILE--
<?php
$cnn = new AMQPConnection();
$cnn->connect();

$ch = new AMQPChannel($cnn);

$ex1 = new AMQPExchange($ch);
$ex1->setName('test-multiple-queues-exchange1');
$ex1->setType(AMQP_EX_TYPE_DIRECT);
$ex1->declareExchange();

// Create a new queue
$q1 = new AMQPQueue($ch);
$q1->setName('test-multiple-queues-queue1');
$q1->declareQueue();

// Bind it on the exchange to routing.key
$q1->bind('test-multiple-queues-exchange1', 'rk1');

$ex2 = new AMQPExchange($ch);
$ex2->setName('test-multiple-queues-exchange2');
$ex2->setType(AMQP_EX_TYPE_DIRECT);
$ex2->declareExchange();

// Create a new queue
$q2 = new AMQPQueue($ch);
$q2->setName('test-multiple-queues-queue2');
$q2->declareQueue();

// Bind it on the exchange to routing.key
$q2->bind('test-multiple-queues-exchange2', 'rk2');

// Publish a message to the exchange with a routing key
$ex1->publish('queue1-message1', 'rk1');
$ex1->publish('queue1-message2', 'rk1');
$ex2->publish('queue2-message1', 'rk2');


// Read from the queue
$q1->consume(function ($env, $queue) {
	echo "Q1: ".$env->getBody().PHP_EOL;
	$queue->ack($env->getDeliveryTag());
	return false;
});


// Read from the queue
$q2->consume(function ($env, $queue) {
	echo "Q2: ".$env->getBody().PHP_EOL;
	$queue->ack($env->getDeliveryTag());
	return false;
});
--EXPECT--
Q1: queue1-message1
Q2: queue2-message1
