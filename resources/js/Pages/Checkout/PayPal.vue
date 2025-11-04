<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Checkout with PayPal</h2>
    </template>

    <div class="py-6">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-6 space-y-4">
          <div v-if="addresses?.length" class="space-y-2">
            <label class="font-medium">Shipping Address</label>
            <select v-model="selectedAddressId" class="w-full border rounded px-3 py-2">
              <option disabled value="">Select an address</option>
              <option v-for="a in addresses" :key="a.id" :value="a.id">
                {{ a.full_name }} – {{ a.line1 }}, {{ a.city }} {{ a.postal_code }} ({{ a.country }})
                <span v-if="a.is_default"> – Default</span>
              </option>
            </select>
            <div class="text-sm">
              <a href="/addresses" class="text-indigo-600 underline">Manage addresses</a>
            </div>
          </div>

          <div class="text-lg">
            Total: $ {{ (amountCents / 100).toFixed(2) }} {{ (currency || '').toUpperCase() }}
          </div>

          <div v-if="error" class="p-3 rounded bg-red-100 text-red-800">{{ error }}</div>
          <div v-if="message" class="p-3 rounded bg-green-100 text-green-800">{{ message }}</div>

          <div id="paypal-buttons" class="mt-2"></div>
        </div>

        <div v-if="cart?.length" class="mt-6 bg-white shadow rounded p-4">
          <h3 class="font-medium mb-2">Cart</h3>
          <ul class="text-sm text-gray-800 list-disc ml-5 space-y-1">
            <li v-for="(i, idx) in cart" :key="idx">
              {{ i.product.name }}
              <span v-if="i.variant?.attributes"> –
                <span v-for="(val, key, aIdx) in i.variant.attributes" :key="key">{{ key }}: {{ val }}<span v-if="aIdx < Object.keys(i.variant.attributes).length - 1">, </span></span>
              </span>
              × {{ i.quantity }} – $ {{ (i.unit_price_cents / 100).toFixed(2) }}
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
import { onMounted, ref } from 'vue';

const props = defineProps({
  amountCents: { type: Number, required: true },
  currency: { type: String, required: true },
  cart: { type: Array, required: true },
  addresses: { type: Array, default: () => [] },
});

const error = ref('');
const message = ref('');
const selectedAddressId = ref(null);

async function loadPayPalJs(clientId, currency) {
  if (window.paypal) return;
  await new Promise((resolve, reject) => {
    const s = document.createElement('script');
    const cur = (currency || 'USD').toUpperCase();
    s.src = `https://www.paypal.com/sdk/js?client-id=${encodeURIComponent(clientId)}&currency=${encodeURIComponent(cur)}`;
    s.async = true;
    s.onload = resolve;
    s.onerror = () => reject(new Error('Failed to load PayPal SDK'));
    document.head.appendChild(s);
  });
}

onMounted(async () => {
  try {
    const clientId = import.meta.env.VITE_PAYPAL_CLIENT_ID;
    if (!clientId) {
      error.value = 'PayPal client ID is not configured (VITE_PAYPAL_CLIENT_ID).';
      return;
    }
    await loadPayPalJs(clientId, props.currency);
    if (props.addresses && props.addresses.length) {
      const def = props.addresses.find(a => a.is_default) || props.addresses[0];
      selectedAddressId.value = def?.id || null;
    }

    if (!window.paypal) {
      error.value = 'PayPal SDK not available.';
      return;
    }

    window.paypal.Buttons({
      createOrder: async () => {
        try {
          const resp = await fetch(route('paypal.createOrder'), { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' } });
          const data = await resp.json();
          if (!resp.ok) throw new Error(data?.message || 'Failed to create order');
          return data.id;
        } catch (e) {
          error.value = e?.message || 'Create order failed.';
          throw e;
        }
      },
      onApprove: async (data) => {
        try {
          const form = new FormData();
          form.append('orderId', data.orderID);
          form.append('address_id', selectedAddressId.value || '');
          const resp = await fetch(route('paypal.capture'), { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }, body: form });
          const out = await resp.json();
          if (!resp.ok) throw new Error(out?.message || 'Capture failed');
          window.location.href = route('orders.show', out.order_id);
        } catch (e) {
          error.value = e?.message || 'Capture failed.';
        }
      },
      onError: (err) => {
        error.value = (err && err.message) ? err.message : 'PayPal error.';
      },
    }).render('#paypal-buttons');
  } catch (e) {
    error.value = e?.message || 'Failed to initialize PayPal.';
  }
});
</script>

