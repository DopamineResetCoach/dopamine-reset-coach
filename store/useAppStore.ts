'use client';

import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { UserProfile, DailyLog, UrgeLog, BadHabit, DailyCheckIn } from '@/types';
import type { Locale } from '@/lib/i18n';
import {
  initializePurchases,
  purchasePremiumPackage,
  getPremiumPackages,
  restorePurchases as rcRestorePurchases,
  checkPremiumStatus,
} from '@/lib/purchases';
import type { PurchasesPackage } from '@revenuecat/purchases-capacitor';
import {
  getInitialScore,
  calculateDayScore,
  getTodayString,
  getCheckInAverage,
  calculateStreak,
  calculateDailyDebt,
  getWeekKey,
} from '@/lib/scoring';
import { getTasksForProfile } from '@/lib/tasks';
import { getTodaySteps, getStepsHistory, requestStepAuthorization } from '@/lib/steps';
import {
  requestNotificationPermission,
  scheduleDailyCheckInReminder,
  cancelCheckInReminder,
  scheduleEveningReflection,
  cancelEveningReflection,
} from '@/lib/notifications';
import type { Translations } from '@/lib/i18n/types';

function morningRotation(t: Translations): string[] {
  return [
    t.notifReminderBody1,
    t.notifReminderBody2,
    t.notifReminderBody3,
    t.notifReminderBody4,
    t.notifReminderBody5,
    t.notifReminderBody6,
    t.notifReminderBody7,
  ];
}

function eveningRotation(t: Translations, motivation?: string): string[] {
  const base = [
    t.notifEveningBody1,
    t.notifEveningBody2,
    t.notifEveningBody3,
    t.notifEveningBody4,
    t.notifEveningBody5,
  ];
  const trimmed = motivation?.trim();
  if (trimmed) {
    return [`${t.motivationReminderPrefix} ${trimmed}`, ...base];
  }
  return base;
}
import { getTranslations } from '@/lib/i18n';

/**
 * Recompute today's dopamine score from all current signals.
 * Pass overrides for any signal that's about to change so the score reflects
 * the post-mutation state (e.g. a just-toggled task list).
 */
function recalcScore(args: {
  profile: UserProfile | null;
  dailyLogs: Record<string, DailyLog>;
  stepGoal: number;
  completedTasks: string[];
  badHabits: BadHabit[];
  todaySteps: number;
  todayCheckIn?: DailyCheckIn;
  challengesToday?: number;
  urgesResisted?: number;
}): number {
  if (!args.profile) return 0;
  const baseScore = getInitialScore(args.profile);
  const today = getTodayString();

  // For check-in averaging include the pending check-in (if any) in today's slot
  const logsForAvg = args.todayCheckIn
    ? {
        ...args.dailyLogs,
        [today]: {
          ...(args.dailyLogs[today] ?? {
            date: today,
            completedTasks: [],
            urges: [],
            dopamineScore: 0,
          }),
          checkIn: args.todayCheckIn,
        },
      }
    : args.dailyLogs;

  const fields: Array<'sleep' | 'energy' | 'mood'> = ['sleep', 'energy', 'mood'];
  const avgs = fields
    .map((f) => getCheckInAverage(logsForAvg, 7, f))
    .filter((v): v is number => v != null);
  const checkInAvg = avgs.length > 0 ? avgs.reduce((s, v) => s + v, 0) / avgs.length : null;

  const streak = calculateStreak(args.dailyLogs);
  const debtPoints = calculateDailyDebt(args.badHabits);

  // If not passed explicitly, derive urgesResisted from today's log.
  const urgesResisted =
    args.urgesResisted ??
    (args.dailyLogs[today]?.urges ?? []).filter((u) => u.completedIntervention).length;

  return calculateDayScore(args.completedTasks, baseScore, {
    checkInAvg,
    streak,
    steps: args.todaySteps,
    stepGoal: args.stepGoal,
    debtPoints,
    challengesToday: args.challengesToday,
    urgesResisted,
  });
}

interface AppState {
  // First-launch welcome screen (shown once before onboarding)
  hasSeenWelcome: boolean;
  markWelcomeSeen: () => void;

