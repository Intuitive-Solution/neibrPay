<template>
  <div class="max-w-7xl bg-white p-6">
    <!-- Error Message -->
    <div
      v-if="errors.general"
      class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg"
    >
      <div class="flex">
        <div class="flex-shrink-0">
          <svg
            class="h-5 w-5 text-red-400"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-800">{{ errors.general }}</p>
        </div>
      </div>
    </div>

    <!-- Three Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Column 1: Units -->
      <div class="bg-white rounded-lg shadow">
        <!-- Card Title -->
        <div class="bg-gray-100 px-6 py-3 rounded-t-lg">
          <h3 class="text-lg font-medium text-gray-900">Units</h3>
        </div>

        <!-- Card Content -->
        <div class="p-6">
          <div class="space-y-4">
            <!-- Unit Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Units <span class="text-red-500">*</span>
              </label>

              <!-- Loading State -->
              <div
                v-if="isLoadingUnits"
                class="flex items-center justify-center py-8"
              >
                <div
                  class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"
                ></div>
                <span class="ml-2 text-gray-600">Loading units...</span>
              </div>

              <!-- Error State -->
              <div
                v-else-if="unitsError"
                class="p-4 text-center text-red-600 bg-red-50 rounded-lg"
              >
                <p>Failed to load units. Please try again.</p>
              </div>

              <!-- Searchable Multiselect Dropdown -->
              <div v-else ref="dropdownRef" class="relative">
                <!-- Main Input Field -->
                <div
                  class="relative min-h-[42px] w-full px-3 py-2 border border-gray-300 rounded-lg focus-within:ring-2 focus-within:ring-primary focus-within:border-primary transition-colors duration-200 cursor-pointer"
                  :class="{
                    'border-red-300 focus-within:ring-red-500 focus-within:border-red-500':
                      errors.unit_ids,
                  }"
                  @click="toggleDropdown"
                >
                  <!-- Selected Units as Chips -->
                  <div class="flex flex-wrap gap-2 items-center min-h-[26px]">
                    <div
                      v-for="unitId in form.unit_ids"
                      :key="unitId"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                    >
                      {{ getUnitTitle(unitId) }}
                      <button
                        type="button"
                        @click.stop="removeUnit(unitId)"
                        class="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-primary"
                      >
                        <svg
                          class="w-2.5 h-2.5"
                          fill="currentColor"
                          viewBox="0 0 20 20"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </div>

                    <!-- Search Input -->
                    <input
                      v-model="searchQuery"
                      type="text"
                      placeholder="Search units..."
                      class="flex-1 min-w-[120px] border-0 outline-none bg-transparent text-sm placeholder-gray-500"
                      @click.stop
                      @keydown.escape="closeDropdown"
                      @keydown.down.prevent="navigateDown"
                      @keydown.up.prevent="navigateUp"
                      @keydown.enter.prevent="selectHighlighted"
                    />
                  </div>

                  <!-- Action Icons -->
                  <div
                    class="absolute inset-y-0 right-0 flex items-center pr-3 space-x-1"
                  >
                    <!-- Clear All Button -->
                    <button
                      v-if="form.unit_ids.length > 0"
                      type="button"
                      @click.stop="clearAllUnits"
                      class="inline-flex items-center justify-center w-5 h-5 rounded-full hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                      <svg
                        class="w-3 h-3 text-gray-400"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>

                    <!-- Dropdown Toggle -->
                    <button
                      type="button"
                      @click.stop="toggleDropdown"
                      class="inline-flex items-center justify-center w-5 h-5 rounded-full hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                      <svg
                        class="w-3 h-3 text-gray-400 transition-transform duration-200"
                        :class="{ 'rotate-180': isDropdownOpen }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 9l-7 7-7-7"
                        />
                      </svg>
                    </button>
                  </div>
                </div>

                <!-- Dropdown Menu -->
                <div
                  v-if="isDropdownOpen"
                  class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto"
                >
                  <!-- Select All Option -->
                  <div
                    class="px-3 py-2 text-sm font-medium text-gray-700 border-b border-gray-100 cursor-pointer hover:bg-gray-50"
                    @click="toggleSelectAll"
                  >
                    <div class="flex items-center">
                      <input
                        :checked="isAllSelected"
                        type="checkbox"
                        class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                      />
                      <span class="ml-2">Select all options</span>
                    </div>
                  </div>

                  <!-- Filtered Units List -->
                  <div
                    v-if="filteredUnits.length === 0"
                    class="px-3 py-2 text-sm text-gray-500 text-center"
                  >
                    No units found matching "{{ searchQuery }}"
                  </div>

                  <div v-else class="divide-y divide-gray-100">
                    <div
                      v-for="(unit, index) in filteredUnits"
                      :key="unit.id"
                      class="px-3 py-2 cursor-pointer hover:bg-gray-50"
                      :class="{ 'bg-gray-50': highlightedIndex === index }"
                      @click="toggleUnitSelection(unit.id)"
                      @mouseenter="highlightedIndex = index"
                    >
                      <div class="flex items-center">
                        <input
                          :checked="form.unit_ids.includes(unit.id)"
                          type="checkbox"
                          class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                          @click.stop="toggleUnitSelection(unit.id)"
                        />
                        <div class="ml-2 flex-1">
                          <div class="text-sm font-medium text-gray-900">
                            {{ unit.title }}
                          </div>
                          <div class="text-xs text-gray-500">
                            {{ unit.resident_name }} • {{ unit.address }},
                            {{ unit.city }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Error Message -->
              <p v-if="errors.unit_ids" class="mt-2 text-sm text-red-600">
                {{ errors.unit_ids }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Column 2: Scheduling/Frequency -->
      <div class="bg-white rounded-lg shadow">
        <!-- Card Title -->
        <div class="bg-gray-100 px-6 py-3 rounded-t-lg">
          <h3 class="text-lg font-medium text-gray-900">Scheduling</h3>
        </div>

        <!-- Card Content -->
        <div class="p-6">
          <div class="space-y-4">
            <!-- Frequency -->
            <div>
              <label
                for="frequency"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Frequency <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <select
                  id="frequency"
                  v-model="form.frequency"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm appearance-none bg-white"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500':
                      errors.frequency,
                  }"
                >
                  <option value="one-time">One Time</option>
                  <option value="monthly">Monthly</option>
                  <option value="weekly">Weekly</option>
                  <option value="quarterly">Quarterly</option>
                  <option value="yearly">Yearly</option>
                </select>
                <div
                  class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
                >
                  <svg
                    class="h-5 w-5 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </div>
              <p v-if="errors.frequency" class="mt-2 text-sm text-red-600">
                {{ errors.frequency }}
              </p>
            </div>

            <!-- Remaining Cycles -->
            <div>
              <label
                for="remaining_cycles"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Remaining Cycles
              </label>
              <div class="relative">
                <select
                  id="remaining_cycles"
                  v-model="form.remaining_cycles"
                  :disabled="isOneTimeFrequency"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm appearance-none bg-white"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500':
                      errors.remaining_cycles,
                    'opacity-50 cursor-not-allowed bg-gray-50':
                      isOneTimeFrequency,
                  }"
                >
                  <option value="endless">Endless</option>
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
                  <option value="6">6</option>
                  <option value="12">12</option>
                  <option value="24">24</option>
                </select>
                <div
                  class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
                >
                  <svg
                    class="h-5 w-5 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </div>
              <p
                v-if="errors.remaining_cycles"
                class="mt-2 text-sm text-red-600"
              >
                {{ errors.remaining_cycles }}
              </p>
            </div>
            <!-- Start Date -->
            <div>
              <label
                for="start_date"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Start Date <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input
                  id="start_date"
                  v-model="form.start_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500':
                      errors.start_date,
                  }"
                />
                <div
                  class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
                >
                  <svg
                    class="h-5 w-5 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                </div>
              </div>
              <p v-if="errors.start_date" class="mt-2 text-sm text-red-600">
                {{ errors.start_date }}
              </p>
            </div>
            <!-- Due Date -->
            <div>
              <label
                for="due_date"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Due Date
              </label>
              <div class="relative">
                <select
                  id="due_date"
                  v-model="form.due_date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm appearance-none bg-white"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500':
                      errors.due_date,
                  }"
                >
                  <option value="use_payment_terms">Use Payment Terms</option>
                  <option value="net_15">Net 15</option>
                  <option value="net_30">Net 30</option>
                  <option value="net_45">Net 45</option>
                  <option value="net_60">Net 60</option>
                  <option value="due_on_receipt">Due on Receipt</option>
                </select>
                <div
                  class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
                >
                  <svg
                    class="h-5 w-5 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </div>
              <p v-if="errors.due_date" class="mt-2 text-sm text-red-600">
                {{ errors.due_date }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Column 3: Invoice Details -->
      <div class="bg-white rounded-lg shadow">
        <!-- Card Title -->
        <div class="bg-gray-100 px-6 py-3 rounded-t-lg">
          <h3 class="text-lg font-medium text-gray-900">Invoice Details</h3>
        </div>

        <!-- Card Content -->
        <div class="p-6">
          <div class="space-y-4">
            <!-- Invoice # -->
            <div>
              <label
                for="invoice_number"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Invoice #
              </label>
              <input
                id="invoice_number"
                v-model="form.invoice_number"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.invoice_number,
                }"
                placeholder="Auto-generated if empty"
              />
              <p v-if="errors.invoice_number" class="mt-2 text-sm text-red-600">
                {{ errors.invoice_number }}
              </p>
            </div>

            <!-- PO # -->
            <div>
              <label
                for="po_number"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                PO #
              </label>
              <input
                id="po_number"
                v-model="form.po_number"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.po_number,
                }"
                placeholder="Purchase order number"
              />
              <p v-if="errors.po_number" class="mt-2 text-sm text-red-600">
                {{ errors.po_number }}
              </p>
            </div>

            <!-- Discount -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Discount
              </label>
              <div class="flex gap-2">
                <div class="flex-1">
                  <input
                    v-model="form.discount_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
                    :class="{
                      'border-red-300 focus:ring-red-500 focus:border-red-500':
                        errors.discount_amount,
                    }"
                    placeholder="0.00"
                  />
                </div>
                <div class="w-24">
                  <select
                    v-model="form.discount_type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm appearance-none bg-white"
                    :class="{
                      'border-red-300 focus:ring-red-500 focus:border-red-500':
                        errors.discount_type,
                    }"
                  >
                    <option value="amount">Amount</option>
                    <option value="percentage">%</option>
                  </select>
                </div>
              </div>
              <p
                v-if="errors.discount_amount || errors.discount_type"
                class="mt-2 text-sm text-red-600"
              >
                {{ errors.discount_amount || errors.discount_type }}
              </p>
            </div>

            <!-- Auto Bill -->
            <div>
              <label
                for="auto_bill"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Auto Bill
              </label>
              <div class="relative">
                <select
                  id="auto_bill"
                  v-model="form.auto_bill"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm appearance-none bg-white"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500':
                      errors.auto_bill,
                  }"
                >
                  <option value="disabled">Disabled</option>
                  <option value="enabled">Enabled</option>
                  <option value="on_due_date">On Due Date</option>
                  <option value="on_send">On Send</option>
                </select>
                <div
                  class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
                >
                  <svg
                    class="h-5 w-5 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </div>
              <p v-if="errors.auto_bill" class="mt-2 text-sm text-red-600">
                {{ errors.auto_bill }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Invoice Items Section with Tabs -->
    <div class="mt-8 bg-white rounded-lg shadow">
      <!-- Tab Navigation -->
      <div class="bg-gray-100 rounded-t-lg">
        <nav class="flex space-x-8 px-6" aria-label="Tabs">
          <button
            v-for="tab in invoiceItemsTabs"
            :key="tab.id"
            @click="activeInvoiceItemsTab = tab.id"
            :class="[
              activeInvoiceItemsTab === tab.id
                ? 'text-blue-600 border-b-2 border-blue-600'
                : 'text-gray-500 hover:text-gray-700',
              'whitespace-nowrap py-4 px-1 font-medium text-sm transition-colors duration-200',
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="p-6">
        <!-- Invoice Items Tab -->
        <div v-if="activeInvoiceItemsTab === 'invoice-items'">
          <!-- Table Header -->
          <div class="mb-4">
            <div
              class="grid grid-cols-5 gap-4 text-sm font-medium text-gray-700 uppercase tracking-wide pb-2 border-b border-gray-200"
            >
              <div class="text-left">Item</div>
              <div class="text-left">Description</div>
              <div class="text-left">Unit Cost</div>
              <div class="text-left">Quantity</div>
              <div class="text-right">Line Total</div>
            </div>
          </div>

          <!-- Table Content -->
          <div class="divide-y divide-gray-200">
            <!-- Empty State -->
            <div v-if="invoiceItems.length === 0" class="text-center py-8">
              <div class="text-gray-500 mb-4">
                <svg
                  class="mx-auto h-12 w-12 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  />
                </svg>
              </div>
              <p class="text-gray-500 text-sm">No items added yet</p>
            </div>

            <!-- Items List -->
            <div v-else>
              <div
                v-for="(item, index) in invoiceItems"
                :key="index"
                class="grid grid-cols-5 gap-4 items-start py-3 hover:bg-gray-50"
              >
                <!-- Item Name -->
                <div>
                  <input
                    v-model="item.name"
                    type="text"
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200"
                    placeholder="Item name"
                  />
                </div>

                <!-- Description -->
                <div>
                  <textarea
                    v-model="item.description"
                    rows="2"
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 resize-none"
                    placeholder="Item description"
                  ></textarea>
                </div>

                <!-- Unit Cost -->
                <div>
                  <div class="relative">
                    <span
                      class="absolute left-2 top-1/2 transform -translate-y-1/2 text-sm text-gray-500"
                      >$</span
                    >
                    <input
                      v-model.number="item.unitCost"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-full pl-6 pr-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200"
                      placeholder="0.00"
                      @input="updateLineTotal(index)"
                    />
                  </div>
                </div>

                <!-- Quantity -->
                <div>
                  <input
                    v-model.number="item.quantity"
                    type="number"
                    min="1"
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200"
                    placeholder="1"
                    @input="updateLineTotal(index)"
                  />
                </div>

                <!-- Line Total (Read-only) -->
                <div class="flex items-center justify-end">
                  <span class="text-sm text-gray-900 mr-3 font-medium">
                    ${{ item.lineTotal.toFixed(2) }}
                  </span>
                  <button
                    type="button"
                    @click="removeItem(index)"
                    class="text-red-600 hover:text-red-800 focus:outline-none text-sm"
                  >
                    Remove
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Add Item Button -->
          <div class="mt-4 flex justify-center">
            <button
              type="button"
              @click="addItem"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
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
              Add Item
            </button>
          </div>
        </div>

        <!-- Documents Tab -->
        <div v-else-if="activeInvoiceItemsTab === 'documents'">
          <!-- Upload Section -->
          <div
            class="border-2 border-dashed border-gray-300 rounded-lg p-6 transition-colors duration-200"
            :class="{ 'border-blue-400 bg-blue-50': isDragOver }"
            @dragover.prevent="handleDragOver"
            @dragleave.prevent="handleDragLeave"
            @drop.prevent="handleDrop"
          >
            <div class="text-center">
              <svg
                class="mx-auto h-12 w-12 text-gray-400"
                stroke="currentColor"
                fill="none"
                viewBox="0 0 48 48"
                aria-hidden="true"
              >
                <path
                  d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
              <div class="mt-4">
                <label
                  for="file-upload"
                  class="cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary"
                >
                  <span>Upload a file</span>
                  <input
                    id="file-upload"
                    name="file-upload"
                    type="file"
                    class="sr-only"
                    @change="handleFileUpload"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif"
                  />
                </label>
                <p class="pl-1">or drag and drop</p>
              </div>
              <p class="text-xs text-gray-500 mt-2">
                PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, PNG, GIF up to
                10MB
              </p>
            </div>
          </div>

          <!-- Upload Progress -->
          <div
            v-if="uploadProgress > 0 && uploadProgress < 100"
            class="mt-4 w-full bg-gray-200 rounded-full h-2.5"
          >
            <div
              class="bg-primary h-2.5 rounded-full transition-all duration-300"
              :style="{ width: uploadProgress + '%' }"
            ></div>
          </div>

          <!-- Attachments List -->
          <div v-if="attachments.length > 0" class="mt-6 space-y-3">
            <h3 class="text-lg font-medium text-gray-900">
              Attached Documents
            </h3>
            <div class="space-y-2">
              <div
                v-for="attachment in attachments"
                :key="attachment.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border"
              >
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0">
                    <svg
                      v-if="attachment.attachment_type === 'pdf'"
                      class="h-8 w-8 text-red-500"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    <svg
                      v-else-if="attachment.attachment_type === 'image'"
                      class="h-8 w-8 text-green-500"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    <svg
                      v-else
                      class="h-8 w-8 text-blue-500"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                      {{ attachment.file_name }}
                    </p>
                    <p class="text-sm text-gray-500">
                      {{ attachment.file_size_human }} •
                      {{ attachment.attachment_type.toUpperCase() }}
                    </p>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <button
                    @click="downloadAttachment(attachment)"
                    class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                  >
                    Download
                  </button>
                  <button
                    @click="removeAttachment(attachment)"
                    class="text-red-600 hover:text-red-900 text-sm font-medium"
                  >
                    Remove
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-8">
            <svg
              class="mx-auto h-12 w-12 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No documents</h3>
            <p class="mt-1 text-sm text-gray-500">
              Get started by uploading a document.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs and Total Panel Section -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Section: Tabs and Rich Text Editor -->
      <div class="lg:col-span-2 bg-white rounded-lg shadow">
        <!-- Tabs -->
        <div class="bg-gray-100 border-b border-gray-200 rounded-t-lg">
          <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                activeTab === tab.id
                  ? 'border-primary text-primary'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              ]"
            >
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Rich Text Editor -->
          <div class="border border-gray-300 rounded-lg">
            <!-- Toolbar -->
            <div class="border-b border-gray-200 p-3 bg-gray-50">
              <div class="flex flex-wrap gap-2">
                <!-- Formatting buttons -->
                <div class="flex items-center space-x-1">
                  <select
                    v-model="selectedFormat"
                    @change="formatBlock(selectedFormat)"
                    class="text-sm border border-gray-300 rounded px-2 py-1"
                  >
                    <option value="p">Paragraph</option>
                    <option value="h1">Heading 1</option>
                    <option value="h2">Heading 2</option>
                    <option value="h3">Heading 3</option>
                  </select>
                  <select
                    v-model="selectedFont"
                    @change="formatText('fontName', selectedFont)"
                    class="text-sm border border-gray-300 rounded px-2 py-1"
                  >
                    <option value="Helvetica">Helvetica</option>
                    <option value="Arial">Arial</option>
                    <option value="Times New Roman">Times New Roman</option>
                    <option value="Georgia">Georgia</option>
                  </select>
                  <select
                    v-model="selectedSize"
                    @change="formatText('fontSize', selectedSize)"
                    class="text-sm border border-gray-300 rounded px-2 py-1"
                  >
                    <option value="3">12px</option>
                    <option value="4">14px</option>
                    <option value="5">16px</option>
                    <option value="6">18px</option>
                    <option value="7">24px</option>
                  </select>
                </div>

                <div class="flex items-center space-x-1">
                  <button
                    @click="formatText('bold')"
                    :class="[
                      'p-1 rounded',
                      isFormatActive('bold')
                        ? 'bg-blue-200'
                        : 'hover:bg-gray-200',
                    ]"
                    title="Bold"
                  >
                    <span class="font-bold text-sm">B</span>
                  </button>
                  <button
                    @click="formatText('italic')"
                    :class="[
                      'p-1 rounded',
                      isFormatActive('italic')
                        ? 'bg-blue-200'
                        : 'hover:bg-gray-200',
                    ]"
                    title="Italic"
                  >
                    <span class="italic text-sm">I</span>
                  </button>
                  <button
                    @click="formatText('underline')"
                    :class="[
                      'p-1 rounded',
                      isFormatActive('underline')
                        ? 'bg-blue-200'
                        : 'hover:bg-gray-200',
                    ]"
                    title="Underline"
                  >
                    <span class="underline text-sm">U</span>
                  </button>
                  <button
                    @click="formatText('strikeThrough')"
                    :class="[
                      'p-1 rounded',
                      isFormatActive('strikeThrough')
                        ? 'bg-blue-200'
                        : 'hover:bg-gray-200',
                    ]"
                    title="Strikethrough"
                  >
                    <span class="line-through text-sm">S</span>
                  </button>
                </div>

                <div class="flex items-center space-x-1">
                  <input
                    type="color"
                    @change="
                      (e: Event) =>
                        formatText(
                          'foreColor',
                          (e.target as HTMLInputElement)?.value
                        )
                    "
                    class="w-6 h-6 border border-gray-300 rounded cursor-pointer"
                    title="Text Color"
                  />
                  <input
                    type="color"
                    @change="
                      (e: Event) =>
                        formatText(
                          'backColor',
                          (e.target as HTMLInputElement)?.value
                        )
                    "
                    class="w-6 h-6 border border-gray-300 rounded cursor-pointer"
                    title="Highlight Color"
                  />
                  <button
                    @click="createLink"
                    class="p-1 hover:bg-gray-200 rounded"
                    title="Link"
                  >
                    <span class="text-sm">🔗</span>
                  </button>
                </div>

                <div class="flex items-center space-x-1">
                  <button
                    @click="formatText('justifyLeft')"
                    :class="[
                      'p-1 rounded',
                      isFormatActive('justifyLeft')
                        ? 'bg-blue-200'
                        : 'hover:bg-gray-200',
                    ]"
                    title="Align Left"
                  >
                    <span class="text-sm">⬅️</span>
                  </button>
                  <button
                    @click="formatText('justifyCenter')"
                    :class="[
                      'p-1 rounded',
                      isFormatActive('justifyCenter')
                        ? 'bg-blue-200'
                        : 'hover:bg-gray-200',
                    ]"
                    title="Align Center"
                  >
                    <span class="text-sm">↔️</span>
                  </button>
                  <button
                    @click="formatText('justifyRight')"
                    :class="[
                      'p-1 rounded',
                      isFormatActive('justifyRight')
                        ? 'bg-blue-200'
                        : 'hover:bg-gray-200',
                    ]"
                    title="Align Right"
                  >
                    <span class="text-sm">➡️</span>
                  </button>
                  <button
                    @click="formatText('justifyFull')"
                    :class="[
                      'p-1 rounded',
                      isFormatActive('justifyFull')
                        ? 'bg-blue-200'
                        : 'hover:bg-gray-200',
                    ]"
                    title="Justify"
                  >
                    <span class="text-sm">⬌</span>
                  </button>
                </div>

                <div class="flex items-center space-x-1">
                  <button
                    @click="formatText('insertUnorderedList')"
                    :class="[
                      'p-1 rounded',
                      isFormatActive('insertUnorderedList')
                        ? 'bg-blue-200'
                        : 'hover:bg-gray-200',
                    ]"
                    title="Bullet List"
                  >
                    <span class="text-sm">•</span>
                  </button>
                  <button
                    @click="formatText('insertOrderedList')"
                    :class="[
                      'p-1 rounded',
                      isFormatActive('insertOrderedList')
                        ? 'bg-blue-200'
                        : 'hover:bg-gray-200',
                    ]"
                    title="Numbered List"
                  >
                    <span class="text-sm">1.</span>
                  </button>
                  <button
                    @click="insertTable"
                    class="p-1 hover:bg-gray-200 rounded"
                    title="Table"
                  >
                    <span class="text-sm">⊞</span>
                  </button>
                </div>

                <div class="flex items-center space-x-1">
                  <button
                    @click="formatText('removeFormat')"
                    class="p-1 hover:bg-gray-200 rounded"
                    title="Clear Formatting"
                  >
                    <span class="text-sm">Tx</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Editor Content Area -->
            <div class="p-4 h-48 overflow-y-auto">
              <div
                ref="editorRef"
                contenteditable="true"
                @input="updateContent"
                @keyup="updateFormatState"
                @mouseup="updateFormatState"
                class="rich-text-editor w-full border-0 outline-none resize-none text-sm focus:outline-none"
                :placeholder="getTabPlaceholder(activeTab)"
              ></div>
            </div>

            <!-- Status Bar -->
            <div
              class="border-t border-gray-200 px-4 py-2 bg-gray-50 flex justify-between items-center text-xs text-gray-500"
            >
              <span>{{ getCurrentTag() }}</span>
              <span
                >{{
                  getWordCount(
                    getPlainText(
                      tabContent[activeTab as keyof typeof tabContent] || ''
                    )
                  )
                }}
                words</span
              >
            </div>
          </div>
        </div>
      </div>

      <!-- Right Section: Total Panel -->
      <div class="bg-white rounded-lg shadow">
        <!-- Card Title -->
        <div class="bg-gray-100 px-6 py-3 rounded-t-lg">
          <h3 class="text-lg font-medium text-gray-900">Invoice Summary</h3>
        </div>

        <!-- Card Content -->
        <div class="p-6">
          <div class="space-y-3">
            <!-- Subtotal -->
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Subtotal</span>
              <span class="text-sm font-medium text-gray-900"
                >${{ subtotal.toFixed(2) }}</span
              >
            </div>

            <!-- Tax -->
            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Tax</span>
                <div class="flex items-center space-x-2">
                  <input
                    v-model.number="taxRate"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    class="w-16 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary"
                    placeholder="0"
                  />
                  <span class="text-sm text-gray-500">%</span>
                </div>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Tax Amount</span>
                <span class="text-sm font-medium text-gray-900"
                  >${{ taxAmount.toFixed(2) }}</span
                >
              </div>
            </div>

            <!-- Total -->
            <div class="border-t border-gray-200 pt-3">
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-900">Total</span>
                <span class="text-sm font-bold text-gray-900"
                  >${{ total.toFixed(2) }}</span
                >
              </div>
            </div>

            <!-- Paid to Date -->
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Paid to Date</span>
              <span class="text-sm font-medium text-gray-900"
                >${{ paidToDate.toFixed(2) }}</span
              >
            </div>

            <!-- Balance Due -->
            <div class="border-t border-gray-200 pt-3">
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-900"
                  >Balance Due</span
                >
                <span class="text-sm font-bold text-gray-900"
                  >${{ balanceDue.toFixed(2) }}</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-8 flex justify-end space-x-4">
      <button
        type="button"
        @click.prevent="handleCancel"
        class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
      >
        Cancel
      </button>
      <button
        type="button"
        @click="handleSubmit"
        :disabled="isSubmitting"
        class="px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="isSubmitting" class="flex items-center">
          <svg
            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
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
          Creating...
        </span>
        <span v-else>Create Invoice</span>
      </button>
    </div>
  </div>
  <!-- PDF Preview Panel -->
  <div v-if="showPreview" class="max-w-7xl bg-white rounded-lg shadow p-6 mt-6">
    <!-- Card Title with Action Buttons -->
    <div
      class="bg-gray-300 px-6 py-3 rounded-t-lg -m-6 mb-6 flex justify-between items-center"
    >
      <div class="flex items-center space-x-4">
        <h3 class="text-lg font-medium text-gray-900">Invoice Preview</h3>

        <!-- Unit Selector Dropdown (only show if multiple units) -->
        <div
          v-if="form.unit_ids.length > 1"
          class="flex items-center space-x-2"
        >
          <label class="text-sm font-medium text-gray-700">Unit:</label>
          <select
            v-model="selectedPreviewUnitId"
            class="px-3 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary bg-white"
          >
            <option
              v-for="unitId in form.unit_ids"
              :key="unitId"
              :value="unitId"
            >
              {{ getUnitTitle(unitId) }}
            </option>
          </select>
        </div>
      </div>

      <div class="flex space-x-2">
        <button
          @click="generatePDFPreview"
          :disabled="isGeneratingPDF"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center transition-colors duration-200"
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
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
            />
          </svg>
          {{ isGeneratingPDF ? 'Generating...' : 'Preview PDF' }}
        </button>

        <button
          @click="downloadPDF"
          :disabled="isGeneratingPDF"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center transition-colors duration-200"
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
          Download PDF
        </button>

        <button
          @click="printPDF"
          :disabled="isGeneratingPDF"
          class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center transition-colors duration-200"
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
              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
            />
          </svg>
          Print PDF
        </button>
      </div>
    </div>

    <!-- Invoice Template Preview -->
    <div class="preview-container">
      <InvoiceTemplate
        :form="previewForm"
        :invoice-items="invoiceItems"
        :subtotal="subtotal"
        :tax-rate="taxRate"
        :tax-amount="taxAmount"
        :total="total"
        :paid-to-date="paidToDate"
        :balance-due="balanceDue"
        :tab-content="tabContent"
        :units="units"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useUnitsForInvoices } from '../composables/useUnits';
