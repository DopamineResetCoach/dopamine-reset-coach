export interface Quote {
  text: string;
  author: string;
}

export const QUOTES: Quote[] = [
  {
    text: "The secret of getting ahead is getting started.",
    author: "Mark Twain",
  },
  {
    text: "Between stimulus and response, there is a space. In that space lies our freedom.",
    author: "Viktor Frankl",
  },
  {
    text: "You do not rise to the level of your goals. You fall to the level of your systems.",
    author: "James Clear",
  },
  {
    text: "The present moment always will have been.",
    author: "Eckhart Tolle",
  },
  {
    text: "Discipline is the bridge between goals and accomplishment.",
    author: "Jim Rohn",
  },
  {
    text: "Every urge that passes unopposed makes the next one easier to face.",
    author: "Unknown",
  },
  {
    text: "Small daily improvements over time create stunning results.",
    author: "Robin Sharma",
  },
  {
    text: "The best time to plant a tree was twenty years ago. The second best time is now.",
    author: "Chinese Proverb",
  },
  {
    text: "Your brain is not your enemy. It's doing exactly what it was trained to do.",
    author: "Andrew Huberman",
  },
  {
    text: "Boredom is the threshold. On the other side is presence.",
    author: "Unknown",
  },
  {
    text: "Real motivation doesn't need a trigger. It's already there — you just have to uncover it.",
    author: "Unknown",
  },
  {
    text: "Comfort is the enemy of progress.",
    author: "P.T. Barnum",
  },
  {
    text: "The mind is everything. What you think, you become.",
    author: "Buddha",
  },
  {
    text: "If it doesn't challenge you, it doesn't change you.",
    author: "Fred DeVito",
  },
  {
    text: "Do the hard thing. Then notice how you feel.",
    author: "Unknown",
  },
  {
    text: "Suffering is the friction between what you want and what you accept.",
    author: "Unknown",
  },
  {
    text: "The cave you fear to enter holds the treasure you seek.",
    author: "Joseph Campbell",
  },
  {
    text: "Your future self is cheering you on from the other side of this moment.",
    author: "Unknown",
  },
  {
    text: "Scrolling is borrowing happiness from tomorrow.",
    author: "Unknown",
  },
  {
    text: "You are not your urges. You are the awareness that notices them.",
    author: "Unknown",
  },
  {
    text: "Stillness is not the absence of life. It's where life becomes visible.",
    author: "Unknown",
  },
  {
    text: "An unexamined life is not worth living.",
    author: "Socrates",
  },
  {
    text: "We suffer more in imagination than in reality.",
    author: "Seneca",
  },
  {
    text: "Slow is smooth. Smooth is fast.",
    author: "Navy SEALs saying",
  },
  {
    text: "The goal isn't happiness. The goal is freedom from compulsion.",
    author: "Unknown",
  },
  {
    text: "What you do every day matters more than what you do once in a while.",
    author: "Gretchen Rubin",
  },
  {
    text: "The dopamine hit from finishing hard work lasts longer than any scroll session.",
    author: "Unknown",
  },
  {
    text: "Your attention is the most valuable thing you own. Don't give it away for free.",
    author: "Unknown",
  },
  {
    text: "Rest when you're tired. Don't rest because you're avoiding discomfort.",
    author: "Unknown",
  },
  {
    text: "You don't need more willpower. You need a better environment.",
    author: "James Clear",
  },
];

export function getDailyQuote(): Quote {
  const dayOfYear = Math.floor(
    (Date.now() - new Date(new Date().getFullYear(), 0, 0).getTime()) /
      86400000
  );
  return QUOTES[dayOfYear % QUOTES.length];
}
