<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Checkout</h2>
    </template>

    <div class="py-6">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-6 space-y-4">
          <div class="text-lg">
            Total: $ {{ (amountCents / 100).toFixed(2) }} {{ (currency || '').toUpperCase() }}
          </div>

          <div v-if="error" class="p-3 rounded bg-red-100 text-red-800">{{ error }}</div>
          <div v-if="message" class="p-3 rounded bg-green-100 text-green-800">{{ message }}</div>

          <div id="payment-element" class="mt-2"></div>

          <button
            :disabled="processing || !ready"
            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-60"
            @click="payNow"
          >
            <span v-if="processing">Processing…</span>
            <span v-else>Pay now</span>
          </button>
        </div>

        <div v-if="cart?.length" class="mt-6 bg-white shadow rounded p-4">
          <h3 class="font-medium mb-2">Cart</h3>
          <ul class="text-sm text-gray-800 list-disc ml-5 space-y-1">
            <li v-for="(i, idx) in cart" :key="idx">
              {{ i.product.name }} × {{ i.quantity }} — $ {{ (i.unit_price_cents / 100).toFixed(2) }}
            </li>
          </ul>
          <div class="mt-4">
            <a href="/cart" class="text-indigo-600 hover:underline">Back to cart</a>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
  
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
  clientSecret: { type: String, required: true },
  amountCents: { type: Number, required: true },
  currency: { type: String, required: true },
  cart: { type: Array, required: true },
});

const error = ref('');
const message = ref('');
const processing = ref(false);
const ready = ref(false);
let stripe = null;
let elements = null;

async function loadStripeJs() {
  if (window.Stripe) return;
  await new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = 'https://js.stripe.com/v3/';
    s.async = true;
    s.onload = resolve;
    s.onerror = () => reject(new Error('Failed to load Stripe.js'));
    document.head.appendChild(s);
  });
}

onMounted(async () => {
  try {
    await loadStripeJs();
    const pk = import.meta.env.VITE_STRIPE_KEY;
    if (!pk) {
      error.value = 'Stripe publishable key is not configured.';
      return;
    }
    if (!window.Stripe) {
      error.value = 'Stripe.js not available.';
      return;
    }
    stripe = window.Stripe(pk);
    elements = stripe.elements({ clientSecret: props.clientSecret });
    const paymentEl = elements.create('payment');
    paymentEl.mount('#payment-element');
    ready.value = true;
  } catch (e) {
    error.value = e?.message || 'Failed to initialize payments.';
  }
});

const form = useForm({ paymentIntentId: '' });

async function payNow() {
  if (!stripe || !elements) return;
  processing.value = true;
  error.value = '';
  try {
    const { error: stripeError, paymentIntent } = await stripe.confirmPayment({
      elements,
      redirect: 'if_required',
    });
    if (stripeError) {
      error.value = stripeError.message || 'Payment failed.';
      return;
    }
    if (!paymentIntent) {
      error.value = 'No payment intent returned.';
      return;
    }
    form.paymentIntentId = paymentIntent.id;
    await form.post(route('checkout.confirm'), {
      preserveScroll: true,
      onError: (errs) => {
        error.value = Object.values(errs || {}).flat().join(', ') || 'Confirmation failed.';
      },
    });
  } catch (e) {
    error.value = e?.message || 'Payment error.';
  } finally {
    processing.value = false;
  }
}

// Handle redirect return states (succeeded/processing/failed/canceled)
onMounted(async () => {
  try {
    if (!window.Stripe) return;
    const pk = import.meta.env.VITE_STRIPE_KEY;
    if (!pk) return;
    const params = new URLSearchParams(window.location.search || '');
    const clientSecret = params.get('payment_intent_client_secret');
    if (!clientSecret) return;
    const stripeLocal = window.Stripe(pk);
    const { paymentIntent, error: retrieveError } = await stripeLocal.retrievePaymentIntent(clientSecret);
    if (retrieveError) {
      error.value = retrieveError.message || 'Unable to retrieve payment result.';
      return;
    }
    if (!paymentIntent) return;
    if (paymentIntent.status === 'succeeded') {
      form.paymentIntentId = paymentIntent.id;
      await form.post(route('checkout.confirm'), {
        preserveScroll: true,
        onError: (errs) => {
          error.value = Object.values(errs || {}).flat().join(', ') || 'Confirmation failed.';
        },
      });
    } else if (paymentIntent.status === 'processing') {
      message.value = 'Your payment is processing. You will be redirected once complete.';
    } else if (paymentIntent.status === 'requires_payment_method') {
      error.value = 'Payment failed or was canceled. Please try again.';
    }
    if (window.history.replaceState) {
      window.history.replaceState({}, document.title, window.location.pathname);
    }
  } catch (_) {
    // no-op
  }
});
</script>
