<template>
  <div class="space-y-6">
    <!-- Controls Section -->
    <div class="card-modern bg-white rounded-lg shadow-sm">
      <div class="px-6 py-4 border-b border-gray-200">
        <div
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
        >
          <div class="flex items-center gap-3">
            <!-- Year Selector -->
            <select
              v-model="selectedYear"
              class="input-field text-sm"
              @change="handleYearChange"
            >
              <option v-for="year in availableYears" :key="year" :value="year">
                {{ year }}
              </option>
            </select>

            <!-- Admin-only buttons -->
            <template v-if="!isResident">
              <button
                @click="showCopyModal = true"
                class="btn-secondary btn-sm whitespace-nowrap"
                :disabled="isCopying"
              >
                <svg
                  class="w-4 h-4 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                  />
                </svg>
                Copy Budget
              </button>
              <button
                @click="showCategoryManager = true"
                class="btn-primary btn-sm whitespace-nowrap"
              >
                <svg
                  class="w-4 h-4 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                  />
                </svg>
                Manage Categories
              </button>
              <button
                type="button"
                class="btn-secondary btn-sm whitespace-nowrap"
                :disabled="!budgetData || isExportingPdf"
                @click="downloadBudgetPdf"
              >
                <svg
                  class="w-4 h-4 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  />
                </svg>
                {{ isExportingPdf ? 'Generating…' : 'Download PDF' }}
              </button>
              <button
                type="button"
                class="btn-secondary btn-sm whitespace-nowrap"
                :disabled="!budgetData || isExportingExcel"
                @click="downloadBudgetExcel"
              >
                <svg
                  class="w-4 h-4 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  />
                </svg>
                {{ isExportingExcel ? 'Generating…' : 'Download Excel' }}
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Summary Cards -->
    <template v-if="budgetData && !isLoading && !error">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Summary Card -->
        <div class="card card-hover">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
              <svg
                class="w-6 h-6 text-blue-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                />
              </svg>
            </div>
            <div class="ml-4 flex-1">
              <h3 class="text-sm font-medium text-gray-600">Summary</h3>
              <div class="mt-2 space-y-1">
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500">Forecast</span>
                  <span class="text-sm font-semibold text-gray-900">
                    {{ formatCurrency(summaryForecast) }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500">Actual</span>
                  <span
                    class="text-sm font-semibold"
                    :class="
                      summaryActual >= summaryForecast
                        ? 'text-green-600'
                        : 'text-red-600'
                    "
                  >
                    {{ formatCurrency(summaryActual) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Income Card -->
        <div class="card card-hover">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
              <svg
                class="w-6 h-6 text-green-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
                />
              </svg>
            </div>
            <div class="ml-4 flex-1">
              <h3 class="text-sm font-medium text-gray-600">Income</h3>
              <div class="mt-2 space-y-1">
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500">Forecast</span>
                  <span class="text-sm font-semibold text-gray-900">
                    {{ formatCurrency(incomeForecast) }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500">Actual</span>
                  <span
                    class="text-sm font-semibold"
                    :class="
                      incomeActual >= incomeForecast
                        ? 'text-green-600'
                        : 'text-red-600'
                    "
                  >
                    {{ formatCurrency(incomeActual) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Expense Card -->
        <div class="card card-hover">
          <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
              <svg
                class="w-6 h-6 text-red-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
                />
              </svg>
            </div>
            <div class="ml-4 flex-1">
              <h3 class="text-sm font-medium text-gray-600">Expense</h3>
              <div class="mt-2 space-y-1">
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500">Forecast</span>
                  <span class="text-sm font-semibold text-gray-900">
                    {{ formatCurrency(expenseForecast) }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500">Actual</span>
                  <span
                    class="text-sm font-semibold"
                    :class="
                      expenseActual <= expenseForecast
                        ? 'text-green-600'
                        : 'text-red-600'
                    "
                  >
                    {{ formatCurrency(expenseActual) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <div class="flex items-center space-x-2">
        <svg
          class="animate-spin h-5 w-5 text-primary"
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
        <span class="text-sm text-gray-600">Loading budget data...</span>
      </div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="card-modern bg-white rounded-lg shadow-sm p-6"
    >
      <div class="text-center">
        <svg
          class="mx-auto h-12 w-12 text-red-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">
          Error loading budget
        </h3>
        <p class="mt-1 text-sm text-gray-500">{{ error }}</p>
      </div>
    </div>

    <!-- Budget Content -->
    <template v-else-if="budgetData">
      <!-- Running Balance Chart and Table -->
      <div class="card-modern bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">
            Running Balance – HOA Account ({{ selectedYear }})
          </h2>
        </div>
        <div class="p-6 space-y-6">
          <!-- Chart -->
          <div v-if="runningBalanceChartData.length > 0" class="relative">
            <div
              class="relative w-full overflow-x-auto"
              style="min-height: 280px"
            >
              <svg
                :viewBox="`0 0 ${chartWidth} ${chartHeight}`"
                class="w-full min-h-[280px]"
                preserveAspectRatio="xMidYMid meet"
              >
                <!-- Y-axis grid and labels (max at top, min at bottom) -->
                <g v-for="(tick, i) in chartYAxisTicks" :key="'grid-' + i">
                  <line
                    :x1="chartPadding.left"
                    :y1="
                      chartPadding.top +
                      chartInnerHeight * (1 - i / (chartYAxisTicks.length - 1))
                    "
                    :x2="chartWidth - chartPadding.right"
                    :y2="
                      chartPadding.top +
                      chartInnerHeight * (1 - i / (chartYAxisTicks.length - 1))
                    "
                    stroke="#E5E7EB"
                    stroke-width="1"
                    stroke-dasharray="2,2"
                  />
                  <text
                    :x="chartPadding.left - 6"
                    :y="
                      chartPadding.top +
                      chartInnerHeight * (1 - i / (chartYAxisTicks.length - 1))
                    "
                    text-anchor="end"
                    dominant-baseline="middle"
                    class="text-[10px] fill-gray-500"
                  >
                    {{ tick }}
                  </text>
                </g>
                <!-- X-axis labels -->
                <g v-for="(d, i) in runningBalanceChartData" :key="'x-' + i">
                  <text
                    :x="
                      chartPadding.left +
                      (i / 11) * chartInnerWidth +
                      chartInnerWidth / 22
                    "
                    :y="chartHeight - chartPadding.bottom + 16"
                    text-anchor="middle"
                    class="text-[10px] fill-gray-500"
                  >
                    {{ getMonthAbbr(d.month) }}
                  </text>
                </g>
                <!-- Actual line (solid) -->
                <path
                  v-if="actualPathD"
                  :d="actualPathD"
                  fill="none"
                  stroke="#374151"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <!-- Forecast line (dotted) -->
                <path
                  v-if="forecastPathD"
                  :d="forecastPathD"
                  fill="none"
                  stroke="#9CA3AF"
                  stroke-width="1.5"
                  stroke-dasharray="4,4"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </div>
            <div class="flex gap-4 mt-2 justify-center flex-wrap">
              <span class="flex items-center gap-2 text-xs text-gray-600">
                <span class="inline-block w-4 h-0.5 bg-gray-700"></span>
                Actual
              </span>
              <span class="flex items-center gap-2 text-xs text-gray-600">
                <span
                  class="inline-block w-4 h-0.5 border-t-2 border-dashed border-gray-400"
                ></span>
                Forecast
              </span>
            </div>
          </div>
          <div
            v-else-if="!isRunningBalanceLoading && runningBalanceError"
            class="py-8 text-center text-sm text-gray-500"
          >
            No running balance data. Connect bank accounts and sync transactions
            to see actuals.
          </div>
          <div
            v-else-if="isRunningBalanceLoading"
            class="py-8 text-center text-sm text-gray-500"
          >
            Loading running balance…
          </div>

          <!-- Table -->
          <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="w-full text-sm min-w-[800px]">
              <thead>
                <tr class="bg-red-50 border-b border-gray-200">
                  <th
                    class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase"
                  >
                    Running Balance
                  </th>
                  <th
                    class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase border-l border-gray-200"
                  >
                    Opening
                  </th>
                  <th
                    v-for="m in 12"
                    :key="m"
                    class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase border-l border-gray-200"
                  >
                    {{ getMonthAbbr(m) }}
                  </th>
                  <th
                    class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase border-l border-gray-200 bg-gray-50"
                  >
                    YEAR
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr class="bg-white border-b border-gray-200">
                  <td class="px-4 py-3 font-medium text-gray-900">
                    Cash Balance
                  </td>
                  <td
                    class="px-3 py-3 text-center text-gray-900 border-l border-gray-200"
                  >
                    {{ formatTableBalance(openingBalance) }}
                  </td>
                  <td
                    v-for="m in 12"
                    :key="m"
                    class="px-3 py-3 text-center text-gray-900 border-l border-gray-200"
                  >
                    {{ formatTableBalance(runningBalanceTableRow[m]) }}
                  </td>
                  <td
                    class="px-4 py-3 text-center border-l border-gray-200 bg-gray-50"
                  >
                    <span class="font-medium text-gray-900">
                      {{ formatTableBalance(runningBalanceYearEnd) }}
                    </span>
                    <span
                      v-if="runningBalanceYearDelta !== null"
                      :class="[
                        'ml-1 text-xs',
                        runningBalanceYearDelta >= 0
                          ? 'text-green-600'
                          : 'text-red-600',
                      ]"
                    >
                      {{ runningBalanceYearDelta >= 0 ? '+' : ''
                      }}{{ formatCurrency(runningBalanceYearDelta) }}
                      {{
                        runningBalanceYearDelta >= 0 ? 'Increase' : 'Decrease'
                      }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Income Section -->
      <div class="card-modern bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Income</h2>
        </div>
        <!-- Income Bar Chart: Forecast vs Actual by month -->
        <div class="px-6 py-4 border-b border-gray-100">
          <div
            class="relative w-full overflow-x-auto"
            style="min-height: 200px"
          >
            <svg
              :viewBox="`0 0 ${budgetBarChartWidth} ${budgetBarChartHeight}`"
              class="w-full min-h-[200px]"
              preserveAspectRatio="xMidYMid meet"
            >
              <!-- Y-axis grid and labels -->
              <g
                v-for="(tick, i) in incomeChartYAxisTicks"
                :key="'income-grid-' + i"
              >
                <line
                  :x1="budgetBarPadding.left"
                  :y1="
                    budgetBarPadding.top +
                    budgetBarInnerHeight *
                      (1 - i / (incomeChartYAxisTicks.length - 1))
                  "
                  :x2="budgetBarChartWidth - budgetBarPadding.right"
                  :y2="
                    budgetBarPadding.top +
                    budgetBarInnerHeight *
                      (1 - i / (incomeChartYAxisTicks.length - 1))
                  "
                  stroke="#E5E7EB"
                  stroke-width="1"
                  stroke-dasharray="2,2"
                />
                <text
                  :x="budgetBarPadding.left - 4"
                  :y="
                    budgetBarPadding.top +
                    budgetBarInnerHeight *
                      (1 - i / (incomeChartYAxisTicks.length - 1))
                  "
                  text-anchor="end"
                  dominant-baseline="middle"
                  class="text-[10px] fill-gray-500"
                >
                  {{ tick }}
                </text>
              </g>
              <!-- Bars and X labels -->
              <g
                v-for="(d, i) in incomeMonthlyChartData"
                :key="'income-bar-' + i"
              >
                <!-- Forecast bar -->
                <rect
                  :x="
                    budgetBarPadding.left +
                    (i / 12) * budgetBarInnerWidth +
                    (budgetBarInnerWidth / 24) * 0.5
                  "
                  :y="incomeBarY(d.forecast)"
                  :width="budgetBarInnerWidth / 24 - 2"
                  :height="Math.max(0, incomeBarHeight(d.forecast))"
                  fill="#93C5FD"
                  class="hover:opacity-90"
                />
                <!-- Actual bar -->
                <rect
                  :x="
                    budgetBarPadding.left +
                    (i / 12) * budgetBarInnerWidth +
                    (budgetBarInnerWidth / 24) * 1.5 +
                    2
                  "
                  :y="incomeBarY(d.actual)"
                  :width="budgetBarInnerWidth / 24 - 2"
                  :height="Math.max(0, incomeBarHeight(d.actual))"
                  fill="#22C55E"
                  class="hover:opacity-90"
                />
                <text
                  :x="
                    budgetBarPadding.left +
                    ((i + 0.5) / 12) * budgetBarInnerWidth +
                    budgetBarInnerWidth / 24
                  "
                  :y="budgetBarChartHeight - budgetBarPadding.bottom + 14"
                  text-anchor="middle"
                  class="text-[10px] fill-gray-500"
                >
                  {{ getMonthAbbr(d.month) }}
                </text>
              </g>
            </svg>
          </div>
          <div
            class="flex gap-4 mt-1 justify-center flex-wrap text-xs text-gray-600"
          >
            <span class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded-sm bg-[#93C5FD]"></span>
              Forecast
            </span>
            <span class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded-sm bg-[#22C55E]"></span>
              Actual
            </span>
          </div>
        </div>
        <BudgetTable
          :categories="budgetData.income"
          :is-resident="isResident"
          :year="selectedYear"
          type="income"
          @update-entry="handleUpdateEntry"
        />
      </div>

      <!-- Expense Section -->
      <div class="card-modern bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Expense</h2>
        </div>
        <!-- Expense Bar Chart: Forecast vs Actual by month -->
        <div class="px-6 py-4 border-b border-gray-100">
          <div
            class="relative w-full overflow-x-auto"
            style="min-height: 200px"
          >
            <svg
              :viewBox="`0 0 ${budgetBarChartWidth} ${budgetBarChartHeight}`"
              class="w-full min-h-[200px]"
              preserveAspectRatio="xMidYMid meet"
            >
              <g
                v-for="(tick, i) in expenseChartYAxisTicks"
                :key="'expense-grid-' + i"
              >
                <line
                  :x1="budgetBarPadding.left"
                  :y1="
                    budgetBarPadding.top +
                    budgetBarInnerHeight *
                      (1 - i / (expenseChartYAxisTicks.length - 1))
                  "
                  :x2="budgetBarChartWidth - budgetBarPadding.right"
                  :y2="
                    budgetBarPadding.top +
                    budgetBarInnerHeight *
                      (1 - i / (expenseChartYAxisTicks.length - 1))
                  "
                  stroke="#E5E7EB"
                  stroke-width="1"
                  stroke-dasharray="2,2"
                />
                <text
                  :x="budgetBarPadding.left - 4"
                  :y="
                    budgetBarPadding.top +
                    budgetBarInnerHeight *
                      (1 - i / (expenseChartYAxisTicks.length - 1))
                  "
                  text-anchor="end"
                  dominant-baseline="middle"
                  class="text-[10px] fill-gray-500"
                >
                  {{ tick }}
                </text>
              </g>
              <g
                v-for="(d, i) in expenseMonthlyChartData"
                :key="'expense-bar-' + i"
              >
                <rect
                  :x="
                    budgetBarPadding.left +
                    (i / 12) * budgetBarInnerWidth +
                    (budgetBarInnerWidth / 24) * 0.5
                  "
                  :y="expenseBarY(d.forecast)"
                  :width="budgetBarInnerWidth / 24 - 2"
                  :height="Math.max(0, expenseBarHeight(d.forecast))"
                  fill="#FCA5A5"
                  class="hover:opacity-90"
                />
                <rect
                  :x="
                    budgetBarPadding.left +
                    (i / 12) * budgetBarInnerWidth +
                    (budgetBarInnerWidth / 24) * 1.5 +
                    2
                  "
                  :y="expenseBarY(d.actual)"
                  :width="budgetBarInnerWidth / 24 - 2"
                  :height="Math.max(0, expenseBarHeight(d.actual))"
                  fill="#EF4444"
                  class="hover:opacity-90"
                />
                <text
                  :x="
                    budgetBarPadding.left +
                    ((i + 0.5) / 12) * budgetBarInnerWidth +
                    budgetBarInnerWidth / 24
                  "
                  :y="budgetBarChartHeight - budgetBarPadding.bottom + 14"
                  text-anchor="middle"
                  class="text-[10px] fill-gray-500"
                >
                  {{ getMonthAbbr(d.month) }}
                </text>
              </g>
            </svg>
          </div>
          <div
            class="flex gap-4 mt-1 justify-center flex-wrap text-xs text-gray-600"
          >
            <span class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded-sm bg-[#FCA5A5]"></span>
              Forecast
            </span>
            <span class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded-sm bg-[#EF4444]"></span>
              Actual
            </span>
          </div>
        </div>
        <BudgetTable
          :categories="budgetData.expense"
          :is-resident="isResident"
          :year="selectedYear"
          type="expense"
          @update-entry="handleUpdateEntry"
        />
      </div>

      <!-- Activity Log -->
      <div class="card-modern bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Activity Log</h2>
        </div>
        <BudgetAuditLog :year="selectedYear" />
      </div>
    </template>

    <!-- Category Manager Modal -->
    <CategoryManager
      v-if="showCategoryManager"
      :is-open="showCategoryManager"
      @close="showCategoryManager = false"
    />

    <!-- Copy Budget Modal -->
    <ConfirmDialog
      v-if="showCopyModal"
      :is-open="showCopyModal"
      title="Copy Budget"
      :message="`Copy budget from ${selectedYear} to which year?`"
      confirm-text="Copy"
      cancel-text="Cancel"
      type="warning"
      :is-loading="isCopying"
      @confirm="handleCopyBudget"
      @cancel="handleCancelCopy"
    >
      <template #default>
        <div class="mt-4 space-y-4">
          <!-- Target Year Dropdown -->
          <div>
            <label
              for="target-year"
              class="block text-sm font-medium text-gray-700 mb-2"
            >
              Target Year
            </label>
            <select
              id="target-year"
              v-model.number="targetYear"
              class="input-field w-full"
            >
              <option
                v-for="year in targetYearOptions"
                :key="year"
                :value="year"
              >
                {{ year }}
              </option>
            </select>
          </div>

          <!-- Copy Type Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              What to Copy
            </label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input
                  v-model="copyType"
                  type="radio"
                  value="all"
                  class="h-4 w-4 text-primary focus:ring-primary border-gray-300"
                />
                <span class="ml-2 text-sm text-gray-700"
                  >All (Income & Expenses)</span
                >
              </label>
              <label class="flex items-center">
                <input
                  v-model="copyType"
                  type="radio"
                  value="income"
                  class="h-4 w-4 text-primary focus:ring-primary border-gray-300"
                />
                <span class="ml-2 text-sm text-gray-700">Income Only</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="copyType"
                  type="radio"
                  value="expense"
                  class="h-4 w-4 text-primary focus:ring-primary border-gray-300"
                />
                <span class="ml-2 text-sm text-gray-700">Expenses Only</span>
              </label>
            </div>
          </div>

          <!-- Warning Message -->
          <div class="rounded-md bg-yellow-50 p-3 border border-yellow-200">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg
                  class="h-5 w-5 text-yellow-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                  />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-yellow-800">
                  <strong>Warning:</strong> This action will overwrite existing
                  budget values for {{ targetYear }}. This cannot be undone.
                </p>
              </div>
            </div>
          </div>
        </div>
      </template>
    </ConfirmDialog>

    <!-- Hidden container for PDF export (filled and shown only during export) -->
    <div
      ref="budgetExportContainer"
      class="fixed left-[-9999px] top-0 z-[-1] w-[800px] bg-white p-6 text-gray-900"
      aria-hidden="true"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import html2canvas from 'html2canvas';
import { jsPDF } from 'jspdf';
import { useBudget, useCopyBudget } from '../composables/useBudget';
import { useAuthStore } from '../stores/auth';
import { useRunningBalance } from '@neibrpay/api-client';
import BudgetTable from '../components/BudgetTable.vue';
import BudgetAuditLog from '../components/BudgetAuditLog.vue';
import CategoryManager from '../components/CategoryManager.vue';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import {
  type BudgetEntryUpdateDto,
  getMonthAbbreviation,
} from '@neibrpay/models';

const authStore = useAuthStore();
const isResident = computed(() => authStore.isResident);

// Year selection
const currentYear = new Date().getFullYear();
const selectedYear = ref(currentYear);
const availableYears = computed(() => {
  const years = [];
  for (let i = currentYear - 2; i <= currentYear + 5; i++) {
    years.push(i);
  }
  return years;
});

// Budget data
const { data: budgetData, isLoading, error } = useBudget(selectedYear);

// Running balance (from plaid_transactions only)
const {
  data: runningBalanceData,
  isLoading: isRunningBalanceLoading,
  error: runningBalanceError,
} = useRunningBalance(selectedYear);

// Copy budget
const showCopyModal = ref(false);
const targetYear = ref(currentYear + 1);
const copyType = ref<'all' | 'income' | 'expense'>('all');
const copyBudgetMutation = useCopyBudget();
const isCopying = computed(() => copyBudgetMutation.isPending.value);

// Available years for target year dropdown (exclude current year and past years)
const targetYearOptions = computed(() => {
  const years = [];
  for (let i = selectedYear.value + 1; i <= currentYear + 10; i++) {
    years.push(i);
  }
  return years;
});

const handleCopyBudget = async () => {
  if (!targetYear.value || targetYear.value <= selectedYear.value) {
    return;
  }

  try {
    await copyBudgetMutation.mutateAsync({
      fromYear: selectedYear.value,
      toYear: targetYear.value,
      type: copyType.value,
    });
    showCopyModal.value = false;
    selectedYear.value = targetYear.value;
    targetYear.value = currentYear + 1;
    copyType.value = 'all';
  } catch (error: any) {
    console.error('Failed to copy budget:', error);
  }
};

const handleCancelCopy = () => {
  showCopyModal.value = false;
  targetYear.value = currentYear + 1;
  copyType.value = 'all';
};

// Category manager
const showCategoryManager = ref(false);

// PDF/Excel export
const budgetExportContainer = ref<HTMLElement | null>(null);
const isExportingPdf = ref(false);
const isExportingExcel = ref(false);

// Update entry handler
const handleUpdateEntry = (entry: BudgetEntryUpdateDto) => {
  // Entry update is handled by BudgetTable component via useUpdateBudgetEntries
  // This emit is just for potential future use
};

// Year change handler
const handleYearChange = () => {
  // The query will automatically refetch when selectedYear changes
};

watch(selectedYear, () => {
  // Ensure we refetch when year changes
});

// Calculate summary totals
const incomeForecast = computed(() => {
  if (!budgetData.value) return 0;
  return budgetData.value.income.reduce(
    (sum, cat) => sum + cat.total.forecast,
    0
  );
});

const incomeActual = computed(() => {
  if (!budgetData.value) return 0;
  return budgetData.value.income.reduce(
    (sum, cat) => sum + cat.total.actual,
    0
  );
});

const expenseForecast = computed(() => {
  if (!budgetData.value) return 0;
  return budgetData.value.expense.reduce(
    (sum, cat) => sum + cat.total.forecast,
    0
  );
});

const expenseActual = computed(() => {
  if (!budgetData.value) return 0;
  return budgetData.value.expense.reduce(
    (sum, cat) => sum + cat.total.actual,
    0
  );
});

const summaryForecast = computed(() => {
  return incomeForecast.value - expenseForecast.value;
});

const summaryActual = computed(() => {
  return incomeActual.value - expenseActual.value;
});

// --- Income / Expense bar chart (monthly Forecast vs Actual) ---
const budgetBarChartWidth = 700;
const budgetBarChartHeight = 200;
const budgetBarPadding = { top: 16, right: 16, bottom: 28, left: 48 };
const budgetBarInnerWidth =
  budgetBarChartWidth - budgetBarPadding.left - budgetBarPadding.right;
const budgetBarInnerHeight =
  budgetBarChartHeight - budgetBarPadding.top - budgetBarPadding.bottom;

const incomeMonthlyChartData = computed(() => {
  if (!budgetData.value) return [];
  return Array.from({ length: 12 }, (_, i) => {
    const month = i + 1;
    let forecast = 0;
    let actual = 0;
    for (const cat of budgetData.value!.income) {
      forecast += cat.months[month]?.forecast ?? 0;
      actual += cat.months[month]?.actual ?? 0;
    }
    return { month, forecast, actual };
  });
});

const incomeChartMax = computed(() => {
  let max = 0;
  for (const d of incomeMonthlyChartData.value) {
    if (d.forecast > max) max = d.forecast;
    if (d.actual > max) max = d.actual;
  }
  return max || 1;
});

const incomeChartYAxisTicks = computed(() => {
  const max = incomeChartMax.value;
  const count = 5;
  return Array.from({ length: count }, (_, i) =>
    formatCurrency((max * i) / (count - 1))
  );
});

function incomeBarHeight(value: number): number {
  const max = incomeChartMax.value;
  if (max <= 0) return 0;
  return (value / max) * budgetBarInnerHeight;
}

function incomeBarY(value: number): number {
  const h = incomeBarHeight(value);
  return budgetBarPadding.top + budgetBarInnerHeight - h;
}

const expenseMonthlyChartData = computed(() => {
  if (!budgetData.value) return [];
  return Array.from({ length: 12 }, (_, i) => {
    const month = i + 1;
    let forecast = 0;
    let actual = 0;
    for (const cat of budgetData.value!.expense) {
      forecast += cat.months[month]?.forecast ?? 0;
      actual += cat.months[month]?.actual ?? 0;
    }
    return { month, forecast, actual };
  });
});

const expenseChartMax = computed(() => {
  let max = 0;
  for (const d of expenseMonthlyChartData.value) {
    if (d.forecast > max) max = d.forecast;
    if (d.actual > max) max = d.actual;
  }
  return max || 1;
});

const expenseChartYAxisTicks = computed(() => {
  const max = expenseChartMax.value;
  const count = 5;
  return Array.from({ length: count }, (_, i) =>
    formatCurrency((max * i) / (count - 1))
  );
});

function expenseBarHeight(value: number): number {
  const max = expenseChartMax.value;
  if (max <= 0) return 0;
  return (value / max) * budgetBarInnerHeight;
}

function expenseBarY(value: number): number {
  const h = expenseBarHeight(value);
  return budgetBarPadding.top + budgetBarInnerHeight - h;
}

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

// --- Running Balance chart and table ---
const currentMonth = new Date().getMonth() + 1; // 1-12

const lastCompletedMonth = computed(() => {
  const y = selectedYear.value;
  if (y < currentYear) return 12;
  if (y > currentYear) return 0;
  return Math.max(0, currentMonth - 1);
});

const runningBalanceByMonth = computed(() => {
  const list = runningBalanceData.value?.monthly_balances ?? [];
  const map: Record<number, number> = {};
  for (const { month, balance } of list) {
    map[month] = balance;
  }
  return map;
});

const openingBalance = computed(
  () => runningBalanceData.value?.opening_balance ?? 0
);

const monthlyNetForecast = computed(() => {
  if (!budgetData.value) return {} as Record<number, number>;
  const net: Record<number, number> = {};
  for (let m = 1; m <= 12; m++) {
    let income = 0;
    let expense = 0;
    for (const cat of budgetData.value!.income) {
      income += cat.months[m]?.forecast ?? 0;
    }
    for (const cat of budgetData.value!.expense) {
      expense += cat.months[m]?.forecast ?? 0;
    }
    net[m] = income - expense;
  }
  return net;
});

const runningBalanceChartData = computed(() => {
  const last = lastCompletedMonth.value;
  const byMonth = runningBalanceByMonth.value;
  const netForecast = monthlyNetForecast.value;
  const opening = openingBalance.value;
  const result: Array<{
    month: number;
    actual: number | null;
    forecast: number | null;
  }> = [];
  let forecastRunning = last > 0 ? (byMonth[last] ?? opening) : opening;
  for (let m = 1; m <= 12; m++) {
    const actual = last >= m ? (byMonth[m] ?? null) : null;
    if (m > last) {
      forecastRunning += netForecast[m] ?? 0;
      result.push({ month: m, actual, forecast: forecastRunning });
    } else {
      result.push({ month: m, actual, forecast: null });
    }
  }
  return result;
});

const chartPadding = { top: 20, right: 20, bottom: 36, left: 52 };
const chartWidth = 700;
const chartHeight = 280;
const chartInnerWidth = chartWidth - chartPadding.left - chartPadding.right;
const chartInnerHeight = chartHeight - chartPadding.top - chartPadding.bottom;

const chartExtent = computed(() => {
  let min = 0;
  let max = 0;
  for (const d of runningBalanceChartData.value) {
    const v = d.actual ?? d.forecast ?? 0;
    if (v < min) min = v;
    if (v > max) max = v;
  }
  if (min === max) {
    min = Math.min(0, min - 1000);
    max = Math.max(0, max + 1000);
  }
  const pad = (max - min) * 0.05 || 1000;
  return [min - pad, max + pad];
});

const chartYAxisTicks = computed(() => {
  const [min, max] = chartExtent.value;
  const count = 6;
  const step = (max - min) / (count - 1);
  return Array.from({ length: count }, (_, i) =>
    formatCurrency(min + i * step)
  );
});

const chartScaleY = (value: number) => {
  const [min, max] = chartExtent.value;
  const t = (value - min) / (max - min);
  return chartPadding.top + chartInnerHeight * (1 - t);
};

const actualPathD = computed(() => {
  const points: string[] = [];
  const data = runningBalanceChartData.value;
  for (let i = 0; i < data.length; i++) {
    const v = data[i].actual;
    if (v === null) continue;
    const x =
      chartPadding.left + (i / 11) * chartInnerWidth + chartInnerWidth / 22;
    const y = chartScaleY(v);
    points.push(`${i === 0 ? 'M' : 'L'} ${x} ${y}`);
  }
  return points.join(' ');
});

const forecastPathD = computed(() => {
  const points: string[] = [];
  const data = runningBalanceChartData.value;
  const last = lastCompletedMonth.value;
  if (last >= 12) return '';
  // Start from last actual month so the dotted line connects to the solid line
  const startIdx = last > 0 ? last - 1 : 0;
  for (let i = startIdx; i < data.length; i++) {
    const v = i <= last - 1 ? data[i].actual : data[i].forecast;
    if (v === null) continue;
    const x =
      chartPadding.left + (i / 11) * chartInnerWidth + chartInnerWidth / 22;
    const y = chartScaleY(v);
    points.push(`${points.length === 0 ? 'M' : 'L'} ${x} ${y}`);
  }
  return points.join(' ');
});

const runningBalanceTableRow = computed(() => {
  const last = lastCompletedMonth.value;
  const byMonth = runningBalanceByMonth.value;
  const netForecast = monthlyNetForecast.value;
  const opening = openingBalance.value;
  const row: Record<number, number> = {};
  let forecastRunning = last > 0 ? (byMonth[last] ?? opening) : opening;
  for (let m = 1; m <= 12; m++) {
    if (m <= last) {
      row[m] = byMonth[m] ?? opening;
    } else {
      forecastRunning += netForecast[m] ?? 0;
      row[m] = forecastRunning;
    }
  }
  return row;
});

const runningBalanceYearEnd = computed(() => {
  return runningBalanceTableRow.value[12] ?? 0;
});

const runningBalanceYearDelta = computed(() => {
  const open = openingBalance.value;
  const end = runningBalanceYearEnd.value;
  if (open === 0 && end === 0) return null;
  return end - open;
});

function getMonthAbbr(month: number): string {
  return getMonthAbbreviation(month);
}

function formatTableBalance(value: number | undefined): string {
  if (value === undefined) return '—';
  return formatCurrency(value);
}

function buildRunningBalanceChartSvg(): string {
  const data = runningBalanceChartData.value;
  if (data.length === 0) return '';
  const w = chartWidth,
    h = chartHeight;
  const p = chartPadding;
  const iW = chartInnerWidth,
    iH = chartInnerHeight;
  const [eMin, eMax] = chartExtent.value;
  const ticks = chartYAxisTicks.value;
  const scaleY = (value: number) => {
    const t = (value - eMin) / (eMax - eMin);
    return p.top + iH * (1 - t);
  };
  let svg = '';
  for (let i = 0; i < ticks.length; i++) {
    const y = p.top + iH * (1 - i / (ticks.length - 1));
    svg += `<line x1="${p.left}" y1="${y}" x2="${w - p.right}" y2="${y}" stroke="#E5E7EB" stroke-width="1" stroke-dasharray="2,2"/>`;
    svg += `<text x="${p.left - 6}" y="${y}" text-anchor="end" dominant-baseline="middle" font-size="10" fill="#6B7280">${ticks[i]}</text>`;
  }
  for (let i = 0; i < data.length; i++) {
    const x = p.left + (i / 11) * iW + iW / 22;
    svg += `<text x="${x}" y="${h - p.bottom + 16}" text-anchor="middle" font-size="10" fill="#6B7280">${getMonthAbbr(data[i].month)}</text>`;
  }
  const actualPts: string[] = [];
  for (let i = 0; i < data.length; i++) {
    const v = data[i].actual;
    if (v === null) continue;
    const x = p.left + (i / 11) * iW + iW / 22;
    actualPts.push(`${actualPts.length === 0 ? 'M' : 'L'} ${x} ${scaleY(v)}`);
  }
  if (actualPts.length)
    svg += `<path d="${actualPts.join(' ')}" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>`;
  const last = lastCompletedMonth.value;
  if (last < 12) {
    const forecastPts: string[] = [];
    const startIdx = last > 0 ? last - 1 : 0;
    for (let i = startIdx; i < data.length; i++) {
      const v = i <= last - 1 ? data[i].actual : data[i].forecast;
      if (v === null) continue;
      const x = p.left + (i / 11) * iW + iW / 22;
      forecastPts.push(
        `${forecastPts.length === 0 ? 'M' : 'L'} ${x} ${scaleY(v)}`
      );
    }
    if (forecastPts.length)
      svg += `<path d="${forecastPts.join(' ')}" fill="none" stroke="#9CA3AF" stroke-width="1.5" stroke-dasharray="4,4" stroke-linecap="round" stroke-linejoin="round"/>`;
  }
  return `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" viewBox="0 0 ${w} ${h}" style="background:#fff">${svg}</svg>`;
}

function buildBarChartSvg(
  chartData: Array<{ month: number; forecast: number; actual: number }>,
  maxVal: number,
  forecastColor: string,
  actualColor: string
): string {
  if (chartData.length === 0) return '';
  const w = budgetBarChartWidth,
    h = budgetBarChartHeight;
  const p = budgetBarPadding;
  const iW = budgetBarInnerWidth,
    iH = budgetBarInnerHeight;
  const mx = maxVal || 1;
  const count = 5;
  const ticks = Array.from({ length: count }, (_, i) =>
    formatCurrency((mx * i) / (count - 1))
  );
  let svg = '';
  for (let i = 0; i < ticks.length; i++) {
    const y = p.top + iH * (1 - i / (ticks.length - 1));
    svg += `<line x1="${p.left}" y1="${y}" x2="${w - p.right}" y2="${y}" stroke="#E5E7EB" stroke-width="1" stroke-dasharray="2,2"/>`;
    svg += `<text x="${p.left - 4}" y="${y}" text-anchor="end" dominant-baseline="middle" font-size="10" fill="#6B7280">${ticks[i]}</text>`;
  }
  for (let i = 0; i < chartData.length; i++) {
    const d = chartData[i];
    const groupX = p.left + (i / 12) * iW;
    const barW = iW / 24 - 2;
    const fH = Math.max(0, (d.forecast / mx) * iH);
    const aH = Math.max(0, (d.actual / mx) * iH);
    svg += `<rect x="${groupX + (iW / 24) * 0.5}" y="${p.top + iH - fH}" width="${barW}" height="${fH}" fill="${forecastColor}"/>`;
    svg += `<rect x="${groupX + (iW / 24) * 1.5 + 2}" y="${p.top + iH - aH}" width="${barW}" height="${aH}" fill="${actualColor}"/>`;
    svg += `<text x="${groupX + iW / 24 + iW / 24}" y="${h - p.bottom + 14}" text-anchor="middle" font-size="10" fill="#6B7280">${getMonthAbbr(d.month)}</text>`;
  }
  return `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" viewBox="0 0 ${w} ${h}" style="background:#fff">${svg}</svg>`;
}

function svgStringToPng(
  svgStr: string,
  width: number,
  height: number
): Promise<string> {
  return new Promise(resolve => {
    if (!svgStr) {
      resolve('');
      return;
    }
    const blob = new Blob([svgStr], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const img = new Image();
    img.onload = () => {
      const canvas = document.createElement('canvas');
      canvas.width = width * 2;
      canvas.height = height * 2;
      const ctx = canvas.getContext('2d')!;
      ctx.scale(2, 2);
      ctx.drawImage(img, 0, 0, width, height);
      URL.revokeObjectURL(url);
      resolve(canvas.toDataURL('image/png'));
    };
    img.onerror = () => {
      URL.revokeObjectURL(url);
      resolve('');
    };
    img.src = url;
  });
}

function buildExportSections(chartImages: {
  rbChart: string;
  incomeChart: string;
  expenseChart: string;
}): string[] {
  const year = selectedYear.value;
  const wrap = (inner: string) =>
    `<div style="font-family:system-ui,sans-serif;font-size:12px">${inner}</div>`;

  const rbRow = runningBalanceTableRow.value;
  const rbCells = Array.from(
    { length: 12 },
    (_, i) =>
      `<td style="padding:6px;text-align:center;border:1px solid #e5e7eb">${formatTableBalance(rbRow[i + 1])}</td>`
  ).join('');
  const yearDelta = runningBalanceYearDelta.value;
  const yearDeltaText =
    yearDelta !== null
      ? ` ${yearDelta >= 0 ? '+' : ''}${formatCurrency(yearDelta)} ${yearDelta >= 0 ? 'Increase' : 'Decrease'}`
      : '';

  const monthHeaderCells = Array.from({ length: 12 }, (_, i) => {
    const abbr = getMonthAbbr(i + 1);
    return `<th style="padding:4px;border:1px solid #e5e7eb">${abbr} F</th><th style="padding:4px;border:1px solid #e5e7eb">${abbr} A</th>`;
  }).join('');

  const section1 = wrap(`
    <h1 style="font-size:18px;margin-bottom:16px">Budget Report – ${year}</h1>
    <div style="display:flex;gap:16px;margin-bottom:24px;flex-wrap:wrap">
      <div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;min-width:180px">
        <div style="font-weight:600;color:#374151">Summary</div>
        <div style="margin-top:8px">Forecast ${formatCurrency(summaryForecast.value)}</div>
        <div>Actual ${formatCurrency(summaryActual.value)}</div>
      </div>
      <div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;min-width:180px">
        <div style="font-weight:600;color:#374151">Income</div>
        <div style="margin-top:8px">Forecast ${formatCurrency(incomeForecast.value)}</div>
        <div>Actual ${formatCurrency(incomeActual.value)}</div>
      </div>
      <div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;min-width:180px">
        <div style="font-weight:600;color:#374151">Expense</div>
        <div style="margin-top:8px">Forecast ${formatCurrency(expenseForecast.value)}</div>
        <div>Actual ${formatCurrency(expenseActual.value)}</div>
      </div>
    </div>
    <h2 style="font-size:14px;margin-bottom:8px">Running Balance – HOA Account (${year})</h2>
    ${chartImages.rbChart ? `<div style="margin-bottom:12px"><img src="${chartImages.rbChart}" style="width:100%;max-width:700px" /></div>` : ''}
    <table style="border-collapse:collapse;margin-bottom:24px;width:100%">
      <tr>
        <th style="padding:6px;text-align:left;border:1px solid #e5e7eb">Running Balance</th>
        <th style="padding:6px;border:1px solid #e5e7eb">Opening</th>
        ${Array.from({ length: 12 }, (_, i) => `<th style="padding:6px;border:1px solid #e5e7eb">${getMonthAbbr(i + 1)}</th>`).join('')}
        <th style="padding:6px;border:1px solid #e5e7eb">YEAR</th>
      </tr>
      <tr>
        <td style="padding:6px;border:1px solid #e5e7eb">Cash Balance</td>
        <td style="padding:6px;text-align:center;border:1px solid #e5e7eb">${formatTableBalance(openingBalance.value)}</td>
        ${rbCells}
        <td style="padding:6px;text-align:center;border:1px solid #e5e7eb">${formatTableBalance(runningBalanceYearEnd.value)}${yearDeltaText}</td>
      </tr>
    </table>
  `);

  let incomeRows = '';
  if (budgetData.value) {
    for (const cat of budgetData.value.income) {
      const cells = Array.from({ length: 12 }, (_, i) => {
        const m = i + 1;
        const f = cat.months[m]?.forecast ?? 0;
        const a = cat.months[m]?.actual ?? 0;
        return `<td style="padding:4px;text-align:center;border:1px solid #e5e7eb">${formatCurrency(f)}</td><td style="padding:4px;text-align:center;border:1px solid #e5e7eb">${formatCurrency(a)}</td>`;
      }).join('');
      incomeRows += `<tr><td style="padding:4px;border:1px solid #e5e7eb">${cat.name}</td>${cells}<td style="padding:4px;text-align:center;border:1px solid #e5e7eb">${formatCurrency(cat.total.forecast)}</td><td style="padding:4px;text-align:center;border:1px solid #e5e7eb">${formatCurrency(cat.total.actual)}</td></tr>`;
    }
  }

  const section2 = wrap(`
    <h2 style="font-size:14px;margin-bottom:8px">Income</h2>
    ${chartImages.incomeChart ? `<div style="margin-bottom:12px"><img src="${chartImages.incomeChart}" style="width:100%;max-width:700px" /></div>` : ''}
    <table style="border-collapse:collapse;margin-bottom:24px;width:100%">
      <tr>
        <th style="padding:4px;border:1px solid #e5e7eb">Category</th>
        ${monthHeaderCells}
        <th style="padding:4px;border:1px solid #e5e7eb">Total F</th>
        <th style="padding:4px;border:1px solid #e5e7eb">Total A</th>
      </tr>
      ${incomeRows}
    </table>
  `);

  let expenseRows = '';
  if (budgetData.value) {
    for (const cat of budgetData.value.expense) {
      const cells = Array.from({ length: 12 }, (_, i) => {
        const m = i + 1;
        const f = cat.months[m]?.forecast ?? 0;
        const a = cat.months[m]?.actual ?? 0;
        return `<td style="padding:4px;text-align:center;border:1px solid #e5e7eb">${formatCurrency(f)}</td><td style="padding:4px;text-align:center;border:1px solid #e5e7eb">${formatCurrency(a)}</td>`;
      }).join('');
      expenseRows += `<tr><td style="padding:4px;border:1px solid #e5e7eb">${cat.name}</td>${cells}<td style="padding:4px;text-align:center;border:1px solid #e5e7eb">${formatCurrency(cat.total.forecast)}</td><td style="padding:4px;text-align:center;border:1px solid #e5e7eb">${formatCurrency(cat.total.actual)}</td></tr>`;
    }
  }

  const section3 = wrap(`
    <h2 style="font-size:14px;margin-bottom:8px">Expense</h2>
    ${chartImages.expenseChart ? `<div style="margin-bottom:12px"><img src="${chartImages.expenseChart}" style="width:100%;max-width:700px" /></div>` : ''}
    <table style="border-collapse:collapse;width:100%">
      <tr>
        <th style="padding:4px;border:1px solid #e5e7eb">Category</th>
        ${monthHeaderCells}
        <th style="padding:4px;border:1px solid #e5e7eb">Total F</th>
        <th style="padding:4px;border:1px solid #e5e7eb">Total A</th>
      </tr>
      ${expenseRows}
    </table>
  `);

  return [section1, section2, section3];
}

async function generateChartImages(): Promise<{
  rbChart: string;
  incomeChart: string;
  expenseChart: string;
}> {
  const [rbChart, incomeChart, expenseChart] = await Promise.all([
    svgStringToPng(buildRunningBalanceChartSvg(), chartWidth, chartHeight),
    svgStringToPng(
      buildBarChartSvg(
        incomeMonthlyChartData.value,
        incomeChartMax.value,
        '#93C5FD',
        '#22C55E'
      ),
      budgetBarChartWidth,
      budgetBarChartHeight
    ),
    svgStringToPng(
      buildBarChartSvg(
        expenseMonthlyChartData.value,
        expenseChartMax.value,
        '#FCA5A5',
        '#EF4444'
      ),
      budgetBarChartWidth,
      budgetBarChartHeight
    ),
  ]);
  return { rbChart, incomeChart, expenseChart };
}

async function renderSectionToCanvas(
  container: HTMLElement,
  html: string
): Promise<HTMLCanvasElement> {
  container.innerHTML = html;
  await nextTick();
  const imgs = container.querySelectorAll(
    'img'
  ) as NodeListOf<HTMLImageElement>;
  await Promise.all(
    Array.from(imgs).map((img: HTMLImageElement) =>
      img.complete
        ? Promise.resolve()
        : new Promise(r => {
            img.onload = r;
            img.onerror = r;
          })
    )
  );
  return html2canvas(container, {
    scale: 2,
    useCORS: true,
    logging: false,
    backgroundColor: '#ffffff',
  });
}

function addCanvasToPdf(
  pdf: InstanceType<typeof jsPDF>,
  canvas: HTMLCanvasElement,
  isFirstSection: boolean
): void {
  const a4W = 210;
  const a4H = 297;
  const imgW = canvas.width;
  const imgH = canvas.height;
  const ratio = a4W / imgW;
  const totalHeightMm = imgH * ratio;

  if (!isFirstSection) pdf.addPage();

  if (totalHeightMm <= a4H) {
    const imgData = canvas.toDataURL('image/jpeg', 0.92);
    pdf.addImage(imgData, 'JPEG', 0, 0, a4W, totalHeightMm);
  } else {
    const totalPages = Math.ceil(totalHeightMm / a4H);
    const sliceHeightPx = a4H / ratio;
    const pageCanvas = document.createElement('canvas');
    pageCanvas.width = imgW;
    const ctx = pageCanvas.getContext('2d');
    for (let p = 0; p < totalPages; p++) {
      if (p > 0) pdf.addPage();
      const sy = p * sliceHeightPx;
      const sh = Math.min(sliceHeightPx, imgH - sy);
      pageCanvas.height = Math.ceil(sh);
      ctx?.clearRect(0, 0, pageCanvas.width, pageCanvas.height);
      ctx?.drawImage(canvas, 0, sy, imgW, sh, 0, 0, imgW, sh);
      const pageData = pageCanvas.toDataURL('image/jpeg', 0.92);
      const sliceHMm = (sh * a4W) / imgW;
      pdf.addImage(pageData, 'JPEG', 0, 0, a4W, sliceHMm);
    }
  }
}

async function downloadBudgetPdf(): Promise<void> {
  if (!budgetData.value || !budgetExportContainer.value) return;
  isExportingPdf.value = true;
  try {
    const chartImages = await generateChartImages();
    const sections = buildExportSections(chartImages);
    const container = budgetExportContainer.value;
    const pdf = new jsPDF('p', 'mm', 'a4');

    for (let i = 0; i < sections.length; i++) {
      const canvas = await renderSectionToCanvas(container, sections[i]);
      addCanvasToPdf(pdf, canvas, i === 0);
    }

    pdf.save(`budget-${selectedYear.value}.pdf`);
  } catch (e) {
    console.error('Budget PDF export failed:', e);
  } finally {
    if (budgetExportContainer.value) budgetExportContainer.value.innerHTML = '';
    isExportingPdf.value = false;
  }
}

async function downloadBudgetExcel(): Promise<void> {
  if (!budgetData.value) return;
  isExportingExcel.value = true;
  try {
    const [{ Workbook }, chartImages] = await Promise.all([
      import('exceljs'),
      generateChartImages(),
    ]);
    const workbook = new Workbook();
    workbook.creator = 'NeibrPay';
    workbook.created = new Date();

    const year = selectedYear.value;
    const monthAbbrs = Array.from({ length: 12 }, (_, i) =>
      getMonthAbbr(i + 1)
    );

    const addChartImage = (
      sheet: InstanceType<typeof Workbook>['worksheets'][0],
      dataUrl: string,
      startRow: number
    ) => {
      if (!dataUrl) return;
      const base64 = dataUrl.split(',')[1];
      if (!base64) return;
      const imageId = workbook.addImage({ base64, extension: 'png' });
      sheet.addImage(imageId, {
        tl: { col: 0, row: startRow } as any,
        ext: { width: 700, height: 280 },
      });
    };

    const summarySheet = workbook.addWorksheet('Summary', {
      views: [{ state: 'frozen', ySplit: 1 }],
    });
    summarySheet.columns = [
      { header: 'Section', width: 14 },
      { header: 'Forecast', width: 14 },
      { header: 'Actual', width: 14 },
    ];
    summarySheet.addRow([
      'Summary',
      summaryForecast.value,
      summaryActual.value,
    ]);
    summarySheet.addRow(['Income', incomeForecast.value, incomeActual.value]);
    summarySheet.addRow([
      'Expense',
      expenseForecast.value,
      expenseActual.value,
    ]);
    summarySheet.getRow(1).font = { bold: true };

    const rbSheet = workbook.addWorksheet('Running Balance', {
      views: [{ state: 'frozen', ySplit: 1 }],
    });
    rbSheet.columns = [
      { header: 'Running Balance', width: 16 },
      { header: 'Opening', width: 12 },
      ...monthAbbrs.map(m => ({ header: m, width: 10 })),
      { header: 'YEAR', width: 14 },
    ];
    const rbRow = runningBalanceTableRow.value;
    const rbValues = [
      'Cash Balance',
      openingBalance.value,
      ...Array.from({ length: 12 }, (_, i) => rbRow[i + 1] ?? ''),
      runningBalanceYearEnd.value,
    ];
    rbSheet.addRow(rbValues);
    rbSheet.getRow(1).font = { bold: true };
    addChartImage(rbSheet, chartImages.rbChart, 3);

    const incomeSheet = workbook.addWorksheet('Income', {
      views: [{ state: 'frozen', ySplit: 1 }],
    });
    const incomeHeaders = [
      'Category',
      ...monthAbbrs.flatMap(m => [`${m} F`, `${m} A`]),
      'Total F',
      'Total A',
    ];
    incomeSheet.columns = incomeHeaders.map(h => ({ header: h, width: 10 }));
    incomeSheet.getRow(1).font = { bold: true };
    for (const cat of budgetData.value.income) {
      const row = [
        cat.name,
        ...Array.from({ length: 12 }, (_, i) => {
          const m = i + 1;
          return [cat.months[m]?.forecast ?? 0, cat.months[m]?.actual ?? 0];
        }).flat(),
        cat.total.forecast,
        cat.total.actual,
      ];
      incomeSheet.addRow(row);
    }
    const incomeDataRows = budgetData.value.income.length;
    addChartImage(incomeSheet, chartImages.incomeChart, incomeDataRows + 2);

    const expenseSheet = workbook.addWorksheet('Expense', {
      views: [{ state: 'frozen', ySplit: 1 }],
    });
    const expenseHeaders = [
      'Category',
      ...monthAbbrs.flatMap(m => [`${m} F`, `${m} A`]),
      'Total F',
      'Total A',
    ];
    expenseSheet.columns = expenseHeaders.map(h => ({ header: h, width: 10 }));
    expenseSheet.getRow(1).font = { bold: true };
    for (const cat of budgetData.value.expense) {
      const row = [
        cat.name,
        ...Array.from({ length: 12 }, (_, i) => {
          const m = i + 1;
          return [cat.months[m]?.forecast ?? 0, cat.months[m]?.actual ?? 0];
        }).flat(),
        cat.total.forecast,
        cat.total.actual,
      ];
      expenseSheet.addRow(row);
    }
    const expenseDataRows = budgetData.value.expense.length;
    addChartImage(expenseSheet, chartImages.expenseChart, expenseDataRows + 2);

    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `budget-${year}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
  } catch (e) {
    console.error('Budget Excel export failed:', e);
  } finally {
    isExportingExcel.value = false;
  }
}
</script>
