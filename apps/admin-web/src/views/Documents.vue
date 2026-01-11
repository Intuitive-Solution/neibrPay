<template>
  <div class="space-y-6">
    <!-- Breadcrumb Navigation -->
    <div class="flex items-center space-x-2 text-sm text-gray-600">
      <button
        @click="navigateToFolder(null)"
        class="hover:text-primary"
        :class="{ 'font-semibold text-gray-900': currentFolderId === null }"
      >
        Root
      </button>
      <template v-if="currentFolderId !== null">
        <span>/</span>
        <span class="font-semibold text-gray-900">Current Folder</span>
      </template>
    </div>

    <!-- Documents List -->
    <div class="card-modern bg-white rounded-lg shadow-sm">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
        >
          <!-- Search -->
          <div class="flex-1 max-w-md">
            <div class="relative">
              <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
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
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  />
                </svg>
              </div>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search documents..."
                class="input-field pl-10 w-full"
              />
            </div>
          </div>

          <!-- Filter Toggle -->
          <div class="flex items-center space-x-3">
            <!-- Show only visible to residents - Hidden for residents -->
            <label v-if="!isResident" class="flex items-center">
              <input
                v-model="filterVisible"
                type="checkbox"
                class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
              />
              <span class="ml-2 text-sm text-gray-700">
                Show only visible to residents
              </span>
            </label>

            <!-- Refresh Button -->
            <button
              @click="() => refetch()"
              :disabled="isLoading"
              class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 transition-colors duration-200"
            >
              <svg
                class="h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                :class="{ 'animate-spin': isLoading }"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                />
              </svg>
            </button>

            <!-- Create Folder Button - Hidden for residents -->
            <button
              v-if="!isResident"
              @click="openFolderModal()"
              class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
              title="Create Folder"
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
                  d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"
                />
              </svg>
              New Folder
            </button>

            <!-- Upload Button (Icon Only) - Hidden for residents -->
            <button
              v-if="!isResident"
              @click="
                () => {
                  uploadForm.folder_id = currentFolderId.value;
                  showUploadModal = true;
                }
              "
              class="btn-primary btn-sm"
              title="Upload Document"
            >
              <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div
        v-if="isLoading || isLoadingFolders"
        class="flex items-center justify-center py-12"
      >
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
          <span class="text-gray-500">Loading documents...</span>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="
          !isLoading &&
          !isLoadingFolders &&
          filteredDocuments.length === 0 &&
          filteredFolders.length === 0
        "
        class="flex flex-col items-center justify-center py-12 px-4"
      >
        <svg
          class="w-16 h-16 text-gray-300 mb-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
          />
        </svg>
        <p class="text-gray-500 text-lg font-medium">No documents found</p>
        <p class="text-gray-400 text-sm mt-1">
          {{
            isResident
              ? 'No documents available'
              : 'Upload your first document to get started'
          }}
        </p>
        <button
          v-if="!isResident"
          @click="showUploadModal = true"
          class="btn-primary mt-4"
        >
          Upload Document
        </button>
      </div>

      <!-- Documents and Folders Table -->
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Name
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Description
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Size
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Uploaded By
              </th>
              <th
                v-if="!isResident"
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Visible to Residents
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Date
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <!-- Folders -->
            <tr
              v-for="folder in filteredFolders"
              :key="`folder-${folder.id}`"
              class="hover:bg-gray-50 cursor-pointer"
              @click="navigateToFolder(folder.id)"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div
                    class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-blue-100 rounded-lg"
                  >
                    <svg
                      class="h-6 w-6 text-blue-600"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"
                      />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ folder.name }}
                    </div>
                    <div class="text-sm text-gray-500">
                      Folder
                      <span v-if="folder.documents_count !== undefined">
                        · {{ folder.documents_count }} document{{
                          folder.documents_count !== 1 ? 's' : ''
                        }}
                      </span>
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900 max-w-xs truncate">
                  {{ folder.description || '—' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">—</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ folder.creator?.name || 'Unknown' }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ folder.creator?.email }}
                </div>
              </td>
              <td v-if="!isResident" class="px-6 py-4 whitespace-nowrap">
                <label
                  class="relative inline-flex items-center cursor-pointer"
                  @click.stop
                >
                  <input
                    type="checkbox"
                    :checked="folder.visible_to_residents"
                    @change="toggleFolderVisibility(folder)"
                    :disabled="updateFolderMutation.isPending.value"
                    class="sr-only peer"
                  />
                  <div
                    class="w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-primary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 relative transition-colors duration-200 ease-in-out"
                  >
                    <span
                      :class="[
                        'absolute top-0.5 left-0.5 bg-white border border-gray-300 rounded-full h-5 w-5 transition-all duration-200 ease-in-out',
                        folder.visible_to_residents
                          ? 'translate-x-5'
                          : 'translate-x-0',
                      ]"
                    ></span>
                  </div>
                </label>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ formatDate(folder.created_at) }}
                </div>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                @click.stop
              >
                <div class="flex items-center justify-end space-x-2">
                  <button
                    v-if="!isResident"
                    @click="openFolderModal(folder)"
                    class="text-primary hover:text-primary-600"
                    title="Edit"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                      />
                    </svg>
                  </button>
                  <button
                    v-if="!isResident"
                    @click="deleteFolder(folder)"
                    class="text-red-600 hover:text-red-800"
                    title="Delete"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>

            <!-- Documents -->
            <tr
              v-for="document in filteredDocuments"
              :key="`doc-${document.id}`"
              class="hover:bg-gray-50"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div
                    class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-gray-100 rounded-lg"
                  >
                    <svg
                      class="h-6 w-6 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                      />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ document.file_name }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ getFileType(document.mime_type) }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900 max-w-xs truncate">
                  {{ document.description || '—' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">
                  {{ formatFileSize(document.file_size) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ document.uploader?.name || 'Unknown' }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ document.uploader?.email }}
                </div>
              </td>
              <td v-if="!isResident" class="px-6 py-4 whitespace-nowrap">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    :checked="document.visible_to_residents"
                    @change="toggleVisibility(document)"
                    :disabled="isToggling"
                    class="sr-only peer"
                  />
                  <div
                    class="w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-primary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 relative transition-colors duration-200 ease-in-out"
                  >
                    <span
                      :class="[
                        'absolute top-0.5 left-0.5 bg-white border border-gray-300 rounded-full h-5 w-5 transition-all duration-200 ease-in-out',
                        document.visible_to_residents
                          ? 'translate-x-5'
                          : 'translate-x-0',
                      ]"
                    ></span>
                  </div>
                </label>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ formatDate(document.created_at) }}
                </div>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
              >
                <div class="flex items-center justify-end space-x-2">
                  <button
                    @click="downloadDocument(document)"
                    class="text-primary hover:text-primary-600"
                    title="Download"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                      />
                    </svg>
                  </button>
                  <button
                    v-if="!isResident"
                    @click="openMoveModal(document)"
                    class="text-gray-600 hover:text-gray-800"
                    title="Move to Folder"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                      />
                    </svg>
                  </button>
                  <button
                    v-if="!isResident"
                    @click="deleteDocument(document)"
                    class="text-red-600 hover:text-red-800"
                    title="Delete"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Upload Modal -->
    <Transition name="modal">
      <div
        v-if="showUploadModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
        <!-- Overlay -->
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          @click="showUploadModal = false"
        ></div>

        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4">
          <div
            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
            @click.stop
          >
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
              <div class="sm:flex sm:items-start">
                <div
                  class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full"
                >
                  <h3
                    class="text-lg font-semibold leading-6 text-gray-900 mb-4"
                    id="modal-title"
                  >
                    Upload Document
                  </h3>

                  <!-- Drag and Drop Area -->
                  <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Select File
                    </label>
                    <div
                      @drop="handleDrop"
                      @dragover.prevent="handleDragOver"
                      @dragenter.prevent="handleDragEnter"
                      @dragleave.prevent="handleDragLeave"
                      :class="[
                        'border-2 border-dashed rounded-lg p-8 text-center transition-colors duration-200 cursor-pointer',
                        isDragOver
                          ? 'border-primary bg-primary-50'
                          : 'border-gray-300 hover:border-gray-400 bg-gray-50',
                        selectedFile ? 'border-primary bg-primary-50' : '',
                      ]"
                      @click="triggerFileInput"
                    >
                      <input
                        ref="fileInput"
                        type="file"
                        @change="handleFileSelect"
                        class="hidden"
                        accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.xls,.xlsx,.csv,.zip"
                      />

                      <div v-if="!selectedFile">
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
                          <p class="text-sm text-gray-600">
                            <span class="font-medium text-primary"
                              >Click to upload</span
                            >
                            or drag and drop
                          </p>
                          <p class="text-xs text-gray-500 mt-1">
                            PDF, DOC, DOCX, TXT, JPG, PNG, GIF, XLS, XLSX, CSV,
                            ZIP (Max 10MB)
                          </p>
                        </div>
                      </div>

                      <div v-else class="flex flex-col items-center">
                        <svg
                          class="mx-auto h-12 w-12 text-primary"
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
                        <p class="mt-2 text-sm font-medium text-gray-900">
                          {{ selectedFile.name }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                          {{ formatFileSize(selectedFile.size) }}
                        </p>
                        <button
                          @click.stop="selectedFile = null"
                          class="mt-2 text-xs text-red-600 hover:text-red-800"
                        >
                          Remove file
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Description -->
                  <div class="mt-4">
                    <label
                      for="description"
                      class="block text-sm font-medium text-gray-700 mb-2"
                    >
                      Description (Optional)
                    </label>
                    <textarea
                      id="description"
                      v-model="uploadForm.description"
                      rows="3"
                      class="input-field w-full"
                      placeholder="Enter document description..."
                    ></textarea>
                  </div>

                  <!-- Folder Selection -->
                  <div class="mt-4">
                    <label
                      for="upload-folder"
                      class="block text-sm font-medium text-gray-700 mb-2"
                    >
                      Folder (Optional)
                    </label>
                    <select
                      id="upload-folder"
                      v-model="uploadForm.folder_id"
                      class="input-field w-full"
                    >
                      <option :value="null">Root</option>
                      <option
                        v-for="folder in availableFolders"
                        :key="folder.id"
                        :value="folder.id"
                      >
                        {{ folder.name }}
                      </option>
                    </select>
                  </div>

                  <!-- Visible to Residents Toggle -->
                  <div class="mt-4">
                    <label class="flex items-center">
                      <input
                        v-model="uploadForm.visible_to_residents"
                        type="checkbox"
                        class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                      />
                      <span class="ml-2 text-sm text-gray-700">
                        Visible to all residents
                      </span>
                    </label>
                  </div>

                  <!-- Error Message -->
                  <div
                    v-if="uploadError"
                    class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg"
                  >
                    <p class="text-sm text-red-800">{{ uploadError }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div
              class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
            >
              <button
                type="button"
                @click="handleUpload"
                :disabled="!selectedFile || isUploading"
                class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary sm:ml-3 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ isUploading ? 'Uploading...' : 'Upload' }}
              </button>
              <button
                type="button"
                @click="cancelUpload"
                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Delete Confirmation Modal -->
    <ConfirmDialog
      :is-open="showDeleteModal"
      title="Delete Document"
      :message="deleteMessage"
      confirm-text="Delete"
      cancel-text="Cancel"
      type="danger"
      :is-loading="isDeleting"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />

    <!-- Folder Modal -->
    <Transition name="modal">
      <div
        v-if="showFolderModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="folder-modal-title"
        role="dialog"
        aria-modal="true"
      >
        <!-- Overlay -->
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          @click="cancelFolderModal"
        ></div>

        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4">
          <div
            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
            @click.stop
          >
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
              <div class="sm:flex sm:items-start">
                <div
                  class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full"
                >
                  <h3
                    class="text-lg font-semibold leading-6 text-gray-900 mb-4"
                    id="folder-modal-title"
                  >
                    {{ editingFolder ? 'Edit Folder' : 'Create Folder' }}
                  </h3>

                  <!-- Folder Name -->
                  <div class="mt-4">
                    <label
                      for="folder-name"
                      class="block text-sm font-medium text-gray-700 mb-2"
                    >
                      Folder Name
                    </label>
                    <input
                      id="folder-name"
                      v-model="folderForm.name"
                      type="text"
                      class="input-field w-full"
                      placeholder="Enter folder name..."
                    />
                  </div>

                  <!-- Description -->
                  <div class="mt-4">
                    <label
                      for="folder-description"
                      class="block text-sm font-medium text-gray-700 mb-2"
                    >
                      Description (Optional)
                    </label>
                    <textarea
                      id="folder-description"
                      v-model="folderForm.description"
                      rows="3"
                      class="input-field w-full"
                      placeholder="Enter folder description..."
                    ></textarea>
                  </div>

                  <!-- Parent Folder Selection -->
                  <div class="mt-4">
                    <label
                      for="folder-parent"
                      class="block text-sm font-medium text-gray-700 mb-2"
                    >
                      Parent Folder (Optional)
                    </label>
                    <select
                      id="folder-parent"
                      v-model="folderForm.parent_id"
                      class="input-field w-full"
                    >
                      <option :value="null">Root</option>
                      <option
                        v-for="folder in availableFolders"
                        :key="folder.id"
                        :value="folder.id"
                        :disabled="
                          editingFolder && folder.id === editingFolder.id
                        "
                      >
                        {{ folder.name }}
                      </option>
                    </select>
                  </div>

                  <!-- Visible to Residents Toggle -->
                  <div class="mt-4">
                    <label class="flex items-center">
                      <input
                        v-model="folderForm.visible_to_residents"
                        type="checkbox"
                        class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                      />
                      <span class="ml-2 text-sm text-gray-700">
                        Visible to all residents
                      </span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div
              class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
            >
              <button
                type="button"
                @click="handleFolderSave"
                :disabled="
                  !folderForm.name ||
                  createFolderMutation.isPending.value ||
                  updateFolderMutation.isPending.value
                "
                class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary sm:ml-3 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{
                  createFolderMutation.isPending.value ||
                  updateFolderMutation.isPending.value
                    ? 'Saving...'
                    : editingFolder
                      ? 'Update'
                      : 'Create'
                }}
              </button>
              <button
                type="button"
                @click="cancelFolderModal"
                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Folder Delete Confirmation Modal -->
    <ConfirmDialog
      :is-open="showFolderDeleteModal"
      title="Delete Folder"
      :message="folderDeleteMessage"
      confirm-text="Delete"
      cancel-text="Cancel"
      type="danger"
      :is-loading="deleteFolderMutation.isPending.value"
      @confirm="confirmDeleteFolder"
      @cancel="cancelDeleteFolder"
    />

    <!-- Move Document Modal -->
    <Transition name="modal">
      <div
        v-if="showMoveModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="move-modal-title"
        role="dialog"
        aria-modal="true"
      >
        <!-- Overlay -->
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          @click="cancelMove"
        ></div>

        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4">
          <div
            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
            @click.stop
          >
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
              <div class="sm:flex sm:items-start">
                <div
                  class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full"
                >
                  <h3
                    class="text-lg font-semibold leading-6 text-gray-900 mb-4"
                    id="move-modal-title"
                  >
                    Move Document
                  </h3>

                  <p class="text-sm text-gray-500 mb-4">
                    Select a folder to move "{{ documentToMove?.file_name }}"
                    to:
                  </p>

                  <!-- Folder Selection -->
                  <div class="mt-4">
                    <label
                      for="move-folder"
                      class="block text-sm font-medium text-gray-700 mb-2"
                    >
                      Folder
                    </label>
                    <select
                      id="move-folder"
                      v-model="moveFolderId"
                      class="input-field w-full"
                    >
                      <option :value="null">Root</option>
                      <option
                        v-for="folder in availableFolders"
                        :key="folder.id"
                        :value="folder.id"
                      >
                        {{ folder.name }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div
              class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
            >
              <button
                type="button"
                @click="confirmMove"
                :disabled="updateMutation.isPending.value"
                class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary sm:ml-3 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ updateMutation.isPending.value ? 'Moving...' : 'Move' }}
              </button>
              <button
                type="button"
                @click="cancelMove"
                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import {
  documentsApi,
  queryKeys,
  type HoaDocument,
  type HoaDocumentFolder,
  type CreateFolderRequest,
  type UpdateFolderRequest,
} from '@neibrpay/api-client';
import { useAuthStore } from '../stores/auth';
import ConfirmDialog from '../components/ConfirmDialog.vue';

