# Claude Code Build Brief – Organic Task App

Use this as your master instruction file or as the basis for a `CLAUDE.md` in the project root.

---

## Goal

Bu([anthropic.com](https://www.anthropic.com/product/claude-code?utm_source=chatgpt.com))ect management app with:

- Manager and Staff roles
- Managers can assign tasks
- Staff can also create tasks
- Internal task management interface
- Public read-only Kanban dashboard for display on a monitor
- Real-time updates on the dashboard
- Clean, premium UI inspired by organic farming

The app should be optimized for maintainability, fast MVP delivery, and Laravel-native architecture.

---

## Required Tech Stack

Use the following stack unless there is a strong technical reason not to:

- Laravel
- Livewire
- Alpine.js
- Tailwind CSS
- MySQL or PostgreSQL
- Laravel Breeze for auth
- Spatie Laravel Permission for roles/permissions
- Laravel Reverb for real-time updates
- Laravel Echo for WebSocket listeners

Do not introduce React, Vue, or a separate frontend SPA unless explicitly requested.

---

## Product Summary

This app is a project and task management system for an organic farming-themed workflow.

There are 3 audiences:

1. **Manager**
   - can create tasks
   - can assign tasks
   - can edit any task
   - can move tasks across statuses
   - can manage projects

2. **Staff**
   - can create tasks
   - can edit tasks they created
   - can update tasks assigned to them
   - can view projects and board

3. **Public Viewer**
   - can only view a read-only public Kanban board
   - should see only tasks marked public
   - should not see comments, internal notes, or admin controls

---

## Design Direction

The UI should feel:

- premium
- clean
- calm
- organic
- monitor-friendly for the public dashboard

Visual inspiration:

- soft greens
- earth tones
- lots of whitespace
- rounded task cards
- subtle motion
- no top navigation on the public monitor dashboard

Public Kanban status labels should be farming-inspired:

- Seeds
- Planted
- Growing
- Harvesting
- Harvested

Internal system can use normalized status keys such as:

- backlog
n- todo
- in_progress
- review
- done

Map those internal keys to public-facing labels.

---

## Build Rules

1. Build the app module by module.
2. After each module:
   - run migrations if needed
   - run tests
   - summarize what was completed
   - list next steps
3. Keep business logic out of Livewire components when possible.
4. Prefer service classes for task movement, ordering, and board logic.
5. Do not overengineer.
6. Prioritize a working MVP first.
7. Use policies and permissions cleanly.
8. Keep the public dashboard isolated from internal/private data.
9. Use sparse ordering for Kanban sort order (e.g. 100, 200, 300).
10. Broadcast only changed task payloads, not full-board refreshes.

---

## Suggested Module Plan

### Module 1 – Project Setup

Tasks:
- create fresh Laravel project
- install Breeze
- install Livewire
- install Tailwind if not already present
- configure database
- configure environment
- install Spatie Laravel Permission
- install broadcasting/Reverb support
- set up base layout

Deliverables:
- working Laravel app
- auth screens
- basic dashboard route
- role package installed

---

### Module 2 – Roles and Permissions

Tasks:
- seed roles: manager, staff
- define permissions for tasks and projects
- assign policies or permission checks
- create a default seeded manager account

Deliverables:
- role/permission structure
- protected routes
- manager/staff access rules

---

### Module 3 – Projects Module

Tasks:
- create projects migration, model, factory, seeder
- project CRUD
- project visibility flag if needed
- internal project index and detail pages

Suggested fields:
- name
- slug
- description
- is_public
- created_by

Deliverables:
- projects can be created and managed

---

### Module 4 – Tasks Module

Tasks:
- create tasks migration and model
- define relationships
- build create/edit/delete task flows
- assign task to user
- track creator and assignee

Suggested fields:
- project_id
- title
- description
- created_by
- assigned_to
- status
- priority
- due_date
- is_public
- sort_order

Deliverables:
- tasks CRUD works
- manager and staff permissions enforced

---

### Module 5 – Internal Kanban Board

Tasks:
- build board page with columns
- group tasks by status
- create Livewire components:
  - BoardPage
  - KanbanColumn
  - TaskCard
  - TaskModal
- support drag and drop ordering
- save column and order changes

Implementation notes:
- use sparse sort ordering
- use a service class like `TaskBoardService`
- keep drag/drop logic testable

Deliverables:
- interactive internal Kanban board

---

### Module 6 – Public Dashboard

Tasks:
- create monitor-friendly public route
- remove navigation from public layout
- render only public tasks
- show read-only Kanban board
- use farming-themed status labels
- add summary metrics above board

Public page should include:
- project selector if needed
- live indicator
- task counts
- beautiful columns
- no edit controls
- no comments
- no private metadata

Deliverables:
- public display-ready dashboard

---

### Module 7 – Real-Time Updates

Tasks:
- configure Laravel Reverb
- configure Laravel Echo
- create broadcast events:
  - TaskCreated
  - TaskUpdated
  - TaskMoved
  - TaskDeleted
- define channels:
  - private-board.{projectId}
  - public-board.{projectId}
- emit only safe public payloads on public channels
- update board UI live without page refresh

Deliverables:
- multiple viewers see task changes live
- monitor dashboard updates automatically

---

### Module 8 – Comments and Activity Log

Tasks:
- create comments migration/model
- create activity_logs migration/model
- log task moves, edits, assignments
- show comments/activity only in internal task modal

Deliverables:
- internal collaboration features
- no public leakage

---

### Module 9 – UI Polish

Tasks:
- refine spacing, typography, colors
- premium task cards
- live animations for task movement
- empty states
- loading states
- responsive behavior
- monitor dashboard optimization

Deliverables:
- production-quality presentation

---

### Module 10 – Testing and Hardening

Tasks:
- feature tests for auth and permissions
- tests for task creation and assignment
- tests for task movement logic
- tests for public visibility rules
- tests for policies
- cleanup and refactor duplicated logic

Deliverables:
- stable MVP

---

## Data Model

### users
Standard Laravel users table.

### projects
- id
- name
- slug
- description
- is_public
- created_by
- timestamps

### tasks
- id
- project_id
- title
- description
- created_by
- assigned_to
- status
- priority
- due_date
- is_public
- sort_order
- timestamps
- soft deletes optional

### comments
- id
- task_id
- user_id
- body
- timestamps

### activity_logs
- id
- task_id
- user_id
- action
- old_value json nullable
- new_value json nullable
- timestamps

---

## Status Mapping

Internal status keys:
- backlog
- todo
- in_progress
- review
- done

Public labels:
- backlog => Seeds
- todo => Planted
- in_progress => Growing
- review => Harvesting
- done => Harvested

---

## Permissions Summary

### Manager
- view projects
- create projects
- update projects
- create task
- assign task
- update any task
- move any task
- delete task

### Staff
- view projects
- create task
- update own task
- move assigned task
- comment on task

### Public
- view public dashboard only

---

## Service Layer Suggestions

Create service classes such as:

- `TaskBoardService`
- `TaskOrderingService`
- `TaskVisibilityService`
- `TaskBroadcastPayloadService`

Responsibilities:

### TaskBoardService
- create task
- update task
- move task
- assign task
- toggle visibility

### TaskOrderingService
- calculate sparse sort order
- move within same column
- move between columns
- rebalance if needed

### TaskBroadcastPayloadService
- return safe public payload
- return richer internal payload

---

## Real-Time Behavior

When a task is changed:

1. validate permissions
2. update DB in transaction
3. write activity log
4. broadcast task event
5. update connected dashboards in real time

For public broadcasts, include only:
- id
- project_id
- title
- status
- priority
- due_date
- public assignee display if allowed
- updated_at

Do not include:
- comments
- internal notes
- audit details
- creator private metadata

---

## UI Requirements for Public Monitor Dashboard

Requirements:
- no top navigation
- full-screen friendly
- large clear typography
- summary metrics strip
- wide column spacing
- elegant live-updating Kanban view
- subtle pulsing live indicator
- monitor-safe contrast
- read-only

Optional enhancements:
- auto-refresh fallback if WebSocket disconnects
- rotate between projects
- full-screen mode styling

---

## Development Workflow Instructions for Claude Code

When implementing this app:

1. Work one module at a time.
2. Before coding, explain the module goal in a few lines.
3. Then implement the code.
4. After coding:
   - show changed files
   - explain what was done
   - run tests or checks
   - note any migration/seed command needed
5. Ask for approval only if a major architectural choice must change.
6. Prefer safe, incremental edits.
7. Keep naming clean and consistent.

---

## First Task to Execute

Start with **Module 1 – Project Setup**.

Steps:
- initialize Laravel app
- configure auth with Breeze
- install Livewire
- install Spatie Permission
- install Reverb broadcasting support
- prepare base layout and dashboard shell
- create a seeded manager account
- verify app boots successfully

After Module 1 is complete:
- summarize the work
- list exact commands run
- identify the next module

---

## Output Style

For each module, output:

- Objective
- Files created/updated
- Commands run
- Code summary
- How to test
- Next module

---

## Instruction to Claude Code

Build this application as a clean Laravel MVP with premium UI foundations. Work sequentially, keep the implementation practical, and prioritize a usable, monitor-friendly public Kanban dashboard with real-time updates.

