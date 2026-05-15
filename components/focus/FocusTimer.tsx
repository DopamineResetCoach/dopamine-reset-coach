'use client';

import { useState, useEffect, useRef, useCallback } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { useT } from '@/hooks/useT';

type Session = 'work' | 'short_break' | 'long_break';

const SESSION_MINUTES: Record<Session, number> = {
  work: 25,
  short_break: 5,
  long_break: 15,
};

const SESSION_COLORS: Record<Session, string> = {
  work: '#5B8A5E',
  short_break: '#6B9FD4',
  long_break: '#C9955A',
};

type SoundType = 'none' | 'white_noise' | 'rain' | 'forest' | 'binaural';

function createWhiteNoise(ctx: AudioContext): AudioBufferSourceNode {
  const bufferSize = ctx.sampleRate * 2;
  const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
  const data = buffer.getChannelData(0);
  for (let i = 0; i < bufferSize; i++) {
    data[i] = Math.random() * 2 - 1;
  }
  const source = ctx.createBufferSource();
  source.buffer = buffer;
  source.loop = true;
  return source;
}

function createRainSound(ctx: AudioContext): AudioBufferSourceNode {
  const bufferSize = ctx.sampleRate * 2;
  const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
  const data = buffer.getChannelData(0);
  let b0 = 0, b1 = 0, b2 = 0, b3 = 0, b4 = 0, b5 = 0;
  for (let i = 0; i < bufferSize; i++) {
    const white = Math.random() * 2 - 1;
    b0 = 0.99886 * b0 + white * 0.0555179;
    b1 = 0.99332 * b1 + white * 0.0750759;
    b2 = 0.96900 * b2 + white * 0.1538520;
    b3 = 0.86650 * b3 + white * 0.3104856;
    b4 = 0.55000 * b4 + white * 0.5329522;
    b5 = -0.7616 * b5 - white * 0.0168980;
    data[i] = (b0 + b1 + b2 + b3 + b4 + b5 + white * 0.5362) * 0.11;
  }
  const source = ctx.createBufferSource();
  source.buffer = buffer;
  source.loop = true;
  return source;
}

function createForestSound(ctx: AudioContext): OscillatorNode {
  const osc = ctx.createOscillator();
  osc.type = 'sine';
  osc.frequency.setValueAtTime(55, ctx.currentTime);
  osc.frequency.setValueAtTime(110, ctx.currentTime + 2);
  osc.frequency.setValueAtTime(55, ctx.currentTime + 4);
  return osc;
}

function createBinauralBeats(ctx: AudioContext): [OscillatorNode, OscillatorNode] {
  const base = 200;
  const beat = 10;
  const left = ctx.createOscillator();
  const right = ctx.createOscillator();
  left.type = 'sine';
  right.type = 'sine';
  left.frequency.value = base;
  right.frequency.value = base + beat;
  return [left, right];
}

function TimerRing({
  progress,
  color,
  timeStr,
  label,
}: {
  progress: number;
  color: string;
  timeStr: string;
  label: string;
}) {
  const r = 88;
  const circ = 2 * Math.PI * r;
  return (
    <div className="relative flex items-center justify-center my-2">
      <svg width="220" height="220" viewBox="0 0 220 220">
        <circle cx="110" cy="110" r={r} fill="none" stroke="#E8E3DC" strokeWidth="8" />
        <circle
          cx="110"
          cy="110"
          r={r}
          fill="none"
          stroke={color}
          strokeWidth="8"
          strokeLinecap="round"
          strokeDasharray={circ}
          strokeDashoffset={circ * (1 - progress)}
          transform="rotate(-90 110 110)"
          style={{ transition: 'stroke-dashoffset 1s linear' }}
        />
      </svg>
      <div className="absolute flex flex-col items-center">
        <span className="text-5xl font-bold tabular-nums text-stone-800">
          {timeStr}
        </span>
        <span
          className="text-sm font-semibold mt-1"
          style={{ color }}
        >
          {label}
        </span>
      </div>
    </div>
  );
}