const queryClient = useQueryClient();
const authStore = useAuthStore();

// Role check
const isResident = computed(() => authStore.isResident);

// State
const searchQuery = ref('');
const filterVisible = ref(false);
const showUploadModal = ref(false);
const selectedFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const isDragOver = ref(false);
const uploadForm = ref({
  description: '',
  visible_to_residents: false,
  folder_id: null as number | null,
});
const uploadError = ref('');
const isUploading = ref(false);
const showDeleteModal = ref(false);
const documentToDelete = ref<HoaDocument | null>(null);
const currentFolderId = ref<number | null>(null);
const showFolderModal = ref(false);
const showFolderDeleteModal = ref(false);
const folderToDelete = ref<HoaDocumentFolder | null>(null);
const folderForm = ref<CreateFolderRequest>({
  name: '',
  description: '',
  parent_id: null,
  visible_to_residents: false,
});
const editingFolder = ref<HoaDocumentFolder | null>(null);
const showMoveModal = ref(false);
const documentToMove = ref<HoaDocument | null>(null);
const moveFolderId = ref<number | null>(null);

// Queries
const queryParams = computed(() => {
  // Always filter by folder_id to show only documents in current folder
  // null = root level, number = specific folder
  const params: {
    visible_to_residents?: boolean;
    folder_id: number | null;
  } = {
    folder_id: currentFolderId.value, // Always include folder_id (null for root)
  };

  if (isResident.value) {
    params.visible_to_residents = true;
  } else if (filterVisible.value) {
    params.visible_to_residents = true;
  }

  return params;
});

