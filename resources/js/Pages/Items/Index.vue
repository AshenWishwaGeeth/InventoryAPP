<template>
  <div class="container py-5">
    <h1 class="display-4 mb-4 text-primary fw-bold text-center">Inventory Management</h1>

    <!-- Feedback Message -->
    <div v-if="message" class="alert alert-success alert-dismissible fade show" role="alert">
      {{ message }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" @click="message = ''"></button>
    </div>

    <!-- Search Bar -->
    <div class="input-group mb-4">
      <input
        v-model="search"
        @input="fetchItems"
        placeholder="Search item by name..."
        class="form-control"
      />
      <button @click="fetchItems" class="btn btn-primary">
        <i class="bi bi-search"></i> Search
      </button>
    </div>

    <!-- Add Items Form (Single) -->
    <div class="card shadow mb-5">
      <div class="card-header bg-primary text-white">
        <h2 class="h5 mb-0">Add Item</h2>
      </div>
      <div class="card-body">
        <form @submit.prevent="addItem">
          <div class="row g-2 align-items-center mb-3">
            <div class="col-md-5">
              <input v-model="newItem.name" placeholder="Item Name" required class="form-control" />
            </div>
            <div class="col-md-3">
              <input v-model="newItem.unit" placeholder="Unit (Kg, pcs, etc.)" required class="form-control" />
            </div>
            <div class="col-md-2">
              <input v-model.number="newItem.quantity" type="number" step="0.01" min="0.01" placeholder="Quantity" required class="form-control" />
            </div>
            <div class="col-md-2 d-grid">
              <button type="submit" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add Item
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Inventory Table -->
    <div class="card shadow">
      <div class="card-header d-flex justify-content-between align-items-center bg-light">
        <h2 class="h5 mb-0 text-primary">Inventory List</h2>
        <button @click="showDeductModal = true" class="btn btn-danger">
          <i class="bi bi-dash-circle"></i> Deduct Multiple Items
        </button>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-primary">
            <tr>
              <th>Name</th>
              <th>Unit</th>
              <th class="text-end">Quantity</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in filteredItems" :key="item.id">
              <td>{{ item.name }}</td>
              <td>{{ item.unit }}</td>
              <td class="text-end">{{ item.quantity }}</td>
              <td class="text-center">
                <form @submit.prevent="updateQuantity(item, 'addition')" class="d-inline-flex align-items-center me-2">
                  <input v-model.number="item.addQty" type="number" placeholder="Qty" min="0.01" step="0.01" class="form-control form-control-sm me-1" style="width: 80px;"/>
                  <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-plus"></i> Add</button>
                </form>
                <form @submit.prevent="updateQuantity(item, 'deduction')" class="d-inline-flex align-items-center me-2">
                  <input v-model.number="item.deductQty" type="number" placeholder="Qty" min="0.01" step="0.01" class="form-control form-control-sm me-1" style="width: 80px;"/>
                  <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-dash"></i> Deduct</button>
                </form>
                <button @click="openHistory(item)" class="btn btn-link btn-sm text-decoration-underline text-primary">
                  <i class="bi bi-clock-history"></i> History
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Deduct Multiple Items Modal -->
    <div v-if="showDeductModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.4);z-index:1050;">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">Deduct Multiple Items</h5>
            <button type="button" class="btn-close btn-close-white" @click="showDeductModal = false"></button>
          </div>
          <form @submit.prevent="deductMultipleItems">
            <div class="modal-body">
              <div v-for="item in items" :key="item.id" class="row align-items-center mb-2">
                <div class="col-6 col-md-5 fw-semibold">{{ item.name }} ({{ item.unit }})</div>
                <div class="col-6 col-md-4">
                  <input v-model.number="multiDeduct[item.id]" type="number" min="0.01" step="0.01" placeholder="Qty" class="form-control"/>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" @click="showDeductModal = false" class="btn btn-secondary">Cancel</button>
              <button type="submit" class="btn btn-danger">Deduct</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Item History Modal -->
    <div v-if="showHistoryModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.4);z-index:1050;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">History for {{ historyItem?.name }}</h5>
            <button type="button" class="btn-close btn-close-white" @click="showHistoryModal = false"></button>
          </div>
          <div class="modal-body">
            <div v-if="loadingHistory" class="text-muted">Loading...</div>
            <div v-else>
              <ul class="list-group">
                <li v-for="h in itemHistory" :key="h.id" class="list-group-item d-flex justify-content-between align-items-center">
                  <span>
                    <span :class="h.type === 'addition' ? 'text-success fw-bold' : 'text-danger fw-bold'">
                      {{ h.type === 'addition' ? '+' : '-' }}{{ h.quantity }}
                    </span>
                    <span class="ms-2 text-secondary">{{ h.unit }}</span>
                  </span>
                  <span class="text-muted small">{{ h.created_at }}</span>
                </li>
              </ul>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="showHistoryModal = false" class="btn btn-secondary">Close</button>
          </div>
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
