'use client';

import { useMemo, useRef, useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { useT } from '@/hooks/useT';
import { toLocalDateString } from '@/lib/scoring';
import {
  getVoiceJournalEntries,
  getReflectionStreak,
  getWeekReflectionStats,
  getReflectionPattern,
  type VoiceJournalEntry,
} from '@/lib/voiceJournal';

const MOOD_EMOJI = ['😞', '😟', '😐', '🙂', '😊'];

function formatDuration(ms: number): string {
  const s = Math.round(ms / 1000);
  return `0:${String(s).padStart(2, '0')}`;
}

function formatTotal(seconds: number): string {
  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  if (m === 0) return `${s}s`;
  return `${m}m ${s}s`;
}

// ISO week start (Monday) as YYYY-MM-DD, computed in local time
function getWeekStartKey(dateStr: string): string {
  const d = new Date(dateStr + 'T12:00:00');
  const day = d.getDay(); // 0=Sun..6=Sat
  const diff = day === 0 ? -6 : 1 - day;
  d.setDate(d.getDate() + diff);
  return toLocalDateString(d);
}

interface WeekGroup {
  weekStart: string;
  entries: VoiceJournalEntry[];
}

function groupByWeek(entries: VoiceJournalEntry[]): WeekGroup[] {
  const map = new Map<string, VoiceJournalEntry[]>();
  for (const e of entries) {
    const key = getWeekStartKey(e.date);
    const list = map.get(key);
    if (list) list.push(e);
    else map.set(key, [e]);
  }
  return Array.from(map.entries())
    .map(([weekStart, es]) => ({ weekStart, entries: es }))
    .sort((a, b) => b.weekStart.localeCompare(a.weekStart));
}

function EntryRow({
  entry,
  language,
  confirming,
  onAskDelete,
  onConfirmDelete,
  onCancel,
  deleteLabel,
  confirmLabel,
  confirmYesLabel,
}: {
  entry: VoiceJournalEntry;
  language: string;
  confirming: boolean;
  onAskDelete: () => void;
  onConfirmDelete: () => void;
  onCancel: () => void;
  deleteLabel: string;
  confirmLabel: string;
  confirmYesLabel: string;
}) {
  const audioRef = useRef<HTMLAudioElement | null>(null);
  const [playing, setPlaying] = useState(false);

  const toggle = () => {
    if (!audioRef.current) return;
    if (playing) {
      audioRef.current.pause();
      audioRef.current.currentTime = 0;
      setPlaying(false);
    } else {
      audioRef.current.play();
      setPlaying(true);
    }
  };

  const label = new Date(entry.date + 'T12:00:00').toLocaleDateString(language || 'en', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
  });

  return (
    <div className="flex items-center gap-3 py-3 border-b border-stone-100 last:border-0">
      <button
        type="button"
        onClick={toggle}
        className="w-10 h-10 rounded-full flex items-center justify-center text-white flex-shrink-0"
        style={{ background: '#5B8A5E' }}
        aria-label={playing ? 'Pause' : 'Play'}
      >
        <span className="text-base leading-none">{playing ? '◼' : '▶'}</span>
      </button>
      <div className="flex-1 min-w-0">
        <p className="text-stone-700 text-sm font-semibold capitalize truncate">{label}</p>
        <p className="text-stone-400 text-xs tabular-nums">{formatDuration(entry.durationMs)}</p>
      </div>

      {confirming ? (
        <div className="flex items-center gap-2 flex-shrink-0">
          <span className="text-stone-500 text-xs">{confirmLabel}</span>
          <button
            type="button"
            onClick={onConfirmDelete}
            className="text-[11px] font-semibold text-red-600 px-2 py-1 rounded-md hover:bg-red-50 active:bg-red-100"
          >
            {confirmYesLabel}
          </button>
          <button
            type="button"
            onClick={onCancel}
            className="w-7 h-7 rounded-full text-stone-400 hover:text-stone-600 flex items-center justify-center text-sm"
            aria-label="Cancel"
          >
            ✕
          </button>
        </div>
      ) : (
        <div className="flex items-center gap-1.5 flex-shrink-0">
          {entry.mood != null && (
            <span className="text-base" aria-hidden="true">{MOOD_EMOJI[entry.mood - 1]}</span>
          )}
          <span className="text-stone-500 text-xs font-bold tabular-nums">{entry.score}</span>
          <button
            type="button"
            onClick={onAskDelete}
            aria-label={deleteLabel}
            className="ml-1 w-7 h-7 rounded-full text-stone-300 hover:text-red-500 hover:bg-red-50 active:bg-red-100 flex items-center justify-center"
          >
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
              <path
                d="M3 4h10M6 4V2.5A.5.5 0 0 1 6.5 2h3a.5.5 0 0 1 .5.5V4M4 4l.5 9a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1L12 4M6.5 7v5M9.5 7v5"
                stroke="currentColor"
                strokeWidth="1.3"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
            </svg>
          </button>
        </div>
      )}

      <audio
        ref={audioRef}
        src={entry.dataUri}
        onEnded={() => setPlaying(false)}
        preload="metadata"
      />
    </div>
  );
}