const {
  data: documents,
  isLoading,
  refetch,
} = useQuery({
  queryKey: computed(() =>
    queryKeys.documents.list({
      visible_to_residents: isResident.value
        ? true
        : filterVisible.value || undefined,
      folder_id: currentFolderId.value,
    })
  ),
  queryFn: () => documentsApi.getDocuments(queryParams.value),
  enabled: true,
});

const folderQueryParams = computed(() => {
  // Always filter by parent_id to show only direct child folders
  // null = root folders, number = folders with that parent
  const params: {
    parent_id: number | null;
    visible_to_residents?: boolean;
  } = {
    parent_id: currentFolderId.value, // Always include parent_id (null for root)
  };

  if (isResident.value) {
    params.visible_to_residents = true;
  }

  return params;
});

const {
  data: folders,
  isLoading: isLoadingFolders,
  refetch: refetchFolders,
} = useQuery({
  queryKey: computed(() =>
    queryKeys.documentFolders.list(folderQueryParams.value)
  ),
  queryFn: () => documentsApi.getFolders(folderQueryParams.value),
  enabled: true,
});

// Query for all folders (for dropdowns)
const { data: allFolders } = useQuery({
  queryKey: computed(() => queryKeys.documentFolders.list({ all: true })),
  queryFn: () => documentsApi.getFolders({ all: true }),
  enabled: true,
});

