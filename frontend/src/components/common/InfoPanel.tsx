import { useState, type ReactNode } from 'react';
import {
  Info,
  Lightbulb,
  BookOpen,
  AlertCircle,
  CheckCircle2,
  ExternalLink,
  ChevronDown,
  ChevronUp,
} from 'lucide-react';

type InfoPanelVariant = 'info' | 'tip' | 'guide' | 'warning' | 'success';

interface InfoPanelProps {
  variant?: InfoPanelVariant;
  title: string;
  children: ReactNode;
  collapsible?: boolean;
  defaultOpen?: boolean;
  learnMoreUrl?: string;
  className?: string;
}

const variantConfig = {
  info: {
    icon: Info,
    bgColor: 'bg-blue-50',
    borderColor: 'border-blue-200',
    iconColor: 'text-blue-500',
    titleColor: 'text-blue-800',
    textColor: 'text-blue-700',
  },
  tip: {
    icon: Lightbulb,
    bgColor: 'bg-amber-50',
    borderColor: 'border-amber-200',
    iconColor: 'text-amber-500',
    titleColor: 'text-amber-800',
    textColor: 'text-amber-700',
  },
  guide: {
    icon: BookOpen,
    bgColor: 'bg-purple-50',
    borderColor: 'border-purple-200',
    iconColor: 'text-purple-500',
    titleColor: 'text-purple-800',
    textColor: 'text-purple-700',
  },
  warning: {
    icon: AlertCircle,
    bgColor: 'bg-orange-50',
    borderColor: 'border-orange-200',
    iconColor: 'text-orange-500',
    titleColor: 'text-orange-800',
    textColor: 'text-orange-700',
  },
  success: {
    icon: CheckCircle2,
    bgColor: 'bg-green-50',
    borderColor: 'border-green-200',
    iconColor: 'text-green-500',
    titleColor: 'text-green-800',
    textColor: 'text-green-700',
  },
};

export function InfoPanel({
  variant = 'info',
  title,
  children,
  collapsible = false,
  defaultOpen = true,
  learnMoreUrl,
  className = '',
}: InfoPanelProps) {
  const [isOpen, setIsOpen] = useState(defaultOpen);
  const config = variantConfig[variant];
  const Icon = config.icon;

  return (
    <div
      className={`rounded-lg border ${config.bgColor} ${config.borderColor} ${className}`}
    >
      <div
        className={`flex items-start gap-3 p-4 ${
          collapsible ? 'cursor-pointer select-none' : ''
        }`}
        onClick={() => collapsible && setIsOpen(!isOpen)}
      >
        <Icon className={`w-5 h-5 ${config.iconColor} flex-shrink-0 mt-0.5`} />
        <div className="flex-1 min-w-0">
          <div className="flex items-center justify-between">
            <h4 className={`font-medium ${config.titleColor}`}>{title}</h4>
            {collapsible && (
              <span className={config.iconColor}>
                {isOpen ? (
                  <ChevronUp className="w-4 h-4" />
                ) : (
                  <ChevronDown className="w-4 h-4" />
                )}
              </span>
            )}
          </div>
          {(!collapsible || isOpen) && (
            <div className={`mt-1 text-sm ${config.textColor}`}>
              {children}
              {learnMoreUrl && (
                <a
                  href={learnMoreUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex items-center gap-1 mt-2 font-medium hover:underline"
                  onClick={(e) => e.stopPropagation()}
                >
                  Learn more
                  <ExternalLink className="w-3 h-3" />
                </a>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

// Collapsible banner (Peanut Suite style)
type BannerVariant = 'info' | 'warning' | 'success' | 'amber';

interface CollapsibleBannerProps {
  variant?: BannerVariant;
  title: string;
  icon?: ReactNode;
  children: ReactNode;
  defaultOpen?: boolean;
  dismissible?: boolean;
  onDismiss?: () => void;
  className?: string;
}

const bannerVariantConfig = {
  info: {
    bgColor: 'bg-blue-50',
    borderColor: 'border-l-blue-400',
    titleColor: 'text-blue-700',
    textColor: 'text-blue-600',
    iconColor: 'text-blue-500',
  },
  warning: {
    bgColor: 'bg-orange-50',
    borderColor: 'border-l-orange-400',
    titleColor: 'text-orange-700',
    textColor: 'text-orange-600',
    iconColor: 'text-orange-500',
  },
  success: {
    bgColor: 'bg-green-50',
    borderColor: 'border-l-green-400',
    titleColor: 'text-green-700',
    textColor: 'text-green-600',
    iconColor: 'text-green-500',
  },
  amber: {
    bgColor: 'bg-amber-50',
    borderColor: 'border-l-amber-400',
    titleColor: 'text-amber-700',
    textColor: 'text-amber-600',
    iconColor: 'text-amber-500',
  },
};

export function CollapsibleBanner({
  variant = 'amber',
  title,
  icon,
  children,
  defaultOpen = true,
  dismissible = false,
  onDismiss,
  className = '',
}: CollapsibleBannerProps) {
  const [isOpen, setIsOpen] = useState(defaultOpen);
  const [isDismissed, setIsDismissed] = useState(false);
  const config = bannerVariantConfig[variant];

  if (isDismissed) return null;

  const handleDismiss = () => {
    setIsDismissed(true);
    onDismiss?.();
  };

  return (
    <div
      className={`rounded-lg border-l-4 ${config.bgColor} ${config.borderColor} ${className}`}
    >
      <div
        className="flex items-center justify-between px-4 py-3 cursor-pointer select-none"
        onClick={() => setIsOpen(!isOpen)}
      >
        <div className="flex items-center gap-2">
          {icon && <span className={config.iconColor}>{icon}</span>}
          <span className={`font-medium ${config.titleColor}`}>{title}</span>
        </div>
        <div className="flex items-center gap-2">
          {dismissible && (
            <button
              onClick={(e) => {
                e.stopPropagation();
                handleDismiss();
              }}
              className={`${config.iconColor} hover:opacity-70 p-1`}
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          )}
          {!dismissible && (
            <span className={config.iconColor}>
              {isOpen ? (
                <ChevronUp className="w-4 h-4" />
              ) : (
                <ChevronDown className="w-4 h-4" />
              )}
            </span>
          )}
        </div>
      </div>
      {isOpen && (
        <div className={`px-4 pb-4 text-sm ${config.textColor}`}>
          {children}
        </div>
      )}
    </div>
  );
}
