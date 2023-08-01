<?php

namespace App\Http\Controllers\Payment;

use Stripe\Charge;
use App\Models\User;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\StripePayment;
use App\Models\Subscription;
use Stripe\Subscription as stripesub;
use Stripe\Customer;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Plan;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Currency;
use PayPal\Api\Payer;
use PayPal\Api\Agreement;
use PayPal\Api\Patch;
use PayPal\Common\PayPalModel;
use PayPal\Api\PatchRequest;
use PayPal\Exception\PayPalConnectionException;
use Carbon\Carbon;
use Exception;

class PaymentController extends Controller
{
    // public function stripe($slug,$price,$duration)
    // {
    //     return view('payment.store', compact('price','slug','duration'));
    // }


    // public function stripePost(Request $request, $slug, $payprice, $duration)

    // {
    //         $userId = auth()->user()->id;
    //         $stripeToken = $request->stripeToken;

    //         Stripe::setApiKey(env('STRIPE_SECRET'));

    //         $existingCustomer = Subscription::where('user_id', $userId)->where('status','1')->first();

    //         if ($existingCustomer) {
    //             $customer = $existingCustomer;
    //         } else {
    //             $customer = Customer::create([
    //                 'source' => $stripeToken,
    //                 'email' => auth()->user()->email,
    //             ]);
    //         }

    //         $product = Product::create([
    //             'name' => 'Dummy Product',
    //             'type' => 'service',
    //         ]);

    //         if($duration == 1) {
    //             $price = Price::create([
    //                 'unit_amount' => $payprice * 100, // Amount in cents, e.g., $100 = 100 * 100
    //                 'currency' => 'usd',
    //                 'recurring' => [
    //                     'interval' => 'month',
    //                 ],
    //                 'product' => $product->id, // Associate the price with the product
    //             ]);
    //         }

    //         if($duration == 3) {
    //             $price = Price::create([
    //                 'unit_amount' => $payprice * 100, // Amount in cents, e.g., $100 = 100 * 100
    //                 'currency' => 'usd',
    //                 'recurring' => [
    //                     'interval' => 'month',
    //                     'interval_count' => 3, // Set the interval count to 3 for 3 months
    //                 ],
    //                 'product' => $product->id, // Associate the price with the product
    //             ]);
    //         }

    //     $response = stripesub::create([
    //         'customer' => $customer->id,
    //         'items' => [
    //             [
    //                 'price' => $price->id,
    //             ],
    //         ],
    //     ]);

    //     if ($response) {
    //         $subscription = new Subscription();
    //         $subscription->user_id = $userId;
    //         $subscription->customer_id = $customer->id;
    //         $subscription->subscription_id = $response->id;
    //         $subscription->payment_method = 'stripe';
    //         $subscription->plan = $slug;
    //         $subscription->amount = $payprice;
    //         $subscription->duration = $duration;
    //         $subscription->ends_at = Carbon::now()->addMonths($duration);
    //         $subscription->status = 1;
    //         $subscription->save();
    //         return redirect('/')->with('success','Subscription created Successfully!');
    //     } else {
    //         // Flash an error message to the user
    //         return redirect('')->with('success','Failed to create subscription!');
    //     }

    // }

    public function CreatePaypalSub($slug,$payprice,$duration)

