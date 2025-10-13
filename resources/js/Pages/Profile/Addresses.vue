<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  addresses: { type: Array, default: () => [] },
});

const editing = ref(null);

const emptyForm = () => ({
  full_name: '',
  line1: '',
  line2: '',
  city: '',
  state: '',
  postal_code: '',
  country: 'US',
  phone: '',
  is_default: false,
});

const form = useForm(emptyForm());

function startAdd() {
  editing.value = null;
  form.reset();
}

function startEdit(a) {
  editing.value = a.id;
  form.reset();
  form.full_name = a.full_name;
  form.line1 = a.line1;
  form.line2 = a.line2 || '';
  form.city = a.city;
  form.state = a.state || '';
  form.postal_code = a.postal_code;
  form.country = a.country || 'US';
  form.phone = a.phone || '';
  form.is_default = !!a.is_default;
}

function save() {
  if (editing.value) {
    form.put(route('addresses.update', editing.value));
  } else {
    form.post(route('addresses.store'));
  }
}

function removeAddress(id) {
  if (!confirm('Delete this address?')) return;
  router.delete(route('addresses.destroy', id));
}

function makeDefault(id) {
  router.post(route('addresses.default', id), {});
}
</script>

<template>
  <Head title="Addresses" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Addresses</h2>
    </template>

    <div class="py-8">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 grid md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow">
          <h3 class="font-medium mb-4 text-gray-900 dark:text-gray-100" v-text="editing ? 'Edit Address' : 'Add Address'" />
          <div class="space-y-3">
            <input v-model="form.full_name" placeholder="Full name" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
            <input v-model="form.line1" placeholder="Address line 1" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
            <input v-model="form.line2" placeholder="Address line 2 (optional)" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
            <div class="grid grid-cols-2 gap-3">
              <input v-model="form.city" placeholder="City" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
              <input v-model="form.state" placeholder="State/Province" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <input v-model="form.postal_code" placeholder="Postal code" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
              <input v-model="form.country" placeholder="Country (ISO2)" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
            </div>
            <input v-model="form.phone" placeholder="Phone (optional)" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
            <label class="flex items-center gap-2 text-gray-800 dark:text-gray-200">
              <input type="checkbox" v-model="form.is_default" class="border-gray-300 dark:border-gray-700" />
              <span>Set as default</span>
            </label>
            <div class="flex gap-2">
              <button @click="save" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700" :disabled="form.processing">
                {{ editing ? 'Update' : 'Add' }}
              </button>
              <button v-if="editing" @click="startAdd" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-800 dark:text-gray-200">Cancel</button>
            </div>
            <div v-if="form.errors && Object.keys(form.errors).length" class="text-sm text-red-600">
              <div v-for="(msg, key) in form.errors" :key="key">{{ msg }}</div>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow">
          <h3 class="font-medium mb-4 text-gray-900 dark:text-gray-100">Your Addresses</h3>
          <div class="space-y-3">
            <div v-if="!addresses.length" class="text-gray-600 dark:text-gray-300">No addresses yet.</div>
            <div v-for="a in addresses" :key="a.id" class="border border-gray-200 dark:border-gray-700 rounded p-3">
              <div class="flex items-center justify-between">
                <div class="font-medium text-gray-900 dark:text-gray-100">
                  {{ a.full_name }}
                  <span v-if="a.is_default" class="ml-2 text-xs px-2 py-0.5 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded">Default</span>
                </div>
                <div class="space-x-2">
                  <button class="text-indigo-600 dark:text-indigo-400" @click="startEdit(a)">Edit</button>
                  <button class="text-red-600" @click="removeAddress(a.id)">Delete</button>
                </div>
              </div>
              <div class="text-sm text-gray-700 dark:text-gray-300 mt-1">
                <div>{{ a.line1 }}</div>
                <div v-if="a.line2">{{ a.line2 }}</div>
                <div>{{ a.city }}<span v-if="a.state">, {{ a.state }}</span> {{ a.postal_code }}</div>
                <div>{{ a.country }}</div>
                <div v-if="a.phone">Phone: {{ a.phone }}</div>
              </div>
              <div class="mt-2">
                <button v-if="!a.is_default" @click="makeDefault(a.id)" class="text-sm text-gray-700 dark:text-gray-300 underline">Make default</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
