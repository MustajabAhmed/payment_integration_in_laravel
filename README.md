stripe k liay:



// Mustajab Code


// use Illuminate\Http\Request;
// use Stripe\PaymentIntent;
// use Stripe\Stripe;



// Route::post('/purchase', function (Request $request) {
//     try {
//         Stripe::setApiKey(config('services.stripe.secret'));
//         $user = User::whereId(Auth::Id())->first();
//         $paymentMethod = $request->input('payment_method');
//         // dd($paymentMethod);
//         $user->updateDefaultPaymentMethod($paymentMethod);
//         $user->updateDefaultPaymentMethodFromStripe();
//         // $data = $user->charge(1000, $paymentMethod);
//         return response()->json(['success' => true]);
//     } catch (Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// })->name('purchase');

// Route::get('/payment', function () {
//     $user = User::whereId(Auth::Id())->first();
//     $setupIntent = $user->createSetupIntent();

//     return view('stripe', [
//         'intent' => $setupIntent
//     ]);
// })->name('payment');

// Route::get('/payment/success', function () {
//     $user = User::whereId(Auth::Id())->first();
//     $paymentMethod = $user->defaultPaymentMethod();
//     // $data = $user->charge(1000, $paymentMethod->id);
//     $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
//     $stripe->charges->create([
//         'amount' => 1200,
//         'currency' => 'usd',
//         'source' => 'tok_visa',
//     ]);

//     return 'success';
// })->name('payment.success');




// sir hasnain code

// Route::get('purchase', function () {
//     Stripe::setApiKey(env('STRIPE_SECRET'));
//     $intent = PaymentIntent::create([
//         'amount' => intval(50 * 100),
//         'currency' => 'usd',
//         // 'metadata' => ['order_id' => $order_id],
//     ]);
//     return view('stripe', ['clientSecret' => $intent]);
//     // return response()->json(['clientSecret' => $intent->client_secret]);
// })->name('purchase');


















@extends('layouts.main')

@section('content')

{{-- 

    Mustajab Code

    <div>
        <input id="card-holder-name" type="text">

        <!-- Stripe Elements Placeholder -->
        <div id="card-element"></div>

        <button id="card-button" data-secret="{{ $intent->client_secret }}">
            Update Payment Method
        </button>
    </div>

    <script src="https://js.stripe.com/v3/"></script>

    <script>
        const stripe = Stripe("{{ config('services.stripe.key') }}");

        const elements = stripe.elements();
        const cardElement = elements.create('card');

        cardElement.mount('#card-element');

        const cardHolderName = document.getElementById('card-holder-name');
        const cardButton = document.getElementById('card-button');
        const clientSecret = cardButton.dataset.secret;

        cardButton.addEventListener('click', async (e) => {
            e.preventDefault();

            const {
                setupIntent,
                error
            } = await stripe.confirmCardSetup(
                clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: cardHolderName.value
                        }
                    },
                    
                }
            );

            if (error) {
                alert(error.message);
            } else {
                const paymentMethod = setupIntent.payment_method;

                const response = await fetch("{{ route('purchase') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        payment_method: paymentMethod
                    })
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = "{{ route('payment.success') }}";
                } else {
                    alert("Payment failed: " + result.error);
                }
            }
        });
    </script> --}}


    {{-- // sir hasnain code --}}


    {{-- <form action="">
        <div id="card-element">
            <!-- A Stripe Element will be inserted here. -->
        </div>
        <input type="text" name="" id="cardholder-name">
        <!-- Used to display form errors. -->
        <div id="card-errors" role="alert"></div>
        <div id="card-message"></div>
        <button id="card-button" type="button" data-secret="{{ $clientSecret }}">
            Process Payment
        </button>
    </form>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        var stripe = Stripe('{{ env('STRIPE_KEY') }}');

        var paymentIntent;
        paymentIntent = @json($clientSecret);


        var elements = stripe.elements();
        var cardElement = elements.create('card');
        cardElement.mount('#card-element');

        var cardholderName = document.getElementById('cardholder-name');
        var cardButton = document.getElementById('card-button');
        var cardMessage = document.getElementById('card-message'); // for testing (to remove)

        cardButton.addEventListener('click', function(ev) {
            ev.preventDefault();
            cardMessage.textContent = "Calling handleCardPayment..."; // for testing (to remove)
            stripe.handleCardPayment(
                paymentIntent.client_secret, cardElement, {
                    payment_method_data: {
                        billing_details: {
                            name: cardholderName.value
                        }
                    }
                }
            ).then(function(result) {
                cardMessage.textContent = JSON.stringify(result, null, 2); // for testing (to remove)
                if (result.error) {
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {}
            });
        });
    </script> --}}
@endsection
