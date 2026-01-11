import { apiClient } from './apiClient';

export interface HoaDocument {
  id: number;
  tenant_id: number;
  folder_id: number | null;
  file_name: string;
  file_path: string;
  file_hash: string;
  file_size: number;
  mime_type: string;
  description: string | null;
  visible_to_residents: boolean;
  uploaded_by: number;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  uploader?: {
    id: number;
    name: string;
    email: string;
  };
  folder?: {
    id: number;
    name: string;
  };
  file_url?: string;
  file_size_human?: string;
}

export interface HoaDocumentFolder {
  id: number;
  tenant_id: number;
  name: string;
  description: string | null;
  parent_id: number | null;
  visible_to_residents: boolean;
  created_by: number;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  creator?: {
    id: number;
    name: string;
    email: string;
  };
  documents_count?: number;
  children_count?: number;
}

export interface CreateDocumentRequest {
  file: File;
  description?: string;
  visible_to_residents?: boolean;
  folder_id?: number | null;
}

export interface UpdateDocumentRequest {
  description?: string;
  visible_to_residents?: boolean;
  folder_id?: number | null;
}

export interface CreateFolderRequest {
  name: string;
  description?: string;
  parent_id?: number | null;
  visible_to_residents?: boolean;
}

export interface UpdateFolderRequest {
  name?: string;
  description?: string;
  parent_id?: number | null;
  visible_to_residents?: boolean;
}

export interface FolderListResponse {
  data: HoaDocumentFolder[];
  meta: {
    total: number;
  };
}

export interface FolderResponse {
  data: HoaDocumentFolder;
}

export interface DocumentListResponse {
  data: HoaDocument[];
  meta: {
    total: number;
  };
}

export interface DocumentResponse {
  data: HoaDocument;
}

export const documentsApi = {
  /**
   * Get all HOA documents with optional filters
   */
  async getDocuments(params?: {
    visible_to_residents?: boolean;
    folder_id?: number | null;
  }): Promise<HoaDocument[]> {
    const response = await apiClient.get('/documents', { params });
    return response.data.data;
  },

  /**
   * Get a specific document by ID
   */
  async getDocument(id: number): Promise<HoaDocument> {
    const response = await apiClient.get(`/documents/${id}`);
    return response.data.data;
  },

  /**
   * Upload a new document
   */
  async createDocument(data: CreateDocumentRequest): Promise<HoaDocument> {
    const formData = new FormData();
    formData.append('file', data.file);
    if (data.description) {
      formData.append('description', data.description);
    }
    // Send as "1" or "0" for Laravel boolean validation
    formData.append(
      'visible_to_residents',
      data.visible_to_residents ? '1' : '0'
    );
    if (data.folder_id !== undefined) {
      formData.append('folder_id', data.folder_id?.toString() || '');
    }

    const response = await apiClient.post('/documents', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data.data;
  },

  /**
   * Update an existing document
   */
  async updateDocument(
    id: number,
    data: UpdateDocumentRequest
  ): Promise<HoaDocument> {
    const response = await apiClient.put(`/documents/${id}`, data);
    return response.data.data;
  },

  /**
   * Delete a document (soft delete)
   */
  async deleteDocument(id: number): Promise<{ message: string }> {
    const response = await apiClient.delete(`/documents/${id}`);
    return response.data;
  },

  /**
   * Permanently delete a document
   */
  async forceDeleteDocument(id: number): Promise<{ message: string }> {
    const response = await apiClient.delete(`/documents/${id}/force`);
    return response.data;
  },

  /**
   * Download a document
   */
  async downloadDocument(id: number): Promise<Blob> {
    const response = await apiClient.get(`/documents/${id}/download`, {
      responseType: 'blob',
    });
    return response.data;
  },

  /**
   * Get all document folders with optional filters
   */
  async getFolders(params?: {
    parent_id?: number | null;
    visible_to_residents?: boolean;
    all?: boolean; // If true, return all folders regardless of parent_id
  }): Promise<HoaDocumentFolder[]> {
    const response = await apiClient.get('/document-folders', { params });
    return response.data.data;
  },

  /**
   * Get a specific folder by ID
   */
  async getFolder(id: number): Promise<HoaDocumentFolder> {
    const response = await apiClient.get(`/document-folders/${id}`);
    return response.data.data;
  },

  /**
   * Create a new folder
   */
  async createFolder(data: CreateFolderRequest): Promise<HoaDocumentFolder> {
    const response = await apiClient.post('/document-folders', data);
    return response.data.data;
  },

  /**
   * Update an existing folder
   */
  async updateFolder(
    id: number,
    data: UpdateFolderRequest
  ): Promise<HoaDocumentFolder> {
    const response = await apiClient.put(`/document-folders/${id}`, data);
    return response.data.data;
  },

  /**
   * Delete a folder
   */
  async deleteFolder(id: number): Promise<{ message: string }> {
    const response = await apiClient.delete(`/document-folders/${id}`);
    return response.data;
  },
};
