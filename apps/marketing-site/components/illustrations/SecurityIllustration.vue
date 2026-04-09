<template>
  <svg
    viewBox="0 0 400 300"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    class="w-full h-auto"
  >
    <!-- Background -->
    <rect x="40" y="20" width="320" height="260" rx="16" fill="#F9FAFB" />

    <!-- Central shield -->
    <path
      d="M200 50l80 30v60c0 55-35 100-80 120-45-20-80-65-80-120V80l80-30z"
      fill="#00C27A"
      opacity="0.1"
      stroke="#00C27A"
      stroke-width="2"
    />
    <path
      d="M200 70l60 22v46c0 42-27 77-60 92-33-15-60-50-60-92V92l60-22z"
      fill="white"
      stroke="#00C27A"
      stroke-width="1.5"
    />

    <!-- Lock icon inside shield -->
    <rect
      x="188"
      y="120"
      width="24"
      height="18"
      rx="3"
      stroke="#00C27A"
      stroke-width="2.5"
      fill="none"
    />
    <path
      d="M193 120v-6a7 7 0 0114 0v6"
      stroke="#00C27A"
      stroke-width="2.5"
      fill="none"
      stroke-linecap="round"
    />
    <circle cx="200" cy="130" r="3" fill="#00C27A" />
    <path
      d="M200 132v4"
      stroke="#00C27A"
      stroke-width="2"
      stroke-linecap="round"
    />

    <!-- Data nodes (left cluster) -->
    <circle
      cx="80"
      cy="100"
      r="12"
      fill="white"
      stroke="#E5E7EB"
      stroke-width="1.5"
    />
    <rect
      x="74"
      y="96"
      width="12"
      height="8"
      rx="1"
      fill="#00C27A"
      opacity="0.4"
    />

    <circle
      cx="80"
      cy="150"
      r="12"
      fill="white"
      stroke="#E5E7EB"
      stroke-width="1.5"
    />
    <rect
      x="74"
      y="146"
      width="12"
      height="8"
      rx="1"
      fill="#00C27A"
      opacity="0.4"
    />

    <circle
      cx="80"
      cy="200"
      r="12"
      fill="white"
      stroke="#E5E7EB"
      stroke-width="1.5"
    />
    <rect
      x="74"
      y="196"
      width="12"
      height="8"
      rx="1"
      fill="#00C27A"
      opacity="0.4"
    />

    <!-- Data nodes (right cluster) -->
    <circle
      cx="320"
      cy="100"
      r="12"
      fill="white"
      stroke="#E5E7EB"
      stroke-width="1.5"
    />
    <rect
      x="314"
      y="96"
      width="12"
      height="8"
      rx="1"
      fill="#00C27A"
      opacity="0.4"
    />

    <circle
      cx="320"
      cy="150"
      r="12"
      fill="white"
      stroke="#E5E7EB"
      stroke-width="1.5"
    />
    <rect
      x="314"
      y="146"
      width="12"
      height="8"
      rx="1"
      fill="#00C27A"
      opacity="0.4"
    />

    <circle
      cx="320"
      cy="200"
      r="12"
      fill="white"
      stroke="#E5E7EB"
      stroke-width="1.5"
    />
    <rect
      x="314"
      y="196"
      width="12"
      height="8"
      rx="1"
      fill="#00C27A"
      opacity="0.4"
    />

    <!-- Connection lines -->
    <path
      d="M92 100 L140 100"
      stroke="#00C27A"
      stroke-width="1"
      stroke-dasharray="4 3"
      opacity="0.4"
    />
    <path
      d="M92 150 L140 140"
      stroke="#00C27A"
      stroke-width="1"
      stroke-dasharray="4 3"
      opacity="0.4"
    />
    <path
      d="M92 200 L140 180"
      stroke="#00C27A"
      stroke-width="1"
      stroke-dasharray="4 3"
      opacity="0.4"
    />
    <path
      d="M260 100 L308 100"
      stroke="#00C27A"
      stroke-width="1"
      stroke-dasharray="4 3"
      opacity="0.4"
    />
    <path
      d="M260 140 L308 150"
      stroke="#00C27A"
      stroke-width="1"
      stroke-dasharray="4 3"
      opacity="0.4"
    />
    <path
      d="M260 180 L308 200"
      stroke="#00C27A"
      stroke-width="1"
      stroke-dasharray="4 3"
      opacity="0.4"
    />

    <!-- Checkmark badge -->
    <circle cx="200" cy="240" r="16" fill="#00C27A" />
    <path
      d="M193 240l5 5 9-9"
      stroke="white"
      stroke-width="2.5"
      stroke-linecap="round"
      stroke-linejoin="round"
    />

    <!-- Label -->
    <text
      x="200"
      y="272"
      fill="#6B7280"
      font-size="11"
      font-family="Inter, sans-serif"
      text-anchor="middle"
      font-weight="500"
    >
      Enterprise-Grade Isolation
    </text>
  </svg>
</template>
