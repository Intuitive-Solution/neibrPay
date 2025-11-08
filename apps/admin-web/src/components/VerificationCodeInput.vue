<template>
  <div class="verification-code-input">
    <div class="code-inputs" ref="inputsContainer">
      <input
        v-for="(digit, index) in digits"
        :key="index"
        :ref="el => setInputRef(el, index)"
        v-model="digits[index]"
        type="text"
        inputmode="numeric"
        maxlength="1"
        class="code-digit"
        :class="{ error: hasError }"
        @input="handleInput(index, $event)"
        @keydown="handleKeydown(index, $event)"
        @paste="handlePaste"
        @focus="handleFocus(index)"
      />
    </div>
    <p v-if="hasError" class="error-message">{{ errorMessage }}</p>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';

const emit = defineEmits<{
  submit: [code: string];
  error: [message: string];
}>();

const digits = ref<string[]>(['', '', '', '', '', '']);
const inputRefs = ref<(HTMLInputElement | null)[]>([]);
const hasError = ref(false);
const errorMessage = ref('');

const setInputRef = (el: HTMLInputElement | null, index: number) => {
  if (el) {
    inputRefs.value[index] = el;
  }
};

const handleInput = (index: number, event: Event) => {
  const target = event.target as HTMLInputElement;
  const value = target.value.replace(/\D/g, ''); // Only allow digits

  if (value) {
    digits.value[index] = value;
    hasError.value = false;
    errorMessage.value = '';

    // Move to next input if not the last one
    if (index < 5) {
      nextTick(() => {
        inputRefs.value[index + 1]?.focus();
      });
    } else {
      // Last digit entered, submit
      submitCode();
    }
  } else {
    digits.value[index] = '';
  }
};

const handleKeydown = (index: number, event: KeyboardEvent) => {
  // Handle backspace
  if (event.key === 'Backspace' && !digits.value[index] && index > 0) {
    nextTick(() => {
      inputRefs.value[index - 1]?.focus();
      inputRefs.value[index - 1]?.select();
    });
  }

  // Handle arrow keys
  if (event.key === 'ArrowLeft' && index > 0) {
    inputRefs.value[index - 1]?.focus();
  }
  if (event.key === 'ArrowRight' && index < 5) {
    inputRefs.value[index + 1]?.focus();
  }
};

const handlePaste = (event: ClipboardEvent) => {
  event.preventDefault();
  const pastedData = event.clipboardData?.getData('text') || '';
  const code = pastedData.replace(/\D/g, '').substring(0, 6);

  if (code.length === 6) {
    for (let i = 0; i < 6; i++) {
      digits.value[i] = code[i];
    }
    nextTick(() => {
      submitCode();
    });
  }
};

const handleFocus = (index: number) => {
  inputRefs.value[index]?.select();
};

const submitCode = () => {
  const code = digits.value.join('');
  if (code.length === 6) {
    emit('submit', code);
  }
};

const clear = () => {
  digits.value = ['', '', '', '', '', ''];
  hasError.value = false;
  errorMessage.value = '';
  nextTick(() => {
    inputRefs.value[0]?.focus();
  });
};

const setError = (message: string) => {
  hasError.value = true;
  errorMessage.value = message;
  // Clear and refocus first input
  clear();
};

onMounted(() => {
  nextTick(() => {
    inputRefs.value[0]?.focus();
  });
});

defineExpose({
  clear,
  setError,
  focus: () => inputRefs.value[0]?.focus(),
});
</script>

<style scoped>
.verification-code-input {
  width: 100%;
}

.code-inputs {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-bottom: 8px;
}

.code-digit {
  width: 56px;
  height: 64px;
  text-align: center;
  font-size: 32px;
  font-weight: 600;
  border: 2px solid #d1d5db;
  border-radius: 8px;
  background-color: #ffffff;
  transition: all 0.2s;
}

.code-digit:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.code-digit.error {
  border-color: #ef4444;
  background-color: #fef2f2;
}

.error-message {
  text-align: center;
  color: #ef4444;
  font-size: 14px;
  margin-top: 8px;
}

@media (max-width: 640px) {
  .code-digit {
    width: 48px;
    height: 56px;
    font-size: 28px;
  }

  .code-inputs {
    gap: 8px;
  }
}
</style>
