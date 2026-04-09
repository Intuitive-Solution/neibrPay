<template>
  <svg
    viewBox="0 0 400 300"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    class="w-full h-auto"
  >
    <!-- Background -->
    <rect x="40" y="20" width="320" height="260" rx="16" fill="#F9FAFB" />

    <!-- Megaphone body -->
    <path
      d="M120 120 L200 90 L200 210 L120 180 Z"
      fill="#00C27A"
      opacity="0.15"
      stroke="#00C27A"
      stroke-width="2"
    />
    <rect
      x="90"
      y="120"
      width="30"
      height="60"
      rx="4"
      fill="#00C27A"
      opacity="0.25"
      stroke="#00C27A"
      stroke-width="2"
    />

    <!-- Megaphone bell -->
    <path
      d="M200 85 Q260 70 260 150 Q260 230 200 215"
      fill="none"
      stroke="#00C27A"
      stroke-width="2.5"
    />

    <!-- Sound waves -->
    <path
      d="M270 120 Q285 150 270 180"
      stroke="#00C27A"
      stroke-width="2"
      fill="none"
      opacity="0.6"
      stroke-linecap="round"
    />
    <path
      d="M285 105 Q305 150 285 195"
      stroke="#00C27A"
      stroke-width="2"
      fill="none"
      opacity="0.4"
      stroke-linecap="round"
    />
    <path
      d="M300 90 Q325 150 300 210"
      stroke="#00C27A"
      stroke-width="2"
      fill="none"
      opacity="0.2"
      stroke-linecap="round"
    />

    <!-- Handle -->
    <path
      d="M105 180 L95 220 Q93 228 100 228 L115 228 Q122 228 120 220 L115 180"
      fill="#00C27A"
      opacity="0.2"
      stroke="#00C27A"
      stroke-width="1.5"
    />

    <!-- Notification dots -->
    <circle cx="290" cy="80" r="10" fill="#00C27A" />
    <text
      x="290"
      y="84"
      fill="white"
      font-size="11"
      font-weight="bold"
      text-anchor="middle"
    >
      !
    </text>

    <!-- Email envelope (floating) -->
    <g transform="translate(310, 130)">
      <rect
        x="0"
        y="5"
        width="36"
        height="24"
        rx="3"
        fill="white"
        stroke="#D1D5DB"
        stroke-width="1.5"
      />
      <path
        d="M0 8l18 12 18-12"
        stroke="#00C27A"
        stroke-width="1.5"
        fill="none"
      />
    </g>

    <!-- Push notification (floating) -->
    <g transform="translate(70, 60)">
      <rect
        x="0"
        y="0"
        width="80"
        height="36"
        rx="6"
        fill="white"
        stroke="#E5E7EB"
        stroke-width="1.5"
      />
      <circle cx="16" cy="18" r="6" fill="#00C27A" opacity="0.2" />
      <circle cx="16" cy="18" r="3" fill="#00C27A" />
      <rect x="28" y="12" width="40" height="4" rx="2" fill="#374151" />
      <rect x="28" y="20" width="28" height="3" rx="1.5" fill="#9CA3AF" />
    </g>

    <!-- Person icons (recipients) -->
    <circle cx="320" cy="220" r="10" fill="#E5E7EB" />
    <circle cx="320" cy="216" r="4" fill="#9CA3AF" />
    <path d="M312 228a8 8 0 0116 0" fill="#9CA3AF" />

    <circle cx="345" cy="220" r="10" fill="#E5E7EB" />
    <circle cx="345" cy="216" r="4" fill="#9CA3AF" />
    <path d="M337 228a8 8 0 0116 0" fill="#9CA3AF" />

    <circle cx="295" cy="220" r="10" fill="#C2F4E0" />
    <circle cx="295" cy="216" r="4" fill="#00C27A" opacity="0.5" />
    <path d="M287 228a8 8 0 0116 0" fill="#00C27A" opacity="0.5" />
  </svg>
</template>
