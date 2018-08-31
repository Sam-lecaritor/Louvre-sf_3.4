<?php
namespace LouvreBundle\Services;

use Stripe\Charge;
use Stripe\Error\Card;
use Stripe\Stripe;

class StripeLouvre
{

    //private $key = "sk_test_5KsDT1yflgRbhvzGrZUEdXl1";
    private $key;

    public function __construct($key){
        $this->key=$key;
    }

    public function createCharge($montant, $token)
    {

        try {
            Stripe::setApiKey($this->key);

            $charge = Charge::create([
                'amount' => $montant * 100,
                'currency' => 'eur',
                'description' => 'Billetterie Louvre',
                'source' => $token,
            ]);
            $result['paid'] = $charge->getLastResponse()->json["paid"];

        } catch (\Stripe\Error\Card $e) {
            $body = $e->getJsonBody();

            $result['error'] = "carte refusé";
        }

        return $result;

    }

}
/**
 * 
 * ERREURS POSSIBLE DE STRIPE
 * 
 */


/* try {
// Use Stripe's library to make requests...
} catch (\Stripe\Error\Card $e) {
// Since it's a decline, \Stripe\Error\Card will be caught
$body = $e->getJsonBody();
$err = $body['error'];

print('Status is:' . $e->getHttpStatus() . "\n");
print('Type is:' . $err['type'] . "\n");
print('Code is:' . $err['code'] . "\n");
// param is '' in this case
print('Param is:' . $err['param'] . "\n");
print('Message is:' . $err['message'] . "\n");
} catch (\Stripe\Error\RateLimit $e) {
// Too many requests made to the API too quickly
} catch (\Stripe\Error\InvalidRequest $e) {
// Invalid parameters were supplied to Stripe's API
} catch (\Stripe\Error\Authentication $e) {
// Authentication with Stripe's API failed
// (maybe you changed API keys recently)
} catch (\Stripe\Error\ApiConnection $e) {
// Network communication with Stripe failed
} catch (\Stripe\Error\Base $e) {
// Display a very generic error to the user, and maybe send
// yourself an email
} catch (Exception $e) {
// Something else happened, completely unrelated to Stripe
} */
