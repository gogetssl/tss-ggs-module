# UI/UX Manifesto (PHP + Smarty Edition)

## Technical Constraints (CRITICAL)
- Templates use `{ }` for Smarty tags.
- If you write Tailwind or inline CSS that uses brackets, ALWAYS wrap them in `{literal}...{/literal}` or use spaces after brackets so Smarty doesn't crash.
- Do NOT mix PHP logic with presentation. Keep templates clean.

## Visual & Aesthetic Direction
- Style: Premium Minimalist Editorial (Inspired by Stripe & Vercel documentation).
- Grid & Layout: Strict Bento-grid or Asymmetric layout using CSS Grid/Flexbox.
- Whitespace: Double the padding you think you need (`p-8` instead of `p-4`). Give elements room to breathe.

## UI/UX Rules of Polish
1. "Typography First": Use stark contrast between huge, crisp headers (font-light, tracking-tight) and small, quiet metadata (text-slate-400).
2. Clean Containers: No heavy shadows or complex rounded corners. Use subtle 1px borders (`border-slate-100`) or alternating soft backgrounds (`bg-slate-50`) to separate sections.
3. Interactive States: Every button and interactive row must have a premium transition (`transition-all duration-200`) and explicit hover/focus states.
