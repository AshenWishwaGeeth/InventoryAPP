<template>
  <div class="max-w-5xl mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Inventory Management </h1>

    <!-- Feedback Message -->
    <div v-if="message" class="mb-4 p-3 rounded bg-green-100 text-green-800 border border-green-300">
      {{ message }}
    </div>

    <!-- Search Bar -->
    <div class="flex items-center mb-6">
      <input
        v-model="search"
        @input="fetchItems"
        placeholder="Search item by name..."
        class="flex-1 border rounded-l px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-300"
      />
      <button @click="fetchItems" class="bg-blue-600 text-white px-4 py-2 rounded-r hover:bg-blue-700">
        Search
      </button>
    </div>

    <!-- Add Items Form (Single) -->
    <div class="bg-white shadow rounded p-6 mb-8">
      <h2 class="text-xl font-semibold mb-4 text-gray-700">Add Item</h2>
      <form @submit.prevent="addItem">
        <div class="flex items-center mb-2 space-x-2">
          <input v-model="newItem.name" placeholder="Item Name" required class="border rounded px-2 py-1 flex-1" />
          <input v-model="newItem.unit" placeholder="Unit (Kg, pcs, etc.)" required class="border rounded px-2 py-1 w-32" />
          <input v-model.number="newItem.quantity" type="number" step="0.01" min="0.01" placeholder="Quantity" required class="border rounded px-2 py-1 w-28" />
        </div>
        <div class="flex space-x-2 mt-2">
          <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            Add Item
          </button>
        </div>
      </form>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white shadow rounded p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-700">Inventory List</h2>
        <button @click="showDeductModal = true" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
          Deduct Multiple Items
        </button>
      </div>
      <table class="w-full border rounded overflow-hidden">
        <thead class="bg-gray-100">
          <tr>
            <th class="border px-3 py-2 text-left">Name</th>
            <th class="border px-3 py-2 text-left">Unit</th>
            <th class="border px-3 py-2 text-right">Quantity</th>
            <th class="border px-3 py-2 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in filteredItems" :key="item.id" class="hover:bg-gray-50">
            <td class="border px-3 py-2">{{ item.name }}</td>
            <td class="border px-3 py-2">{{ item.unit }}</td>
            <td class="border px-3 py-2 text-right">{{ item.quantity }}</td>
            <td class="border px-3 py-2 text-center space-x-2">
              <form @submit.prevent="updateQuantity(item, 'addition')" class="inline-flex items-center space-x-1">
                <input v-model.number="item.addQty" type="number" placeholder="Qty" min="0.01" step="0.01" class="border rounded px-2 py-1 w-20"/>
                <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Add</button>
              </form>
              <form @submit.prevent="updateQuantity(item, 'deduction')" class="inline-flex items-center space-x-1">
                <input v-model.number="item.deductQty" type="number" placeholder="Qty" min="0.01" step="0.01" class="border rounded px-2 py-1 w-20"/>
                <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Deduct</button>
              </form>
              <button @click="openHistory(item)" class="text-blue-600 underline hover:text-blue-800">History</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Deduct Multiple Items Modal -->
    <div v-if="showDeductModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
      <div class="bg-white rounded shadow-lg p-6 w-full max-w-2xl">
        <h3 class="text-lg font-semibold mb-4">Deduct Multiple Items</h3>
        <form @submit.prevent="deductMultipleItems">
          <div v-for="item in items" :key="item.id" class="flex items-center mb-2 space-x-2">
            <span class="w-40">{{ item.name }} ({{ item.unit }})</span>
            <input v-model.number="multiDeduct[item.id]" type="number" min="0.01" step="0.01" placeholder="Qty" class="border rounded px-2 py-1 w-24"/>
          </div>
          <div class="flex justify-end space-x-2 mt-4">
            <button type="button" @click="showDeductModal = false" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">Deduct</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Item History Modal -->
    <div v-if="showHistoryModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
      <div class="bg-white rounded shadow-lg p-6 w-full max-w-lg">
        <h3 class="text-lg font-semibold mb-4">History for {{ historyItem?.name }}</h3>
        <div v-if="loadingHistory" class="text-gray-500">Loading...</div>
        <div v-else>
          <ul class="divide-y">
            <li v-for="h in itemHistory" :key="h.id" class="py-2 flex justify-between">
              <span>
                <span :class="h.type === 'addition' ? 'text-green-600' : 'text-red-600'">
                  {{ h.type === 'addition' ? '+' : '-' }}{{ h.quantity }}
                </span>
                <span class="ml-2 text-gray-700">{{ h.unit }}</span>
              </span>
              <span class="text-gray-500 text-sm">{{ h.created_at }}</span>
            </li>
          </ul>
        </div>
        <div class="flex justify-end mt-4">
          <button @click="showHistoryModal = false" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/inertia-vue3';

const props = usePage().props.value;
const items = ref(props.items || []);
const message = ref(props.flash?.success || '');

const search = ref('');
const newItem = ref({ name: '', unit: '', quantity: 0 });

// Filtered items based on search
const filteredItems = computed(() => {
  if (!search.value) return items.value;
  // Find exact match (case-insensitive)
  const found = items.value.find(
    i => i.name.trim().toLowerCase() === search.value.trim().toLowerCase()
  );
  return found ? [found] : [];
});

function fetchItems() {
  // Only fetch from server if search is empty, otherwise filter locally
  if (!search.value) {
    Inertia.get('/items', {}, { preserveState: true, replace: true });
  }
  // Otherwise, filtering is handled by computed property
}

function addItem() {
  Inertia.post('/items', newItem.value, {
    onSuccess: () => {
      newItem.value = { name: '', unit: '', quantity: 0 };
      message.value = 'Item added successfully!';
    }
  });
}

function updateQuantity(item, type) {
  const qty = type === 'addition' ? item.addQty : item.deductQty;
  if (!qty || qty <= 0) return;
  Inertia.post(`/items/${item.id}/update-quantity`, { type, quantity: qty }, {
    onSuccess: () => {
      item.addQty = 0;
      item.deductQty = 0;
      message.value = `Quantity ${type === 'addition' ? 'added' : 'deducted'} successfully!`;
    }
  });
}

// Deduct Multiple Items
const showDeductModal = ref(false);
const multiDeduct = reactive({});
function deductMultipleItems() {
  const deductions = Object.entries(multiDeduct)
    .filter(([id, qty]) => qty && qty > 0)
    .map(([id, qty]) => ({ id, quantity: qty }));
  if (!deductions.length) return;
  Inertia.post('/items/deduct-multiple', { deductions }, {
    onSuccess: () => {
      Object.keys(multiDeduct).forEach(k => multiDeduct[k] = 0);
      showDeductModal.value = false;
      message.value = 'Items deducted successfully!';
    }
  });
}

// Item History Modal
const showHistoryModal = ref(false);
const historyItem = ref(null);
const itemHistory = ref([]);
const loadingHistory = ref(false);

function openHistory(item) {
  showHistoryModal.value = true;
  historyItem.value = item;
  loadingHistory.value = true;
  // Fetch history via Inertia visit and update modal data
  Inertia.get(`/items/${item.id}/history`, {}, {
    preserveState: true,
    only: ['history'],
    onSuccess: (page) => {
      itemHistory.value = page.props.history || [];
      loadingHistory.value = false;
    }
  });
}
</script>