import { useCreateInvoice } from '../composables/useInvoices';
import {
  useUploadInvoiceAttachment,
  useDeleteInvoiceAttachment,
  useDownloadInvoiceAttachment,
} from '../composables/useInvoiceAttachments';
import type { UnitWithResident, CreateInvoiceRequest } from '@neibrpay/models';
import InvoiceTemplate from '../components/InvoiceTemplate.vue';
import { jsPDF } from 'jspdf';
import html2canvas from 'html2canvas';

// Router
const router = useRouter();

// Form data
const form = ref({
  unit_ids: [] as number[], // Changed to support multiple units with proper typing
  frequency: 'monthly',
  start_date: '',
  remaining_cycles: 'endless',
  due_date: 'use_payment_terms',
  invoice_number: '',
  po_number: '',
  discount_amount: '',
  discount_type: 'amount',
  auto_bill: 'disabled',
});

// Form errors
const errors = ref({
  general: '',
  unit_ids: '', // Changed to match form data
  frequency: '',
  start_date: '',
  remaining_cycles: '',
  due_date: '',
  invoice_number: '',
  po_number: '',
  discount_amount: '',
  discount_type: '',
  auto_bill: '',
});

// Loading state
const isSubmitting = computed(() => createInvoiceMutation.isPending.value);
const isGeneratingPDF = ref(false);

