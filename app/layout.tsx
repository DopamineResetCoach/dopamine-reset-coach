import type { Metadata, Viewport } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import DirectionProvider from "@/components/DirectionProvider";

const inter = Inter({
  variable: "--font-inter",
  subsets: ["latin"],
  display: "swap",
});

export const metadata: Metadata = {
  title: "Dopamine Reset Coach",
  description:
    "Reset your dopamine system, regain focus, and rebuild natural motivation.",
  manifest: "/manifest.json",
  appleWebApp: {
    capable: true,
    statusBarStyle: "default",
    title: "Reset Coach",
  },
};

export const viewport: Viewport = {
  width: "device-width",
  initialScale: 1,
  maximumScale: 1,
  userScalable: false,
  themeColor: "#F5F0EB",
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" className={inter.variable}>
      <body>
        <DirectionProvider />
        {children}
      </body>
    </html>
  );
}
