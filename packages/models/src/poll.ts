export enum PollStatus {
  DRAFT = 'draft',
  OPEN = 'open',
  CLOSED = 'closed',
}

export enum PollQuestionType {
  SINGLE_CHOICE = 'single_choice',
  MULTI_SELECT = 'multi_select',
  YES_NO = 'yes_no',
}

export enum PollResultsVisibility {
  AFTER_CLOSE = 'after_close',
  LIVE = 'live',
  ADMINS_ONLY = 'admins_only',
}

export enum PollRecipientType {
  ALL_UNITS = 'all_units',
  UNIT = 'unit',
}

export interface PollOption {
  id: number;
  label: string;
  sort_order: number;
}

export interface PollQuestion {
  id: number;
  prompt: string;
  type: PollQuestionType;
  sort_order: number;
  options: PollOption[];
}

export interface PollRecipient {
  id: number;
  recipient_type: PollRecipientType;
  recipient_id: number | null;
  unit?: {
    id: number;
    title: string;
    address: string;
  } | null;
}

export interface PollOptionResult {
  id: number;
  label: string;
  votes: number;
  percentage: number;
}

export interface PollQuestionResults {
  question_id: number;
  prompt: string;
  type: PollQuestionType;
  /**
   * Total selections made on this question. For multi-select this can exceed
   * the number of units that voted, since one unit may pick several options.
   */
  total_votes: number;
  /** Number of units that answered this question at all. */
  units_answered: number;
  options: PollOptionResult[];
}

export interface PollResults {
  questions: PollQuestionResults[];
}

/**
 * One row of the participation roster. Deliberately carries no answer data —
 * the API never joins a unit to the options it picked.
 */
export interface PollParticipant {
  unit_id: number;
  unit_title: string;
  unit_address: string;
  owner_names: string[];
  has_voted: boolean;
  responded_at: string | null;
}

export interface Poll {
  id: number;
  tenant_id: number;
  title: string;
  description: string | null;
  status: PollStatus;
  opens_at: string | null;
  closes_at: string | null;
  results_visibility: PollResultsVisibility;
  anonymous_responses: boolean;
  created_by: number;
  created_at: string;
  updated_at: string;
  creator?: {
    id: number;
    name: string;
    email: string;
  } | null;
  questions: PollQuestion[];
  recipients: PollRecipient[];
  target_unit_count: number;
  responded_unit_count: number;
  // Present on the detail endpoint only
  results?: PollResults;
  participation?: PollParticipant[];
}

/**
 * A poll as a resident sees it, with their unit's voting state attached.
 */
export interface ResidentPoll {
  id: number;
  title: string;
  description: string | null;
  status: PollStatus;
  opens_at: string | null;
  closes_at: string | null;
  results_visibility: PollResultsVisibility;
  unit_id: number | null;
  questions: PollQuestion[];
  has_voted: boolean;
  voted_at: string | null;
  voted_by_me: boolean;
  voted_by_name: string | null;
  can_vote: boolean;
  results_visible: boolean;
  results: PollResults | null;
  target_unit_count: number;
  responded_unit_count: number;
}

export interface PollQuestionDto {
  prompt: string;
  type: PollQuestionType;
  options: Array<{ label: string }>;
}

export interface CreatePollDto {
  title: string;
  description?: string | null;
  status: PollStatus.DRAFT | PollStatus.OPEN;
  opens_at?: string | null;
  closes_at?: string | null;
  results_visibility: PollResultsVisibility;
  questions: PollQuestionDto[];
  recipients: Array<{
    recipient_type: PollRecipientType;
    recipient_id?: number | null;
  }>;
}

export type UpdatePollDto = CreatePollDto;

export interface PollFilters {
  status?: PollStatus | 'all';
}

export interface PollListResponse {
  data: Poll[];
  meta: {
    total: number;
    status?: string | null;
    open_count: number;
    unit_count: number;
  };
}

export interface PollResponsePayload {
  data: Poll;
  message?: string;
}

export interface ResidentPollListResponse {
  data: ResidentPoll[];
  meta: {
    total: number;
    open_count: number;
  };
}

export interface PollAnswerDto {
  question_id: number;
  option_ids: number[];
}

export interface CastVoteDto {
  answers: PollAnswerDto[];
  unit_id?: number | null;
}

const STATUS_LABELS: Record<PollStatus, string> = {
  [PollStatus.DRAFT]: 'Draft',
  [PollStatus.OPEN]: 'Open',
  [PollStatus.CLOSED]: 'Closed',
};

const QUESTION_TYPE_LABELS: Record<PollQuestionType, string> = {
  [PollQuestionType.SINGLE_CHOICE]: 'Single choice',
  [PollQuestionType.MULTI_SELECT]: 'Multiple choice',
  [PollQuestionType.YES_NO]: 'Yes / No',
};

export function getPollStatusLabel(status: PollStatus): string {
  return STATUS_LABELS[status] || status;
}

export function getQuestionTypeLabel(type: PollQuestionType): string {
  return QUESTION_TYPE_LABELS[type] || type;
}

/**
 * Whether a question accepts more than one selected option.
 */
export function allowsMultipleAnswers(type: PollQuestionType): boolean {
  return type === PollQuestionType.MULTI_SELECT;
}

/**
 * Summarize the questions the way the list rows show them — a single question
 * describes itself ("Single choice · 3 options"), several are just counted.
 */
export function formatQuestionSummary(questions: PollQuestion[]): string {
  if (!questions || questions.length === 0) {
    return 'No questions';
  }

  if (questions.length > 1) {
    return `${questions.length} questions`;
  }

  const question = questions[0];

  if (question.type === PollQuestionType.YES_NO) {
    return 'Yes / No';
  }

  return `${getQuestionTypeLabel(question.type)} · ${question.options.length} options`;
}

/**
 * Describe who a poll targets, e.g. "all units" or "12 units".
 */
export function formatAudienceSummary(poll: {
  recipients: PollRecipient[];
  target_unit_count: number;
}): string {
  const targetsAll = poll.recipients.some(
    r => r.recipient_type === PollRecipientType.ALL_UNITS
  );

  if (targetsAll) {
    return 'all units';
  }

  const count = poll.target_unit_count;
  return `${count} unit${count === 1 ? '' : 's'}`;
}

/**
 * Participation as a whole percentage, guarding against a zero-unit audience.
 */
export function participationPercentage(poll: {
  target_unit_count: number;
  responded_unit_count: number;
}): number {
  if (!poll.target_unit_count) {
    return 0;
  }

  return Math.round((poll.responded_unit_count / poll.target_unit_count) * 100);
}