// PDF Preview state
const selectedPreviewUnitId = ref<number | null>(null);

// Fetch units with resident information
const {
  data: units,
  isLoading: isLoadingUnits,
  error: unitsError,
} = useUnitsForInvoices();

// Create invoice mutation
const createInvoiceMutation = useCreateInvoice();

// Dropdown state
const isDropdownOpen = ref(false);
const searchQuery = ref('');
const highlightedIndex = ref(-1);
const dropdownRef = ref<HTMLElement | null>(null);

// Invoice items
const invoiceItems = ref([
  // Sample data for demonstration
  {
    name: 'Monthly HOA Fee',
    description: 'Homeowners Association monthly maintenance fee',
    unitCost: 150.0,
    quantity: 1,
    lineTotal: 150.0,
  },
  {
    name: 'Landscaping Service',
    description: 'Monthly lawn care and garden maintenance',
    unitCost: 75.0,
    quantity: 1,
    lineTotal: 75.0,
  },
]);

// Tabs data
const tabs = ref([
  { id: 'public-notes', name: 'Public Notes' },
  { id: 'private-notes', name: 'Private Notes' },
  { id: 'terms', name: 'Terms' },
  { id: 'footer', name: 'Footer' },
]);

const activeTab = ref('public-notes');

// Invoice items tabs
const invoiceItemsTabs = ref([
  { id: 'invoice-items', name: 'Invoice Items' },
  { id: 'documents', name: 'Documents' },
]);

