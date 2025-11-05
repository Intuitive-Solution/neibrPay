<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
  >
    <!-- Background overlay -->
    <div
      class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
      @click="handleClose"
    ></div>

    <!-- Modal panel -->
    <div
      class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
    >
      <div
        class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
      >
        <!-- Close button -->
        <div class="absolute right-0 top-0 pr-4 pt-4">
          <button
            type="button"
            class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            @click="handleClose"
          >
            <span class="sr-only">Close</span>
            <svg
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <!-- Modal content -->
        <div class="sm:flex sm:items-start">
          <div
            class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary-100 sm:mx-0 sm:h-10 sm:w-10"
          >
            <svg
              class="h-6 w-6 text-primary"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"
              />
            </svg>
          </div>
          <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
            <h3
              class="text-base font-semibold leading-6 text-gray-900"
              id="modal-title"
            >
              Complete Your Registration
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Please provide your community name to complete your HOA
                management account setup.
              </p>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="mt-6">
          <div class="space-y-4">
            <!-- Email Input (shown only for member signup) -->
            <div v-if="props.isMemberSignup && props.lockedEmail">
              <label
                for="email"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Email <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  readonly
                  disabled
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm bg-gray-50 cursor-not-allowed"
                  placeholder="Enter your email"
                />
                <svg
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                  />
                </svg>
              </div>
            </div>

            <!-- Community Name Input -->
            <div>
              <label
                for="communityName"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Community Name <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input
                  id="communityName"
                  v-model="form.communityName"
                  type="text"
                  required
                  :readonly="props.isMemberSignup"
                  :disabled="props.isMemberSignup"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500':
                      errors.communityName,
                    'bg-gray-50 cursor-not-allowed': props.isMemberSignup,
                  }"
                  placeholder="e.g., Sunset Hills HOA, Oakwood Community"
                />
                <svg
                  v-if="props.isMemberSignup"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                  />
                </svg>
              </div>
              <p v-if="errors.communityName" class="mt-1 text-sm text-red-600">
                {{ errors.communityName }}
              </p>
            </div>

            <!-- Phone Number Input (Optional) -->
            <div>
              <label
                for="phoneNumber"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Phone Number (Optional)
              </label>
              <input
                id="phoneNumber"
                v-model="formattedPhoneNumber"
                @input="handlePhoneInput"
                type="tel"
                maxlength="14"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                placeholder="555-555-5555"
              />
              <p class="mt-1 text-sm text-gray-500">
                Optional: Enter a valid US phone number (10 digits).
              </p>
            </div>
          </div>

          <!-- Action buttons -->
          <div class="mt-6 sm:flex sm:flex-row-reverse">
            <button
              type="submit"
              :disabled="isLoading"
              class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary sm:ml-3 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="isLoading" class="flex items-center">
                <svg
                  class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                Creating Account...
              </span>
              <span v-else>Complete Registration</span>
            </button>
            <button
              type="button"
              @click="handleClose"
              class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, watch, ref } from 'vue';

interface Props {
  isOpen: boolean;
  isLoading?: boolean;
  isMemberSignup?: boolean;
  lockedCommunityName?: string | null;
  lockedEmail?: string | null;
}

interface Emits {
  (e: 'close'): void;
  (e: 'submit', data: { communityName: string; phoneNumber?: string }): void;
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
  isMemberSignup: false,
  lockedCommunityName: null,
  lockedEmail: null,
});

const emit = defineEmits<Emits>();

const form = reactive({
  email: '',
  communityName: '',
  phoneNumber: '',
});

const errors = reactive({
  communityName: '',
});

// Phone number formatting
const formattedPhoneNumber = ref('');

// Format phone number as user types
const formatPhoneNumber = (value: string): string => {
  // Remove all non-numeric characters
  const phoneNumber = value.replace(/\D/g, '');

  // Limit to 10 digits
  const limitedPhoneNumber = phoneNumber.substring(0, 10);

  // Format as XXX-XXX-XXXX
  if (limitedPhoneNumber.length >= 6) {
    return `${limitedPhoneNumber.substring(0, 3)}-${limitedPhoneNumber.substring(3, 6)}-${limitedPhoneNumber.substring(6)}`;
  } else if (limitedPhoneNumber.length >= 3) {
    return `${limitedPhoneNumber.substring(0, 3)}-${limitedPhoneNumber.substring(3)}`;
  } else {
    return limitedPhoneNumber;
  }
};

// Handle phone input changes
const handlePhoneInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const formatted = formatPhoneNumber(target.value);
  formattedPhoneNumber.value = formatted;

  // Store the raw phone number (digits only) in the form
  form.phoneNumber = formatted.replace(/\D/g, '');
};

const validateForm = () => {
  errors.communityName = '';

  if (!form.communityName.trim()) {
    errors.communityName = 'Community name is required';
    return false;
  }

  if (form.communityName.trim().length < 3) {
    errors.communityName = 'Community name must be at least 3 characters';
    return false;
  }

  return true;
};

const handleSubmit = () => {
  if (validateForm()) {
    emit('submit', {
      communityName: form.communityName.trim(),
      phoneNumber: form.phoneNumber.trim() || undefined,
    });
  }
};

const handleClose = () => {
  if (!props.isLoading) {
    emit('close');
  }
};

// Reset form when modal opens, or prefill if member signup
watch(
  () => props.isOpen,
  (isOpen: boolean) => {
    if (isOpen) {
      if (props.isMemberSignup) {
        if (props.lockedEmail) {
          form.email = props.lockedEmail;
        }
        if (props.lockedCommunityName) {
          form.communityName = props.lockedCommunityName;
        }
      } else {
        form.email = '';
        form.communityName = '';
      }
      form.phoneNumber = '';
      errors.communityName = '';
    }
  }
);

// Watch for lockedCommunityName changes
watch(
  () => props.lockedCommunityName,
  (newValue: string | null) => {
    if (props.isMemberSignup && newValue) {
      form.communityName = newValue;
    }
  }
);

// Watch for lockedEmail changes
watch(
  () => props.lockedEmail,
  (newValue: string | null) => {
    if (props.isMemberSignup && newValue) {
      form.email = newValue;
    }
  }
);
</script>
