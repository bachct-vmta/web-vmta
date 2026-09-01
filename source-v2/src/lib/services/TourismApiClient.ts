import axios, { AxiosInstance } from 'axios';

export interface UpstreamConversation {
  id: string;
  title?: string;
  created_at?: string;
}

export interface DocumentGroup {
  code: string;
  name: string;
}

export interface DocumentItem {
  id: string;
  filename: string;
  group_code: string;
  status: string;
}

export class TourismApiClient {
  private http: AxiosInstance;
  private apiBase: string;
  private tokenCache: { token: string; expiresAt: number } | null = null;
  private adminTokenCache: { token: string; expiresAt: number } | null = null;

  constructor() {
    this.apiBase = (process.env.CHATBOT_API_BASE || 'https://tourrismbotapp.onelink.vn').replace(/\/$/, '');
    this.http = axios.create({
      baseURL: this.apiBase,
      timeout: 30000,
      headers: {
        'Content-Type': 'application/json',
      },
    });
  }

  /** Get Bearer token for user `tichhop` role */
  public async getToken(): Promise<string> {
    const now = Date.now();
    if (this.tokenCache && this.tokenCache.expiresAt > now) {
      return this.tokenCache.token;
    }

    const username = process.env.CHATBOT_USERNAME || 'tichhop';
    const password = process.env.CHATBOT_PASSWORD || '123456AaA@@';

    const response = await this.http.post('/api/auth/login', { username, password });
    const token = response.data.access_token || response.data.token;
    const expiresIn = response.data.expires_in || 28800; // 8 hours default

    this.tokenCache = {
      token,
      expiresAt: now + (expiresIn - 1800) * 1000, // Refresh buffer
    };

    return token;
  }

  /** Get Bearer token for admin role */
  public async getAdminToken(): Promise<string> {
    const now = Date.now();
    if (this.adminTokenCache && this.adminTokenCache.expiresAt > now) {
      return this.adminTokenCache.token;
    }

    const username = process.env.CHATBOT_ADMIN_USERNAME || 'tichhop';
    const password = process.env.CHATBOT_ADMIN_PASSWORD || '123456AaA@@';

    const response = await this.http.post('/api/auth/login', { username, password });
    const token = response.data.access_token || response.data.token;
    const expiresIn = response.data.expires_in || 28800;

    this.adminTokenCache = {
      token,
      expiresAt: now + (expiresIn - 1800) * 1000,
    };

    return token;
  }

  /** Create a new upstream conversation */
  public async createConversation(documentGroup?: string, aiProvider?: string): Promise<string> {
    const token = await this.getToken();
    const payload: Record<string, string> = {};
    if (documentGroup) payload.document_group = documentGroup;
    if (aiProvider) payload.ai_provider = aiProvider;

    const response = await this.http.post('/api/chat/conversations', payload, {
      headers: { Authorization: `Bearer ${token}` },
    });

    return response.data.id || response.data.conversation_id;
  }

  /** Stream message response directly via Fetch ReadableStream */
  public async streamMessage(conversationId: string, content: string): Promise<Response> {
    const token = await this.getToken();
    const url = `${this.apiBase}/api/chat/conversations/${conversationId}/messages/stream`;

    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({ content }),
    });
  }

  /** List user conversations */
  public async listConversations(skip = 0, limit = 30): Promise<UpstreamConversation[]> {
    const token = await this.getToken();
    const response = await this.http.get('/api/chat/conversations', {
      params: { skip, limit },
      headers: { Authorization: `Bearer ${token}` },
    });
    return response.data;
  }

  /** Get conversation details */
  public async getConversation(conversationId: string): Promise<Record<string, unknown>> {
    const token = await this.getToken();
    const response = await this.http.get(`/api/chat/conversations/${conversationId}`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    return response.data;
  }

  /** List document groups (admin) */
  public async listDocumentGroups(): Promise<DocumentGroup[]> {
    const token = await this.getAdminToken();
    const response = await this.http.get('/api/document-groups/', {
      headers: { Authorization: `Bearer ${token}` },
    });
    return response.data;
  }

  /** List documents (admin) */
  public async listDocuments(query?: string): Promise<DocumentItem[]> {
    const token = await this.getAdminToken();
    const response = await this.http.get('/api/documents/', {
      params: query ? { q: query } : {},
      headers: { Authorization: `Bearer ${token}` },
    });
    return response.data;
  }
}

export const tourismApiClient = new TourismApiClient();
