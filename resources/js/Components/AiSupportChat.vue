<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  context: { type: String, required: true }, // 'product' | 'order'
  id: { type: Number, required: true },
});

const page = usePage();
const isAuthed = computed(() => !!(page?.props?.auth && page.props.auth.user));

const open = ref(false);
const loading = ref(false);
const input = ref('');
const messages = ref([]); // {role:'user'|'assistant', content:string}

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

function handleGlobalOpen() {
  open.value = true;
}

onMounted(() => {
  window.addEventListener('open-ai-support', handleGlobalOpen);
});

onBeforeUnmount(() => {
  window.removeEventListener('open-ai-support', handleGlobalOpen);
});

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
      body: JSON.stringify({ message: text, context: props.context, id: props.id }),
    });
    const data = await res.json();
    let reply = data?.reply || 'Sorry, I could not get a reply.';
    if (!res.ok) {
      reply = data?.error || reply;
    }
    messages.value.push({ role: 'assistant', content: reply });
  } catch (e) {
    messages.value.push({ role: 'assistant', content: 'Network error. Please try again.' });
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div v-if="isAuthed" class="fixed bottom-6 right-6 z-40" data-ai-support-present>
    <div v-if="open" class="w-80 h-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl flex flex-col overflow-hidden">
      <div class="p-3 bg-indigo-600 text-white flex items-center justify-between">
        <div class="font-semibold text-sm">AI Support ({{ context }})</div>
        <button @click="open = false" class="text-white/80 hover:text-white">✕</button>
      </div>
      <div class="flex-1 p-3 space-y-2 overflow-auto">
        <div v-if="messages.length === 0" class="text-sm text-gray-600 dark:text-gray-300">
          Ask about this {{ context === 'product' ? 'product\'s details' : 'order\'s details' }}.
        </div>
        <div v-for="(m, idx) in messages" :key="idx" class="text-sm">
          <div :class="m.role === 'user' ? 'text-gray-900 dark:text-gray-100' : 'text-gray-700 dark:text-gray-300'">
            <span class="font-medium">{{ m.role === 'user' ? 'You' : 'AI' }}:</span>
            <span class="whitespace-pre-line"> {{ m.content }}</span>
          </div>
        </div>
        <div v-if="loading" class="text-xs text-gray-500">Thinking…</div>
      </div>
      <form class="p-2 border-t dark:border-gray-700 flex items-center gap-2" @submit.prevent="send">
        <input v-model="input" :placeholder="'Ask about this ' + (context === 'product' ? 'product' : 'order')" class="flex-1 input-field" />
        <button class="btn-primary" :disabled="loading">Send</button>
      </form>
    </div>
  </div>

  
</template>
