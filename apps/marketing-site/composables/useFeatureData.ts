export interface Feature {
  title: string;
  slug: string;
  shortDescription: string;
  heroDescription: string;
  benefits: string[];
  illustrationType: 'screenshot' | 'svg';
  illustrationSrc: string;
  relatedFeatures: string[];
  metaDescription: string;
}

const features: Feature[] = [
  {
    title: 'Invoice & Payment Management',
    slug: 'invoice-payment-management',
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
      'financial-reporting',
      'owner-portal',
    ],
    metaDescription:
      'Automate HOA dues collection with online payments via Zelle, ACH, and card. Send invoices, track payments, and reduce late fees with NeibrPay.',
  },
  {
    title: 'Vendor & Expense Tracking',
    slug: 'vendor-expense-tracking',
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
      'financial-reporting',
      'document-storage',
    ],
    metaDescription:
      'Track HOA vendor payments and expenses with receipt attachments. Organize vendor relationships and generate expense reports with NeibrPay.',
  },
  {
    title: 'Document Storage',
    slug: 'document-storage',
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
      'vendor-expense-tracking',
    ],
    metaDescription:
      'Centralized HOA document management for bylaws, meeting minutes, and financial records. Secure, organized, and always accessible with NeibrPay.',
  },
  {
    title: 'Owner Portal',
    slug: 'owner-portal',
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
  },
  {
    title: 'Multi-tenant Architecture',
    slug: 'multi-tenant-architecture',
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
      'financial-reporting',
      'document-storage',
    ],
    metaDescription:
      'Enterprise-grade security for HOA data with isolated multi-tenant architecture, bank-grade encryption, and PCI-compliant payment processing.',
  },
  {
    title: 'Financial Reporting',
    slug: 'financial-reporting',
    shortDescription:
      "See your HOA's financial health at a glance with real-time dashboards and reports.",
    heroDescription:
      'Make informed financial decisions with comprehensive dashboards and reports. Track income vs. expenses, monitor budgets, and generate board-ready financial statements — all in real time.',
    benefits: [
      'Real-time dashboard with income, expenses, and budget tracking',
      'Forecast vs. actual budget comparisons with monthly breakdowns',
      'Running balance charts for clear financial visibility',
      'Export reports as PDF or Excel for board meetings and audits',
      'Track financial health trends across months and years',
    ],
    illustrationType: 'screenshot',
    illustrationSrc: '/images/features/financial-reporting.png',
    relatedFeatures: [
      'invoice-payment-management',
      'vendor-expense-tracking',
      'document-storage',
    ],
    metaDescription:
      'Real-time HOA financial reporting with budget tracking, income vs. expense dashboards, and exportable reports. Make informed decisions with NeibrPay.',
  },
  {
    title: 'Announcements & Communication',
    slug: 'announcements-communication',
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
    relatedFeatures: [
      'owner-portal',
      'document-storage',
      'invoice-payment-management',
    ],
    metaDescription:
      'HOA community announcements via email and push notifications. Keep residents informed and connected with NeibrPay communication tools.',
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

  return {
    features,
    getFeatureBySlug,
    getRelatedFeatures,
  };
}
