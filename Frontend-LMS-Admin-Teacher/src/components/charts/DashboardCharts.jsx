import { useEffect, useMemo, useState } from 'react';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Filler,
  Tooltip,
  Legend
} from 'chart.js';
import { Line, Bar, Doughnut } from 'react-chartjs-2';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Filler,
  Tooltip,
  Legend
);

const MATERIAL_TYPE_LABELS = {
  text: 'Teks / Artikel',
  video: 'Video',
  pdf: 'Dokumen PDF',
  document: 'Dokumen Office',
  image: 'Gambar',
  audio: 'Audio',
  link: 'Tautan Web'
};

function useChartTheme() {
  const [isDark, setIsDark] = useState(() =>
    typeof document !== 'undefined' && document.documentElement.classList.contains('dark')
  );

  useEffect(() => {
    if (typeof document === 'undefined') return undefined;
    const sync = () => setIsDark(document.documentElement.classList.contains('dark'));
    window.addEventListener('dls_theme_change', sync);
    const observer = new MutationObserver(sync);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    return () => {
      window.removeEventListener('dls_theme_change', sync);
      observer.disconnect();
    };
  }, []);

  return useMemo(() => ({
    isDark,
    textColor: isDark ? '#94a3b8' : '#666d75',
    strongText: isDark ? '#f1f5f9' : '#25292e',
    gridColor: isDark ? 'rgba(255, 255, 255, 0.07)' : 'rgba(15, 23, 42, 0.06)',
    tooltipBg: isDark ? 'rgba(11, 15, 21, 0.94)' : 'rgba(20, 24, 30, 0.92)',
    cardBorder: isDark ? '#171e29' : '#ffffff',
    primary: '#d71920',
    primaryFill: 'rgba(215, 25, 32, 0.16)',
    primaryBar: 'rgba(215, 25, 32, 0.72)',
    secondary: isDark ? '#60a5fa' : '#173e67',
    secondaryFill: isDark ? 'rgba(96, 165, 250, 0.16)' : 'rgba(23, 62, 103, 0.12)',
    secondaryBar: isDark ? 'rgba(96, 165, 250, 0.72)' : 'rgba(23, 62, 103, 0.75)',
    emerald: '#26734d',
    emeraldFill: 'rgba(38, 115, 77, 0.18)',
    emeraldBar: 'rgba(38, 115, 77, 0.74)',
    amber: '#d97706',
    blue: '#2563eb',
    purple: '#7c3aed',
    slate: isDark ? '#64748b' : '#94a3b8'
  }), [isDark]);
}

function EmptyChartState({ message = 'Belum ada data untuk ditampilkan pada periode ini.' }) {
  return (
    <div className="flex h-full min-h-[160px] items-center justify-center rounded-lg border border-dashed border-outline bg-surface-low/50 px-4 text-center text-xs text-muted">
      {message}
    </div>
  );
}

/**
 * Compact Chart.js sparkline embedded inside KPI cards.
 */
export function KpiSparklineChart({ values = [], labels = [], color = '#d71920', fill = 'rgba(215, 25, 32, 0.14)', unit = '' }) {
  const palette = useChartTheme();
  const safeValues = Array.isArray(values) && values.length > 0 ? values : [0];
  const safeLabels = Array.isArray(labels) && labels.length === safeValues.length
    ? labels
    : safeValues.map((_, i) => `H-${safeValues.length - i - 1}`);

  const data = useMemo(() => ({
    labels: safeLabels,
    datasets: [
      {
        data: safeValues,
        borderColor: color,
        backgroundColor: fill,
        borderWidth: 2,
        pointRadius: 0,
        pointHoverRadius: 4,
        pointHoverBackgroundColor: color,
        tension: 0.32,
        fill: true
      }
    ]
  }), [safeLabels, safeValues, color, fill]);

  const options = useMemo(() => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: palette.tooltipBg,
        titleFont: { size: 11, weight: '600' },
        bodyFont: { size: 11, weight: '700' },
        padding: 8,
        displayColors: false,
        callbacks: {
          label: ctx => `${ctx.parsed.y}${unit ? ` ${unit}` : ''}`
        }
      }
    },
    scales: {
      x: { display: false },
      y: {
        display: false,
        beginAtZero: false,
        grace: '15%'
      }
    }
  }), [palette.tooltipBg, unit]);

  return (
    <div className="h-14 w-full" aria-hidden="true">
      <Line data={data} options={options} />
    </div>
  );
}