// Watch for filter changes to ensure refetch
watch(
  () => filterVisible.value,
  () => {
    refetch();
    refetchFolders();
  }
);

watch(
  () => currentFolderId.value,
  () => {
    refetch();
    refetchFolders();
  }
);

// Mutations
const uploadMutation = useMutation({
  mutationFn: documentsApi.createDocument,
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.documents.all });
    cancelUpload();
  },
});

const updateMutation = useMutation({
  mutationFn: ({
    id,
    data,
  }: {
    id: number;
    data: {
      visible_to_residents: boolean;
      description?: string;
      folder_id?: number | null;
    };
  }) => documentsApi.updateDocument(id, data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.documents.all });
    queryClient.invalidateQueries({ queryKey: queryKeys.documentFolders.all });
  },
});

const deleteMutation = useMutation({
  mutationFn: documentsApi.deleteDocument,
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.documents.all });
    queryClient.invalidateQueries({ queryKey: queryKeys.documentFolders.all });
    showDeleteModal.value = false;
    documentToDelete.value = null;
  },
});

const createFolderMutation = useMutation({
  mutationFn: documentsApi.createFolder,
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.documentFolders.all });
    cancelFolderModal();
  },
});

const updateFolderMutation = useMutation({
  mutationFn: ({ id, data }: { id: number; data: UpdateFolderRequest }) =>
    documentsApi.updateFolder(id, data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.documentFolders.all });
    cancelFolderModal();
  },
});

