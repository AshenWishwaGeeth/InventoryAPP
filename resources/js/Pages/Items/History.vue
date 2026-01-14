<template>
  <div class="container py-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h1 class="display-6 fw-bold text-primary">{{ item.name }} - History</h1>
      <a
        href="/items"
        class="btn btn-primary"
      >
        <i class="bi bi-arrow-left"></i> Back to Inventory 
      </a>
    </div>

    <!-- Summary Section -->
    <div class="mb-4 card shadow-sm">
      <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
        <div>
          <div class="fw-semibold text-secondary">Current Quantity:</div>
          <div class="h4 fw-bold text-primary">{{ item.quantity }} <span class="fs-6 text-muted">{{ item.unit }}</span></div>
        </div>
        <div>
          <div class="fw-semibold text-secondary">Total Additions:</div>
          <div class="text-success fw-bold fs-5">
            {{ totalAdditions }}
          </div>
          <div class="fw-semibold text-secondary mt-2">Total Deductions:</div>
          <div class="text-danger fw-bold fs-5">
            {{ totalDeductions }}
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Type</th>
              <th>Quantity</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in transactions" :key="t.id">
              <td class="text-capitalize">
                <span :class="t.type === 'addition' ? 'text-success' : 'text-danger'">
                  {{ t.type }}
                </span>
              </td>
              <td>{{ t.quantity }} <span class="text-muted">{{ item.unit }}</span></td>
              <td>{{ new Date(t.created_at).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="transactions.length === 0" class="text-center text-muted py-4">
        No history found for this item.
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';

const props = usePage().props.value;
const item = props.item;
const transactions = props.transactions;

const totalAdditions = computed(() =>
  transactions.filter(t => t.type === 'addition').reduce((sum, t) => sum + Number(t.quantity), 0)
);
const totalDeductions = computed(() =>
  transactions.filter(t => t.type === 'deduction').reduce((sum, t) => sum + Number(t.quantity), 0)
);
</script>