const activeInvoiceItemsTab = ref('invoice-items');

// Document management
const attachments = ref<any[]>([]);
const uploadProgress = ref(0);
const isDragOver = ref(false);
const uploadAttachmentMutation = useUploadInvoiceAttachment();
const deleteAttachmentMutation = useDeleteInvoiceAttachment();
const downloadAttachmentMutation = useDownloadInvoiceAttachment();

// Tab content
const tabContent = ref({
  'public-notes': '',
  'private-notes': '',
  terms: '',
  footer: '',
});

// Financial calculations
const taxRate = ref(0);
const paidToDate = ref(0);

// Rich text editor state
const editorRef = ref<HTMLElement | null>(null);
const selectedFormat = ref('p');
const selectedFont = ref('Helvetica');
const selectedSize = ref('4');
const formatState = ref({
  bold: false,
  italic: false,
  underline: false,
  strikeThrough: false,
  justifyLeft: false,
  justifyCenter: false,
  justifyRight: false,
  justifyFull: false,
  insertUnorderedList: false,
  insertOrderedList: false,
});

// Computed properties
const filteredUnits = computed((): UnitWithResident[] => {
  if (!units.value) return [];
  if (!searchQuery.value) return units.value;

  const query = searchQuery.value.toLowerCase();
  return units.value.filter(
    (unit: UnitWithResident) =>
      unit.title.toLowerCase().includes(query) ||
      unit.resident_name.toLowerCase().includes(query) ||
      unit.address.toLowerCase().includes(query) ||
      unit.city.toLowerCase().includes(query)
  );
});