export default function FocusTimer() {
  const t = useT();
  const { toggleTask } = useAppStore();
  const [session, setSession] = useState<Session>('work');
  const [running, setRunning] = useState(false);
  const [secondsLeft, setSecondsLeft] = useState(SESSION_MINUTES.work * 60);
  const [sessions, setSessions] = useState(0);
  const [sound, setSound] = useState<SoundType>('none');
  const [showSoundPicker, setShowSoundPicker] = useState(false);

  const audioCtxRef = useRef<AudioContext | null>(null);
  const soundNodeRef = useRef<AudioNode | null>(null);
  const gainRef = useRef<GainNode | null>(null);

  const sessionLabels: Record<Session, string> = {
    work: t.focusWork,
    short_break: t.focusShortBreak,
    long_break: t.focusLongBreak,
  };

  const sessionSubtitles: Record<Session, string> = {
    work: t.focusSubtitle,
    short_break: t.focusSubtitleShortBreak,
    long_break: t.focusSubtitleLongBreak,
  };

  const sessionHowBodies: Record<Session, string> = {
    work: t.focusHowBody,
    short_break: t.focusHowBodyShortBreak,
    long_break: t.focusHowBodyLongBreak,
  };

  const sounds: { id: SoundType; label: string; emoji: string }[] = [
    { id: 'none', label: t.focusNone, emoji: '🔇' },
    { id: 'white_noise', label: t.focusWhiteNoise, emoji: '🌫️' },
    { id: 'rain', label: t.focusRain, emoji: '🌧️' },
    { id: 'forest', label: t.focusForest, emoji: '🌲' },
    { id: 'binaural', label: t.focusBinaural, emoji: '🎵' },
  ];

  const totalSeconds = SESSION_MINUTES[session] * 60;
  const progress = 1 - secondsLeft / totalSeconds;
  const mins = Math.floor(secondsLeft / 60);
  const secs = secondsLeft % 60;
  const timeStr = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
  const color = SESSION_COLORS[session];

  const stopSound = useCallback(() => {
    if (soundNodeRef.current) {
      try {
        (soundNodeRef.current as AudioBufferSourceNode).stop?.();
        (soundNodeRef.current as OscillatorNode).stop?.();
      } catch {}
      soundNodeRef.current = null;
    }
  }, []);

  const startSound = useCallback(
    (type: SoundType) => {
      stopSound();
      if (type === 'none') return;

      if (!audioCtxRef.current) {
        audioCtxRef.current = new AudioContext();
      }
      const ctx = audioCtxRef.current;
      const gain = ctx.createGain();
      gain.gain.value = 0.15;
      gain.connect(ctx.destination);
      gainRef.current = gain;

      if (type === 'white_noise') {
        const node = createWhiteNoise(ctx);
        node.connect(gain);
        node.start();
        soundNodeRef.current = node;
      } else if (type === 'rain') {
        const node = createRainSound(ctx);
        const filter = ctx.createBiquadFilter();
        filter.type = 'lowpass';
        filter.frequency.value = 800;
        node.connect(filter);
        filter.connect(gain);
        node.start();
        soundNodeRef.current = node;
      } else if (type === 'forest') {
        gain.gain.value = 0.08;
        const node = createForestSound(ctx);
        node.connect(gain);
        node.start();
        soundNodeRef.current = node;
      } else if (type === 'binaural') {
        gain.gain.value = 0.1;
        const [left, right] = createBinauralBeats(ctx);
        const merger = ctx.createChannelMerger(2);
        left.connect(merger, 0, 0);
        right.connect(merger, 0, 1);
        merger.connect(gain);
        left.start();
        right.start();
        soundNodeRef.current = left;
      }
    },
    [stopSound]
  );

  useEffect(() => {
    if (!running) return;
    const timer = setInterval(() => {
      setSecondsLeft((s) => {
        if (s <= 1) {
          setRunning(false);
          if (session === 'work') {
            setSessions((n) => n + 1);
            toggleTask('deep_work');
          }
          return 0;
        }
        return s - 1;
      });
    }, 1000);
    return () => clearInterval(timer);
  }, [running, session, toggleTask]);

  const handleSessionChange = (s: Session) => {
    setSession(s);
    setSecondsLeft(SESSION_MINUTES[s] * 60);
    setRunning(false);
  };

  const handleToggle = () => {
    if (!running && sound !== 'none') startSound(sound);
    if (running) stopSound();
    setRunning((v) => !v);
  };

  const handleReset = () => {
    setRunning(false);
    stopSound();
    setSecondsLeft(SESSION_MINUTES[session] * 60);
  };

  const handleSoundChange = (s: SoundType) => {
    setSound(s);
    if (running) startSound(s);
    else stopSound();
    setShowSoundPicker(false);
  };

  return (
    <div
      className="min-h-screen bg-[#F5F0EB] pb-52 overflow-y-auto"
      style={{ animation: 'fade-in 0.3s ease-out' }}
    >
      <div className="max-w-sm mx-auto px-4 pt-12 pb-4">
        <h1 className="text-2xl font-bold text-stone-800 mb-1">{t.focusTitle}</h1>
        <p className="text-stone-400 text-sm mb-6">{sessionSubtitles[session]}</p>

        {/* Session type selector */}
        <div className="flex bg-stone-100 rounded-2xl p-1 mb-6">
          {(Object.keys(SESSION_MINUTES) as Session[]).map((id) => (
            <button
              key={id}
              onClick={() => handleSessionChange(id)}
              className={`flex-1 py-2 rounded-xl text-sm font-semibold transition-all ${
                session === id
                  ? 'bg-white text-stone-800 shadow-sm'
                  : 'text-stone-400'
              }`}
            >
              {sessionLabels[id]}
            </button>
          ))}
        </div>

        {/* Timer ring */}
        <div className="flex justify-center">
          <TimerRing
            progress={progress}
            color={color}
            timeStr={timeStr}
            label={sessionLabels[session]}
          />
        </div>

        {/* Controls */}
        <div className="flex items-center justify-center gap-4 mb-6">
          <button
            onClick={handleReset}
            className="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-stone-400 active:scale-90 transition-transform"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path
                d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
              />
              <path d="M3 3v5h5" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            </svg>
          </button>

          <button
            onClick={handleToggle}
            className="w-20 h-20 rounded-full flex items-center justify-center shadow-lg active:scale-95 transition-transform"
            style={{ backgroundColor: color }}
          >
            {running ? (
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <rect x="6" y="5" width="4" height="14" rx="1" fill="white" />
                <rect x="14" y="5" width="4" height="14" rx="1" fill="white" />
              </svg>
            ) : (
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M8 5l11 7-11 7V5z" fill="white" />
              </svg>
            )}
          </button>

          <button
            onClick={() => setShowSoundPicker((v) => !v)}
            className="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-stone-400 active:scale-90 transition-transform"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path
                d="M11 5L6 9H2v6h4l5 4V5z"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
              {sound !== 'none' && (
                <path
                  d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"
                  stroke={color}
                  strokeWidth="2"
                  strokeLinecap="round"
                />
              )}
            </svg>
          </button>
        </div>

        {/* Sound picker */}
        {showSoundPicker && (
          <div className="bg-white rounded-2xl p-4 mb-4 shadow-sm">
            <p className="text-stone-600 font-semibold text-sm mb-3">
              {t.focusAmbient}
            </p>
            <div className="grid grid-cols-5 gap-2">
              {sounds.map((s) => (
                <button
                  key={s.id}
                  onClick={() => handleSoundChange(s.id)}
                  className={`flex flex-col items-center gap-1 p-2 rounded-xl transition-all ${
                    sound === s.id
                      ? 'bg-[#5B8A5E]/8 border-2 border-[#5B8A5E]'
                      : 'bg-stone-50 border-2 border-transparent'
                  }`}
                >
                  <span className="text-xl">{s.emoji}</span>
                  <span className="text-xs text-stone-500 text-center leading-tight">
                    {s.label}
                  </span>
                </button>
              ))}
            </div>
          </div>
        )}

        {/* Session counter */}
        <div className="bg-white rounded-2xl p-4 mb-4">
          <div className="flex justify-between items-center mb-3">
            <p className="text-stone-600 font-semibold text-sm">{t.focusSessionsToday}</p>
            <p className="text-[#5B8A5E] font-bold text-sm">{sessions} / 4</p>
          </div>
          <div className="flex gap-2">
            {[0, 1, 2, 3].map((i) => (
              <div
                key={i}
                className={`flex-1 h-2 rounded-full ${
                  i < sessions ? 'bg-[#5B8A5E]' : 'bg-stone-100'
                }`}
              />
            ))}
          </div>
          {sessions >= 4 && (
            <p className="text-[#3D6640] text-sm mt-2 text-center font-medium">
              {t.focusCongrats}
            </p>
          )}
        </div>

        {/* Tips */}
        <div className="bg-stone-50 rounded-2xl p-4">
          <p className="text-stone-500 text-sm leading-relaxed">
            <strong className="text-stone-600">{t.focusHowTitle}</strong>{' '}
            {sessionHowBodies[session]}
          </p>
        </div>
      </div>
    </div>
  );
}
