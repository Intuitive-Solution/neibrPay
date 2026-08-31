<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50"
    @click.self="$emit('cancel')"
  >
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">
          Send a voting reminder?
        </h2>
      </div>
      <div class="px-6 py-4 space-y-3 text-sm text-gray-600 leading-relaxed">
        <p>
          This emails the owners of the
          <strong>{{ pendingCount }}</strong>
          unit{{ pendingCount === 1 ? '' : 's' }} that still have not voted.
        </p>
        <p>
          Units that already voted will not be contacted. Each email includes a
          <strong>Log in</strong> button so they can sign in and vote.
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
          {{ isSubmitting ? 'Sending...' : 'Send reminder' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  isOpen: boolean;
  pendingCount: number;
  isSubmitting?: boolean;
}>();

defineEmits<{
  cancel: [];
  confirm: [];
}>();
</script>