const isAllSelected = computed(() => {
  if (!filteredUnits.value || filteredUnits.value.length === 0) return false;
  return filteredUnits.value.every((unit: UnitWithResident) =>
    form.value.unit_ids.includes(unit.id)
  );
});

const isOneTimeFrequency = computed(() => {
  return form.value.frequency === 'one-time';
});

// Financial calculations
const subtotal = computed(() => {
  return invoiceItems.value.reduce(
    (sum: number, item: any) => sum + item.lineTotal,
    0
  );
});

const taxAmount = computed(() => {
  return (subtotal.value * taxRate.value) / 100;
});

const total = computed(() => {
  return subtotal.value + taxAmount.value;
});

const balanceDue = computed(() => {
  return total.value - paidToDate.value;
});

// Show preview when conditions are met
const showPreview = computed(() => {
  return form.value.unit_ids.length > 0 && invoiceItems.value.length > 0;
});

// Preview form with only the selected unit
const previewForm = computed(() => {
  const selectedUnitId = selectedPreviewUnitId.value || form.value.unit_ids[0];
  return {
    ...form.value,
    unit_ids: [selectedUnitId],
  };
});

// Watchers
watch(
  () => form.value.frequency,
  (newFrequency: string, oldFrequency: string) => {
    if (newFrequency === 'one-time') {
      form.value.remaining_cycles = '';
    } else if (oldFrequency === 'one-time' && newFrequency !== 'one-time') {
      form.value.remaining_cycles = 'endless';
    }
  }
);

