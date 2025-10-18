<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Product #{{ $product->id }}</h2>
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

            <form action="{{ url('/admin/products/'.$product->id) }}" method="POST" enctype="multipart/form-data" class="card space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="input-label">Name</label>
                    <input name="name" value="{{ old('name', $product->name) }}" class="input-field" required>
                </div>
                <div>
                    <label class="input-label">Slug</label>
                    <input name="slug" value="{{ old('slug', $product->slug) }}" class="input-field" required>
                </div>
                <div>
                    <label class="input-label">Description</label>
                    <textarea name="description" class="input-field" rows="4">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Stock</label>
                        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="0" class="input-field" required>
                    </div>
                    <div>
                        <label class="input-label">Category</label>
                        <input name="category" value="{{ old('category', $product->category) }}" class="input-field">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="input-label">Net Price</label>
                        <input type="number" name="net_price_cents" value="{{ old('net_price_cents', $product->net_price_cents) }}" min="0" step="0.01" class="input-field">
                    </div>
                    <div>
                        <label class="input-label">Tax</label>
                        <input type="number" name="tax_cents" value="{{ old('tax_cents', $product->tax_cents) }}" min="0" step="0.01" class="input-field">
                    </div>
                    <div>
                        <label class="input-label">Selling Price</label>
                        <input type="number" name="selling_price_cents" value="{{ old('selling_price_cents', $product->selling_price_cents) }}" min="0" step="0.01" class="input-field">
                    </div>
                </div>
                <div>
                    <label class="input-label">Image</label>
                    <input type="file" name="image" accept="image/*" class="input-field">
                    @if ($product->image_path)
                        <div class="mt-2">
                            <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="w-32 h-32 object-cover rounded">
                        </div>
                    @endif
                </div>
                <div>
                    <div class="flex items-center justify-between">
                        <label class="input-label">Variants</label>
                        <button type="button" id="add-variant" class="btn-muted">+ Add Variant</button>
                    </div>
                    <div id="variants-list" class="space-y-4 mt-2"></div>

                    <textarea name="variants_json" id="variants-json" class="hidden" rows="6">{{ old('variants_json', $product->variants?->map(fn($v) => [
                        'sku' => $v->sku,
                        'attributes' => $v->attributes,
                        'price' => $v->price,
                        'net_price' => $v->net_price,
                        'tax' => $v->tax,
                        'stock' => $v->stock,
                    ])->toJson(JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)) }}</textarea>
                    <p class="text-sm text-gray-500 mt-2">Optional. Replace all variants by managing rows. Price entered as decimal; saved in cents.</p>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ url('/admin/products') }}" class="btn-muted">Cancel</a>
                    <button class="btn-primary">Save</button>
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
      const variant = v || { sku: '', net_price: '', tax: '', selling_price: '', price: '', stock: 0, attributes: [] };
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
          <div class=\"grid grid-cols-1 md:grid-cols-6 gap-3 items-end\">\n            <div>\n              <label class=\"input-label\">SKU<\/label>\n              <input class=\"input-field\" data-field=\"sku\" value=\"${v.sku || ''}\" \/>\n            <\/div>\n            <div>\n              <label class=\"input-label\">Net Price<\/label>\n              <input type=\"number\" step=\"0.01\" min=\"0\" class=\"input-field\" data-field=\"net_price\" value=\"${v.net_price ?? ''}\" \/>\n            <\/div>\n            <div>\n              <label class=\"input-label\">Tax<\/label>\n              <input type=\"number\" step=\"0.01\" min=\"0\" class=\"input-field\" data-field=\"tax\" value=\"${v.tax ?? ''}\" \/>\n            <\/div>\n            <div>\n              <label class=\"input-label\">Price<\/label>\n              <input type=\"number\" step=\"0.01\" min=\"0\" class=\"input-field\" data-field=\"price\" value=\"${v.price ?? ''}\" \/>\n            <\/div>\n            <div>\n              <label class=\"input-label\">Stock<\/label>\n              <input type=\"number\" min=\"0\" class=\"input-field\" data-field=\"stock\" value=\"${v.stock ?? 0}\" \/>\n            <\/div>\n            <div class=\"flex justify-end\">\n              <button type=\"button\" class=\"btn-muted\" data-remove>Remove<\/button>\n            <\/div>\n          <\/div>\n          <div>\n            <div class=\"flex items-center justify-between mb-2\">\n              <div class=\"text-sm font-medium\">Attributes<\/div>\n              <button type=\"button\" class=\"btn-muted\" data-add-attr>+ Add Attribute<\/button>\n            <\/div>\n            <div class=\"space-y-2\" data-attrs><\/div>\n          <\/div>`;

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
            <div class=\"md:col-span-2\">\n              <input class=\"input-field\" placeholder=\"Key (e.g. color)\" value=\"${a.key || ''}\" data-akey \/>\n            <\/div>\n            <div class=\"md:col-span-2\">\n              <input class=\"input-field\" placeholder=\"Value (e.g. Red)\" value=\"${a.value || ''}\" data-avalue \/>\n            <\/div>\n            <div class=\"flex items-center\">\n              <button type=\"button\" class=\"btn-muted\" data-adel>Remove<\/button>\n            <\/div>`;
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
        .filter(x => (x.sku || Object.keys(x.attributes).length || (x.price_cents !== null) || x.stock));
      textarea.value = JSON.stringify(payload);
    }

    // initialize from textarea JSON
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
    const form = textarea.closest('form');
    if (form) form.addEventListener('submit', () => sync());
    render();
  })();
</script>




