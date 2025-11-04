<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class PaymentSettingsController extends Controller
{
    public function index()
    {
        $enabled = (array) (Setting::get('payment.methods.enabled') ?: []);
        $stripeConfigured = (bool) config('stripe.key') && (bool) config('stripe.secret');
        $paypalConfigured = (bool) config('paypal.client_id') && (bool) config('paypal.secret');

        // Default: enable Stripe if configured
        if ($enabled === []) {
            $enabled = [
                'stripe' => $stripeConfigured,
                'paypal' => false,
            ];
        }

        return view('admin.payments', [
            'enabled' => $enabled,
            'stripeConfigured' => $stripeConfigured,
            'paypalConfigured' => $paypalConfigured,
            'currency' => config('stripe.currency', 'usd'),
            'paypalMode' => config('paypal.mode', 'sandbox'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'methods' => ['array'],
            'methods.stripe' => ['nullable', 'in:on'],
            'methods.paypal' => ['nullable', 'in:on'],
        ]);

        $methods = [
            'stripe' => ($data['methods']['stripe'] ?? null) === 'on',
            'paypal' => ($data['methods']['paypal'] ?? null) === 'on',
        ];

        Setting::set('payment.methods.enabled', $methods);

        return redirect()->back()->with('status', 'Payment methods updated');
    }
}

