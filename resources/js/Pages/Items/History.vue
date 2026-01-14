<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">{{ item.name }} - History</h1>
    <a href="/items" class="text-blue-600 underline mb-4 inline-block">Back to Inventory</a>
    <table class="w-full border">
      <thead>
        <tr>
          <th class="border px-2 py-1">Type</th>
          <th class="border px-2 py-1">Quantity</th>
          <th class="border px-2 py-1">Date</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="t in transactions" :key="t.id">
          <td class="border px-2 py-1">{{ t.type }}</td>
          <td class="border px-2 py-1">{{ t.quantity }}</td>
          <td class="border px-2 py-1">{{ new Date(t.created_at).toLocaleString() }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/inertia-vue3';
const props = usePage().props.value;

const item = props.item;
const transactions = props.transactions;
</script>