// Watch for tab changes to load content
watch(activeTab, (newTab: string) => {
  if (editorRef.value) {
    editorRef.value.innerHTML =
      tabContent.value[newTab as keyof typeof tabContent.value] || '';
  }
});

// Watch for unit changes to update selected preview unit
watch(
  () => form.value.unit_ids,
  (newUnitIds: number[]) => {
    if (newUnitIds.length > 0) {
      // If no unit is selected or the selected unit is not in the new list, select the first one
      if (
        !selectedPreviewUnitId.value ||
        !newUnitIds.includes(selectedPreviewUnitId.value)
      ) {
        selectedPreviewUnitId.value = newUnitIds[0];
      }
    } else {
      selectedPreviewUnitId.value = null;
    }
  },
  { immediate: true }
);

// PDF Generation Methods
const generatePDFPreview = async () => {
  try {
    isGeneratingPDF.value = true;

    const element = document.getElementById('invoice-preview');
    if (!element) {
      console.error('Invoice preview element not found');
      return;
    }

    const canvas = await html2canvas(element, {
      scale: 2, // Higher quality
      useCORS: true,
      allowTaint: true,
      backgroundColor: '#ffffff',
    });

    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF('p', 'mm', 'a4');

    const imgWidth = 210; // A4 width in mm
    const pageHeight = 295; // A4 height in mm
    const imgHeight = (canvas.height * imgWidth) / canvas.width;
    let heightLeft = imgHeight;
    let position = 0;

    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
    heightLeft -= pageHeight;

    while (heightLeft >= 0) {
      position = heightLeft - imgHeight;
      pdf.addPage();
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
      heightLeft -= pageHeight;
    }

    // Open PDF in new tab
    const pdfBlob = pdf.output('blob');
    const pdfUrl = URL.createObjectURL(pdfBlob);
    window.open(pdfUrl, '_blank');

    // Clean up
    setTimeout(() => URL.revokeObjectURL(pdfUrl), 1000);
  } catch (error) {
    console.error('Error generating PDF preview:', error);
    alert('Error generating PDF preview. Please try again.');
  } finally {
    isGeneratingPDF.value = false;
  }
};

const downloadPDF = async () => {
  try {
    isGeneratingPDF.value = true;

    const element = document.getElementById('invoice-preview');
    if (!element) {
      console.error('Invoice preview element not found');
      return;
    }

    const canvas = await html2canvas(element, {
      scale: 2,
      useCORS: true,
      allowTaint: true,
      backgroundColor: '#ffffff',
    });

    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF('p', 'mm', 'a4');

    const imgWidth = 210;
    const pageHeight = 295;
    const imgHeight = (canvas.height * imgWidth) / canvas.width;
    let heightLeft = imgHeight;
    let position = 0;

    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
    heightLeft -= pageHeight;

    while (heightLeft >= 0) {
      position = heightLeft - imgHeight;
      pdf.addPage();
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
      heightLeft -= pageHeight;
    }

    // Download the PDF
    const fileName = `invoice-${form.value.invoice_number || 'preview'}-${new Date().toISOString().split('T')[0]}.pdf`;
    pdf.save(fileName);
  } catch (error) {
    console.error('Error downloading PDF:', error);
    alert('Error downloading PDF. Please try again.');
  } finally {
    isGeneratingPDF.value = false;
  }
};

const printPDF = async () => {
  try {
    isGeneratingPDF.value = true;

    const element = document.getElementById('invoice-preview');
    if (!element) {
      console.error('Invoice preview element not found');
      return;
    }

    const canvas = await html2canvas(element, {
      scale: 2,
      useCORS: true,
      allowTaint: true,
      backgroundColor: '#ffffff',
    });

    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF('p', 'mm', 'a4');

    const imgWidth = 210;
    const pageHeight = 295;
    const imgHeight = (canvas.height * imgWidth) / canvas.width;
    let heightLeft = imgHeight;
    let position = 0;

    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
    heightLeft -= pageHeight;

    while (heightLeft >= 0) {
      position = heightLeft - imgHeight;
      pdf.addPage();
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
      heightLeft -= pageHeight;
    }

    // Create blob and open in new window for printing
    const pdfBlob = pdf.output('blob');
    const pdfUrl = URL.createObjectURL(pdfBlob);

    // Open PDF in new window and trigger print dialog
    const printWindow = window.open(pdfUrl, '_blank');
    if (printWindow) {
      printWindow.onload = () => {
        printWindow.print();
      };

      // Clean up after printing
      setTimeout(() => {
        URL.revokeObjectURL(pdfUrl);
        printWindow.close();
      }, 1000);
    }
  } catch (error) {
    console.error('Error printing PDF:', error);
    alert('Error printing PDF. Please try again.');
  } finally {
    isGeneratingPDF.value = false;
  }
};

// Methods
const getUnitTitle = (unitId: number) => {
  const unit = units.value?.find((u: UnitWithResident) => u.id === unitId);
  return unit ? unit.title : `Unit ${unitId}`;
};

const toggleUnitSelection = (unitId: number) => {
  const index = form.value.unit_ids.indexOf(unitId);
  if (index > -1) {
    form.value.unit_ids.splice(index, 1);
  } else {
    form.value.unit_ids.push(unitId);
  }
};

const removeUnit = (unitId: number) => {
  const index = form.value.unit_ids.indexOf(unitId);
  if (index > -1) {
    form.value.unit_ids.splice(index, 1);
  }
};

const clearAllUnits = () => {
  form.value.unit_ids = [];
};

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
  if (isDropdownOpen.value) {
    highlightedIndex.value = -1;
  }
};

const closeDropdown = () => {
  isDropdownOpen.value = false;
  highlightedIndex.value = -1;
  searchQuery.value = '';
};

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    // If all visible units are selected, deselect all visible units
    const filteredUnitIds = filteredUnits.value.map(
      (unit: UnitWithResident) => unit.id
    );
    form.value.unit_ids = form.value.unit_ids.filter(
      (id: number) => !filteredUnitIds.includes(id)
    );
  } else {
    // If not all visible units are selected, select all visible units
    const filteredUnitIds = filteredUnits.value.map(
      (unit: UnitWithResident) => unit.id
    );
    const newSelections = filteredUnitIds.filter(
      (id: number) => !form.value.unit_ids.includes(id)
    );
    form.value.unit_ids = [...form.value.unit_ids, ...newSelections];
  }
};

const navigateDown = () => {
  if (highlightedIndex.value < filteredUnits.value.length - 1) {
    highlightedIndex.value++;
  }
};

const navigateUp = () => {
  if (highlightedIndex.value > 0) {
    highlightedIndex.value--;
  }
};

const selectHighlighted = () => {
  if (
    highlightedIndex.value >= 0 &&
    highlightedIndex.value < filteredUnits.value.length
  ) {
    const unit = filteredUnits.value[highlightedIndex.value];
    toggleUnitSelection(unit.id);
  }
};

// Invoice items methods
const addItem = () => {
  const newItem = {
    name: 'New Item',
    description: 'Item description',
    unitCost: 0.0,
    quantity: 1,
    lineTotal: 0.0,
  };
  invoiceItems.value.push(newItem);
};

