export enum RecipientType {
  ALL_MEMBERS = 'all_members',
  ALL_ADMINS = 'all_admins',
  UNIT = 'unit',
  RESIDENT = 'resident',
}

export interface AnnouncementRecipient {
  id: number;
  announcement_id: number;
  recipient_type: RecipientType;
  recipient_id: number | null;
  created_at: string;
  updated_at: string;
  unit?: {
    id: number;
    title: string;
    address: string;
  };
  resident?: {
    id: number;
    name: string;
    email: string;
  };
}

export interface Announcement {
  id: number;
  tenant_id: number;
  subject: string;
  message: string;
  removal_date: string | null;
  created_by: number;
  created_at: string;
  updated_at: string;
  deleted_at?: string | null;
  creator?: {
    id: number;
    name: string;
    email: string;
  };
  recipients?: AnnouncementRecipient[];
}

export interface CreateAnnouncementDto {
  subject: string;
  message: string;
  removal_date?: string | null;
  recipients: Array<{
    recipient_type: RecipientType;
    recipient_id?: number | null;
  }>;
}

export interface UpdateAnnouncementDto {
  subject?: string;
  message?: string;
  removal_date?: string | null;
  recipients?: Array<{
    recipient_type: RecipientType;
    recipient_id?: number | null;
  }>;
}

export interface AnnouncementFilters {
  status?: 'active' | 'expired' | 'all';
  include_deleted?: boolean;
}

export interface AnnouncementListResponse {
  data: Announcement[];
  meta: {
    total: number;
    include_deleted?: boolean;
    status?: string;
  };
}

export interface AnnouncementResponse {
  data: Announcement;
  message?: string;
}

export interface UserAnnouncementsResponse {
  data: Announcement[];
  meta: {
    total: number;
  };
}

// Helper function to get recipient type display name
export function getRecipientTypeDisplayName(type: RecipientType): string {
  const displayNames: Record<RecipientType, string> = {
    [RecipientType.ALL_MEMBERS]: 'All Members',
    [RecipientType.ALL_ADMINS]: 'All Admins',
    [RecipientType.UNIT]: 'Unit',
    [RecipientType.RESIDENT]: 'Resident',
  };

  return displayNames[type] || type;
}

// Helper function to format recipient summary
export function formatRecipientSummary(
  recipients: AnnouncementRecipient[]
): string {
  if (!recipients || recipients.length === 0) {
    return 'No recipients';
  }

  const summaries: string[] = [];
  const allMembers = recipients.find(
    r => r.recipient_type === RecipientType.ALL_MEMBERS
  );
  const allAdmins = recipients.find(
    r => r.recipient_type === RecipientType.ALL_ADMINS
  );
  const units = recipients.filter(r => r.recipient_type === RecipientType.UNIT);
  const residents = recipients.filter(
    r => r.recipient_type === RecipientType.RESIDENT
  );

  if (allMembers) {
    summaries.push('All Members');
  }
  if (allAdmins) {
    summaries.push('All Admins');
  }
  if (units.length > 0) {
    summaries.push(`${units.length} Unit${units.length > 1 ? 's' : ''}`);
  }
  if (residents.length > 0) {
    summaries.push(
      `${residents.length} Resident${residents.length > 1 ? 's' : ''}`
    );
  }

  return summaries.join(', ');
}
