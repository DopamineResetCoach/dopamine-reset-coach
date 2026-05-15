'use client';

import { useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { getTranslatedTasks } from '@/lib/tasks';
import { getTodayString } from '@/lib/scoring';
import { Task } from '@/types';
import { useT } from '@/hooks/useT';

function TooltipIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
      <circle cx="8" cy="8" r="7" stroke="#9CA3AF" strokeWidth="1.4" />
      <path
        d="M8 7v5M8 5.5v.5"
        stroke="#9CA3AF"
        strokeWidth="1.5"
        strokeLinecap="round"
      />
    </svg>
  );
}

function TaskItem({
  task,
  completed,
  onToggle,
}: {
  task: Task;
  completed: boolean;
  onToggle: () => void;
}) {
  const [showTooltip, setShowTooltip] = useState(false);
  const t = useT();

  return (
    <div
      className={`bg-white rounded-2xl px-4 py-4 transition-all duration-200 ${
        completed ? 'opacity-60' : ''
      }`}
    >
      <div className="flex items-start gap-3">
        {/* Checkbox */}
        <button
          onClick={onToggle}
          className={`flex-shrink-0 w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all mt-0.5 ${
            completed
              ? 'bg-[#5B8A5E] border-[#5B8A5E]'
              : 'border-stone-300 bg-white active:scale-95'
          }`}
          aria-label={completed ? 'Mark incomplete' : 'Mark complete'}
        >
          {completed && (
            <svg width="12" height="10" viewBox="0 0 12 10" fill="none">
              <path
                d="M1 5l4 4 6-8"
                stroke="white"
                strokeWidth="1.8"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
            </svg>
          )}
        </button>

        {/* Content */}
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2">
            <span className="text-lg">{task.icon}</span>
            <span
              className={`font-semibold text-sm transition-all ${
                completed ? 'line-through text-stone-400' : 'text-stone-800'
              }`}
            >
              {task.title}
            </span>
            {task.hardModeOnly && (
              <span className="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-600">
                {t.hardBadge}
              </span>
            )}
          </div>
          <p
            className={`text-xs mt-0.5 ${
              completed ? 'text-stone-300' : 'text-stone-400'
            }`}
          >
            {task.description}
          </p>

          {/* Tooltip */}
          {showTooltip && (
            <div className="mt-2 bg-stone-50 border border-stone-100 rounded-xl p-3">
              <p className="text-stone-600 text-xs leading-relaxed">
                {task.tooltip}
              </p>
            </div>
          )}
        </div>

        {/* Points + info */}
        <div className="flex flex-col items-end gap-1.5 flex-shrink-0">
          <span className="text-[#5B8A5E] font-bold text-xs">
            +{task.points}pts
          </span>
          <button
            onClick={() => setShowTooltip((v) => !v)}
            className="text-stone-300 hover:text-stone-500 transition-colors"
            aria-label="Why does this help?"
          >
            <TooltipIcon />
          </button>
        </div>
      </div>
    </div>
  );
}

export default function DailyChecklist() {
  const { profile, dailyLogs, toggleTask } = useAppStore();
  const t = useT();
  if (!profile) return null;

  const tasks = getTranslatedTasks(t, profile.hardMode);
  const today = getTodayString();
  const log = dailyLogs[today];
  const completedTasks = log?.completedTasks ?? [];

  const completed = tasks.filter((task) => completedTasks.includes(task.id));
  const remaining = tasks.filter((task) => !completedTasks.includes(task.id));
  const pct = Math.round((completed.length / tasks.length) * 100);

  return (
    <div className="mb-4">
      <div className="flex items-center justify-between mb-3">
        <h2 className="text-stone-800 font-bold text-base">{t.navToday}</h2>
        <span className="text-stone-400 text-sm">
          {completed.length}/{tasks.length}
        </span>
      </div>

      {/* Progress bar */}
      <div className="h-1.5 bg-stone-100 rounded-full mb-4 overflow-hidden">
        <div
          className="h-full bg-[#5B8A5E] rounded-full transition-all duration-500"
          style={{ width: `${pct}%` }}
        />
      </div>

      {pct === 100 && (
        <div className="bg-[#5B8A5E]/10 border border-[#5B8A5E]/20 rounded-2xl px-4 py-3 mb-3 text-center">
          <p className="text-[#3D6640] font-semibold text-sm">
            {t.checklistAllDone}
          </p>
        </div>
      )}

      <div className="space-y-2">
        {remaining.map((task) => (
          <TaskItem
            key={task.id}
            task={task}
            completed={false}
            onToggle={() => toggleTask(task.id)}
          />
        ))}
        {completed.map((task) => (
          <TaskItem
            key={task.id}
            task={task}
            completed
            onToggle={() => toggleTask(task.id)}
          />
        ))}
      </div>
    </div>
  );
}
