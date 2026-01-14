<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Inventory</h1>

    <!-- Search -->
    <input v-model="search" @input="fetchItems" placeholder="Search item..." class="border p-2 mb-4" />

    <!-- Add Item Form -->
    <form @submit.prevent="addItem" class="mb-6">
      <input v-model="newItem.name" placeholder="Item Name" required class="border p-2 mr-2" />
      <input v-model="newItem.unit" placeholder="Unit (Kg, pcs, etc.)" required class="border p-2 mr-2" />
      <input v-model.number="newItem.quantity" type="number" step="0.01" placeholder="Quantity" required class="border p-2 mr-2" />
      <button type="submit" class="bg-blue-500 text-white px-4 py-2">Add Item</button>
    </form>

    <!-- Inventory Table -->
    <table class="w-full border">
      <thead>
        <tr>
          <th class="border px-2 py-1">Name</th>
          <th class="border px-2 py-1">Unit</th>
          <th class="border px-2 py-1">Quantity</th>
          <th class="border px-2 py-1">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td class="border px-2 py-1">{{ item.name }}</td>
          <td class="border px-2 py-1">{{ item.unit }}</td>
          <td class="border px-2 py-1">{{ item.quantity }}</td>
          <td class="border px-2 py-1 space-x-2">
            <form @submit.prevent="updateQuantity(item, 'addition')" class="inline">
              <input v-model.number="item.addQty" type="number" placeholder="Qty" min="0.01" step="0.01" class="border p-1 w-20"/>
              <button type="submit" class="bg-green-500 text-white px-2 py-1">Add</button>
            </form>
            <form @submit.prevent="updateQuantity(item, 'deduction')" class="inline">
              <input v-model.number="item.deductQty" type="number" placeholder="Qty" min="0.01" step="0.01" class="border p-1 w-20"/>
              <button type="submit" class="bg-red-500 text-white px-2 py-1">Deduct</button>
            </form>
            <a :href="`/items/${item.id}/history`" class="text-blue-600 underline">History</a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/inertia-vue3';

const props = usePage().props.value;
const items = ref(props.items || []);

const newItem = ref({
  name: '',
  unit: '',
  quantity: 0
});

const search = ref('');

function fetchItems() {
  Inertia.get('/items', { search: search.value }, { preserveState: true, replace: true });
}

function addItem() {
  Inertia.post('/items', newItem.value, { onSuccess: () => { newItem.value = { name: '', unit: '', quantity: 0 }; } });
}

function updateQuantity(item, type) {
  const qty = type === 'addition' ? item.addQty : item.deductQty;
  if (!qty || qty <= 0) return;
  Inertia.post(`/items/${item.id}/update-quantity`, { type, quantity: qty }, { onSuccess: () => { item.addQty = 0; item.deductQty = 0; } });
}
</script>