  // Onboarding
  hasCompletedOnboarding: boolean;
  profile: UserProfile | null;

  // Daily logs keyed by YYYY-MM-DD
  dailyLogs: Record<string, DailyLog>;

  // Premium
  isPremium: boolean;

  // Completed challenges
  completedChallenges: string[];
  lastChallengeWeek: string;
  lastChallengeDay: string;
  challengeResetMode: 'daily' | 'weekly';
  setChallengeResetMode: (mode: 'daily' | 'weekly') => void;

  // UI state
  activeTab: 'today' | 'progress' | 'focus' | 'settings';

  // Language
  language: Locale;

  // Steps
  stepGoal: number;
  todaySteps: number;
  stepsHistory: Record<string, number>;
  lastHistoryRefresh: number;
  setStepGoal: (goal: number) => void;
  refreshSteps: () => Promise<void>;
  refreshStepsHistory: (daysBack?: number) => Promise<void>;

  // Notifications
  notificationsEnabled: boolean;
  notificationTime: { hour: number; minute: number };
  setNotificationsEnabled: (enabled: boolean) => Promise<'granted' | 'denied' | 'unavailable'>;
  setNotificationTime: (time: { hour: number; minute: number }) => Promise<void>;
  eveningReflectionEnabled: boolean;
  eveningReflectionTime: { hour: number; minute: number };
  setEveningReflectionEnabled: (enabled: boolean) => Promise<'granted' | 'denied' | 'unavailable'>;
  setEveningReflectionTime: (time: { hour: number; minute: number }) => Promise<void>;

  // Daily check-in prompt
  checkInPromptDisabled: boolean;
  setCheckInPromptDisabled: (value: boolean) => void;

  // Brain Recovery Stages — the user only sees the transition modal once
  // per stage they reach. The current stage itself is derived from
  // dailyLogs + profile.startDate (see lib/stages.ts).
  lastSeenStage: number;
  markStageSeen: (stageId: number) => void;

  // Actions
  completeOnboarding: (profile: UserProfile) => void;
  setLanguage: (lang: Locale) => void;
  toggleTask: (taskId: string) => void;
  logUrge: (urge: Omit<UrgeLog, 'id' | 'timestamp'>) => void;
  logBadHabit: (habit: Omit<BadHabit, 'id' | 'timestamp'>) => void;
  removeBadHabit: (id: string) => void;
  deleteVoiceNote: (date: string) => void;
  deleteVoiceNotesForDates: (dates: string[]) => void;
  saveCheckIn: (input: Omit<DailyCheckIn, 'date' | 'timestamp'>) => void;
  dismissCheckInCard: () => void;
  updateProfileField: (patch: Partial<UserProfile>) => void;
  completeChallenge: (challengeId: string) => void;
  uncompleteChallenge: (challengeId: string) => void;
  resetChallengesIfNeeded: () => void;
  /** @deprecated use resetChallengesIfNeeded */
  resetChallengesIfNewWeek: () => void;
  setActiveTab: (tab: AppState['activeTab']) => void;
  toggleHardMode: () => void;
  setPremium: (value: boolean) => void;
  initPurchases: () => Promise<void>;
  purchasePremium: (pkg?: PurchasesPackage) => Promise<'success' | 'cancelled' | 'error'>;
  getPremiumPackages: () => Promise<PurchasesPackage[]>;
  restorePurchases: () => Promise<'restored' | 'not_found' | 'error'>;
  resetApp: () => void;
  ensureTodayLog: () => void;
}

const initialState = {
  hasSeenWelcome: false,
  hasCompletedOnboarding: false,
  profile: null as UserProfile | null,
  dailyLogs: {} as Record<string, DailyLog>,
  isPremium: false,
  completedChallenges: [] as string[],
  lastChallengeWeek: '',
  lastChallengeDay: '',
  challengeResetMode: 'weekly' as 'daily' | 'weekly',
  activeTab: 'today' as const,
  language: 'en' as Locale,
  stepGoal: 10000,
  todaySteps: 0,
  stepsHistory: {} as Record<string, number>,
  lastHistoryRefresh: 0,
  notificationsEnabled: false,
  notificationTime: { hour: 9, minute: 0 },
  eveningReflectionEnabled: false,
  eveningReflectionTime: { hour: 21, minute: 0 },
  checkInPromptDisabled: false,
  lastSeenStage: 1,
};