const removeItem = (index: number) => {
  invoiceItems.value.splice(index, 1);
};

const updateLineTotal = (index: number) => {
  const item = invoiceItems.value[index];
  if (
    item &&
    typeof item.unitCost === 'number' &&
    typeof item.quantity === 'number'
  ) {
    item.lineTotal = item.unitCost * item.quantity;
  }
};

// Document management functions
const handleFileUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];

  if (!file) return;

  processFile(file);

  // Clear the input
  target.value = '';
};

const removeAttachment = async (attachment: any) => {
  if (attachment.is_local) {
    // Remove from local array
    const index = attachments.value.findIndex(
      (a: any) => a.id === attachment.id
    );
    if (index > -1) {
      attachments.value.splice(index, 1);
    }
  } else {
    // Delete from server (this would require an invoice ID)
    try {
      await deleteAttachmentMutation.mutateAsync({
        invoiceId: 0, // This would be the actual invoice ID
        attachmentId: attachment.id,
      });

      // Remove from local array
      const index = attachments.value.findIndex(
        (a: any) => a.id === attachment.id
      );
      if (index > -1) {
        attachments.value.splice(index, 1);
      }
    } catch (error) {
      console.error('Error deleting attachment:', error);
      alert('Failed to delete attachment');
    }
  }
};

const downloadAttachment = async (attachment: any) => {
  if (attachment.is_local) {
    // For local files, create a download link
    const url = URL.createObjectURL(attachment.file);
    const link = document.createElement('a');
    link.href = url;
    link.download = attachment.file_name;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  } else {
    // Download from server
    try {
      await downloadAttachmentMutation.mutateAsync({
        invoiceId: 0, // This would be the actual invoice ID
        attachmentId: attachment.id,
        fileName: attachment.file_name,
      });
    } catch (error) {
      console.error('Error downloading attachment:', error);
      alert('Failed to download attachment');
    }
  }
};

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getAttachmentType = (mimeType: string, fileName: string): string => {
  if (
    mimeType === 'application/pdf' ||
    fileName.toLowerCase().endsWith('.pdf')
  ) {
    return 'pdf';
  }
  if (mimeType.startsWith('image/')) {
    return 'image';
  }
  const documentExtensions = [
    '.doc',
    '.docx',
    '.xls',
    '.xlsx',
    '.ppt',
    '.pptx',
    '.txt',
  ];
  if (documentExtensions.some(ext => fileName.toLowerCase().endsWith(ext))) {
    return 'document';
  }
  return 'other';
};

// Drag and drop handlers
const handleDragOver = (event: DragEvent) => {
  event.preventDefault();
  isDragOver.value = true;
};

const handleDragLeave = (event: DragEvent) => {
  event.preventDefault();
  // Only set isDragOver to false if we're leaving the drop zone entirely
  const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();
  const x = event.clientX;
  const y = event.clientY;

  if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
    isDragOver.value = false;
  }
};

const handleDrop = (event: DragEvent) => {
  event.preventDefault();
  isDragOver.value = false;

  const files = event.dataTransfer?.files;
  if (files && files.length > 0) {
    const file = files[0];
    processFile(file);
  }
};

const processFile = (file: File) => {
  // Validate file size (10MB max)
  if (file.size > 10 * 1024 * 1024) {
    alert('File size must be less than 10MB');
    return;
  }

  // Validate file type
  const allowedTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'text/plain',
    'image/jpeg',
    'image/png',
    'image/gif',
  ];

  const allowedExtensions = [
    '.pdf',
    '.doc',
    '.docx',
    '.xls',
    '.xlsx',
    '.ppt',
    '.pptx',
    '.txt',
    '.jpg',
    '.jpeg',
    '.png',
    '.gif',
  ];
  const fileName = file.name.toLowerCase();

  if (
    !allowedTypes.includes(file.type) &&
    !allowedExtensions.some(ext => fileName.endsWith(ext))
  ) {
    alert(
      'File type not supported. Please upload PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, PNG, or GIF files.'
    );
    return;
  }

  // Process the file (same logic as handleFileUpload)
  try {
    // Simulate upload progress
    uploadProgress.value = 0;
    const progressInterval = setInterval(() => {
      if (uploadProgress.value < 90) {
        uploadProgress.value += 10;
      }
    }, 100);

    // Create a local attachment object
    const attachment = {
      id: Date.now(), // Temporary ID
      file_name: file.name,
      file_size: file.size,
      file_size_human: formatFileSize(file.size),
      attachment_type: getAttachmentType(file.type, file.name),
      mime_type: file.type,
      file: file, // Store the actual file for later upload
      is_local: true, // Flag to indicate this is a local file
    };

    attachments.value.push(attachment);
    uploadProgress.value = 100;

    setTimeout(() => {
      uploadProgress.value = 0;
      clearInterval(progressInterval);
    }, 500);
  } catch (error) {
    console.error('Error processing file:', error);
    alert('Failed to process file');
    uploadProgress.value = 0;
  }
};

// Tab helper methods
const getTabPlaceholder = (tabId: string) => {
  const placeholders: Record<string, string> = {
    'public-notes': 'Add public notes that will be visible to the customer...',
    'private-notes': 'Add private notes for internal use only...',
    terms: 'Add payment terms and conditions...',
    footer: 'Add footer text for the invoice...',
  };
  return placeholders[tabId] || 'Enter content...';
};

const getWordCount = (text: string) => {
  if (!text) return 0;
  return text
    .trim()
    .split(/\s+/)
    .filter((word: string) => word.length > 0).length;
};

// Rich text editor methods
const formatText = (command: string, value?: string) => {
  document.execCommand(command, false, value);
  updateFormatState();
};

const formatBlock = (tag: string) => {
  // Focus the editor first
  if (editorRef.value) {
    editorRef.value.focus();
  }

  // Simple and reliable approach using execCommand
  document.execCommand('formatBlock', false, tag);
  updateFormatState();
};

const isFormatActive = (command: string) => {
  return formatState.value[command as keyof typeof formatState.value] || false;
};

const updateFormatState = () => {
  if (!editorRef.value) return;

  formatState.value.bold = document.queryCommandState('bold');
  formatState.value.italic = document.queryCommandState('italic');
  formatState.value.underline = document.queryCommandState('underline');
  formatState.value.strikeThrough = document.queryCommandState('strikeThrough');
  formatState.value.justifyLeft = document.queryCommandState('justifyLeft');
  formatState.value.justifyCenter = document.queryCommandState('justifyCenter');
  formatState.value.justifyRight = document.queryCommandState('justifyRight');
  formatState.value.justifyFull = document.queryCommandState('justifyFull');
  formatState.value.insertUnorderedList = document.queryCommandState(
    'insertUnorderedList'
  );
  formatState.value.insertOrderedList =
    document.queryCommandState('insertOrderedList');

  // Update the selected format in dropdown
  const selection = window.getSelection();
  if (selection && selection.rangeCount > 0) {
    const range = selection.getRangeAt(0);
    const container = range.commonAncestorContainer;
    const element =
      container.nodeType === Node.TEXT_NODE
        ? container.parentElement
        : (container as Element);

    if (element) {
      const tagName = element.tagName.toLowerCase();
      if (['h1', 'h2', 'h3', 'p'].includes(tagName)) {
        selectedFormat.value = tagName;
      } else {
        selectedFormat.value = 'p';
      }
    }
  }
};

