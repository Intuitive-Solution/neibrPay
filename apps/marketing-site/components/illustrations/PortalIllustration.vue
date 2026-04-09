<template>
  <svg
    viewBox="0 0 400 300"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    class="w-full h-auto"
  >
    <!-- Background -->
    <rect x="40" y="20" width="320" height="260" rx="16" fill="#F9FAFB" />

    <!-- Phone frame -->
    <rect
      x="130"
      y="35"
      width="140"
      height="245"
      rx="20"
      fill="white"
      stroke="#D1D5DB"
      stroke-width="2"
    />
    <rect x="130" y="35" width="140" height="245" rx="20" fill="white" />

    <!-- Status bar -->
    <rect x="145" y="48" width="110" height="4" rx="2" fill="#E5E7EB" />

    <!-- Header bar -->
    <rect
      x="145"
      y="64"
      width="110"
      height="24"
      rx="4"
      fill="#00C27A"
      opacity="0.1"
    />
    <rect x="155" y="72" width="50" height="6" rx="3" fill="#00C27A" />

    <!-- Balance card -->
    <rect x="145" y="98" width="110" height="50" rx="6" fill="#00C27A" />
    <rect
      x="155"
      y="108"
      width="40"
      height="4"
      rx="2"
      fill="white"
      opacity="0.6"
    />
    <rect x="155" y="118" width="60" height="8" rx="4" fill="white" />
    <rect
      x="155"
      y="132"
      width="30"
      height="4"
      rx="2"
      fill="white"
      opacity="0.5"
    />

    <!-- Invoice list items -->
    <rect
      x="145"
      y="158"
      width="110"
      height="30"
      rx="4"
      fill="white"
      stroke="#E5E7EB"
      stroke-width="1"
    />
    <rect x="153" y="167" width="50" height="4" rx="2" fill="#374151" />
    <rect x="153" y="175" width="30" height="3" rx="1.5" fill="#9CA3AF" />
    <rect x="225" y="168" width="22" height="10" rx="5" fill="#C2F4E0" />

    <rect
      x="145"
      y="194"
      width="110"
      height="30"
      rx="4"
      fill="white"
      stroke="#E5E7EB"
      stroke-width="1"
    />
    <rect x="153" y="203" width="50" height="4" rx="2" fill="#374151" />
    <rect x="153" y="211" width="30" height="3" rx="1.5" fill="#9CA3AF" />
    <rect x="225" y="204" width="22" height="10" rx="5" fill="#FDE68A" />

    <rect
      x="145"
      y="230"
      width="110"
      height="30"
      rx="4"
      fill="white"
      stroke="#E5E7EB"
      stroke-width="1"
    />
    <rect x="153" y="239" width="50" height="4" rx="2" fill="#374151" />
    <rect x="153" y="247" width="30" height="3" rx="1.5" fill="#9CA3AF" />
    <rect x="225" y="240" width="22" height="10" rx="5" fill="#C2F4E0" />

    <!-- Phone notch -->
    <rect x="175" y="40" width="50" height="6" rx="3" fill="#E5E7EB" />

    <!-- Notification bell (floating) -->
    <circle cx="310" cy="70" r="18" fill="#00C27A" opacity="0.1" />
    <path
      d="M306 74v-6a4 4 0 018 0v6"
      stroke="#00C27A"
      stroke-width="2"
      stroke-linecap="round"
    />
    <path
      d="M303 74h14"
      stroke="#00C27A"
      stroke-width="2"
      stroke-linecap="round"
    />
    <circle cx="310" cy="79" r="1.5" fill="#00C27A" />
    <circle cx="316" cy="64" r="4" fill="#00C27A" />
    <text
      x="314.5"
      y="66.5"
      fill="white"
      font-size="6"
      font-weight="bold"
      text-anchor="middle"
    >
      2
    </text>

    <!-- Payment icon (floating) -->
    <circle cx="90" cy="140" r="18" fill="#00C27A" opacity="0.1" />
    <rect
      x="79"
      y="133"
      width="22"
      height="14"
      rx="2"
      stroke="#00C27A"
      stroke-width="2"
      fill="none"
    />
    <path d="M79 139h22" stroke="#00C27A" stroke-width="2" />
  </svg>
</template>
