<template>
  <div class=\"md:flex\">
    <AdminSidebar />
    <div class=\"flex-1\">
      <AdminTopbar @toggle-theme=\"toggleTheme\" />
      
  <div>
    <h1 class="text-xl font-semibold mb-4">New Product</h1>
    <div v-if="errors.length" class="mb-4 p-4 rounded bg-red-100 text-red-800">
      <ul class="list-disc ml-5"><li v-for="(e,i) in errors" :key="i">{{ e }}</li></ul>
    </div>
    <form @submit.prevent="submit" class="card">
      <div>
        <label class="input-label">Name</label>
        <input v-model="form.name" class="input-field" required />
      </div>
      <div>
        <label class="input-label">Slug</label>
        <input v-model="form.slug" class="input-field" required />
      </div>
      <div>
        <label class="input-label">Description</label>
        <textarea v-model="form.description" class="input-field" rows="4" />
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="input-label">Stock</label>
          <input type="number" v-model.number="form.stock" min="0" class="input-field" required />
        </div>
        <div>
          <label class="input-label">Image Path</label>
          <input v-model="form.image_path" class="input-field" />
        </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="input-label">Net Price</label>
          <input type="number" v-model.number="form.net_price_cents" min="0" class="input-field" required  step="0.01" />
        </div>
        <div>
          <label class="input-label">Tax</label>
          <input type="number" v-model.number="form.tax_cents" min="0" class="input-field" required  step="0.01" />
        </div>
        <div>
          <label class="input-label">Selling Price</label>
          <input type="number" v-model.number="form.selling_price_cents" min="0" class="input-field" required  step="0.01" />
        </div>
      </div>
      </div>

      <div class="mt-6">
        <div class="flex items-center justify-between mb-2">
          <label class="input-label">Variants</label>
          <button type="button" class="btn-muted" @click="addVariant">+ Add Variant</button>
        </div>
        <div class="space-y-4">
          <div v-for="(v, idx) in variants" :key="idx" class="border rounded p-3 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
              <div>
                <label class="input-label">SKU</label>
                <input v-model="v.sku" class="input-field" />
              </div>
              <div>
                <label class="input-label">Net Price</label>
                <input type="number" step="0.01" min="0" v-model.number="v.net_price" class="input-field" />
              </div>
              <div>
                <label class="input-label">Tax</label>
                <input type="number" step="0.01" min="0" v-model.number="v.tax" class="input-field" />
              </div>
              <div>
                <label class="input-label">Price</label>
                <input type="number" step="0.01" min="0" v-model.number="v.price" class="input-field" />
              </div>
              <div>
                <label class="input-label">Stock</label>
                <input type="number" min="0" v-model.number="v.stock" class="input-field" />
              </div>
              <div class="flex justify-end">
                <button type="button" class="btn-muted" @click="removeVariant(idx)">Remove</button>
              </div>
            </div>
            <div>
              <div class="flex items-center justify-between mb-2">
                <div class="text-sm font-medium">Attributes</div>
                <button type="button" class="btn-muted" @click="addAttr(v)">+ Add Attribute</button>
              </div>
              <div class="space-y-2">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-2" v-for="(a, aIdx) in v.attributes" :key="aIdx">
                  <div class="md:col-span-2">
                    <input class="input-field" placeholder="Key (e.g. color)" v-model="a.key" />
                  </div>
                  <div class="md:col-span-2">
                    <input class="input-field" placeholder="Value (e.g. Red)" v-model="a.value" />
                  </div>
                  <div class="flex items-center">
                    <button type="button" class="btn-muted" @click="v.attributes.splice(aIdx, 1)">Remove</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="flex items-center justify-end gap-2">
        <RouterLink to="/app/admin/products" class="btn-muted">Cancel</RouterLink>
        <button class="btn-primary">Create</button>
      </div>
    </form>
  </div>

    </div>
  </div>
</template><script setup>
import AdminTopbar from '../../components/AdminTopbar.vue';
import AdminSidebar from '../../components/AdminSidebar.vue';



import axios from 'axios';
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const form = reactive({ name: '', slug: '', description: '', stock: 0, image_path: '', net_price_cents: 0, tax_cents: 0, selling_price_cents: 0, variants_json: '' });
const errors = ref([]);
const variants = ref([]);

function addVariant(){ variants.value.push({ sku: '', net_price: null, tax: null, price: null, stock: 0, attributes: [] }); }
function removeVariant(idx){ variants.value.splice(idx,1); }
function addAttr(v){ v.attributes.push({ key: '', value: '' }); }

function buildVariantsPayload() {
  return JSON.stringify(
    variants.value
      .map(v => {
        const attrs = {};
        (v.attributes||[]).forEach(a => { if ((a.key||'').trim() !== '') attrs[a.key] = a.value; });
        const net = v.net_price == null || v.net_price === '' ? null : Number(v.net_price);
        const tax = v.tax == null || v.tax === '' ? null : Number(v.tax);
        const price = v.price == null || v.price === '' ? ((net != null || tax != null) ? ((net || 0) + (tax || 0)) : null) : Number(v.price);
        return {
          sku: v.sku || null,
          attributes: attrs,
          net_price: Number.isFinite(net) ? net : null,
          tax: Number.isFinite(tax) ? tax : null,
          price: Number.isFinite(price) ? price : null,
          stock: Number.isFinite(v.stock) ? v.stock : 0,
        };
      })
      .filter(x => (x.sku || Object.keys(x.attributes).length || x.price != null || x.stock))
  );
}

async function submit() {
  errors.value = [];
  try {
    form.variants_json = buildVariantsPayload();
    await axios.post('/admin/products', form);
    router.push('/app/admin/products');
  } catch (e) {
    const res = e.response?.data;
    if (res?.errors) errors.value = Object.values(res.errors).flat();
    else errors.value = [res?.message || 'Failed to create product'];
  }
}







function toggleTheme(){
  const root = document.documentElement;
  const isDark = root.classList.toggle('dark');
  try { localStorage.setItem('theme', isDark ? 'dark' : 'light'); } catch(e) {}
}
</script>
