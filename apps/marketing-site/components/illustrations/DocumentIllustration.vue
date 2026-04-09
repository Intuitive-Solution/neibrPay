<template>
  <svg
    viewBox="0 0 400 300"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    class="w-full h-auto"
  >
    <!-- Background -->
    <rect x="40" y="30" width="320" height="240" rx="16" fill="#F9FAFB" />

    <!-- Folder back -->
    <rect x="100" y="70" width="200" height="160" rx="8" fill="#E5E7EB" />
    <path
      d="M100 78a8 8 0 018-8h60l16 20h108a8 8 0 018 8v0H100v-20z"
      fill="#D1D5DB"
    />

    <!-- Document 1 (back) -->
    <rect
      x="130"
      y="100"
      width="120"
      height="150"
      rx="4"
      fill="white"
      stroke="#E5E7EB"
      stroke-width="1.5"
      transform="rotate(-5 130 100)"
    />
    <rect
      x="148"
      y="118"
      width="70"
      height="4"
      rx="2"
      fill="#E5E7EB"
      transform="rotate(-5 148 118)"
    />
    <rect
      x="150"
      y="130"
      width="50"
      height="4"
      rx="2"
      fill="#E5E7EB"
      transform="rotate(-5 150 130)"
    />

    <!-- Document 2 (middle) -->
    <rect
      x="140"
      y="95"
      width="120"
      height="150"
      rx="4"
      fill="white"
      stroke="#E5E7EB"
      stroke-width="1.5"
    />
    <rect x="156" y="112" width="80" height="4" rx="2" fill="#C2F4E0" />
    <rect x="156" y="124" width="60" height="4" rx="2" fill="#E5E7EB" />
    <rect x="156" y="136" width="72" height="4" rx="2" fill="#E5E7EB" />
    <rect x="156" y="148" width="48" height="4" rx="2" fill="#E5E7EB" />

    <!-- Document 3 (front) -->
    <rect
      x="155"
      y="85"
      width="120"
      height="150"
      rx="4"
      fill="white"
      stroke="#D1D5DB"
      stroke-width="1.5"
      transform="rotate(3 155 85)"
    />
    <rect
      x="172"
      y="102"
      width="80"
      height="5"
      rx="2.5"
      fill="#00C27A"
      transform="rotate(3 172 102)"
    />
    <rect
      x="174"
      y="115"
      width="60"
      height="4"
      rx="2"
      fill="#E5E7EB"
      transform="rotate(3 174 115)"
    />
    <rect
      x="175"
      y="127"
      width="72"
      height="4"
      rx="2"
      fill="#E5E7EB"
      transform="rotate(3 175 127)"
    />

    <!-- Search icon -->
    <circle cx="300" cy="80" r="20" fill="#00C27A" opacity="0.1" />
    <circle
      cx="297"
      cy="77"
      r="8"
      stroke="#00C27A"
      stroke-width="2.5"
      fill="none"
    />
    <path
      d="M303 83l5 5"
      stroke="#00C27A"
      stroke-width="2.5"
      stroke-linecap="round"
    />

    <!-- Checkmark badge -->
    <circle cx="280" cy="220" r="14" fill="#00C27A" />
    <path
      d="M274 220l4 4 8-8"
      stroke="white"
      stroke-width="2.5"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
  </svg>
</template>