/**
 * Multi-series Line/Area chart tracking daily LMS activities & student learning updates.
 */
export function ActivityTrendChart({ trends = [] }) {
  const palette = useChartTheme();

  if (!trends.length) {
    return <EmptyChartState message="Data tren harian belum tersedia." />;
  }

  const data = {
    labels: trends.map(item => item.label),
    datasets: [
      {
        label: 'Update Belajar Student',
        data: trends.map(item => item.studentUpdates),
        borderColor: palette.primary,
        backgroundColor: palette.primaryFill,
        borderWidth: 2.2,
        pointRadius: trends.length > 16 ? 1.5 : 3,
        pointHoverRadius: 5,
        pointBackgroundColor: palette.primary,
        fill: true,
        tension: 0.3
      },
      {
        label: 'Aktivitas Staff & Audit',
        data: trends.map(item => item.activities),
        borderColor: palette.secondary,
        backgroundColor: palette.secondaryFill,
        borderWidth: 2,
        pointRadius: trends.length > 16 ? 1.5 : 3,
        pointHoverRadius: 5,
        pointBackgroundColor: palette.secondary,
        fill: true,
        tension: 0.3
      },
      {
        label: 'Pengajuan Materi',
        data: trends.map(item => item.submissions),
        borderColor: palette.emerald,
        backgroundColor: 'transparent',
        borderWidth: 2,
        borderDash: [4, 4],
        pointRadius: trends.length > 16 ? 0 : 2.5,
        pointHoverRadius: 4,
        tension: 0.25
      }
    ]
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: {
        position: 'top',
        align: 'end',
        labels: {
          color: palette.textColor,
          boxWidth: 10,
          boxHeight: 10,
          usePointStyle: true,
          pointStyle: 'circle',
          font: { size: 11, weight: '600' },
          padding: 14
        }
      },
      tooltip: {
        backgroundColor: palette.tooltipBg,
        padding: 10,
        titleFont: { size: 12, weight: '700' },
        bodyFont: { size: 12 }
      }
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { color: palette.textColor, font: { size: 11 }, maxRotation: 0, autoSkipPadding: 12 }
      },
      y: {
        beginAtZero: true,
        border: { display: false },
        grid: { color: palette.gridColor },
        ticks: { color: palette.textColor, font: { size: 11 }, precision: 0 }
      }
    }
  };

  return <Line data={data} options={options} />;
}

/**
 * Grouped bar chart comparing top active courses by enrolled students, completions, and materials.
 */
export function CoursePerformanceChart({ courses = [] }) {
  const palette = useChartTheme();

  if (!courses.length) {
    return <EmptyChartState message="Belum ada course aktif untuk dibandingkan." />;
  }

  const trimmed = courses.slice(0, 7);
  const data = {
    labels: trimmed.map(c => (c.title.length > 22 ? `${c.title.slice(0, 20)}...` : c.title)),
    datasets: [
      {
        label: 'Total Peserta',
        data: trimmed.map(c => c.student_count),
        backgroundColor: palette.primaryBar,
        borderRadius: 4,
        barPercentage: 0.72,
        categoryPercentage: 0.75
      },
      {
        label: 'Peserta Lulus',
        data: trimmed.map(c => c.completed_count),
        backgroundColor: palette.emeraldBar,
        borderRadius: 4,
        barPercentage: 0.72,
        categoryPercentage: 0.75
      },
      {
        label: 'Materi Terbit',
        data: trimmed.map(c => c.material_count),
        backgroundColor: palette.secondaryBar,
        borderRadius: 4,
        barPercentage: 0.72,
        categoryPercentage: 0.75
      }
    ]
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: {
        position: 'top',
        align: 'end',
        labels: {
          color: palette.textColor,
          boxWidth: 10,
          boxHeight: 10,
          usePointStyle: true,
          font: { size: 11, weight: '600' }
        }
      },
      tooltip: {
        backgroundColor: palette.tooltipBg,
        padding: 10,
        callbacks: {
          title: items => {
            const idx = items[0]?.dataIndex ?? 0;
            const c = trimmed[idx];
            return c ? `${c.title} (${c.course_code})` : '';
          }
        }
      }
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { color: palette.textColor, font: { size: 11 } }
      },
      y: {
        beginAtZero: true,
        border: { display: false },
        grid: { color: palette.gridColor },
        ticks: { color: palette.textColor, font: { size: 11 }, precision: 0 }
      }
    }
  };

  return <Bar data={data} options={options} />;
}

