export interface UserProfile {
  screenTimeHours: number;
  sleepQuality: number; // 1–5
  energyLevel: number; // 1–5
  brainFog: boolean;
  habits: {
    socialMedia: boolean;
    caffeine: boolean;
    junkFood: boolean;
    alcohol: boolean;
    porn: boolean;
    gaming: boolean;
  };
  planDuration: 7 | 14 | 30;
  hardMode: boolean;
  startDate: string; // ISO date YYYY-MM-DD
}

export interface Task {
  id: string;
  title: string;
  description: string;
  icon: string;
  tooltip: string;
  points: number;
  hardModeOnly: boolean;
}

export interface UrgeLog {
  id: string;
  timestamp: string; // ISO
  type: string;
  completedIntervention: boolean;
}

export interface BadHabit {
  id: string;
  timestamp: string; // ISO
  type: 'scrolling' | 'porn' | 'junk_food' | 'sugar' | 'gaming' | 'alcohol' | 'caffeine' | 'other';
  debtPoints: number;
}

export interface DailyCheckIn {
  date: string; // YYYY-MM-DD
  timestamp: string; // ISO
  sleep: number; // 1–5
  energy: number; // 1–5
  mood: number; // 1–5
  note?: string;
  dismissed?: boolean; // user dismissed the "done" confirmation card on Today tab
}

export interface DailyLog {
  date: string; // YYYY-MM-DD
  completedTasks: string[];
  dopamineScore: number; // 0–100
  urges: UrgeLog[];
  badHabits?: BadHabit[];
  checkIn?: DailyCheckIn;
  challengesCompletedToday?: string[];
}

export type TabId = 'today' | 'progress' | 'focus' | 'settings';

export type UrgeType =
  | 'scrolling'
  | 'porn'
  | 'junk_food'
  | 'sugar'
  | 'gaming'
  | 'alcohol'
  | 'caffeine'
  | 'other';