const deleteFolderMutation = useMutation({
  mutationFn: documentsApi.deleteFolder,
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.documentFolders.all });
    showFolderDeleteModal.value = false;
    folderToDelete.value = null;
  },
  onError: (error: any) => {
    alert(error.response?.data?.message || 'Failed to delete folder');
  },
});

// Computed
const filteredDocuments = computed(() => {
  let filtered = documents.value || [];

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(
      (doc: HoaDocument) =>
        doc.file_name.toLowerCase().includes(query) ||
        (doc.description && doc.description.toLowerCase().includes(query))
    );
  }

  return filtered;
});

const filteredFolders = computed(() => {
  let filtered = folders.value || [];

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(
      (folder: HoaDocumentFolder) =>
        folder.name.toLowerCase().includes(query) ||
        (folder.description && folder.description.toLowerCase().includes(query))
    );
  }

  return filtered;
});

const breadcrumbPath = computed(() => {
  // For now, we'll just show the current folder name
  // In a full implementation, you'd fetch parent folders recursively
  if (currentFolderId.value === null) {
    return [{ id: null, name: 'Root' }];
  }
  // TODO: Implement full breadcrumb by fetching folder hierarchy
  return [
    { id: null, name: 'Root' },
    { id: currentFolderId.value, name: 'Current Folder' },
  ];
});