    {
        // dd('here');
        $loginCheck = auth()->check();
        if ($loginCheck) {
            if (config('paypal.settings.mode') == 'live') {
                $client_id = config('paypal.live_client_id');
                $secret = config('paypal.live_secret');
            } else {
                $client_id = config('paypal.sandbox_client_id');
                $secret = config('paypal.sandbox_secret');
            }

            $apiContext = new ApiContext(new OAuthTokenCredential($client_id, $secret));
            $apiContext->setConfig(config('paypal.settings'));

            try {
                $plan = new Plan();
                $plan->setName('App Name Monthly Billing')
                    ->setDescription('Monthly Subscription to the App Name')
                    ->setType('infinite');

                    if($duration == 1) {
                    $paymentDefinition = new PaymentDefinition();
                    $paymentDefinition->setName('Regular Payments')
                    ->setType('REGULAR')
                    ->setFrequency('Month')
                    ->setFrequencyInterval('1')
                    ->setCycles('0')
                    ->setAmount(new Currency(array('value' => $payprice, 'currency' => 'USD')));
                    }

                    if($duration == 3) {
                    $paymentDefinition = new PaymentDefinition();
                    $paymentDefinition->setName('Regular Payments')
                    ->setType('REGULAR')
                    ->setFrequency('Month')
                    ->setFrequencyInterval('3')
                    ->setCycles('0')
                    ->setAmount(new Currency(array('value' => $payprice, 'currency' => 'USD')));
                    }
                    else {
                    $paymentDefinition = new PaymentDefinition();
                    $paymentDefinition->setName('Regular Payments')
                    ->setType('REGULAR')
                    ->setFrequency('Month')
                    ->setFrequencyInterval('1')
                    ->setCycles('0')
                    ->setAmount(new Currency(array('value' => $payprice, 'currency' => 'USD')));
                    }


                $merchantPreferences = new MerchantPreferences();
                $paypalSuccessUrl = 'http://127.0.0.1:8000/paypal/subscription/success/'. $slug . '/' . $payprice . '/'. $duration;
                $merchantPreferences->setReturnUrl($paypalSuccessUrl)
                    ->setCancelUrl('http://127.0.0.1:8000/paypal/subscription/cancel')
                    ->setAutoBillAmount('yes')
                    ->setInitialFailAmountAction('CONTINUE')
                    ->setMaxFailAttempts('0');

                $plan->setPaymentDefinitions(array($paymentDefinition));
                $plan->setMerchantPreferences($merchantPreferences);

                $createdPlan = $plan->create($apiContext);

                $patch = new Patch();
                $value = new PayPalModel('{"state":"ACTIVE"}');
                $patch->setOp('replace')
                    ->setPath('/')
                    ->setValue($value);
                $patchRequest = new PatchRequest();
                $patchRequest->addPatch($patch);
                $createdPlan->update($patchRequest, $apiContext);
                $plan = Plan::get($createdPlan->getId(), $apiContext);

                echo 'Plan ID:' . $plan->getId();

                try {
                    $agreement = new Agreement();
                    $agreement->setName('Subscription Agreement')
                        ->setDescription('Monthly subscription agreement')
                        ->setStartDate(date('Y-m-d\TH:i:s\Z', strtotime('+1 day')))
                        ->setPlan(new Plan(['id' => $plan->getId()]));

                    $payer = new Payer();
                    $payer->setPaymentMethod('paypal');
                    $agreement->setPayer($payer);

                    $createdAgreement = $agreement->create($apiContext);
                    $approvalUrl = $createdAgreement->getApprovalLink();

                    header('Location: ' . $approvalUrl);
                    exit;
                } catch (PayPalConnectionException $ex) {
                    echo $ex->getCode();
                    echo $ex->getData();
                    die($ex);
                } catch (Exception $ex) {
                    die($ex);
                }
            } catch (PayPalConnectionException $ex) {
                echo $ex->getCode();
                echo $ex->getData();
                die($ex);
            } catch (Exception $ex) {
                die($ex);
            }
        } else {
            return redirect('login');
        }
    }

    public function success(Request $request, $slug,$payprice, $duration)
    {
        $userId = auth()->user()->id;
        $agreementId = $request->input('token');
        $saveData = null;

            $saveData = Subscription::where('user_id', $userId)->first();

            if ($saveData) {
                $saveData->status = 1;
                $saveData->save();
            } else {
                $saveData = Subscription::create([
                    'user_id' => $userId,
                    'usertoken' => $agreementId,
                    'payment_method' => 'paypal',
                    'plan' => $slug,
                    'amount' => $payprice,
                    'duration' => $duration,
                    'ends_at' => Carbon::now()->addMonths($duration),
                    'status' => 1,
                ]);
            }

        if ($saveData !== null) {
            return redirect('/')->with('success', 'Subscription created successfully');
        }else{
            return redirect('/')->with('success', 'Subscription not created!');
        }

    }

    public function cancel()
    {
        $userId = auth()->user()->id;
        $saveData = null;

            $saveData = Subscription::where('user_id', $userId)->first();

            if ($saveData) {
                $saveData->status = 0;
                $saveData->save();
            }

        if ($saveData) {
            return redirect()->back()->with('success', 'Subscription canceled');
        }
    }
}
