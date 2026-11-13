import React from 'react';
import { TrendingUp, TrendingDown, Minus } from 'lucide-react';
import { KpiSparklineChart } from './charts/DashboardCharts';

/**
 * StatCard Component (design.md operational metric card + optional Chart.js sparkline).
 * Backward compatible with legacy callers while supporting delta badges and real-data sparklines.
 */
export default function StatCard({
  label,
  value,
  icon,
  tone = 'primary',
  subtext,
  delta,
  deltaLabel,
  deltaTone = 'neutral',
  sparkline,
  sparklineLabels,
  sparklineUnit = '',
  loading = false,
  className = ''
}) {
  const toneStyles = {
    primary: {
      badge: 'bg-primary/10 text-primary dark:bg-red-950/50 dark:text-red-300',
      stroke: '#d71920',
      fill: 'rgba(215, 25, 32, 0.14)'
    },
    secondary: {
      badge: 'bg-secondary/10 text-secondary dark:bg-slate-800 dark:text-slate-200',
      stroke: '#173e67',
      fill: 'rgba(23, 62, 103, 0.14)'
    },
    tertiary: {
      badge: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300',
      stroke: '#26734d',
      fill: 'rgba(38, 115, 77, 0.14)'
    },
    info: {
      badge: 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300',
      stroke: '#2563eb',
      fill: 'rgba(37, 99, 235, 0.14)'
    },
    warning: {
      badge: 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300',
      stroke: '#d97706',
      fill: 'rgba(217, 119, 6, 0.16)'
    },
    neutral: {
      badge: 'bg-surface-low text-muted dark:bg-slate-800 dark:text-slate-300',
      stroke: '#666d75',
      fill: 'rgba(102, 109, 117, 0.14)'
    }
  };

  const deltaPillStyles = {
    positive: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
    negative: 'bg-red-50 text-red-700 dark:bg-red-950/60 dark:text-red-300',
    neutral: 'bg-surface-low text-muted dark:bg-slate-800 dark:text-slate-300'
  };

  const activeTone = toneStyles[tone] || toneStyles.primary;
  const hasSparkline = Array.isArray(sparkline) && sparkline.length > 0;

  return (
    <div
      className={`dls-card-surface relative flex flex-col justify-between overflow-hidden rounded-xl p-5 sm:p-6 transition-shadow hover:shadow-md ${className}`}
    >
      <div className="flex items-start justify-between gap-3">
        <div className="min-w-0 flex-1">
          <p className="truncate text-xs font-semibold uppercase tracking-[0.05em] text-muted">
            {label}
          </p>
          {loading ? (
            <div className="mt-2.5 h-8 w-24 animate-pulse rounded-md bg-surface-container" />
          ) : (
            <div className="mt-2 flex flex-wrap items-baseline gap-2.5">
              <p className="font-mono text-2xl font-bold tracking-tight text-on-surface sm:text-[1.75rem]">
                {value}
              </p>
              {delta !== undefined && delta !== null && (
                <span
                  className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold ${
                    deltaPillStyles[deltaTone] || deltaPillStyles.neutral
                  }`}
                >
                  {deltaTone === 'positive' ? (
                    <TrendingUp className="h-3 w-3" />
                  ) : deltaTone === 'negative' ? (
                    <TrendingDown className="h-3 w-3" />
                  ) : (
                    <Minus className="h-3 w-3" />
                  )}
                  <span>{delta}</span>
                </span>
              )}
            </div>
          )}
        </div>

        {icon && (
          <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${activeTone.badge}`}>
            {typeof icon === 'string' ? (
              <span className="material-symbol text-[20px]">{icon}</span>
            ) : (
              icon
            )}
          </div>
        )}
      </div>

      {hasSparkline && !loading && (
        <div className="mt-3 -mx-1">
          <KpiSparklineChart
            values={sparkline}
            labels={sparklineLabels}
            color={activeTone.stroke}
            fill={activeTone.fill}
            unit={sparklineUnit}
          />
        </div>
      )}

      {(subtext || deltaLabel) && !loading && (
        <div className="mt-2.5 flex items-center justify-between gap-2 border-t border-outline/60 pt-2.5 text-xs text-muted">
          <span className="truncate">{subtext}</span>
          {deltaLabel && <span className="shrink-0 font-medium text-on-surface/80">{deltaLabel}</span>}
        </div>
      )}
    </div>
  );
}
