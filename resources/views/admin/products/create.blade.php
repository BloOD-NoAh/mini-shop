<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">New Product</h2>
    </x-slot>

    <div class="py-6">
        <div class="container-narrow">
            @if (session('status'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 rounded bg-red-100 text-red-800">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ url('/admin/products') }}" method="POST" enctype="multipart/form-data" class="card space-y-4">
                @csrf
                <div>
                    <label class="input-label">Name</label>
                    <input name="name" value="{{ old('name') }}" class="input-field" required>
                </div>
                <div>
                    <label class="input-label">Slug</label>
                    <input name="slug" value="{{ old('slug') }}" class="input-field" required>
                </div>
                <div>
                    <label class="input-label">Description</label>
                    <textarea name="description" class="input-field" rows="4">{{ old('description') }}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Stock</label>
                        <input type="number" name="stock" value="{{ old('stock') }}" min="0" class="input-field" required>
                    </div>
                    <div>
                        <label class="input-label">Category</label>
                        <input name="category" value="{{ old('category') }}" class="input-field">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="input-label">Net Price</label>
                        <input type="number" name="net_price_cents" value="{{ old('net_price_cents') }}" min="0" class="input-field" required step="0.01">
                    </div>
                    <div>
                        <label class="input-label">Tax</label>
                        <input type="number" name="tax_cents" value="{{ old('tax_cents') }}" min="0" class="input-field" required step="0.01">
                    </div>
                    <div>
                        <label class="input-label">Selling Price</label>
                        <input type="number" name="selling_price_cents" value="{{ old('selling_price_cents') }}" min="0" class="input-field" required step="0.01">
                    </div>
                </div>
                <div>
                    <label class="input-label">Image</label>
                    <input type="file" name="image" accept="image/*" class="input-field">
                </div>
                <div>
                    <div class="flex items-center justify-between">
                        <label class="input-label">Variants</label>
                        <button type="button" id="add-variant" class="btn-muted">+ Add Variant</button>
                    </div>
                    <div id="variants-list" class="space-y-4 mt-2"></div>

                    <textarea name="variants_json" id="variants-json" class="hidden" rows="6">{{ old('variants_json') }}</textarea>
                    <p class="text-sm text-gray-500 mt-2">Optional. Add variants with SKU, price, stock and attributes. Price entered as decimal; saved in cents.</p>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ url('/admin/products') }}" class="btn-muted">Cancel</a>
                    <button class="btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>

