<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Subscription;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeElementPaymentController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function checkout(Request $request)
    {
        $validateData = Validator::make($request->all(), [
            'plan_id' => 'required|exists:subscriptions,id',
            'discount_code' => 'nullable|string|max:255',
        ]);

        if ($validateData->fails()) {
            return $this->error($validateData->errors(), $validateData->errors()->first(), 422);
        }

        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found.', 200);
        }

        $subscriptionPlan = Subscription::find($request->plan_id);

        if (!$subscriptionPlan || !$subscriptionPlan->stripe_price_id) {
            return $this->error([], 'Subscription plan or Stripe price ID not found.', 200);
        }

        $CouponCode = null;
        if ($request->discount_code) {
            $coupon = Coupon::where('code', $request->discount_code)
                ->where('is_active', true)
                ->first();

            if ($coupon) {
                $CouponCode = $coupon->stripe_promotion_code_id;
            }
        }

        $intent = PaymentIntent::create([
            'amount' => $subscriptionPlan->stripe_price_id,
            'currency' => 'usd',
            'metadata' => [
                'user_id' => 1,
                'subscription_id' => 1,
            ],
            'automatic_payment_methods' => ['enabled' => true],
        ]);
    }
}
