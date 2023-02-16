<?php

require(APPPATH.'/vendor/autoload.php');

$CI = & get_instance();
$CI->load-->model('CartModel');

//echo 'hii';
// This is your Stripe CLI webhook secret for testing your endpoint locally.
\Stripe\Stripe::setApiKey('sk_test_51MMufsSBkuhk1xmVvwSrCUaHAgGUgUYH8ztbVGglv5wT4X60Gb927w7K8yP964Ilfk49yeWHXwDjZ6Vd8b83fXRB00ed7K8MeW');
$endpoint_secret = 'whsec_c0afa0a7f8319c5c31dd789424e3d9c255600697dedb50fad89df66db37ea63d';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
//echo $sig_header = print_r($this->input->request_headers());
$event = null;

try {
$event = \Stripe\Webhook::constructEvent(
    $payload, $sig_header, $endpoint_secret
);
} catch(\UnexpectedValueException $e) {
// Invalid payload
echo 'Invalid Payload';
http_response_code(400);
exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
// Invalid signature
echo 'Invalid Signature';
http_response_code(400);
exit();
}

// Handle the event
switch ($event->type) {
case 'payment_intent.amount_capturable_updated':
    $paymentIntent = $event->data->object;
    print_r($paymentIntent);
case 'payment_intent.canceled':
    $paymentIntent = $event->data->object;
    print_r($paymentIntent);
case 'payment_intent.created':
    $paymentIntent = $event->data->object;
    print_r($paymentIntent);
case 'payment_intent.partially_funded':
    $paymentIntent = $event->data->object;
    print_r($paymentIntent);
case 'payment_intent.payment_failed':
    $paymentIntent = $event->data->object;
    print_r($paymentIntent);
case 'payment_intent.processing':
    $paymentIntent = $event->data->object;
    print_r($paymentIntent);
case 'payment_intent.requires_action':
    $paymentIntent = $event->data->object;
    print_r($paymentIntent);
case 'payment_intent.succeeded':
    $paymentIntent = $event->data->object;
    $orderId = $event->data->object->metadata->order_id;
    $email = $event->data->object->metadata->email;
    $paymentIntentId = $event->data->object->id;
    $amount = $event->data->object->amount;
    $stripePaymentStatus = $event->data->object->status;

    $order_id = $orderId;
    $order1['eOrderStatus'] 			  = 'Completed';
    $order1['vPaymentData'] 			  = $paymentIntent;
    $order1['vOrderPaymentTransactionId'] = $paymentIntentId;

    $res = $CI->CartModel->update_order_status($order_id,$order1);
    $CI->session->sess_destroy();
// ... handle other event types
default:
    echo 'Received unknown event type ' . $event->type;
}