/**
 * Doughnut chart showing Material Moderation Workflow statuses.
 */
export function MaterialPipelineChart({ pipeline = {} }) {
  const palette = useChartTheme();
  const entries = [
    { key: 'published', label: 'Dipublikasi', value: Number(pipeline.published || 0), color: '#26734d' },
    { key: 'approved', label: 'Disetujui', value: Number(pipeline.approved || 0), color: '#7c3aed' },
    { key: 'pending_review', label: 'Ditinjau Reviewer', value: Number(pipeline.pending_review || 0), color: '#2563eb' },
    { key: 'pending_admin', label: 'Antrean Admin', value: Number(pipeline.pending_admin || 0), color: '#d97706' },
    { key: 'rejected', label: 'Perlu Revisi', value: Number(pipeline.rejected || 0), color: '#d71920' }
  ];
  const total = entries.reduce((sum, e) => sum + e.value, 0);

  if (total === 0) {
    return <EmptyChartState message="Belum ada pengajuan materi di alur persetujuan." />;
  }

  const data = {
    labels: entries.map(e => e.label),
    datasets: [
      {
        data: entries.map(e => e.value),
        backgroundColor: entries.map(e => e.color),
        borderColor: palette.cardBorder,
        borderWidth: 2,
        hoverOffset: 4
      }
    ]
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '66%',
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          color: palette.textColor,
          boxWidth: 10,
          boxHeight: 10,
          usePointStyle: true,
          padding: 12,
          font: { size: 11, weight: '600' }
        }
      },
      tooltip: {
        backgroundColor: palette.tooltipBg,
        padding: 10,
        callbacks: {
          label: ctx => ` ${ctx.label}: ${ctx.parsed} materi (${Math.round((ctx.parsed / total) * 100)}%)`
        }
      }
    }
  };

  return <Doughnut data={data} options={options} />;
}

/**
 * Horizontal Bar chart of Student Progress distribution buckets + Material format breakdown.
 */
export function ProgressBucketChart({ buckets = [] }) {
  const palette = useChartTheme();
  const total = buckets.reduce((sum, b) => sum + Number(b.count || 0), 0);

  if (!buckets.length || total === 0) {
    return <EmptyChartState message="Belum ada data progres belajar peserta." />;
  }

  const data = {
    labels: buckets.map(b => b.label),
    datasets: [
      {
        label: 'Jumlah Enrollment',
        data: buckets.map(b => Number(b.count || 0)),
        backgroundColor: [
          'rgba(215, 25, 32, 0.55)',
          'rgba(217, 119, 6, 0.65)',
          palette.secondaryBar,
          palette.emeraldBar
        ],
        borderRadius: 6,
        barThickness: 22
      }
    ]
  };

  const options = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: palette.tooltipBg,
        padding: 10,
        callbacks: {
          label: ctx => ` ${ctx.parsed.x} peserta (${Math.round((ctx.parsed.x / total) * 100)}%)`
        }
      }
    },
    scales: {
      x: {
        beginAtZero: true,
        border: { display: false },
        grid: { color: palette.gridColor },
        ticks: { color: palette.textColor, font: { size: 11 }, precision: 0 }
      },
      y: {
        grid: { display: false },
        ticks: { color: palette.strongText, font: { size: 12, weight: '600' } }
      }
    }
  };

  return <Bar data={data} options={options} />;
}

/**
 * Compact bar chart for published course material formats.
 */
export function MaterialTypeChart({ materialTypes = [] }) {
  const palette = useChartTheme();
  const total = materialTypes.reduce((sum, item) => sum + Number(item.count || 0), 0);

  if (!materialTypes.length || total === 0) {
    return <EmptyChartState message="Belum ada materi terpublikasi." />;
  }

  const data = {
    labels: materialTypes.map(item => MATERIAL_TYPE_LABELS[item.type] || item.type.toUpperCase()),
    datasets: [
      {
        label: 'Jumlah Materi',
        data: materialTypes.map(item => item.count),
        backgroundColor: palette.primaryBar,
        borderRadius: 4,
        barPercentage: 0.6
      }
    ]
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: palette.tooltipBg,
        padding: 10
      }
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { color: palette.textColor, font: { size: 11 } }
      },
      y: {
        beginAtZero: true,
        border: { display: false },
        grid: { color: palette.gridColor },
        ticks: { color: palette.textColor, font: { size: 11 }, precision: 0 }
      }
    }
  };

  return <Bar data={data} options={options} />;
}

