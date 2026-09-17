# AGENTS.md

## Project

- Frontend: Next.js App Router + TypeScript
- Backend: Laravel
- Database: PostgreSQL
- Styling: Tailwind CSS

## Development Rules

- Inspect existing implementation before writing new code.
- Reuse existing components, services, hooks, and utilities.
- Do not create duplicate abstractions.
- Do not install dependencies unless necessary.
- Do not change API contracts without explicit instruction.
- Make the smallest change required to complete the task.
- Do not refactor unrelated code.

## Frontend

- Follow existing design system.
- Use anti-slop principles.
- Avoid unnecessary gradients, glassmorphism, cards, badges, and decorative UI.
- Preserve responsive behavior.

## Backend

- Follow existing Laravel architecture.
- Reuse existing models, services, requests, resources, and policies.
- Avoid unnecessary migrations.
- Preserve backward compatibility.

## Token / Context Efficiency

- Do not scan the entire repository unnecessarily.
- Start from files directly related to the task.
- Search for existing implementations before creating new ones.
- Avoid repeatedly reading files already inspected unless they changed.
- Keep explanations concise unless analysis is requested.