export default function VoiceJournalCard() {
  const dailyLogs = useAppStore((s) => s.dailyLogs);
  const language = useAppStore((s) => s.language);
  const deleteVoiceNote = useAppStore((s) => s.deleteVoiceNote);
  const deleteVoiceNotesForDates = useAppStore((s) => s.deleteVoiceNotesForDates);
  const t = useT();

  const [confirmEntry, setConfirmEntry] = useState<string | null>(null);
  const [confirmWeek, setConfirmWeek] = useState<string | null>(null);

  const entries = useMemo(() => getVoiceJournalEntries(dailyLogs), [dailyLogs]);
  const streak = useMemo(() => getReflectionStreak(dailyLogs), [dailyLogs]);
  const week = useMemo(() => getWeekReflectionStats(dailyLogs), [dailyLogs]);
  const pattern = useMemo(() => getReflectionPattern(dailyLogs), [dailyLogs]);
  const grouped = useMemo(() => groupByWeek(entries), [entries]);

  const patternLine = (() => {
    if (pattern.kind === 'higher') {
      return t.voiceJournalPatternHigher.replace('{n}', String(pattern.delta));
    }
    if (pattern.kind === 'lower') {
      return t.voiceJournalPatternLower.replace('{n}', String(pattern.delta));
    }
    if (pattern.kind === 'flat') return t.voiceJournalPatternFlat;
    return null;
  })();

  return (
    <div className="bg-white rounded-2xl p-4 mb-4 shadow-sm">
      <h3 className="text-stone-700 font-bold text-sm mb-3">{t.voiceJournalTitle}</h3>

      {entries.length === 0 ? (
        <p className="text-stone-400 text-xs leading-relaxed">{t.voiceJournalEmpty}</p>
      ) : (
        <>
          <div className="grid grid-cols-2 gap-2 mb-3">
            <div className="bg-stone-50 rounded-xl px-3 py-2.5">
              <p className="text-stone-400 text-[10px] font-medium uppercase tracking-wider">
                {t.voiceJournalStreakLabel}
              </p>
              <p className="text-stone-700 font-bold text-base mt-0.5">
                {streak} <span className="text-stone-400 text-xs font-normal">{streak === 1 ? t.voiceJournalDayUnit : t.voiceJournalDaysUnit}</span>
              </p>
            </div>
            <div className="bg-stone-50 rounded-xl px-3 py-2.5">
              <p className="text-stone-400 text-[10px] font-medium uppercase tracking-wider">
                {t.voiceJournalWeekLabel}
              </p>
              <p className="text-stone-700 font-bold text-base mt-0.5 tabular-nums">
                {week.count}× <span className="text-stone-400 text-xs font-normal">· {formatTotal(week.totalSeconds)}</span>
              </p>
            </div>
          </div>

          <div className="-mx-1">
            {grouped.map((group) => {
              const headerDate = new Date(group.weekStart + 'T12:00:00').toLocaleDateString(
                language || 'en',
                { day: 'numeric', month: 'short' },
              );
              const headerLabel = t.voiceJournalWeekHeader.replace('{date}', headerDate);
              const weekConfirming = confirmWeek === group.weekStart;

              return (
                <div key={group.weekStart} className="mt-3 first:mt-0">
                  <div className="flex items-center justify-between px-1 pb-1 border-b border-stone-100">
                    <p className="text-stone-400 text-[11px] font-medium uppercase tracking-wider">
                      {headerLabel}
                    </p>
                    {weekConfirming ? (
                      <div className="flex items-center gap-1.5">
                        <span className="text-stone-500 text-[11px]">{t.voiceJournalDeleteConfirm}</span>
                        <button
                          type="button"
                          onClick={() => {
                            deleteVoiceNotesForDates(group.entries.map((e) => e.date));
                            setConfirmWeek(null);
                            setConfirmEntry(null);
                          }}
                          className="text-[11px] font-semibold text-red-600 px-1.5 py-0.5 rounded hover:bg-red-50 active:bg-red-100"
                        >
                          {t.voiceJournalDeleteConfirmYes}
                        </button>
                        <button
                          type="button"
                          onClick={() => setConfirmWeek(null)}
                          className="w-5 h-5 rounded-full text-stone-400 hover:text-stone-600 flex items-center justify-center text-xs"
                          aria-label="Cancel"
                        >
                          ✕
                        </button>
                      </div>
                    ) : (
                      <button
                        type="button"
                        onClick={() => {
                          setConfirmWeek(group.weekStart);
                          setConfirmEntry(null);
                        }}
                        className="text-[11px] text-stone-400 hover:text-red-500 px-1.5 py-0.5 rounded"
                      >
                        {t.voiceJournalDeleteWeek}
                      </button>
                    )}
                  </div>

                  {group.entries.map((e) => (
                    <EntryRow
                      key={e.date}
                      entry={e}
                      language={language}
                      confirming={confirmEntry === e.date}
                      onAskDelete={() => {
                        setConfirmEntry(e.date);
                        setConfirmWeek(null);
                      }}
                      onConfirmDelete={() => {
                        deleteVoiceNote(e.date);
                        setConfirmEntry(null);
                      }}
                      onCancel={() => setConfirmEntry(null)}
                      deleteLabel={t.voiceJournalDelete}
                      confirmLabel={t.voiceJournalDeleteConfirm}
                      confirmYesLabel={t.voiceJournalDeleteConfirmYes}
                    />
                  ))}
                </div>
              );
            })}
          </div>

          {patternLine && (
            <div className="mt-3 bg-[#5B8A5E]/8 border border-[#5B8A5E]/20 rounded-xl px-3 py-2.5">
              <p className="text-[#3D6640] text-xs leading-relaxed">{patternLine}</p>
            </div>
          )}
        </>
      )}
    </div>
  );
}
