import { apiClient } from './apiClient';

export interface HoaDocument {
  id: number;
  tenant_id: number;
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
  file_url?: string;
  file_size_human?: string;
}

export interface CreateDocumentRequest {
  file: File;
  description?: string;
  visible_to_residents?: boolean;
}

export interface UpdateDocumentRequest {
  description?: string;
  visible_to_residents?: boolean;
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
};
