<x-admin-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">AI Analytics (Ollama)</h2>
  </x-slot>

  <div class="py-6">
    <div class="container-wide">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 card p-4 space-y-3">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
              <label class="input-label">Start date</label>
              <input id="filter-start" type="date" class="input-field w-full" value="{{ $defaultStart ?? '' }}" />
            </div>
            <div>
              <label class="input-label">End date</label>
              <input id="filter-end" type="date" class="input-field w-full" value="{{ $defaultEnd ?? '' }}" />
            </div>
            <div>
              <label class="input-label">Scope</label>
              <select id="filter-scope" class="input-field w-full">
                <option value="overview" selected>Overview</option>
                <option value="customer">Customer</option>
                <option value="order">Order</option>
                <option value="product">Product</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3" id="filter-extra" style="display:none;">
            <div id="filter-customer" style="display:none;">
              <label class="input-label">Customer ID</label>
              <input id="filter-customer-id" type="number" class="input-field w-full" placeholder="e.g. 123" />
            </div>
            <div id="filter-order" style="display:none;">
              <label class="input-label">Order ID</label>
              <input id="filter-order-id" type="number" class="input-field w-full" placeholder="e.g. 456" />
            </div>
            <div id="filter-product" style="display:none;">
              <label class="input-label">Product ID</label>
              <input id="filter-product-id" type="number" class="input-field w-full" placeholder="e.g. 789" />
            </div>
          </div>
          <div id="chat-log" class="min-h-[320px] max-h-[520px] overflow-auto space-y-2 text-sm"></div>
          <form id="chat-form" class="flex items-center gap-2">
            <input id="chat-input" class="input-field flex-1" placeholder="Ask about sales, orders, or customers..." />
            <button class="btn-primary" type="submit">Send</button>
          </form>
          <div id="chat-status" class="text-xs text-gray-500"></div>
        </div>

        <div class="card p-4 space-y-3">
          <div class="font-semibold">Setup</div>
          <ul class="text-sm list-disc ml-5 space-y-1">
            <li>Run Ollama locally (default http://localhost:11434)</li>
            <li>Set <code>OLLAMA_MODEL</code> in .env (e.g. llama3)</li>
            <li>Optional: <code>OLLAMA_BASE_URL</code></li>
            <li>Provider can be changed in AI Settings</li>
          </ul>
          <div class="text-xs text-gray-500">This chat uses your live sales, orders, and customers data (summarized).</div>
        </div>
      </div>
    </div>
  </div>

  <script>
    (function(){
      const csrf = document.querySelector('meta[name="csrf-token"]').content;
      const form = document.getElementById('chat-form');
      const input = document.getElementById('chat-input');
      const log = document.getElementById('chat-log');
      const status = document.getElementById('chat-status');
      const start = document.getElementById('filter-start');
      const end = document.getElementById('filter-end');
      const scope = document.getElementById('filter-scope');
      const extra = document.getElementById('filter-extra');
      const custWrap = document.getElementById('filter-customer');
      const orderWrap = document.getElementById('filter-order');
      const productWrap = document.getElementById('filter-product');
      const custId = document.getElementById('filter-customer-id');
      const orderId = document.getElementById('filter-order-id');
      const productId = document.getElementById('filter-product-id');

      function append(role, text){
        const row = document.createElement('div');
        row.className = role === 'user' ? 'text-gray-900 dark:text-gray-100' : 'text-gray-700 dark:text-gray-300';
        row.innerHTML = `<span class="font-medium">${role === 'user' ? 'You' : 'AI'}:</span> <span class="whitespace-pre-line">${text.replace(/</g, '&lt;')}</span>`;
        log.appendChild(row); log.scrollTop = log.scrollHeight;
      }

      function refreshScopeInputs(){
        extra.style.display = scope.value === 'overview' ? 'none' : '';
        custWrap.style.display = scope.value === 'customer' ? '' : 'none';
        orderWrap.style.display = scope.value === 'order' ? '' : 'none';
        productWrap.style.display = scope.value === 'product' ? '' : 'none';
      }
      scope.addEventListener('change', refreshScopeInputs);
      refreshScopeInputs();

      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = (input.value || '').trim();
        if(!text) return;
        append('user', text);
        input.value='';
        status.textContent = 'Thinking...';
        try{
          const payload = { message: text, start_date: start.value || null, end_date: end.value || null, scope: scope.value };
          if (scope.value === 'customer') payload.customer_id = custId.value ? Number(custId.value) : null;
          if (scope.value === 'order') payload.order_id = orderId.value ? Number(orderId.value) : null;
          if (scope.value === 'product') payload.product_id = productId.value ? Number(productId.value) : null;
          const res = await fetch("{{ route('admin.ai.analytics.assist') }}", {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(payload)
          });
          const data = await res.json();
          let reply = data?.reply || 'No reply available.';
          if(!res.ok) reply = data?.error || reply;
          append('assistant', reply);
        }catch(err){
          append('assistant', 'Network error. Please try again.');
        } finally {
          status.textContent = '';
        }
      });
    })();
  </script>
</x-admin-layout>