<script>
  (function(){
    const list = document.getElementById('variants-list');
    const btnAdd = document.getElementById('add-variant');
    const textarea = document.getElementById('variants-json');
    let variants = [];

    function addVariant(v = null){
      const variant = v || { sku: '', net_price: '', tax: '', price: '', stock: 0, attributes: [] };
      variants.push(variant);
      render();
    }

    function addAttr(variant){
      variant.attributes.push({ key: '', value: '' });
      render();
    }

    function removeVariant(idx){
      variants.splice(idx,1);
      render();
    }

    function render(){
      list.innerHTML = '';
      variants.forEach((v, idx) => {
        const el = document.createElement('div');
        el.className = 'border rounded p-3 space-y-3';
        el.innerHTML = `
          <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div>
              <label class="input-label">SKU</label>
              <input class="input-field" data-field="sku" value="${v.sku || ''}" />
            </div>
            <div>
              <label class="input-label">Net Price</label>
              <input type="number" step="0.01" min="0" class="input-field" data-field="net_price" value="${v.net_price ?? ''}" />
            </div>
            <div>
              <label class="input-label">Tax</label>
              <input type="number" step="0.01" min="0" class="input-field" data-field="tax" value="${v.tax ?? ''}" />
            </div>
            <div>
              <label class="input-label">Price</label>
              <input type="number" step="0.01" min="0" class="input-field" data-field="price" value="${v.price ?? ''}" />
            </div>
            <div>
              <label class="input-label">Stock</label>
              <input type="number" min="0" class="input-field" data-field="stock" value="${v.stock ?? 0}" />
            </div>
            <div class="flex justify-end">
              <button type="button" class="btn-muted" data-remove>Remove</button>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between mb-2">
              <div class="text-sm font-medium">Attributes</div>
              <button type="button" class="btn-muted" data-add-attr>+ Add Attribute</button>
            </div>
            <div class="space-y-2" data-attrs></div>
          </div>`;

        // Wire inputs
        el.querySelector('[data-field="sku"]').addEventListener('input', e => { v.sku = e.target.value; sync(); });
        el.querySelector('[data-field="net_price"]').addEventListener('input', e => { v.net_price = e.target.value; sync(); });
        el.querySelector('[data-field="tax"]').addEventListener('input', e => { v.tax = e.target.value; sync(); });
        el.querySelector('[data-field="price"]').addEventListener('input', e => { v.price = e.target.value; sync(); });
        el.querySelector('[data-field="stock"]').addEventListener('input', e => { v.stock = parseInt(e.target.value||'0',10)||0; sync(); });
        el.querySelector('[data-remove]').addEventListener('click', () => removeVariant(idx));
        el.querySelector('[data-add-attr]').addEventListener('click', () => { addAttr(v); });

        const attrsWrap = el.querySelector('[data-attrs]');
        (v.attributes || []).forEach((a, aIdx) => {
          const row = document.createElement('div');
          row.className = 'grid grid-cols-1 md:grid-cols-5 gap-2';
          row.innerHTML = `
            <div class="md:col-span-2">
              <input class="input-field" placeholder="Key (e.g. color)" value="${a.key || ''}" data-akey />
            </div>
            <div class="md:col-span-2">
              <input class="input-field" placeholder="Value (e.g. Red)" value="${a.value || ''}" data-avalue />
            </div>
            <div class="flex items-center">
              <button type="button" class="btn-muted" data-adel>Remove</button>
            </div>`;
          row.querySelector('[data-akey]').addEventListener('input', e => { a.key = e.target.value; sync(); });
          row.querySelector('[data-avalue]').addEventListener('input', e => { a.value = e.target.value; sync(); });
          row.querySelector('[data-adel]').addEventListener('click', () => { v.attributes.splice(aIdx,1); render(); });
          attrsWrap.appendChild(row);
        });

        list.appendChild(el);
      });
      sync();
    }

    function sync(){
      // Build JSON payload expected by backend
      const payload = variants
        .map(v => {
          const attrs = {};
          (v.attributes||[]).forEach(a => { if ((a.key||'').trim() !== '') attrs[a.key] = a.value; });
          const net = v.net_price === '' || v.net_price == null ? null : parseFloat(v.net_price);
          const tax = v.tax === '' || v.tax == null ? null : parseFloat(v.tax);
          const price = v.price === '' || v.price == null ? (net != null || tax != null ? ((net || 0) + (tax || 0)) : null) : parseFloat(v.price);
          return {
            sku: v.sku || null,
            attributes: attrs,
            price: Number.isFinite(price) ? price : null,
            net_price: Number.isFinite(net) ? net : null,
            tax: Number.isFinite(tax) ? tax : null,
            stock: Number.isFinite(v.stock) ? v.stock : 0,
          };
        })
        // drop completely empty rows
        .filter(x => (x.sku || Object.keys(x.attributes).length || (x.price_cents !== null) || x.stock));
      textarea.value = JSON.stringify(payload);
    }

    // initialize from old textarea if present
    try {
      const initial = textarea.value ? JSON.parse(textarea.value) : [];
      if (Array.isArray(initial)) {
        initial.forEach(iv => {
          addVariant({
            sku: iv.sku || '',
            net_price: (iv.net_price ?? ''),
            tax: (iv.tax ?? ''),
            price: (iv.price ?? ((iv.net_price != null || iv.tax != null) ? (((iv.net_price || 0) + (iv.tax || 0)).toFixed(2)) : '')),
            stock: iv.stock || 0,
            attributes: Object.entries(iv.attributes || {}).map(([key, value]) => ({ key, value }))
          });
        });
      }
    } catch (e) {}

    btnAdd.addEventListener('click', () => addVariant());
    // Ensure sync before submit
    const form = textarea.closest('form');
    if (form) form.addEventListener('submit', () => sync());
    // Render initial (no items)
    render();
  })();
</script>


