<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue']);

const show = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
});

const step = ref('select'); // 'select' | 'chat'
const context = ref('order'); // 'order' | 'product'
const id = ref('');
const loading = ref(false);
const input = ref('');
const messages = ref([]); // {role, content}
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

function startChat() {
  const idNum = Number(id.value);
  if ((context.value === 'order' || context.value === 'product') && Number.isFinite(idNum) && idNum > 0) {
    step.value = 'chat';
    messages.value = [];
    input.value = '';
  }
}

async function send() {
  const text = input.value.trim();
  if (!text) return;
  messages.value.push({ role: 'user', content: text });
  input.value = '';
  loading.value = true;
  try {
    const res = await fetch('/chat/assist', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({ message: text, context: context.value, id: Number(id.value) }),
    });
    const data = await res.json();
    let reply = data?.reply || 'Sorry, I could not get a reply.';
    if (!res.ok) reply = data?.error || reply;
    messages.value.push({ role: 'assistant', content: reply });
  } catch (e) {
    messages.value.push({ role: 'assistant', content: 'Network error. Please try again.' });
  } finally {
    loading.value = false;
  }
}

function close() {
  show.value = false;
  setTimeout(() => { step.value = 'select'; id.value=''; input.value=''; messages.value=[]; }, 200);
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/40" @click="close"></div>
    <div class="absolute inset-x-0 bottom-0 md:inset-0 md:flex md:items-center md:justify-center">
      <div class="md:w-[720px] w-full md:h-auto h-[80vh] bg-white dark:bg-gray-800 rounded-t-2xl md:rounded-2xl shadow-2xl overflow-hidden">
        <div class="p-4 bg-indigo-600 text-white flex items-center justify-between">
          <div class="font-semibold">Customer Support</div>
          <button @click="close" class="text-white/80 hover:text-white">✕</button>
        </div>

        <div v-if="step==='select'" class="p-4 space-y-4">
          <div class="text-sm text-gray-700 dark:text-gray-300">Select what you want help with:</div>
          <div class="flex items-center gap-6">
            <label class="inline-flex items-center gap-2">
              <input type="radio" value="order" v-model="context" />
              <span>Order</span>
            </label>
            <label class="inline-flex items-center gap-2">
              <input type="radio" value="product" v-model="context" />
              <span>Product</span>
            </label>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Enter {{ context }} ID</label>
            <input type="number" v-model="id" min="1" class="input-field w-40" placeholder="e.g. 123" />
            <p class="text-xs text-gray-500 mt-1" v-if="context==='order'">Find it on the Orders page.</p>
            <p class="text-xs text-gray-500 mt-1" v-else>Open a product page to see its ID.</p>
          </div>

          <div>
            <button class="btn-primary" :disabled="!(Number(id) > 0)" @click="startChat">Start Chat</button>
          </div>
        </div>

        <div v-else class="flex flex-col h-[calc(80vh-64px)] md:h-[540px]">
          <div class="p-3 border-b dark:border-gray-700 text-sm text-gray-700 dark:text-gray-300">Context: <b>{{ context }}</b> • ID: <b>{{ id }}</b></div>
          <div class="flex-1 p-4 space-y-2 overflow-auto">
            <div v-if="messages.length === 0" class="text-sm text-gray-600 dark:text-gray-300">Ask about this {{ context }}’s details only.</div>
            <div v-for="(m, idx) in messages" :key="idx" class="text-sm">
              <div :class="m.role==='user' ? 'text-gray-900 dark:text-gray-100' : 'text-gray-700 dark:text-gray-300'">
                <span class="font-medium">{{ m.role==='user' ? 'You' : 'AI' }}:</span>
                <span class="whitespace-pre-line"> {{ m.content }}</span>
              </div>
            </div>
            <div v-if="loading" class="text-xs text-gray-500">Thinking…</div>
          </div>
          <form class="p-3 border-t dark:border-gray-700 flex items-center gap-2" @submit.prevent="send">
            <input v-model="input" :placeholder="'Ask about this ' + context" class="flex-1 input-field" />
            <button class="btn-primary" :disabled="loading">Send</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  
</template>

