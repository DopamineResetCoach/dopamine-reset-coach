import { Task, UserProfile } from '@/types';
import type { Translations } from '@/lib/i18n';

type HabitKey = keyof UserProfile['habits'];

type TaskMeta = Omit<Task, 'title' | 'description' | 'tooltip'> & {
  titleKey: keyof Translations;
  descKey: keyof Translations;
  tipKey: keyof Translations;
  habitTag?: HabitKey;
};

const TASKS_META: TaskMeta[] = [
  { id: 'no_social_media', icon: '📵', points: 15, hardModeOnly: false, titleKey: 'taskNoSocialTitle',    descKey: 'taskNoSocialDesc',    tipKey: 'taskNoSocialTip' },
  { id: 'cold_shower',     icon: '🚿', points: 10, hardModeOnly: false, titleKey: 'taskColdShowerTitle',  descKey: 'taskColdShowerDesc',  tipKey: 'taskColdShowerTip' },
  { id: 'exercise',        icon: '🏃', points: 12, hardModeOnly: false, titleKey: 'taskExerciseTitle',    descKey: 'taskExerciseDesc',    tipKey: 'taskExerciseTip' },
  { id: 'sunlight',        icon: '☀️', points:  8, hardModeOnly: false, titleKey: 'taskSunlightTitle',    descKey: 'taskSunlightDesc',    tipKey: 'taskSunlightTip' },
  { id: 'deep_work',       icon: '🎯', points: 12, hardModeOnly: false, titleKey: 'taskDeepWorkTitle',    descKey: 'taskDeepWorkDesc',    tipKey: 'taskDeepWorkTip' },
  { id: 'journaling',      icon: '📓', points:  6, hardModeOnly: false, titleKey: 'taskJournalingTitle',  descKey: 'taskJournalingDesc',  tipKey: 'taskJournalingTip' },
  { id: 'no_junk_food',    icon: '🥗', points:  8, hardModeOnly: false, titleKey: 'taskEatCleanTitle',    descKey: 'taskEatCleanDesc',    tipKey: 'taskEatCleanTip' },
  { id: 'meditation',      icon: '🧘', points:  8, hardModeOnly: false, titleKey: 'taskMeditationTitle',  descKey: 'taskMeditationDesc',  tipKey: 'taskMeditationTip' },
  { id: 'early_sleep',     icon: '🌙', points: 10, hardModeOnly: false, titleKey: 'taskSleepTitle',       descKey: 'taskSleepDesc',       tipKey: 'taskSleepTip' },
  { id: 'no_caffeine',     icon: '☕', points:  8, hardModeOnly: true,  habitTag: 'caffeine', titleKey: 'taskNoCaffeineTitle',  descKey: 'taskNoCaffeineDesc',  tipKey: 'taskNoCaffeineTip' },
  { id: 'no_alcohol',      icon: '🍷', points:  8, hardModeOnly: true,  habitTag: 'alcohol',  titleKey: 'taskNoAlcoholTitle',   descKey: 'taskNoAlcoholDesc',   tipKey: 'taskNoAlcoholTip' },
  { id: 'read_book',       icon: '📚', points:  6, hardModeOnly: false, titleKey: 'taskReadTitle',        descKey: 'taskReadDesc',        tipKey: 'taskReadTip' },
  { id: 'walk_outside',    icon: '🌳', points:  8, hardModeOnly: false, titleKey: 'taskWalkOutsideTitle', descKey: 'taskWalkOutsideDesc', tipKey: 'taskWalkOutsideTip' },
  { id: 'gratitude',       icon: '🙏', points:  5, hardModeOnly: false, titleKey: 'taskGratitudeTitle',   descKey: 'taskGratitudeDesc',   tipKey: 'taskGratitudeTip' },
  { id: 'stretch_yoga',    icon: '🧘‍♂️', points:  6, hardModeOnly: false, titleKey: 'taskStretchTitle',     descKey: 'taskStretchDesc',     tipKey: 'taskStretchTip' },
  { id: 'hydrate',         icon: '💧', points:  4, hardModeOnly: false, titleKey: 'taskHydrateTitle',     descKey: 'taskHydrateDesc',     tipKey: 'taskHydrateTip' },
  { id: 'strength',        icon: '💪', points: 12, hardModeOnly: false, titleKey: 'taskStrengthTitle',    descKey: 'taskStrengthDesc',    tipKey: 'taskStrengthTip' },
  { id: 'sauna',           icon: '🧖', points: 10, hardModeOnly: false, titleKey: 'taskSaunaTitle',       descKey: 'taskSaunaDesc',       tipKey: 'taskSaunaTip' },
  { id: 'no_sugar',        icon: '🚫', points: 10, hardModeOnly: true,  habitTag: 'junkFood', titleKey: 'taskNoSugarTitle',     descKey: 'taskNoSugarDesc',     tipKey: 'taskNoSugarTip' },
  { id: 'no_porn',         icon: '🚫', points: 10, hardModeOnly: true,  habitTag: 'porn',     titleKey: 'taskNoPornTitle',      descKey: 'taskNoPornDesc',      tipKey: 'taskNoPornTip' },
  { id: 'no_nicotine',     icon: '🚭', points:  8, hardModeOnly: true,  titleKey: 'taskNoNicotineTitle',  descKey: 'taskNoNicotineDesc',  tipKey: 'taskNoNicotineTip' },
];

export const TASKS = TASKS_META;

export function getTasksForProfile(hardMode: boolean, habits?: UserProfile['habits']): TaskMeta[] {
  return TASKS_META.filter((task) => {
    if (!task.hardModeOnly) return true;
    if (hardMode) return true;
    if (task.habitTag && habits?.[task.habitTag]) return true;
    return false;
  });
}

export function getTranslatedTasks(
  t: Translations,
  hardMode: boolean,
  habits?: UserProfile['habits'],
): Task[] {
  return getTasksForProfile(hardMode, habits).map((meta) => ({
    id: meta.id,
    icon: meta.icon,
    points: meta.points,
    hardModeOnly: meta.hardModeOnly,
    title: t[meta.titleKey],
    description: t[meta.descKey],
    tooltip: t[meta.tipKey],
  }));
}