const availableFolders = computed(() => {
  // Get all folders for the move/select dropdowns
  // Exclude the folder being edited to prevent circular references
  const all = allFolders.value || [];
  if (editingFolder.value) {
    return all.filter(f => f.id !== editingFolder.value!.id);
  }
  return all;
});

const isToggling = computed(() => updateMutation.isPending.value);
const isDeleting = computed(() => deleteMutation.isPending.value);

const deleteMessage = computed(() => {
  if (!documentToDelete.value) return '';
  return `Are you sure you want to delete "${documentToDelete.value.file_name}"? This action cannot be undone.`;
});

const folderDeleteMessage = computed(() => {
  if (!folderToDelete.value) return '';
  return `Are you sure you want to delete the folder "${folderToDelete.value.name}"? This action cannot be undone.`;
});

// Methods
const triggerFileInput = () => {
  if (!isUploading.value) {
    fileInput.value?.click();
  }
};

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    const file = target.files[0];
    validateAndSetFile(file);
  }
};

const handleDragOver = (event: DragEvent) => {
  event.preventDefault();
  isDragOver.value = true;
};

const handleDragEnter = (event: DragEvent) => {
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
    // Only take the first file
    const file = files[0];
    validateAndSetFile(file);
  }
};

const validateAndSetFile = (file: File) => {
  // Validate file size (10MB max)
  const maxSize = 10 * 1024 * 1024; // 10MB
  if (file.size > maxSize) {
    uploadError.value = 'File size must be less than 10MB';
    return;
  }

  // Validate file type
  const allowedExtensions = [
    '.pdf',
    '.doc',
    '.docx',
    '.txt',
    '.jpg',
    '.jpeg',
    '.png',
    '.gif',
    '.xls',
    '.xlsx',
    '.csv',
    '.zip',
  ];

  const fileName = file.name.toLowerCase();
  const hasValidExtension = allowedExtensions.some(ext =>
    fileName.endsWith(ext)
  );

  if (!hasValidExtension) {
    uploadError.value =
      'File type not supported. Please upload PDF, DOC, DOCX, TXT, JPG, PNG, GIF, XLS, XLSX, CSV, or ZIP files.';
    return;
  }

  // File is valid
  selectedFile.value = file;
  uploadError.value = '';
};

const handleUpload = async () => {
  if (!selectedFile.value) return;

  uploadError.value = '';
  isUploading.value = true;

  try {
    await uploadMutation.mutateAsync({
      file: selectedFile.value,
      description: uploadForm.value.description || undefined,
      visible_to_residents: uploadForm.value.visible_to_residents,
      folder_id: uploadForm.value.folder_id,
    });
  } catch (error: any) {
    uploadError.value = error.message || 'Failed to upload document';
    console.error('Upload error:', error);
  } finally {
    isUploading.value = false;
  }
};