const updateContent = () => {
  if (editorRef.value) {
    const key = activeTab.value as keyof typeof tabContent.value;
    tabContent.value[key] = editorRef.value.innerHTML;
  }
};

const createLink = () => {
  const url = prompt('Enter URL:');
  if (url) {
    formatText('createLink', url);
  }
};

const insertTable = () => {
  const table =
    '<table border="1" style="border-collapse: collapse; width: 100%;"><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr></table>';
  formatText('insertHTML', table);
};

const getCurrentTag = () => {
  if (!editorRef.value) return 'p';
  const selection = window.getSelection();
  if (selection && selection.rangeCount > 0) {
    const range = selection.getRangeAt(0);
    const container = range.commonAncestorContainer;
    const element =
      container.nodeType === Node.TEXT_NODE
        ? container.parentElement
        : (container as Element);
    return element?.tagName?.toLowerCase() || 'p';
  }
  return 'p';
};

const getPlainText = (html: string) => {
  const temp = document.createElement('div');
  temp.innerHTML = html;
  return temp.textContent || temp.innerText || '';
};

// Click outside handler
const handleClickOutside = (event: Event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    closeDropdown();
  }
};

const handleSubmit = async () => {
  // Clear previous errors
  errors.value = {
    general: '',
    unit_ids: '',
    frequency: '',
    start_date: '',
    remaining_cycles: '',
    due_date: '',
    invoice_number: '',
    po_number: '',
    discount_amount: '',
    discount_type: '',
    auto_bill: '',
  };

  // Basic validation
  if (form.value.unit_ids.length === 0) {
    errors.value.unit_ids = 'Please select at least one unit';
    return;
  }

  if (!form.value.frequency) {
    errors.value.frequency = 'Please select a frequency';
    return;
  }

  if (!form.value.start_date) {
    errors.value.start_date = 'Please select a start date';
    return;
  }

  if (invoiceItems.value.length === 0) {
    errors.value.general = 'Please add at least one invoice item';
    return;
  }

  try {
    // Prepare the request data
    const requestData: CreateInvoiceRequest = {
      unit_ids: form.value.unit_ids,
      frequency: form.value.frequency as any,
      start_date: form.value.start_date,
      remaining_cycles:
        form.value.frequency === 'one-time'
          ? undefined
          : form.value.remaining_cycles,
      due_date: form.value.due_date as any,
      discount_amount: form.value.discount_amount
        ? parseFloat(form.value.discount_amount)
        : undefined,
      discount_type: form.value.discount_type as any,
      auto_bill: form.value.auto_bill as any,
      items: invoiceItems.value.map((item: any) => ({
        name: item.name,
        description: item.description,
        unit_cost: item.unitCost,
        quantity: item.quantity,
        line_total: item.lineTotal,
        sort_order: 0,
        taxable: true,
      })),
      tax_rate: taxRate.value,
      notes: {
        public_notes: tabContent.value['public-notes'],
        private_notes: tabContent.value['private-notes'],
        terms: tabContent.value.terms,
        footer: tabContent.value.footer,
      },
      po_number: form.value.po_number || undefined,
    };

    // Create the invoice
    const response = await createInvoiceMutation.mutateAsync(requestData);

    // Upload attachments if any - create one attachment record for each invoice (unit)
    if (attachments.value.length > 0 && response.length > 0) {
      // Upload attachments to each created invoice (one for each unit)
      for (const invoice of response) {
        for (const attachment of attachments.value.filter(
          (a: any) => a.is_local
        )) {
          try {
            await uploadAttachmentMutation.mutateAsync({
              invoiceId: invoice.id,
              file: attachment.file,
            });
          } catch (error) {
            console.error(
              `Error uploading attachment to invoice ${invoice.id}:`,
              error
            );
            // Continue with other attachments even if one fails
          }
        }
      }
    }

    // Show success message and redirect
    console.log('Invoice created successfully:', response);
    router.push('/invoices');
  } catch (error: any) {
    console.error('Error creating invoice:', error);

    // Handle validation errors
    if (error.errors) {
      // Map backend validation errors to form errors
      Object.keys(error.errors).forEach(field => {
        if (errors.value.hasOwnProperty(field)) {
          errors.value[field as keyof typeof errors.value] =
            error.errors[field][0];
        }
      });
    } else {
      errors.value.general =
        error.message || 'Failed to create invoice. Please try again.';
    }
  }
};

const handleCancel = () => {
  console.log('Cancel button clicked');
  try {
    // Try to go back in history first, fallback to invoices list
    if (window.history.length > 1) {
      console.log('Going back in history');
      router.go(-1);
    } else {
      console.log('Navigating to /invoices');
      router.push('/invoices');
    }
  } catch (error) {
    console.error('Navigation error:', error);
    // Fallback to direct navigation
    window.location.href = '/invoices';
  }
};

// Initialize form with default values
onMounted(() => {
  // Set default start date to today
  const today = new Date();
  form.value.start_date = today.toISOString().split('T')[0];

  // Add click outside listener
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  // Remove click outside listener
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style>
/* Rich text editor styles - Global styles for contenteditable */
.rich-text-editor h1 {
  font-size: 2rem !important;
  font-weight: bold !important;
  margin: 0.5rem 0 !important;
  line-height: 1.2 !important;
  color: #1f2937 !important;
}

.rich-text-editor h2 {
  font-size: 1.5rem !important;
  font-weight: bold !important;
  margin: 0.5rem 0 !important;
  line-height: 1.3 !important;
  color: #1f2937 !important;
}

.rich-text-editor h3 {
  font-size: 1.25rem !important;
  font-weight: bold !important;
  margin: 0.5rem 0 !important;
  line-height: 1.4 !important;
  color: #1f2937 !important;
}

.rich-text-editor p {
  margin: 0.5rem 0 !important;
  line-height: 1.5 !important;
}

.rich-text-editor ul,
.rich-text-editor ol {
  margin: 0.5rem 0 !important;
  padding-left: 1.5rem !important;
}

.rich-text-editor li {
  margin: 0.25rem 0 !important;
}

.rich-text-editor table {
  border-collapse: collapse !important;
  width: 100% !important;
  margin: 0.5rem 0 !important;
}

.rich-text-editor td {
  border: 1px solid #ccc !important;
  padding: 0.5rem !important;
  min-width: 50px !important;
}

.rich-text-editor a {
  color: #2563eb !important;
  text-decoration: underline !important;
}

.rich-text-editor a:hover {
  color: #1d4ed8 !important;
}

/* PDF Preview Container Styles */
.preview-container {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
  background: #f9fafb;
  padding: 20px;
  max-height: 80vh;
  overflow-y: auto;
}

.preview-container .invoice-template {
  transform: scale(0.8);
  transform-origin: top left;
  margin-bottom: -20%;
}
</style>
