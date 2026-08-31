<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50"
    @click.self="$emit('cancel')"
  >
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Publish this poll?</h2>
      </div>
      <div class="px-6 py-4 space-y-3 text-sm text-gray-600 leading-relaxed">
        <p>
          Publishing opens the poll for voting and emails every owner in the
          selected audience.
        </p>
        <p>
          Each email includes a <strong>Log in</strong> button so residents can
          sign in and cast their unit’s vote.
        </p>
        <p class="text-[13px] text-gray-500">
          Save as a draft if you are not ready to notify anyone yet.
        </p>
      </div>
      <div
        class="px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-3"
      >
        <button
          type="button"
          class="btn-outline btn-sm"
          @click="$emit('cancel')"
        >
          Cancel
        </button>
        <button
          type="button"
          class="btn-primary btn-sm"
          :disabled="isSubmitting"
          @click="$emit('confirm')"
        >
          {{ isSubmitting ? 'Publishing...' : 'Publish and send email' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  isOpen: boolean;
  isSubmitting?: boolean;
}>();

defineEmits<{
  cancel: [];
  confirm: [];
}>();
</script>
