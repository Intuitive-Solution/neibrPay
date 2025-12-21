<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
        <!-- Overlay -->
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          @click="$emit('close')"
        ></div>

        <!-- Modal Container -->
        <div
          class="flex min-h-full items-center justify-center p-4 text-center"
        >
          <div
            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
            @click.stop
          >
            <!-- Header -->
            <div class="bg-white px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <h3
                  class="text-lg font-semibold text-gray-900"
                  id="modal-title"
                >
                  Manage Budget Categories
                </h3>
                <button
                  @click="$emit('close')"
                  class="text-gray-400 hover:text-gray-500"
                >
                  <svg
                    class="h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="bg-white px-6 py-4 max-h-[70vh] overflow-y-auto">
              <!-- Tabs -->
              <div class="border-b border-gray-200 mb-4">
                <nav class="-mb-px flex space-x-8">
                  <button
                    @click="activeTab = 'income'"
                    :class="[
                      'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                      activeTab === 'income'
                        ? 'border-primary text-primary'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    ]"
                  >
                    Income Categories
                  </button>
                  <button
                    @click="activeTab = 'expense'"
                    :class="[
                      'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                      activeTab === 'expense'
                        ? 'border-primary text-primary'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    ]"
                  >
                    Expense Categories
                  </button>
                </nav>
              </div>

              <!-- Add New Category Form -->
              <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-900 mb-3">
                  Add New Category
                </h4>
                <div class="flex gap-2">
                  <input
                    v-model="newCategoryName"
                    type="text"
                    placeholder="Category name"
                    class="flex-1 input-field text-sm"
                    @keyup.enter="handleCreateCategory"
                  />
                  <button
                    @click="handleCreateCategory"
                    :disabled="!newCategoryName.trim() || isCreating"
                    class="btn-primary btn-sm"
                  >
                    Add
                  </button>
                </div>
              </div>

              <!-- Categories List -->
              <div class="space-y-2">
                <div
                  v-for="category in filteredCategories"
                  :key="category.id"
                  class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg hover:border-gray-300 transition-colors"
                >
                  <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-900">
                      {{ category.name }}
                    </span>
                    <span
                      v-if="category.deleted_at"
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800"
                    >
                      Deleted
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <button
                      @click="startEditCategory(category)"
                      class="text-primary hover:text-primary-600 text-sm"
                    >
                      Edit
                    </button>
                    <button
                      @click="handleDeleteCategory(category)"
                      :disabled="isDeleting === category.id"
                      class="text-red-600 hover:text-red-700 text-sm"
                    >
                      {{
                        isDeleting === category.id ? 'Deleting...' : 'Delete'
                      }}
                    </button>
                  </div>
                </div>

                <div
                  v-if="filteredCategories.length === 0"
                  class="text-center py-8 text-sm text-gray-500"
                >
                  No categories yet. Create one above.
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
              <div class="flex justify-end">
                <button @click="$emit('close')" class="btn-secondary btn-sm">
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Edit Category Modal -->
    <ConfirmDialog
      v-if="editingCategory"
      :is-open="!!editingCategory"
      title="Edit Category"
      :message="`Update category name for ${editingCategory.name}`"
      confirm-text="Update"
      cancel-text="Cancel"
      type="info"
      :is-loading="isUpdating"
      @confirm="handleUpdateCategory"
      @cancel="editingCategory = null"
    >
      <template #default>
        <div class="mt-4">
          <label
            for="edit-category-name"
            class="block text-sm font-medium text-gray-700 mb-2"
          >
            Category Name
          </label>
          <input
            id="edit-category-name"
            v-model="editCategoryName"
            type="text"
            class="input-field w-full"
            placeholder="Enter category name"
          />
        </div>
      </template>
    </ConfirmDialog>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import {
  useBudgetCategories,
  useCreateBudgetCategory,
  useUpdateBudgetCategory,
  useDeleteBudgetCategory,
} from '../composables/useBudget';
import ConfirmDialog from './ConfirmDialog.vue';
import type { BudgetCategory, BudgetCategoryType } from '@neibrpay/models';

interface Props {
  isOpen: boolean;
}

defineProps<Props>();
defineEmits<{
  (e: 'close'): void;
}>();

const activeTab = ref<'income' | 'expense'>('income');
const newCategoryName = ref('');
const editingCategory = ref<BudgetCategory | null>(null);
const editCategoryName = ref('');
const isDeleting = ref<number | null>(null);

// Fetch categories
const { data: incomeCategories } = useBudgetCategories('income');
const { data: expenseCategories } = useBudgetCategories('expense');

const filteredCategories = computed(() => {
  const categories =
    activeTab.value === 'income'
      ? incomeCategories.value || []
      : expenseCategories.value || [];
  return categories.filter(cat => !cat.deleted_at);
});

// Mutations
const createMutation = useCreateBudgetCategory();
const updateMutation = useUpdateBudgetCategory();
const deleteMutation = useDeleteBudgetCategory();

const isCreating = computed(() => createMutation.isPending.value);
const isUpdating = computed(() => updateMutation.isPending.value);

const handleCreateCategory = async () => {
  if (!newCategoryName.value.trim()) return;

  try {
    await createMutation.mutateAsync({
      name: newCategoryName.value.trim(),
      type: activeTab.value,
    });
    newCategoryName.value = '';
  } catch (error) {
    console.error('Failed to create category:', error);
  }
};

const startEditCategory = (category: BudgetCategory) => {
  editingCategory.value = category;
  editCategoryName.value = category.name;
};

const handleUpdateCategory = async () => {
  if (!editingCategory.value || !editCategoryName.value.trim()) return;

  try {
    await updateMutation.mutateAsync({
      id: editingCategory.value.id,
      data: {
        name: editCategoryName.value.trim(),
      },
    });
    editingCategory.value = null;
    editCategoryName.value = '';
  } catch (error) {
    console.error('Failed to update category:', error);
  }
};

const handleDeleteCategory = async (category: BudgetCategory) => {
  if (!confirm(`Are you sure you want to delete "${category.name}"?`)) {
    return;
  }

  isDeleting.value = category.id;
  try {
    await deleteMutation.mutateAsync(category.id);
  } catch (error) {
    console.error('Failed to delete category:', error);
  } finally {
    isDeleting.value = null;
  }
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
