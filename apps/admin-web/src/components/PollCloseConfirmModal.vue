<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50"
    @click.self="$emit('cancel')"
  >
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Close this poll?</h2>
      </div>
      <div class="px-6 py-4 space-y-3 text-sm text-gray-600 leading-relaxed">
        <p>
          Closing stops voting immediately. Units that have not voted will no
          longer be able to cast a ballot.
        </p>
        <p v-if="emailsResults">
          Every owner in the audience will get an email that results are ready.
          Each email includes a <strong>View results</strong> button that opens
          My Polls.
        </p>
        <p v-else>
          Results are set to admins only, so residents will
          <strong>not</strong> be emailed about the outcome.
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
          {{
            isSubmitting
              ? 'Closing...'
              : emailsResults
                ? 'Close and send results'
                : 'Close poll'
          }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  isOpen: boolean;
  emailsResults: boolean;
  isSubmitting?: boolean;
}>();

defineEmits<{
  cancel: [];
  confirm: [];
}>();
</script>
