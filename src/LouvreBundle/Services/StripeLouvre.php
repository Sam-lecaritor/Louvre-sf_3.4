<?php
namespace LouvreBundle\Services;

use Stripe\Charge;
use Stripe\Stripe;

class StripeLouvre
{

    private $key = "sk_test_5KsDT1yflgRbhvzGrZUEdXl1";

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

        } catch (Exception $e) {

            echo "<div class='debug'>";
            echo "ERREUR STRIPE ===>";
            var_dump($e->getMessage());
            echo "</br>";
            echo "</div>";

        }

        return $charge->getLastResponse()->json["paid"];

    }

}
