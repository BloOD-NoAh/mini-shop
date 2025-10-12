<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Checkout</h2>
    </x-slot>

    <div class="py-6">
        <div class="container-narrow">
            <div class="card space-y-4">
                <div class="text-lg">Total: $ {{ number_format($subtotal_cents / 100, 2) }}</div>
                <div id="status" class="hidden p-3 rounded"></div>

                <div id="stripe-area" class="space-y-3" style="display:none;">
                    <div id="payment-element"></div>
                    <button id="pay-btn" class="btn-primary">Pay</button>
                </div>

                <div id="mock-area" style="display:none;">
                    <p class="text-gray-700 dark:text-gray-300 mb-3">Stripe not configured. Using mock payment.</p>
                    <button id="mock-pay" class="btn-primary">Mock Pay</button>
                </div>
            </div>
        </div>
    </div>

    @if ($has_stripe && $stripe_key)
        <script src="https://js.stripe.com/v3/"></script>
    @endif
    <script>
        (async () => {
            const status = document.getElementById('status');
            function showStatus(msg, ok=true) {
                status.textContent = msg;
                status.classList.remove('hidden');
                status.className = 'p-3 rounded ' + (ok ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
            }

            // Create or mock payment intent
            const resp = await fetch('{{ url('/checkout') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            });
            const data = await resp.json();

            const useStripe = {{ $has_stripe && $stripe_key ? 'true' : 'false' }} && !data.mock;

            if (useStripe) {
                const stripe = Stripe(@json($stripe_key));
                const elements = stripe.elements({ clientSecret: data.client_secret });
                const paymentElement = elements.create('payment');
                paymentElement.mount('#payment-element');
                document.getElementById('stripe-area').style.display = '';
                document.getElementById('pay-btn').addEventListener('click', async () => {
                    const {error, paymentIntent} = await stripe.confirmPayment({
                        elements,
                        redirect: 'if_required',
                    });
                    if (error) {
                        showStatus(error.message || 'Payment error', false);
                        return;
                    }
                    // Confirm on server (finalize order)
                    const r = await fetch('{{ url('/checkout/confirm') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }});
                    const d = await r.json();
                    if (r.ok) {
                        window.location = '{{ url('/checkout/success') }}';
                    } else {
                        showStatus(d.message || 'Could not finalize order', false);
                    }
                });
            } else {
                document.getElementById('mock-area').style.display = '';
                document.getElementById('mock-pay').addEventListener('click', async () => {
                    const r = await fetch('{{ url('/checkout/confirm') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }});
                    const d = await r.json();
                    if (r.ok) {
                        window.location = '{{ url('/checkout/success') }}';
                    } else {
                        showStatus(d.message || 'Checkout failed', false);
                    }
                });
            }
        })();
    </script>
</x-app-layout>


