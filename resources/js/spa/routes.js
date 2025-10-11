import ProductsIndex from './views/ProductsIndex.vue';
import ProductShow from './views/ProductShow.vue';
import CartView from './views/CartView.vue';
import OrderShow from './views/OrderShow.vue';
import AdminProductsIndex from './views/admin/AdminProductsIndex.vue';
import AdminProductCreate from './views/admin/AdminProductCreate.vue';
import AdminProductEdit from './views/admin/AdminProductEdit.vue';

export default [
  { path: '/', component: ProductsIndex },
  { path: '/products/:slug', component: ProductShow, props: true },
  { path: '/cart', component: CartView },
  { path: '/orders/:id', component: OrderShow, props: true },

  { path: '/admin/products', component: AdminProductsIndex },
  { path: '/admin/products/create', component: AdminProductCreate },
  { path: '/admin/products/:id/edit', component: AdminProductEdit, props: true },
];

