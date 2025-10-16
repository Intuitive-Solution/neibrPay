<template>
  <div class="relative inline-block" ref="dropdownRef">
    <!-- Trigger Button -->
    <button
      @click="toggleDropdown"
      ref="triggerRef"
      class="p-1 rounded-lg hover:bg-gray-100 transition-colors duration-200"
      :class="triggerClass"
    >
      <svg
        class="w-5 h-5 text-gray-700 hover:text-gray-900 transition-colors duration-200"
        fill="currentColor"
        viewBox="0 0 20 20"
      >
        <path
          d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"
        />
      </svg>
    </button>

    <!-- Dropdown Menu - Using Teleport to avoid clipping -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-if="isOpen"
          ref="menuRef"
          class="fixed bg-white rounded-lg shadow-2xl border-2 border-gray-300 py-1 min-w-max w-56"
          :style="menuStyle"
          style="z-index: 9999"
        >
          <slot :close="closeDropdown" />
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';

interface Props {
  position?: 'left' | 'right';
  triggerClass?: string;
  menuClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  position: 'right',
  triggerClass: '',
  menuClass: '',
});

const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);
const triggerRef = ref<HTMLButtonElement | null>(null);
const menuRef = ref<HTMLElement | null>(null);

const menuStyle = computed(() => {
  if (!triggerRef.value || !isOpen.value) return {};

  const rect = triggerRef.value.getBoundingClientRect();
  const scrollY = window.scrollY || window.pageYOffset;
  const scrollX = window.scrollX || window.pageXOffset;

  const style: any = {
    top: `${rect.bottom + scrollY + 8}px`, // 8px margin below trigger
  };

  if (props.position === 'left') {
    style.left = `${rect.left + scrollX}px`;
  } else {
    style.right = `${window.innerWidth - rect.right - scrollX}px`;
  }

  return style;
});

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const closeDropdown = () => {
  isOpen.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as Node;
  if (
    dropdownRef.value &&
    !dropdownRef.value.contains(target) &&
    menuRef.value &&
    !menuRef.value.contains(target)
  ) {
    closeDropdown();
  }
};

// Recalculate position on scroll or resize
const updatePosition = () => {
  if (isOpen.value && triggerRef.value) {
    // Force recompute by toggling
    const wasOpen = isOpen.value;
    isOpen.value = false;
    nextTick(() => {
      isOpen.value = wasOpen;
    });
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  window.addEventListener('scroll', updatePosition, true);
  window.addEventListener('resize', updatePosition);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  window.removeEventListener('scroll', updatePosition, true);
  window.removeEventListener('resize', updatePosition);
});
</script>
