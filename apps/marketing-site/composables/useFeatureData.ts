export type FeatureCategory =
  | 'financial'
  | 'management'
  | 'communications'
  | 'platform';

export interface FeatureFaq {
  question: string;
  answer: string;
}

export interface FeatureTestimonial {
  quote: string;
  attribution: string;
}

export interface Feature {
  title: string;
  slug: string;
  category: FeatureCategory;
  shortDescription: string;
  heroDescription: string;
  benefits: string[];
  illustrationType: 'screenshot' | 'svg';
  illustrationSrc: string;
  relatedFeatures: string[];
  metaDescription: string;
  testimonial: FeatureTestimonial;
  faqs: FeatureFaq[];
}

const features: Feature[] = [
  {
    title: 'Invoice & Payment Management',
    slug: 'invoice-payment-management',
    category: 'financial',
    shortDescription:
      'Stop chasing late payments with automated reminders and online payment collection via Zelle, ACH, or card.',
    heroDescription:
      'Automate your entire dues collection process. Create invoices, send automated reminders, and let residents pay online through Zelle, ACH, or card — powered by Stripe. No more chasing checks or tracking spreadsheets.',
    benefits: [
      'Create and send professional invoices in minutes',
      'Automated payment reminders reduce late payments by up to 60%',
      'Accept Zelle, ACH, and card payments online — powered by Stripe',
      'Track partial payments, late fees, and payment history automatically',
      'Real-time dashboard shows open, overdue, and paid invoice status',
    ],
    illustrationType: 'screenshot',
    illustrationSrc: '/images/features/invoice-management.png',
    relatedFeatures: [
      'vendor-expense-tracking',
      'budgets-and-reports',
      'owner-portal',
    ],
    metaDescription:
      'Automate HOA dues collection with online payments via Zelle, ACH, and card. Send invoices, track payments, and reduce late fees with NeibrPay.',
    testimonial: {
      quote:
        'Payments went from "whenever someone remembers" to automatic. Late payments dropped within the first month.',
      attribution: 'HOA Treasurer, 28-unit condo association',
    },
    faqs: [
      {
        question: 'How do residents pay their dues?',
        answer:
          'Residents can pay online via Zelle, ACH bank transfer, or credit/debit card. Card and ACH are processed through Stripe, and bank account linking uses Plaid for security. No app download required — everything runs in the browser.',
      },
      {
        question: 'Can I set up recurring invoices?',
        answer:
          'Yes. You can create recurring monthly, quarterly, or annual dues schedules that invoice every unit automatically on the schedule you choose. You can also create one-off invoices for special assessments or move-in fees.',
      },
      {
        question: 'Does NeibrPay handle late fees automatically?',
        answer:
          'Yes. Configure a late-fee policy once (flat fee, percentage, or compounding) and NeibrPay applies it automatically when invoices pass their due date. You can also send automated past-due reminders.',
      },
      {
        question: 'What are the payment processing fees?',
        answer:
          'Standard Stripe fees apply for card and ACH payments (e.g. 2.9% + 30¢ for cards, 0.8% capped for ACH). Zelle transfers are free. You can choose whether the HOA absorbs fees or passes them to the paying resident.',
      },
      {
        question: 'Can residents set up autopay?',
        answer:
          'Yes. Residents can save a payment method and enable autopay from their owner portal. They get email confirmations on every charge and can turn autopay off at any time.',
      },
    ],
  },
  {
    title: 'Vendor & Expense Tracking',
    slug: 'vendor-expense-tracking',
    category: 'financial',
    shortDescription:
      'Know exactly where every dollar goes with receipt tracking and vendor management.',
    heroDescription:
      'Keep your HOA finances transparent and organized. Track every vendor payment, attach receipts, and generate expense reports that make board meetings a breeze.',
    benefits: [
      'Manage all vendor relationships and contracts in one place',
      'Attach receipts and supporting documents to every expense',
      'Categorize expenses for clear financial reporting',
      'Track unpaid and paid expenses with real-time status updates',
      'Export expense data for tax preparation and audits',
    ],
    illustrationType: 'screenshot',
    illustrationSrc: '/images/features/vendor-expenses.png',
    relatedFeatures: [
      'invoice-payment-management',
      'budgets-and-reports',
      'document-storage',
    ],
    metaDescription:
      'Track HOA vendor payments and expenses with receipt attachments. Organize vendor relationships and generate expense reports with NeibrPay.',
    testimonial: {
      quote:
        'Every expense has a receipt attached. When auditors asked questions, I answered them in minutes instead of digging through email.',
      attribution: 'Board Treasurer, 42-unit townhome HOA',
    },
    faqs: [
      {
        question: 'Can I attach receipts to expenses?',
        answer:
          'Yes. Drag and drop receipts (PDF, JPG, PNG) onto any expense entry. Receipts are stored securely and remain attached to the transaction forever, which is invaluable at audit time.',
      },
      {
        question: 'Can I store vendor contracts and insurance certificates?',
        answer:
          'Yes. Each vendor profile includes a document area where you can upload W-9s, insurance certificates, signed contracts, and any other paperwork you need to keep on file.',
      },
      {
        question: 'How are expenses categorized?',
        answer:
          'Use the built-in chart of accounts out of the box, or customize expense categories (landscaping, maintenance, utilities, insurance, etc.) to match your budget. Every expense is coded to a category for clean reporting.',
      },
      {
        question: 'Can I pay vendors directly through NeibrPay?',
        answer:
          'You can record vendor payments made outside the platform, or schedule vendor payments by ACH through the expense workflow. Every payment is matched to the vendor and expense for a clean audit trail.',
      },
    ],
  },
  {
    title: 'Budgets & Reports',
    slug: 'budgets-and-reports',
    category: 'financial',
    shortDescription:
      "See your HOA's financial health at a glance with real-time dashboards, annual budgets, and board-ready reports.",
    heroDescription:
      'Build your annual budget, track forecast vs. actual every month, and generate professional financial reports your board and auditors will love — all in real time.',
    benefits: [
      'Build an annual operating budget by category in minutes',
      'Forecast vs. actual budget comparisons with monthly breakdowns',
      'Real-time dashboard with income, expenses, and running balance',
      'Export P&L, balance sheet, and AR aging as PDF or Excel',
      'Choose cash or accrual accounting to match your needs',
      'Track financial health trends across months and years',
    ],
    illustrationType: 'screenshot',
    illustrationSrc: '/images/features/financial-reporting.png',
    relatedFeatures: [
      'invoice-payment-management',
      'vendor-expense-tracking',
      'bank-reconciliation',
    ],
    metaDescription:
      'Build HOA annual budgets and generate professional financial reports. Track forecast vs. actual, export PDF/Excel, and get audit-ready with NeibrPay.',
    testimonial: {
      quote:
        'I used to spend a weekend before every board meeting building reports in Excel. Now I hit export and show up.',
      attribution: 'HOA Treasurer, 35-unit community',
    },
    faqs: [
      {
        question: 'Can I build an annual budget in NeibrPay?',
        answer:
          'Yes. Create a budget by expense category for the fiscal year, and NeibrPay automatically compares actual spending against your budget every month. You can roll budgets forward year over year.',
      },
      {
        question: 'What reports are included?',
        answer:
          'Profit & loss, balance sheet, accounts receivable aging, income vs. expense by category, budget vs. actual, and a running-balance chart. Every report exports to PDF or Excel.',
      },
      {
        question: 'Does NeibrPay support cash or accrual accounting?',
        answer:
          'Both. You can switch between cash and accrual views on most reports so the books match how your board prefers to see the numbers.',
      },
      {
        question:
          'Can I share reports with the board without giving them logins?',
        answer:
          'Yes. Export any report as a PDF packet with a cover sheet and share it by email, or publish board-facing reports directly to the owner portal for board members.',
      },
      {
        question: 'Can auditors or accountants export the data?',
        answer:
          'Yes. All transactions, invoices, payments, and expenses export to Excel or CSV. Your accountant can reconcile and file without ever logging into NeibrPay.',
      },
    ],
  },
  {
    title: 'Bank Reconciliation',
    slug: 'bank-reconciliation',
    category: 'financial',
    shortDescription:
      'Close the books every month with automated bank feeds that match payments and expenses against real bank statements.',
    heroDescription:
      "Stop exporting CSVs and hand-matching transactions in a spreadsheet. NeibrPay connects your HOA's bank account through Plaid, imports transactions automatically, and matches them against invoices and expenses so you can reconcile in minutes, not hours.",
    benefits: [
      'Connect your bank securely through Plaid — 12,000+ institutions supported',
      'Auto-import deposits, withdrawals, and fees daily',
      'Automatic matching of bank transactions to NeibrPay invoices and expenses',
      'Flag discrepancies and unmatched transactions for review',
      'Month-end close checklist with running reconciled balance',
      'Keep the Operating and Reserve fund accounts reconciled separately',
    ],
    illustrationType: 'svg',
    illustrationSrc: 'SecurityIllustration',
    relatedFeatures: [
      'budgets-and-reports',
      'reserve-fund-management',
      'vendor-expense-tracking',
    ],
    metaDescription:
      'Automated HOA bank reconciliation via Plaid. Match bank transactions to NeibrPay invoices and expenses, flag discrepancies, and close the books monthly.',
    testimonial: {
      quote:
        'Month-end close used to take an entire Sunday. Now it takes 20 minutes and I trust the numbers more.',
      attribution: 'Board Treasurer, 22-unit condo HOA',
    },
    faqs: [
      {
        question: 'How does the bank connection work?',
        answer:
          'NeibrPay uses Plaid to connect to your HOA bank account. You log in to your bank through a secure Plaid popup; NeibrPay never sees or stores your bank credentials. Plaid provides read-only access to transactions.',
      },
      {
        question: 'How is matching done?',
        answer:
          'Incoming deposits are matched to open invoices by amount, date, and payer. Outgoing transactions are matched to recorded expenses by vendor and amount. Anything that cannot be matched automatically is flagged for your review.',
      },
      {
        question: 'What happens if a transaction does not match?',
        answer:
          'Unmatched transactions appear in a reconciliation queue. You can match them to an existing record, create a new invoice or expense on the spot, or mark them as a transfer (e.g. between Operating and Reserve accounts).',
      },
      {
        question: 'Can I reconcile multiple bank accounts?',
        answer:
          'Yes. Connect your Operating account, Reserve account, and any other HOA accounts. Each account has its own reconciliation queue and running balance.',
      },
      {
        question: 'Is the bank connection secure?',
        answer:
          'Yes. Plaid is used by major financial apps (Venmo, Robinhood, Acorns) and is SOC 2 Type II certified. NeibrPay only receives read-only transaction data — no one can move money through this connection.',
      },
    ],
  },
  {
    title: 'Reserve Fund Management',
    slug: 'reserve-fund-management',
    category: 'financial',
    shortDescription:
      'Keep the Operating Fund and Reserve Fund separate — the way HOA accounting is supposed to work.',
    heroDescription:
      'Separating your Operating Fund from your Reserve Fund is a core HOA compliance requirement, and conflating the two is one of the most common audit findings. NeibrPay gives each fund its own ledger, its own reconciled bank account, and a clear reserve balance on your financial dashboard.',
    benefits: [
      'Separate ledgers for Operating Fund and Reserve Fund',
      'Track reserve contributions automatically from monthly dues',
      'Record transfers between funds with a full audit trail',
      'Reserve fund balance visible on the financial dashboard',
      'Report on reserve funding levels vs. your reserve study',
      'Keep major-repair savings protected and accounted for',
    ],
    illustrationType: 'svg',
    illustrationSrc: 'SecurityIllustration',
    relatedFeatures: [
      'budgets-and-reports',
      'bank-reconciliation',
      'invoice-payment-management',
    ],
    metaDescription:
      'Separate Operating and Reserve Fund ledgers for HOA compliance. Track reserve contributions, transfers, and fund balances with NeibrPay.',
    testimonial: {
      quote:
        'Our CPA finally stopped lecturing us about commingled funds. The two ledgers are always clean.',
      attribution: 'Board President, 48-unit community',
    },
    faqs: [
      {
        question: 'Why separate Operating and Reserve Funds?',
        answer:
          'Most state HOA statutes and governing documents require reserves for major repairs (roofs, paving, siding) to be tracked separately from operating cash. Mixing the two is a common audit finding and a compliance risk.',
      },
      {
        question: 'How do reserve contributions get recorded?',
        answer:
          'Configure what portion of each monthly assessment is a reserve contribution (e.g. $25 of a $250 due goes to reserves). NeibrPay automatically books that portion to the Reserve Fund ledger as payments come in.',
      },
      {
        question: 'Can I transfer money between funds?',
        answer:
          'Yes. Record a transfer between the Operating and Reserve ledgers with a reason and supporting document. Every transfer leaves a full audit trail and shows up on both fund statements.',
      },
      {
        question: 'Can I tie the reserve balance to a reserve study?',
        answer:
          'Yes. Enter your reserve study targets by component, and the dashboard shows current reserve balance vs. funded target so the board always knows where you stand.',
      },
    ],
  },
  {
    title: 'Violations',
    slug: 'violations',
    category: 'management',
    shortDescription:
      'Enforce CC&Rs fairly and consistently with templated violation notices, photo evidence, and status tracking.',
    heroDescription:
      'Stop writing violation letters from scratch every time. Create templates, attach photos, escalate through custom statuses, and track the entire history on each home — so your board enforces CC&Rs consistently and defensibly.',
    benefits: [
      'Send unlimited violation notices with photo evidence',
      'Save and reuse unlimited custom violation templates',
      'Custom statuses (first notice, second notice, hearing, resolved)',
      'Add and track fines directly on the violation record',
      'Full violation history stored on every home',
      'Professional violation reports for committee and board review',
    ],
    illustrationType: 'svg',
    illustrationSrc: 'DocumentIllustration',
    relatedFeatures: [
      'architectural-requests',
      'announcements-communication',
      'owner-portal',
    ],
    metaDescription:
      'HOA violation management with photo evidence, templates, custom statuses, and fine tracking. Enforce CC&Rs consistently with NeibrPay.',
    testimonial: {
      quote:
        'We went from scribbled notes and angry emails to a clean, documented process. Enforcement is fair and the board is protected.',
      attribution: 'Board President, 40-unit townhome HOA',
    },
    faqs: [
      {
        question: 'Can I attach photos to a violation?',
        answer:
          'Yes. Upload one or more photos directly on the violation record. Photos are stored as evidence and are visible on the homeowner-facing copy of the notice.',
      },
      {
        question: 'Can I add fines to a violation?',
        answer:
          'Yes. Add a fine when you create the violation, or assess a fine later after a hearing. Fines flow to the homeowner account as a charge and appear on their next statement.',
      },
      {
        question: 'Can I create violation templates?',
        answer:
          'Yes. Save any violation letter as a reusable template. Changing the status of a violation (e.g. from "First Notice" to "Second Notice") can automatically trigger the matching template.',
      },
      {
        question: 'Is there a history of violations on each home?',
        answer:
          "Yes. Every home has a full violation history visible to board administrators. When a home changes ownership, new owners do not see the previous owner's violations.",
      },
      {
        question: 'Can we pull reports on violations?',
        answer:
          'Yes. Filter violations by unit, status, type, created date, or past-due fines and export the results for committee review or annual reporting.',
      },
    ],
  },
  {
    title: 'Architectural & Request Forms',
    slug: 'architectural-requests',
    category: 'management',
    shortDescription:
      'Replace paper ARC forms with online request tickets, approval workflows, and a clean audit trail.',
    heroDescription:
      'Get architectural change requests, maintenance requests, and general homeowner requests out of your inbox. Build custom forms, route them to the right committee, and track every decision — all in one place.',
    benefits: [
      'Custom request form builder — ask any questions, require attachments',
      'Route requests to the right committee or board member automatically',
      'Approval workflows with comments and decisions on every request',
      'Threaded communication keeps each conversation on its own ticket',
      'Custom statuses to track progress through your review process',
      'Full reporting dashboard — admins are notified on every change',
    ],
    illustrationType: 'svg',
    illustrationSrc: 'DocumentIllustration',
    relatedFeatures: ['violations', 'document-storage', 'owner-portal'],
    metaDescription:
      'Online HOA architectural (ARC) and request forms with approval workflows, attachments, and status tracking. Keep requests out of your inbox with NeibrPay.',
    testimonial: {
      quote:
        "ARC requests used to get lost in five people's inboxes. Now everything lives on one ticket with a clear decision.",
      attribution: 'ARC Committee Chair, 60-unit community',
    },
    faqs: [
      {
        question: 'Can homeowners attach plans or photos to requests?',
        answer:
          'Yes. Residents can upload drawings, plans, photos, contractor bids, or any other supporting documents directly on the request ticket.',
      },
      {
        question: 'Can I build custom request forms?',
        answer:
          'Yes. The form builder supports text, dropdown, multi-select, date, and file-upload questions. Build a form once and homeowners can submit it from their portal forever.',
      },
      {
        question: 'Is there an approval workflow?',
        answer:
          'Yes. Assign approvers to each request type. Approvers receive an email, can comment, and approve or deny directly on the ticket. All decisions are logged.',
      },
      {
        question: 'Can committee members see only the requests they need?',
        answer:
          'Yes. Role-based permissions let you restrict a committee to only the request types they review, so landscaping requests do not appear in front of the ARC, and vice versa.',
      },
      {
        question: 'Is the request history stored on the home?',
        answer:
          'Yes. Every home keeps a full history of requests and decisions, which is invaluable years later when a new owner asks whether a previous approval is on file.',
      },
    ],
  },
  {
    title: 'Online Voting & Polls',
    slug: 'online-voting-polls',
    category: 'management',
    shortDescription:
      'Run board elections, budget approvals, and community polls online — with quorum tracking and a live tally.',
    heroDescription:
      'Many HOAs are legally required to hold votes for elections, bylaw amendments, and budget approvals. NeibrPay lets you create a vote or poll, invite every unit, track quorum in real time, and publish results — without tabulating a single paper ballot.',
    benefits: [
      'Create ballots for elections, bylaw amendments, or simple yes/no polls',
      'Vote per owner or per household to match your governing documents',
      'Weighted voting for communities with unequal ownership interests',
      'Real-time quorum and tally tracking as ballots come in',
      'Optional anonymous polling for sensitive topics',
      'Publish certified results to the owner portal when voting closes',
    ],
    illustrationType: 'svg',
    illustrationSrc: 'PortalIllustration',
    relatedFeatures: [
      'meeting-management',
      'announcements-communication',
      'owner-portal',
    ],
    metaDescription:
      'Run HOA board elections, bylaw amendments, and community polls online. Track quorum, weighted votes, and publish results with NeibrPay.',
    testimonial: {
      quote:
        "We hit quorum in 48 hours instead of chasing proxies for a month. The election was the smoothest we've ever had.",
      attribution: 'Board Secretary, 55-unit HOA',
    },
    faqs: [
      {
        question: 'Can votes be anonymous?',
        answer:
          'Yes. Anonymous polling is a per-vote option. The tally is recorded without storing who voted which way — useful for board elections or sensitive bylaw changes.',
      },
      {
        question: 'How is quorum tracked?',
        answer:
          'Set your quorum rule once (e.g. 25% of units, or 51% of ownership interest). The voting dashboard shows live participation and tells you the moment quorum is met.',
      },
      {
        question: 'Does NeibrPay support weighted voting?',
        answer:
          'Yes. Apply different voting weights by unit (e.g. by square footage or percentage ownership), and the tally is calculated automatically using those weights.',
      },
      {
        question: 'Can residents vote from their phone?',
        answer:
          'Yes. Residents receive an email with a secure link, log in to their owner portal, and cast their vote from any phone or computer. No app download is required.',
      },
      {
        question: 'What about owners who cannot vote online?',
        answer:
          'You can record paper or proxy ballots on the vote manually, and they are tallied alongside online votes. Every vote has a timestamp and source for a clean audit trail.',
      },
    ],
  },
  {
    title: 'Meeting Management',
    slug: 'meeting-management',
    category: 'management',
    shortDescription:
      'Schedule board meetings, publish agendas, and store minutes — all linked to your community records.',
    heroDescription:
      'Give meetings the structure they deserve. Schedule with calendar invites, build agendas from templates, record decisions during the meeting, and publish approved minutes to the owner portal automatically.',
    benefits: [
      'Schedule board and annual meetings with calendar invites for every owner',
      'Build agendas from reusable templates or from scratch',
      'Attach supporting documents (budgets, proposals, bids) to agenda items',
      'Record motions, votes, and decisions directly in the minutes editor',
      'Publish approved minutes to the owner portal automatically',
      'Searchable archive of every past meeting and its minutes',
    ],
    illustrationType: 'svg',
    illustrationSrc: 'DocumentIllustration',
    relatedFeatures: [
      'online-voting-polls',
      'document-storage',
      'announcements-communication',
    ],
    metaDescription:
      'HOA meeting management with calendar invites, agenda builder, minutes editor, and automatic publishing to the owner portal. Run better meetings with NeibrPay.',
    testimonial: {
      quote:
        'Agendas go out ahead of time, minutes are published within a day, and nobody is asking "what did we decide in March?" anymore.',
      attribution: 'Board Secretary, 30-unit community',
    },
    faqs: [
      {
        question: 'Can I send calendar invites to all owners?',
        answer:
          'Yes. When you schedule a meeting, NeibrPay sends an email with a calendar (.ics) attachment that works in Google Calendar, Apple Calendar, and Outlook.',
      },
      {
        question: 'Can agenda items be linked to votes?',
        answer:
          'Yes. Attach an online vote or poll to an agenda item (e.g. "Approve 2026 budget") so the decision is captured alongside the meeting record.',
      },
      {
        question: 'Who can view meeting minutes?',
        answer:
          'You choose. Publish minutes to the full owner portal, to board members only, or keep them as a draft until approved at the next meeting.',
      },
      {
        question: 'Can I attach documents to an agenda?',
        answer:
          'Yes. Attach budgets, vendor bids, or any other supporting documents to agenda items. Owners see the same packet the board does when it is published.',
      },
      {
        question: 'Is there an archive of past meetings?',
        answer:
          'Yes. Every past meeting, its agenda, and its approved minutes live in a searchable archive so the next treasurer or president always has full context.',
      },
    ],
  },
  {
    title: 'Events Calendar',
    slug: 'events-calendar',
    category: 'communications',
    shortDescription:
      'Schedule, publish, and remind residents of community events — from pool parties to annual meetings.',
    heroDescription:
      'Keep your community connected with a shared events calendar. Publish events to the owner portal, send email and push reminders, and collect RSVPs so you know how many residents are coming.',
    benefits: [
      'Publish community events to every owner portal',
      'Email and push-notification reminders before each event',
      'Optional RSVP tracking with a headcount',
      'Attach flyers, maps, or menus to any event',
      'Recurring events for regular meetings or community gatherings',
      'Exportable calendar feed (.ics) owners can subscribe to',
    ],
    illustrationType: 'svg',
    illustrationSrc: 'AnnouncementIllustration',
    relatedFeatures: [
      'announcements-communication',
      'meeting-management',
      'owner-portal',
    ],
    metaDescription:
      'HOA community events calendar with RSVPs, reminders, and subscribable .ics feed. Keep residents informed of events with NeibrPay.',
    testimonial: {
      quote:
        "Pool party attendance doubled the first year we moved reminders into NeibrPay. Residents love that it's on their phone calendar.",
      attribution: 'Social Committee Chair, 65-unit community',
    },
    faqs: [
      {
        question: 'Can residents RSVP to events?',
        answer:
          'Yes. Enable RSVPs on any event and residents can respond Yes / No / Maybe from the owner portal. You get a live headcount so you can plan catering, chairs, or supplies.',
      },
      {
        question: 'How are event reminders delivered?',
        answer:
          'Residents receive email reminders (and push notifications if they have opted in) at intervals you set — typically one week, one day, and one hour before the event.',
      },
      {
        question: 'Can I attach a flyer or map to an event?',
        answer:
          'Yes. Upload a PDF or image to any event. Residents see the attachment alongside the event details in their portal.',
      },
      {
        question: 'Can residents add events to their own calendar?',
        answer:
          'Yes. Every event has a one-click "Add to calendar" option, and residents can subscribe to a personal feed so new events appear automatically in Google Calendar, Apple Calendar, or Outlook.',
      },
    ],
  },
  {
    title: 'Document Storage',
    slug: 'document-storage',
    category: 'management',
    shortDescription:
      'Find any HOA document in seconds — organized, searchable, always accessible.',
    heroDescription:
      'Centralize all your HOA documents in one secure, organized location. From governing documents to meeting minutes, everything is easy to find for board members and residents alike.',
    benefits: [
      'Centralized storage for bylaws, meeting minutes, and financial records',
      'Organize documents by unit, category, or date',
      'Secure access controls — board-only vs. resident-visible',
      'Upload and share documents with drag-and-drop simplicity',
      'Residents access their unit-specific documents through the owner portal',
    ],
    illustrationType: 'svg',
    illustrationSrc: 'DocumentIllustration',
    relatedFeatures: [
      'owner-portal',
      'announcements-communication',
      'meeting-management',
    ],
    metaDescription:
      'Centralized HOA document management for bylaws, meeting minutes, and financial records. Secure, organized, and always accessible with NeibrPay.',
    testimonial: {
      quote:
        'New board members are productive on day one because everything they need is already organized in one place.',
      attribution: 'Board President, 45-unit HOA',
    },
    faqs: [
      {
        question: 'What types of files can I upload?',
        answer:
          'PDF, Word, Excel, images (JPG, PNG), and most other common file types. Use it for bylaws, CC&Rs, meeting minutes, insurance certificates, reserve studies — anything your community relies on.',
      },
      {
        question: 'Is there a storage limit?',
        answer:
          'No hard limit. Document storage is included in every plan and we do not meter usage for typical HOA workloads.',
      },
      {
        question: 'Can I control who sees each document?',
        answer:
          'Yes. Every folder and document can be marked board-only, resident-visible, or restricted to a specific group. Share exactly what each audience needs and nothing more.',
      },
      {
        question: 'Can residents see unit-specific documents?',
        answer:
          'Yes. Attach documents to a specific unit (e.g. architectural approvals, move-in paperwork) and only the current owner of that unit sees them in their portal.',
      },
    ],
  },
  {
    title: 'Owner Portal',
    slug: 'owner-portal',
    category: 'management',
    shortDescription:
      'Residents pay on time and stay informed while your board spends less time on tickets.',
    heroDescription:
      'Give residents a self-service portal where they can view invoices, make payments, access documents, and stay up to date with community announcements — reducing the load on your board.',
    benefits: [
      'Residents view and pay invoices online via Zelle, ACH, or card',
      'Access community documents and unit-specific files anytime',
      'View announcements and community updates in one place',
      'Payment history and receipt downloads for personal records',
      'Reduce board workload by empowering residents with self-service',
    ],
    illustrationType: 'svg',
    illustrationSrc: 'PortalIllustration',
    relatedFeatures: [
      'invoice-payment-management',
      'document-storage',
      'announcements-communication',
    ],
    metaDescription:
      'Self-service HOA owner portal for online payments, document access, and community updates. Empower residents and reduce board workload with NeibrPay.',
    testimonial: {
      quote:
        'Resident questions to the board dropped by 70% once the portal was live. People answer themselves now.',
      attribution: 'Board Treasurer, 50-unit community',
    },
    faqs: [
      {
        question: 'Do residents need to download an app?',
        answer:
          'No. The owner portal runs in any modern browser on any phone or computer. It also installs as a progressive web app on iPhone and Android for one-tap access.',
      },
      {
        question: 'What can residents do in the portal?',
        answer:
          'View their account balance and payment history, pay invoices, set up autopay, read announcements, download community documents, submit requests, vote in polls, and RSVP to events.',
      },
      {
        question: 'Can multiple owners share a unit?',
        answer:
          'Yes. Add as many owners as needed to a single unit. Each owner has their own login and can make payments toward the shared balance.',
      },
      {
        question: 'What does a new owner see after a unit changes hands?',
        answer:
          "A clean account. New owners do not see the previous owner's payment history, violations, or requests — but the board keeps the full history for its own records.",
      },
    ],
  },
  {
    title: 'Announcements & Communication',
    slug: 'announcements-communication',
    category: 'communications',
    shortDescription:
      'Keep every resident in the loop with email and push notification announcements.',
    heroDescription:
      'Communicate effectively with your entire community. Create announcements, send them via email and push notifications, and ensure every resident stays informed about what matters most.',
    benefits: [
      'Send announcements to all residents or targeted groups',
      'Deliver via email and push notifications for maximum reach',
      'Schedule announcements in advance for planned communications',
      'Residents view announcement history in their owner portal',
      'Reduce miscommunication and keep your community connected',
    ],
    illustrationType: 'svg',
    illustrationSrc: 'AnnouncementIllustration',
    relatedFeatures: ['owner-portal', 'events-calendar', 'meeting-management'],
    metaDescription:
      'HOA community announcements via email and push notifications. Keep residents informed and connected with NeibrPay communication tools.',
    testimonial: {
      quote:
        'One announcement reaches every owner by email and phone. No more "nobody told me" at the next meeting.',
      attribution: 'Board President, 38-unit community',
    },
    faqs: [
      {
        question: 'How are announcements delivered?',
        answer:
          'Every announcement is sent by email and as a push notification to owners who have enabled them. A copy also lives in the owner portal indefinitely, so residents can always scroll back.',
      },
      {
        question: 'Can I schedule announcements?',
        answer:
          'Yes. Write an announcement today and schedule it to go out at a specific date and time — useful for holiday reminders or pre-meeting nudges.',
      },
      {
        question: 'Can I target specific units or groups?',
        answer:
          'Yes. Send to the entire community, to a single building or section, to a specific unit, or to custom groups you define (e.g. pool access, parking permit holders).',
      },
      {
        question: 'Can I attach documents to an announcement?',
        answer:
          'Yes. Attach PDFs, flyers, or images to any announcement. Attachments are available in the email and in the portal copy.',
      },
    ],
  },
  {
    title: 'Multi-tenant Architecture',
    slug: 'multi-tenant-architecture',
    category: 'platform',
    shortDescription:
      'Your data stays yours — enterprise-grade isolation for every community.',
    heroDescription:
      'Built from the ground up for security and scale. Every HOA community gets fully isolated data, ensuring your financial information and resident data are protected with enterprise-grade infrastructure.',
    benefits: [
      'Complete data isolation between communities — no cross-contamination',
      'Bank-grade encryption for all data at rest and in transit',
      'Secure authentication with role-based access controls',
      'Payment processing through PCI-compliant Stripe infrastructure',
      'Bank verification through Plaid for secure account linking',
    ],
    illustrationType: 'svg',
    illustrationSrc: 'SecurityIllustration',
    relatedFeatures: [
      'invoice-payment-management',
      'budgets-and-reports',
      'document-storage',
    ],
    metaDescription:
      'Enterprise-grade security for HOA data with isolated multi-tenant architecture, bank-grade encryption, and PCI-compliant payment processing.',
    testimonial: {
      quote:
        "Knowing our community's financial data is fully isolated and PCI-compliant was the reason we picked NeibrPay over a spreadsheet.",
      attribution: 'Board Treasurer, 44-unit condo association',
    },
    faqs: [
      {
        question: "How is my community's data kept separate from others?",
        answer:
          'Every HOA is a separate tenant with isolated data at the application and database layer. Users from one community cannot read, list, or reference data from another community.',
      },
      {
        question: 'Is payment data secure?',
        answer:
          'Yes. All card and ACH payments are processed by Stripe, which is Level 1 PCI-DSS certified. NeibrPay never stores full card numbers.',
      },
      {
        question: 'How is bank account linking secured?',
        answer:
          'Bank accounts are linked through Plaid, which is SOC 2 Type II certified. NeibrPay only receives read-only access tokens — your bank credentials are never exposed to us.',
      },
      {
        question: 'Is data encrypted?',
        answer:
          'Yes. All data is encrypted in transit (TLS 1.2+) and at rest. Database backups are encrypted using industry-standard AES-256.',
      },
    ],
  },
];

export function useFeatureData() {
  const getFeatureBySlug = (slug: string): Feature | undefined => {
    return features.find(f => f.slug === slug);
  };

  const getRelatedFeatures = (slugs: string[]): Feature[] => {
    return slugs
      .map(slug => features.find(f => f.slug === slug))
      .filter((f): f is Feature => f !== undefined);
  };

  const getFeaturesByCategory = (category: FeatureCategory): Feature[] => {
    return features.filter(f => f.category === category);
  };

  return {
    features,
    getFeatureBySlug,
    getRelatedFeatures,
    getFeaturesByCategory,
  };
}