const cancelUpload = () => {
  showUploadModal.value = false;
  selectedFile.value = null;
  isDragOver.value = false;
  uploadForm.value = {
    description: '',
    visible_to_residents: false,
    folder_id: currentFolderId.value,
  };
  uploadError.value = '';
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const toggleVisibility = async (doc: HoaDocument) => {
  try {
    await updateMutation.mutateAsync({
      id: doc.id,
      data: {
        visible_to_residents: !doc.visible_to_residents,
      },
    });
  } catch (error: any) {
    console.error('Failed to update visibility:', error);
    alert('Failed to update document visibility');
  }
};

const downloadDocument = async (doc: HoaDocument) => {
  try {
    const blob = await documentsApi.downloadDocument(doc.id);
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = doc.file_name;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  } catch (error: any) {
    console.error('Failed to download document:', error);
    alert('Failed to download document');
  }
};

const deleteDocument = (doc: HoaDocument) => {
  documentToDelete.value = doc;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!documentToDelete.value) return;

  try {
    await deleteMutation.mutateAsync(documentToDelete.value.id);
  } catch (error: any) {
    console.error('Failed to delete document:', error);
    alert('Failed to delete document');
  }
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  documentToDelete.value = null;
};

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
};

const getFileType = (mimeType: string): string => {
  const types: Record<string, string> = {
    'application/pdf': 'PDF',
    'application/msword': 'DOC',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
      'DOCX',
    'text/plain': 'TXT',
    'image/jpeg': 'JPEG',
    'image/png': 'PNG',
    'image/gif': 'GIF',
    'application/vnd.ms-excel': 'XLS',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'XLSX',
    'text/csv': 'CSV',
    'application/zip': 'ZIP',
  };
  return types[mimeType] || 'File';
};

const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

// Folder methods
const navigateToFolder = (folderId: number | null) => {
  currentFolderId.value = folderId;
};

const openFolderModal = (folder?: HoaDocumentFolder) => {
  if (folder) {
    editingFolder.value = folder;
    folderForm.value = {
      name: folder.name,
      description: folder.description || '',
      parent_id: folder.parent_id,
      visible_to_residents: folder.visible_to_residents,
    };
  } else {
    editingFolder.value = null;
    folderForm.value = {
      name: '',
      description: '',
      parent_id: currentFolderId.value,
      visible_to_residents: false,
    };
  }
  showFolderModal.value = true;
};

const cancelFolderModal = () => {
  showFolderModal.value = false;
  editingFolder.value = null;
  folderForm.value = {
    name: '',
    description: '',
    parent_id: null,
    visible_to_residents: false,
  };
};

const handleFolderSave = async () => {
  try {
    if (editingFolder.value) {
      await updateFolderMutation.mutateAsync({
        id: editingFolder.value.id,
        data: folderForm.value,
      });
    } else {
      await createFolderMutation.mutateAsync(folderForm.value);
    }
  } catch (error: any) {
    console.error('Failed to save folder:', error);
    alert(error.response?.data?.message || 'Failed to save folder');
  }
};

const deleteFolder = (folder: HoaDocumentFolder) => {
  folderToDelete.value = folder;
  showFolderDeleteModal.value = true;
};

const confirmDeleteFolder = async () => {
  if (!folderToDelete.value) return;

  try {
    await deleteFolderMutation.mutateAsync(folderToDelete.value.id);
  } catch (error: any) {
    console.error('Failed to delete folder:', error);
    // Error is already handled in mutation onError
  }
};

const cancelDeleteFolder = () => {
  showFolderDeleteModal.value = false;
  folderToDelete.value = null;
};

const toggleFolderVisibility = async (folder: HoaDocumentFolder) => {
  try {
    await updateFolderMutation.mutateAsync({
      id: folder.id,
      data: {
        visible_to_residents: !folder.visible_to_residents,
      },
    });
  } catch (error: any) {
    console.error('Failed to update folder visibility:', error);
    alert('Failed to update folder visibility');
  }
};

const openMoveModal = (doc: HoaDocument) => {
  documentToMove.value = doc;
  moveFolderId.value = doc.folder_id;
  showMoveModal.value = true;
};

const cancelMove = () => {
  showMoveModal.value = false;
  documentToMove.value = null;
  moveFolderId.value = null;
};

const confirmMove = async () => {
  if (!documentToMove.value) return;

  try {
    await updateMutation.mutateAsync({
      id: documentToMove.value.id,
      data: {
        folder_id: moveFolderId.value,
      },
    });
    cancelMove();
  } catch (error: any) {
    console.error('Failed to move document:', error);
    alert('Failed to move document');
  }
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
}
</style>
