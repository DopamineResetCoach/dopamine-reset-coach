'use client';

import { useAppStore } from '@/store/useAppStore';
import { useT } from '@/hooks/useT';

function TodayIcon({ active }: { active: boolean }) {
  return (
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
      <circle
        cx="12"
        cy="12"
        r="9"
        stroke={active ? '#5B8A5E' : '#9CA3AF'}
        strokeWidth="1.8"
      />
      <path
        d="M12 7v5l3 3"
        stroke={active ? '#5B8A5E' : '#9CA3AF'}
        strokeWidth="1.8"
        strokeLinecap="round"
      />
    </svg>
  );
}

function ProgressIcon({ active }: { active: boolean }) {
  return (
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
      <path
        d="M4 18l4-5 4 3 4-7 4 4"
        stroke={active ? '#5B8A5E' : '#9CA3AF'}
        strokeWidth="1.8"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  );
}

function FocusIcon({ active }: { active: boolean }) {
  return (
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
      <circle
        cx="12"
        cy="12"
        r="9"
        stroke={active ? '#5B8A5E' : '#9CA3AF'}
        strokeWidth="1.8"
      />
      <circle
        cx="12"
        cy="12"
        r="3"
        fill={active ? '#5B8A5E' : '#9CA3AF'}
      />
    </svg>
  );
}

function SettingsIcon({ active }: { active: boolean }) {
  return (
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
      <circle
        cx="12"
        cy="12"
        r="3"
        stroke={active ? '#5B8A5E' : '#9CA3AF'}
        strokeWidth="1.8"
      />
      <path
        d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"
        stroke={active ? '#5B8A5E' : '#9CA3AF'}
        strokeWidth="1.8"
        strokeLinecap="round"
      />
    </svg>
  );
}

export default function BottomNav() {
  const { activeTab, setActiveTab } = useAppStore();
  const t = useT();

  const tabs = [
    { id: 'today' as const, label: t.navToday, icon: TodayIcon },
    { id: 'progress' as const, label: t.navProgress, icon: ProgressIcon },
    { id: 'focus' as const, label: t.navFocus, icon: FocusIcon },
    { id: 'settings' as const, label: t.navSettings, icon: SettingsIcon },
  ];

  return (
    <nav className="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-stone-100 pb-safe flex justify-center">
      <div className="w-full max-w-sm flex items-center px-2">
        {tabs.map(({ id, label, icon: Icon }) => {
          const isActive = activeTab === id;
          return (
            <button
              key={id}
              onClick={() => setActiveTab(id)}
              className="flex-1 flex flex-col items-center gap-1 py-3 transition-all duration-200"
              aria-label={label}
              aria-current={isActive ? 'page' : undefined}
            >
              <Icon active={isActive} />
              <span
                className={`text-[10px] font-medium tracking-wide transition-colors ${
                  isActive ? 'text-[#5B8A5E]' : 'text-stone-400'
                }`}
              >
                {label}
              </span>
            </button>
          );
        })}
      </div>
    </nav>
  );
}
