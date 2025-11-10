<template>
  <div class="neibr-pay-logo" :class="sizeClass">
    <!-- Full Logo Version -->
    <div class="logo-full">
      <svg
        viewBox="0 0 40 40"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        class="logo-icon-part"
      >
        <path
          d="M20 8L8 18V32C8 33.1046 8.89543 34 10 34H16V26C16 24.8954 16.8954 24 18 24H22C23.1046 24 24 24.8954 24 26V34H30C31.1046 34 32 33.1046 32 32V18L20 8Z"
          fill="#00C27A"
        />
        <path
          d="M4 36C8 34 12 36 16 36C20 36 24 34 28 34C32 34 36 36 36 36"
          stroke="#00C27A"
          stroke-width="2"
          stroke-linecap="round"
          opacity="0.6"
        />
      </svg>
      <div class="logo-text">
        <span class="logo-neibr">Neibr</span>
        <span class="logo-pay">Pay</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
});

const sizeClass = computed(() => {
  return `logo-${props.size}`;
});
</script>

<style scoped>
.neibr-pay-logo {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Size Variants */
.logo-sm .logo-icon-part {
  width: 32px;
  height: 32px;
}

.logo-sm .logo-text {
  font-size: 16px;
}

.logo-md .logo-icon-part {
  width: 40px;
  height: 40px;
}

.logo-md .logo-text {
  font-size: 20px;
}

.logo-lg .logo-icon-part {
  width: 48px;
  height: 48px;
}

.logo-lg .logo-text {
  font-size: 24px;
}

/* Full Logo Layout */
.logo-full {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-text {
  display: flex;
  font-weight: 700;
  line-height: 1;
}

.logo-neibr {
  color: #1f2937; /* Dark gray */
}

.logo-pay {
  color: #00c27a; /* Bonsai green */
}

/* Icon Animations */
.neibr-pay-logo:hover .logo-icon-part path[fill='#00C27A'] {
  fill: #00a868; /* Slightly darker green on hover */
  transition: fill 0.3s ease;
}
</style>