export const useAppStore = create<AppState>()(
  persist(
    (set, get) => ({
      ...initialState,

      completeOnboarding: (profile) => {
        const today = getTodayString();
        const startScore = getInitialScore(profile);
        const initialLog: DailyLog = {
          date: today,
          completedTasks: [],
          dopamineScore: startScore,
          urges: [],
        };
        // Seed motivation history with the onboarding value so the timeline
        // begins at day 0, not at the first edit.
        const seededProfile: UserProfile = profile.motivation && profile.motivation.trim()
          ? { ...profile, motivationHistory: [{ text: profile.motivation.trim(), setAt: new Date().toISOString() }] }
          : profile;
        set({
          hasCompletedOnboarding: true,
          profile: seededProfile,
          dailyLogs: { [today]: initialLog },
        });
      },

      ensureTodayLog: () => {
        const state = get();
        const { profile, dailyLogs, stepGoal, todaySteps } = state;
        if (!profile) return;
        const today = getTodayString();

        // Prune voice notes from check-ins older than 7 days. Audio is
        // megabyte-scale and would blow past localStorage quota over months.
        // The transcripted text in `note` stays — only the audio blob expires.
        const cutoff = new Date();
        cutoff.setDate(cutoff.getDate() - 7);
        const cutoffKey = cutoff.toISOString().slice(0, 10);
        let prunedAny = false;
        const prunedLogs = Object.fromEntries(
          Object.entries(dailyLogs).map(([k, log]) => {
            if (k >= cutoffKey || !log.checkIn?.voiceNote) return [k, log];
            prunedAny = true;
            const { voiceNote: _v, voiceNoteDurationMs: _d, ...restCheckIn } = log.checkIn;
            return [k, { ...log, checkIn: restCheckIn }];
          }),
        );
        if (prunedAny) {
          set({ dailyLogs: prunedLogs });
        }

        if (!dailyLogs[today]) {
          // Compute the score using the same recalc as every other path. This
          // keeps the first view of the day consistent with what the user
          // would see after their first tap — no more "score drops when I
          // complete a task" surprise. Carries forward implicit signals like
          // a long check-in streak and yesterday's task baseline.
          const initialScore = recalcScore({
            profile,
            dailyLogs,
            stepGoal,
            completedTasks: [],
            badHabits: [],
            todaySteps,
            todayCheckIn: undefined,
            challengesToday: 0,
            urgesResisted: 0,
          });
          set((s) => ({
            dailyLogs: {
              ...s.dailyLogs,
              [today]: {
                date: today,
                completedTasks: [],
                dopamineScore: initialScore,
                urges: [],
              },
            },
          }));
        }
      },

      toggleTask: (taskId) => {
        const state = get();
        const { profile, dailyLogs, todaySteps, stepGoal } = state;
        if (!profile) return;
        const today = getTodayString();
        const log = dailyLogs[today];
        if (!log) return;

        const isCompleted = log.completedTasks.includes(taskId);
        const updatedTasks = isCompleted
          ? log.completedTasks.filter((id) => id !== taskId)
          : [...log.completedTasks, taskId];

        const newScore = recalcScore({
          profile,
          dailyLogs,
          stepGoal,
          completedTasks: updatedTasks,
          badHabits: log.badHabits ?? [],
          todaySteps,
          todayCheckIn: log.checkIn,
          challengesToday: (log.challengesCompletedToday ?? []).length,
        });

        set((s) => ({
          dailyLogs: {
            ...s.dailyLogs,
            [today]: {
              ...log,
              completedTasks: updatedTasks,
              dopamineScore: newScore,
            },
          },
        }));
      },

      logUrge: (urge) => {
        const today = getTodayString();
        const state = get();
        const log = state.dailyLogs[today];
        if (!log) return;

        const newUrge: UrgeLog = {
          ...urge,
          id: crypto.randomUUID(),
          timestamp: new Date().toISOString(),
        };

        const updatedUrges = [...log.urges, newUrge];
        const urgesResisted = updatedUrges.filter((u) => u.completedIntervention).length;

        const newScore = recalcScore({
          profile: state.profile,
          dailyLogs: state.dailyLogs,
          stepGoal: state.stepGoal,
          completedTasks: log.completedTasks,
          badHabits: log.badHabits ?? [],
          todaySteps: state.todaySteps,
          todayCheckIn: log.checkIn,
          challengesToday: log.challengesCompletedToday?.length ?? 0,
          urgesResisted,
        });

        set((s) => ({
          dailyLogs: {
            ...s.dailyLogs,
            [today]: {
              ...log,
              urges: updatedUrges,
              dopamineScore: newScore,
            },
          },
        }));
      },

      logBadHabit: (habit) => {
        const state = get();
        const { profile, dailyLogs, todaySteps, stepGoal } = state;
        const today = getTodayString();
        const log = dailyLogs[today];
        if (!log) return;
        const newHabit: BadHabit = {
          ...habit,
          id: crypto.randomUUID(),
          timestamp: new Date().toISOString(),
        };
        const updatedHabits = [...(log.badHabits ?? []), newHabit];
        const newScore = recalcScore({
          profile,
          dailyLogs,
          stepGoal,
          completedTasks: log.completedTasks,
          badHabits: updatedHabits,
          todaySteps,
          todayCheckIn: log.checkIn,
          challengesToday: (log.challengesCompletedToday ?? []).length,
        });
        set((s) => ({
          dailyLogs: {
            ...s.dailyLogs,
            [today]: {
              ...log,
              badHabits: updatedHabits,
              dopamineScore: newScore,
            },
          },
        }));
      },

      removeBadHabit: (id) => {
        const state = get();
        const { profile, dailyLogs, todaySteps, stepGoal } = state;
        const today = getTodayString();
        const log = dailyLogs[today];
        if (!log) return;
        const updatedHabits = (log.badHabits ?? []).filter((h) => h.id !== id);
        const newScore = recalcScore({
          profile,
          dailyLogs,
          stepGoal,
          completedTasks: log.completedTasks,
          badHabits: updatedHabits,
          todaySteps,
          todayCheckIn: log.checkIn,
          challengesToday: (log.challengesCompletedToday ?? []).length,
        });
        set((s) => ({
          dailyLogs: {
            ...s.dailyLogs,
            [today]: {
              ...log,
              badHabits: updatedHabits,
              dopamineScore: newScore,
            },
          },
        }));
      },

      deleteVoiceNote: (date) => {
        set((s) => {
          const log = s.dailyLogs[date];
          if (!log?.checkIn?.voiceNote) return s;
          const { voiceNote: _v, voiceNoteDurationMs: _d, ...restCheckIn } = log.checkIn;
          return {
            dailyLogs: {
              ...s.dailyLogs,
              [date]: { ...log, checkIn: restCheckIn },
            },
          };
        });
      },

      deleteVoiceNotesForDates: (dates) => {
        set((s) => {
          const updated: Record<string, DailyLog> = { ...s.dailyLogs };
          let changed = false;
          for (const date of dates) {
            const log = updated[date];
            if (!log?.checkIn?.voiceNote) continue;
            const { voiceNote: _v, voiceNoteDurationMs: _d, ...restCheckIn } = log.checkIn;
            updated[date] = { ...log, checkIn: restCheckIn };
            changed = true;
          }
          return changed ? { dailyLogs: updated } : s;
        });
      },

      saveCheckIn: (input) => {
        const state = get();
        const { profile, dailyLogs, todaySteps, stepGoal } = state;
        const today = getTodayString();
        const log = dailyLogs[today];
        if (!log) return;
        const checkIn: DailyCheckIn = {
          ...input,
          date: today,
          timestamp: new Date().toISOString(),
        };
        const newScore = recalcScore({
          profile,
          dailyLogs,
          stepGoal,
          completedTasks: log.completedTasks,
          badHabits: log.badHabits ?? [],
          todaySteps,
          todayCheckIn: checkIn,
          challengesToday: (log.challengesCompletedToday ?? []).length,
        });
        set((s) => ({
          dailyLogs: {
            ...s.dailyLogs,
            [today]: { ...log, checkIn, dopamineScore: newScore },
          },
        }));
      },

      dismissCheckInCard: () => {
        const today = getTodayString();
        const log = get().dailyLogs[today];
        if (!log?.checkIn) return;
        set((s) => ({
          dailyLogs: {
            ...s.dailyLogs,
            [today]: { ...log, checkIn: { ...log.checkIn!, dismissed: true } },
          },
        }));
      },

      setCheckInPromptDisabled: (value) => set({ checkInPromptDisabled: value }),

      markStageSeen: (stageId) =>
        set((s) => ({ lastSeenStage: Math.max(s.lastSeenStage, stageId) })),

      updateProfileField: (patch) => {
        const state = get();
        const { profile, dailyLogs, stepGoal, todaySteps, eveningReflectionEnabled, eveningReflectionTime, language } = state;
        if (!profile) return;
        const newProfile: UserProfile = { ...profile, ...patch };

        // Motivation is an append-only journal: every distinct value the user
        // commits to gets a timestamp, so the timeline reads as a journey.
        if ('motivation' in patch) {
          const next = (patch.motivation ?? '').trim();
          const prevHistory = profile.motivationHistory ?? [];
          const lastText = prevHistory.length > 0 ? prevHistory[prevHistory.length - 1].text : '';
          if (next && next !== lastText) {
            newProfile.motivationHistory = [...prevHistory, { text: next, setAt: new Date().toISOString() }];
          }
          if (eveningReflectionEnabled) {
            const t = getTranslations(language);
            scheduleEveningReflection(eveningReflectionTime, t.notifEveningTitle, eveningRotation(t, newProfile.motivation));
          }
        }

        // Profile changes (sleep/energy/screen-time/habits) shift the baseline
        // used by recalcScore. Recompute today's score so the UI doesn't lag
        // until the next user action.
        const today = getTodayString();
        const log = dailyLogs[today];
        if (log) {
          const newScore = recalcScore({
            profile: newProfile,
            dailyLogs,
            stepGoal,
            completedTasks: log.completedTasks,
            badHabits: log.badHabits ?? [],
            todaySteps,
            todayCheckIn: log.checkIn,
            challengesToday: log.challengesCompletedToday?.length ?? 0,
          });
          set((s) => ({
            profile: newProfile,
            dailyLogs: {
              ...s.dailyLogs,
              [today]: { ...log, dopamineScore: newScore },
            },
          }));
        } else {
          set({ profile: newProfile });
        }
      },

      completeChallenge: (challengeId) => {
        const state = get();
        const { profile, dailyLogs, todaySteps, stepGoal, completedChallenges } = state;
        const alreadyCompletedGlobally = completedChallenges.includes(challengeId);
        const today = getTodayString();
        const log = dailyLogs[today];

        // No-op if already completed historically AND today's log is missing
        if (alreadyCompletedGlobally && !log) return;

        const todayChallenges = log?.challengesCompletedToday ?? [];
        const alreadyToday = todayChallenges.includes(challengeId);
        const updatedTodayChallenges = alreadyToday
          ? todayChallenges
          : [...todayChallenges, challengeId];

        const updatedGlobal = alreadyCompletedGlobally
          ? completedChallenges
          : [...completedChallenges, challengeId];

        if (log && profile) {
          const newScore = recalcScore({
            profile,
            dailyLogs,
            stepGoal,
            completedTasks: log.completedTasks,
            badHabits: log.badHabits ?? [],
            todaySteps,
            todayCheckIn: log.checkIn,
            challengesToday: updatedTodayChallenges.length,
          });
          set((s) => ({
            completedChallenges: updatedGlobal,
            dailyLogs: {
              ...s.dailyLogs,
              [today]: {
                ...log,
                challengesCompletedToday: updatedTodayChallenges,
                dopamineScore: newScore,
              },
            },
          }));
        } else {
          set({ completedChallenges: updatedGlobal });
        }
      },

      uncompleteChallenge: (challengeId) => {
        const state = get();
        const { profile, dailyLogs, todaySteps, stepGoal, completedChallenges } = state;
        const today = getTodayString();
        const log = dailyLogs[today];

        const updatedGlobal = completedChallenges.filter((id) => id !== challengeId);
        const updatedTodayChallenges = (log?.challengesCompletedToday ?? []).filter(
          (id) => id !== challengeId,
        );

        if (log && profile) {
          const newScore = recalcScore({
            profile,
            dailyLogs,
            stepGoal,
            completedTasks: log.completedTasks,
            badHabits: log.badHabits ?? [],
            todaySteps,
            todayCheckIn: log.checkIn,
            challengesToday: updatedTodayChallenges.length,
          });
          set((s) => ({
            completedChallenges: updatedGlobal,
            dailyLogs: {
              ...s.dailyLogs,
              [today]: {
                ...log,
                challengesCompletedToday: updatedTodayChallenges,
                dopamineScore: newScore,
              },
            },
          }));
        } else {
          set({ completedChallenges: updatedGlobal });
        }
      },

      resetChallengesIfNeeded: () => {
        const { challengeResetMode, lastChallengeWeek, lastChallengeDay } = get();
        if (challengeResetMode === 'daily') {
          const today = getTodayString();
          if (lastChallengeDay === today) return;
          if (!lastChallengeDay) {
            set({ lastChallengeDay: today });
            return;
          }
          set({ completedChallenges: [], lastChallengeDay: today });
          return;
        }
        const currentWeek = getWeekKey();
        if (lastChallengeWeek === currentWeek) return;
        if (!lastChallengeWeek) {
          set({ lastChallengeWeek: currentWeek });
          return;
        }
        set({ completedChallenges: [], lastChallengeWeek: currentWeek });
      },

      resetChallengesIfNewWeek: () => get().resetChallengesIfNeeded(),

      setChallengeResetMode: (mode) => set({ challengeResetMode: mode }),

      setPremium: (value) => set({ isPremium: value }),

      initPurchases: async () => {
        // Skip on web / SSR / screenshot tooling. window.Capacitor exists even in
        // the browser (it's injected by the SDK), so we check isNativePlatform()
        // to detect real iOS/Android. Without this guard, checkPremiumStatus()
        // returns false and overwrites legitimately-restored premium state.
        if (typeof window === 'undefined') return;
        const cap = (window as unknown as { Capacitor?: { isNativePlatform?: () => boolean } }).Capacitor;
        if (!cap?.isNativePlatform?.()) return;

        await initializePurchases((isPremium) => {
          if (isPremium !== get().isPremium) set({ isPremium });
        });
        // Sync premium status vanuit RevenueCat bij app start
        const premium = await checkPremiumStatus();
        if (premium !== get().isPremium) {
          set({ isPremium: premium });
        }
      },

      purchasePremium: async (pkg?: PurchasesPackage) => {
        try {
          const success = await purchasePremiumPackage(pkg);
          if (success) {
            set({ isPremium: true });
            return 'success';
          }
          return 'cancelled';
        } catch (e) {
          console.error('[Store] purchasePremium error:', e);
          return 'error';
        }
      },

      getPremiumPackages: async () => {
        return getPremiumPackages();
      },

      restorePurchases: async () => {
        try {
          const hasPremium = await rcRestorePurchases();
          set({ isPremium: hasPremium });
          return hasPremium ? 'restored' : 'not_found';
        } catch (e) {
          console.error('[Store] restorePurchases error:', e);
          return 'error';
        }
      },

      setLanguage: (lang) => set({ language: lang }),

      setActiveTab: (tab) => set({ activeTab: tab }),

      markWelcomeSeen: () => set({ hasSeenWelcome: true }),

      toggleHardMode: () => {
        const state = get();
        const { profile, dailyLogs, stepGoal, todaySteps } = state;
        if (!profile) return;
        const newProfile = { ...profile, hardMode: !profile.hardMode };
        // Recompute today's score — Hard Mode changes the baseline via
        // habit-penalties in getInitialScore.
        const today = getTodayString();
        const log = dailyLogs[today];
        if (log) {
          const newScore = recalcScore({
            profile: newProfile,
            dailyLogs,
            stepGoal,
            completedTasks: log.completedTasks,
            badHabits: log.badHabits ?? [],
            todaySteps,
            todayCheckIn: log.checkIn,
            challengesToday: log.challengesCompletedToday?.length ?? 0,
          });
          set((s) => ({
            profile: newProfile,
            dailyLogs: {
              ...s.dailyLogs,
              [today]: { ...log, dopamineScore: newScore },
            },
          }));
        } else {
          set({ profile: newProfile });
        }
      },

      setStepGoal: (goal: number) => set({ stepGoal: goal }),

      refreshStepsHistory: async (daysBack = 90) => {
        // No-op on web/screenshot — keep whatever steps are in store (mock/persisted).
        if (typeof window !== 'undefined') {
          const cap = (window as unknown as { Capacitor?: { isNativePlatform?: () => boolean } }).Capacitor;
          if (!cap?.isNativePlatform?.()) return;
        }
        await requestStepAuthorization();
        const history = await getStepsHistory(daysBack);
        set({ stepsHistory: history, lastHistoryRefresh: Date.now() });
      },

      refreshSteps: async () => {
        // No-op on web/screenshot — keep whatever steps are in store (mock/persisted).
        if (typeof window !== 'undefined') {
          const cap = (window as unknown as { Capacitor?: { isNativePlatform?: () => boolean } }).Capacitor;
          if (!cap?.isNativePlatform?.()) return;
        }
        await requestStepAuthorization();
        const steps = await getTodaySteps();
        set({ todaySteps: steps });

        // Recompute today's score now that steps changed
        const state = get();
        const { profile, dailyLogs, stepGoal } = state;
        const today = getTodayString();
        const log = dailyLogs[today];
        if (!profile || !log) return;
        const newScore = recalcScore({
          profile,
          dailyLogs,
          stepGoal,
          completedTasks: log.completedTasks,
          badHabits: log.badHabits ?? [],
          todaySteps: steps,
          todayCheckIn: log.checkIn,
          challengesToday: (log.challengesCompletedToday ?? []).length,
        });
        set((s) => ({
          dailyLogs: {
            ...s.dailyLogs,
            [today]: { ...log, dopamineScore: newScore },
          },
        }));
      },

      setNotificationsEnabled: async (enabled) => {
        if (!enabled) {
          await cancelCheckInReminder();
          set({ notificationsEnabled: false });
          return 'granted';
        }
        const result = await requestNotificationPermission();
        if (result !== 'granted') {
          set({ notificationsEnabled: false });
          return result;
        }
        const { notificationTime, language } = get();
        const t = getTranslations(language);
        await scheduleDailyCheckInReminder(notificationTime, t.notifReminderTitle, morningRotation(t));
        set({ notificationsEnabled: true });
        return 'granted';
      },

      setNotificationTime: async (time) => {
        set({ notificationTime: time });
        const { notificationsEnabled, language } = get();
        if (notificationsEnabled) {
          const t = getTranslations(language);
          await scheduleDailyCheckInReminder(time, t.notifReminderTitle, morningRotation(t));
        }
      },

      setEveningReflectionEnabled: async (enabled) => {
        if (!enabled) {
          await cancelEveningReflection();
          set({ eveningReflectionEnabled: false });
          return 'granted';
        }
        const result = await requestNotificationPermission();
        if (result !== 'granted') {
          set({ eveningReflectionEnabled: false });
          return result;
        }
        const { eveningReflectionTime, language } = get();
        const t = getTranslations(language);
        await scheduleEveningReflection(eveningReflectionTime, t.notifEveningTitle, eveningRotation(t, get().profile?.motivation));
        set({ eveningReflectionEnabled: true });
        return 'granted';
      },

      setEveningReflectionTime: async (time) => {
        set({ eveningReflectionTime: time });
        const { eveningReflectionEnabled, language } = get();
        if (eveningReflectionEnabled) {
          const t = getTranslations(language);
          await scheduleEveningReflection(time, t.notifEveningTitle, eveningRotation(t, get().profile?.motivation));
        }
      },

      resetApp: () => {
        cancelCheckInReminder();
        cancelEveningReflection();
        set(initialState);
      },
    }),
    {
      name: 'dopamine-reset-coach',
      skipHydration: true,
    }
  )
);
