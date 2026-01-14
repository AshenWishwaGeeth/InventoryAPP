<template>
  <div class="max-w-3xl mx-auto py-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-bold text-gray-800">{{ item.name }} - History</h1>
      <a
        href="/items"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition"
      >
        Back to Inventory 
      </a>
    </div>

    <!-- Summary Section -->
    <div class="mb-6 bg-white rounded-lg shadow p-4 flex items-center justify-between">
      <div>
        <div class="text-lg font-semibold text-gray-700">Current Quantity:</div>
        <div class="text-2xl font-bold text-blue-700">{{ item.quantity }} <span class="text-base text-gray-500">{{ item.unit }}</span></div>
      </div>
      <div>
        <div class="text-lg font-semibold text-gray-700">Total Additions:</div>
        <div class="text-green-600 font-bold text-xl">
          {{ totalAdditions }}
        </div>
        <div class="text-lg font-semibold text-gray-700 mt-2">Total Deductions:</div>
        <div class="text-red-600 font-bold text-xl">
          {{ totalDeductions }}
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-gray-600 font-medium uppercase tracking-wider">Type</th>
            <th class="px-6 py-3 text-left text-gray-600 font-medium uppercase tracking-wider">Quantity</th>
            <th class="px-6 py-3 text-left text-gray-600 font-medium uppercase tracking-wider">Date</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="t in transactions" :key="t.id">
            <td class="px-6 py-4 capitalize">
              <span
                :class="t.type === 'addition' ? 'text-green-600' : 'text-red-600'"
              >
                {{ t.type }}
              </span>
            </td>
            <td class="px-6 py-4">{{ t.quantity }} <span class="text-gray-500">{{ item.unit }}</span></td>
            <td class="px-6 py-4">{{ new Date(t.created_at).toLocaleString() }}</td>
          </tr>
        </tbody>
      </table>
      <div v-if="transactions.length === 0" class="text-center text-gray-500 py-8">
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
