<template>
  <div class="overflow-x-auto">
    <table class="w-full divide-y divide-gray-200">
      <thead class="bg-gray-100 border-b border-gray-200">
        <tr>
          <th
            class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider sticky left-0 bg-gray-100 z-10"
          >
            Category
          </th>
          <th
            v-for="month in months"
            :key="month.number"
            class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider"
            colspan="2"
          >
            {{ month.abbr }}
          </th>
          <th
            class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider"
            colspan="2"
          >
            Total
          </th>
        </tr>
        <tr>
          <th
            class="px-4 py-2 text-left text-xs font-medium text-gray-600 sticky left-0 bg-gray-100 z-10"
          ></th>
          <template v-for="month in months" :key="month.number">
            <th class="px-2 py-2 text-center text-xs font-medium text-gray-600">
              Forecast
            </th>
            <th class="px-2 py-2 text-center text-xs font-medium text-gray-600">
              Actual
            </th>
          </template>
          <th class="px-2 py-2 text-center text-xs font-medium text-gray-600">
            Forecast
          </th>
          <th class="px-2 py-2 text-center text-xs font-medium text-gray-600">
            Actual
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr
          v-for="category in categories"
          :key="category.id"
          class="hover:bg-gray-50 transition-colors"
        >
          <!-- Category Name -->
          <td
            class="px-4 py-3 text-sm font-medium text-gray-900 sticky left-0 bg-white z-10"
          >
            {{ category.name }}
          </td>

          <!-- Month Columns -->
          <template v-for="month in months" :key="month.number">
            <!-- Forecast Cell -->
            <td class="px-2 py-3 text-center">
              <EditableCell
                v-if="!isResident"
                :value="category.months[month.number]?.forecast || 0"
                :category-id="category.id"
                :month="month.number"
                :year="year"
                @update="handleUpdateForecast"
              />
              <span v-else class="text-sm text-gray-900">
                {{
                  formatCurrency(category.months[month.number]?.forecast || 0)
                }}
              </span>
            </td>

            <!-- Actual Cell -->
            <td
              class="px-2 py-3 text-center text-sm"
              :class="getActualCellClass(category.months[month.number])"
            >
              {{ formatCurrency(category.months[month.number]?.actual || 0) }}
            </td>
          </template>

          <!-- Total Columns -->
          <td class="px-2 py-3 text-center text-sm font-medium text-gray-900">
            {{ formatCurrency(category.total.forecast) }}
          </td>
          <td
            class="px-2 py-3 text-center text-sm font-medium"
            :class="getActualCellClass(category.total)"
          >
            {{ formatCurrency(category.total.actual) }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useUpdateBudgetEntries } from '../composables/useBudget';
import EditableCell from './EditableCell.vue';
import type {
  BudgetCategoryData,
  BudgetEntryUpdateDto,
} from '@neibrpay/models';
import { getMonthAbbreviation, isOnBudget } from '@neibrpay/models';

interface Props {
  categories: BudgetCategoryData[];
  isResident: boolean;
  year: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'update-entry', entry: BudgetEntryUpdateDto): void;
}>();

const updateEntriesMutation = useUpdateBudgetEntries();

const months = computed(() => {
  return Array.from({ length: 12 }, (_, i) => ({
    number: i + 1,
    abbr: getMonthAbbreviation(i + 1),
  }));
});

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

const getActualCellClass = (monthData?: {
  forecast: number;
  actual: number;
}): string => {
  if (!monthData) return 'text-gray-900';
  return isOnBudget(monthData.actual, monthData.forecast)
    ? 'text-green-600 font-medium'
    : 'text-red-600 font-medium';
};

const handleUpdateForecast = async (
  categoryId: number,
  month: number,
  value: number
) => {
  const entry: BudgetEntryUpdateDto = {
    budget_category_id: categoryId,
    year: props.year,
    month,
    forecast_amount: value,
  };

  try {
    await updateEntriesMutation.mutateAsync({
      entries: [entry],
    });
    emit('update-entry', entry);
  } catch (error) {
    console.error('Failed to update forecast:', error);
  }
};
</script>
