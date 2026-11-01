# UI/UX Manifesto & Design System

## Aesthetic Direction
- Style: Strict Swiss Editorial / Minimalist High-End SaaS (Do NOT use generic AI cards or multi-colored gradients).
- Layout Philosophy: Asymmetric balance, editorial whitespace, and high contrast.

## Typography
- Headings: Playfair Display (Serif), tracking-tight, font-light.
- Body: Inter (Sans-serif), leading-relaxed, text-slate-600.

## Color Palette (Strictly enforce these Tailwind classes)
- Background: `bg-[#f8f9fa]` (Off-white, never pure white or default slate).
- Text Primary: `text-[#1a1a1a]` (Deep charcoal, never pure black).
- Accent: `bg-[#0f172a]` (Slate-900) for high-impact single moments.

## Rules of Execution
1. "Spend your boldness in one place": Only one component per viewport can have an eye-catching element (e.g., a large type or a subtle interaction). The rest must remain ultra-quiet.
2. No placeholders: Never draw generic "Feature 1, Feature 2" layout cards. Use realistic data layout.
3. Shadows & Borders: No heavy blurs or ambient colored shadows. Use crisp, 1px borders (`border-slate-200`) or zero borders with clean negative space.
