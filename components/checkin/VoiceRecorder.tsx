'use client';

import { useEffect, useRef, useState } from 'react';
import { useT } from '@/hooks/useT';

const MAX_SECONDS = 60;

interface Props {
  value: { dataUri: string; durationMs: number } | null;
  onChange: (v: { dataUri: string; durationMs: number } | null) => void;
}

function pickMimeType(): string {
  const candidates = ['audio/mp4', 'audio/aac', 'audio/webm;codecs=opus', 'audio/webm'];
  for (const c of candidates) {
    if (typeof MediaRecorder !== 'undefined' && MediaRecorder.isTypeSupported(c)) return c;
  }
  return '';
}

async function blobToDataUri(blob: Blob): Promise<string> {
  return new Promise((resolve, reject) => {
    const r = new FileReader();
    r.onload = () => resolve(r.result as string);
    r.onerror = reject;
    r.readAsDataURL(blob);
  });
}

function formatSec(ms: number): string {
  const s = Math.round(ms / 1000);
  return `0:${String(s).padStart(2, '0')}`;
}

export default function VoiceRecorder({ value, onChange }: Props) {
  const t = useT();
  const [state, setState] = useState<'idle' | 'recording' | 'denied' | 'unsupported'>('idle');
  const [elapsed, setElapsed] = useState(0);
  const [isPlaying, setIsPlaying] = useState(false);
  const recorderRef = useRef<MediaRecorder | null>(null);
  const chunksRef = useRef<Blob[]>([]);
  const streamRef = useRef<MediaStream | null>(null);
  const startedAtRef = useRef<number>(0);
  const tickRef = useRef<number | null>(null);
  const audioRef = useRef<HTMLAudioElement | null>(null);

  useEffect(() => {
    if (typeof MediaRecorder === 'undefined' || !navigator.mediaDevices?.getUserMedia) {
      setState('unsupported');
    }
  }, []);

  useEffect(() => () => {
    if (tickRef.current) window.clearInterval(tickRef.current);
    streamRef.current?.getTracks().forEach((tr) => tr.stop());
  }, []);

  const start = async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      streamRef.current = stream;
      const mimeType = pickMimeType();
      const rec = mimeType ? new MediaRecorder(stream, { mimeType }) : new MediaRecorder(stream);
      recorderRef.current = rec;
      chunksRef.current = [];
      rec.ondataavailable = (e) => { if (e.data.size > 0) chunksRef.current.push(e.data); };
      rec.onstop = async () => {
        const blob = new Blob(chunksRef.current, { type: rec.mimeType || 'audio/webm' });
        const uri = await blobToDataUri(blob);
        const dur = Date.now() - startedAtRef.current;
        onChange({ dataUri: uri, durationMs: dur });
        streamRef.current?.getTracks().forEach((tr) => tr.stop());
        streamRef.current = null;
      };
      startedAtRef.current = Date.now();
      rec.start();
      setState('recording');
      setElapsed(0);
      tickRef.current = window.setInterval(() => {
        const ms = Date.now() - startedAtRef.current;
        setElapsed(ms);
        if (ms >= MAX_SECONDS * 1000) stop();
      }, 100);
    } catch (e) {
      console.error('[Voice] mic denied', e);
      setState('denied');
    }
  };

  const stop = () => {
    if (tickRef.current) { window.clearInterval(tickRef.current); tickRef.current = null; }
    recorderRef.current?.stop();
    recorderRef.current = null;
    setState('idle');
  };

  const togglePlay = () => {
    if (!value || !audioRef.current) return;
    if (isPlaying) {
      audioRef.current.pause();
      audioRef.current.currentTime = 0;
      setIsPlaying(false);
    } else {
      audioRef.current.play();
      setIsPlaying(true);
    }
  };

  if (state === 'unsupported') return null;

  return (
    <div className="mb-4">
      <p className="text-stone-700 font-semibold text-sm mb-2">{t.voiceNoteLabel}</p>

      {value && state !== 'recording' && (
        <div className="flex items-center gap-3 bg-stone-50 rounded-2xl px-3 py-2.5">
          <button
            type="button"
            onClick={togglePlay}
            className="w-9 h-9 rounded-full flex items-center justify-center text-white"
            style={{ background: '#5B8A5E' }}
            aria-label={isPlaying ? t.voiceNotePause : t.voiceNotePlay}
          >
            <span className="text-base leading-none">{isPlaying ? '◼' : '▶'}</span>
          </button>
          <div className="flex-1">
            <p className="text-stone-700 text-xs font-semibold tabular-nums">{formatSec(value.durationMs)}</p>
            <p className="text-stone-400 text-[10px]">{t.voiceNoteSaved}</p>
          </div>
          <button
            type="button"
            onClick={() => { onChange(null); setIsPlaying(false); }}
            className="text-stone-400 active:text-stone-600 text-xs font-medium px-2 py-1"
          >
            {t.voiceNoteDelete}
          </button>
          <audio
            ref={audioRef}
            src={value.dataUri}
            onEnded={() => setIsPlaying(false)}
            preload="metadata"
          />
        </div>
      )}

      {!value && state !== 'recording' && (
        <button
          type="button"
          onClick={start}
          className="w-full flex items-center justify-center gap-2 bg-stone-50 active:bg-stone-100 rounded-2xl py-3 text-stone-600 font-semibold text-sm border border-stone-100"
        >
          <span className="w-2 h-2 rounded-full bg-red-400 inline-block" />
          {t.voiceNoteRecord}
        </button>
      )}

      {state === 'recording' && (
        <button
          type="button"
          onClick={stop}
          className="w-full flex items-center justify-between bg-red-50 active:bg-red-100 rounded-2xl px-4 py-3 text-red-600 font-semibold text-sm"
        >
          <span className="flex items-center gap-2">
            <span className="w-2 h-2 rounded-full bg-red-500 inline-block animate-pulse" />
            {t.voiceNoteStop}
          </span>
          <span className="text-xs font-bold tabular-nums">{formatSec(elapsed)} / 1:00</span>
        </button>
      )}

      {state === 'denied' && (
        <p className="text-amber-600 text-xs mt-2">{t.voiceNoteDenied}</p>
      )}
    </div>
  );
}
