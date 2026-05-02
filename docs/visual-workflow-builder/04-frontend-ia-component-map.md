# Frontend IA and Component Map (React + TypeScript)

## Frontend Goals

- Build a visual-first workflow editor with low cognitive load.
- Keep state predictable under frequent drag/drop and autosave.
- Separate canvas editing concerns from runtime/analytics concerns.

## Route Information Architecture

- `/workflows`
- `/workflows/:workflowId/editor`
- `/workflows/:workflowId/analytics`
- `/contacts`
- `/templates` (placeholder for post-MVP)
- `/settings/integrations`

## Editor Page Layout

1. Left Sidebar: app navigation, workspace switcher.
2. Top Bar: workflow name, status pill, save/preview/publish actions.
3. Center Canvas: React Flow nodes, edges, minimap, controls.
4. Right Panel:
   - Block Library mode (no selection)
   - Block Config mode (selected node)

## State Management (Zustand)

## `useWorkflowEditorStore`

- `workflow`: id, name, status, version, timezone
- `nodes`: React Flow node array
- `edges`: React Flow edge array
- `selectedNodeId`
- `validationErrors`
- `history`: undo/redo stacks
- `dirty`: unsaved changes flag
- `lastSavedAt`

Actions:
- `setNodes`, `setEdges`, `addNode`, `updateNodeConfig`, `deleteNode`
- `connectNodes`, `deleteEdge`
- `selectNode`, `clearSelection`
- `undo`, `redo`
- `applyValidationResult`
- `markSaved`

## `useExecutionMonitorStore`

- `executionFilters`
- `executionList`
- `selectedExecution`
- `historyByExecutionId`

## `useContactsStore`

- `contacts`
- `tags`
- `segments`
- `importJobs`

## Component Ownership Map

## Shell and Navigation

- `WorkflowEditorPage`
- `WorkflowTopBar`
- `AppSidebar`
- `WorkspaceSwitcher`

## Canvas System

- `WorkflowCanvas`
- `FlowNodeRenderer` (node type switch)
- `FlowEdgeRenderer`
- `CanvasMiniMap`
- `CanvasControls`
- `CanvasGridBackground`

## Node Components (V1)

- `TriggerNodeCard`
- `SendMessageNodeCard`
- `SendSequenceNodeCard`
- `DelayNodeCard`
- `BranchNodeCard`
- `TagNodeCard`
- `GoalNodeCard`
- `EndNodeCard`

## Right Panel

- `RightPanelRoot`
- `BlockLibraryPanel`
- `BlockSearchInput`
- `BlockCategorySection`
- `BlockConfigPanel`
- `NodeSettingsFormFactory`

## Message Editing

- `MessageEditor`
- `VariablePicker`
- `ChannelPreviewTabs`
- `EmailPreview`
- `WhatsAppPreview`
- `SubjectInput`
- `AttachmentPicker`

## Validation and Publish UX

- `WorkflowValidationBanner`
- `NodeErrorBadges`
- `PublishModal`
- `PreviewRunnerModal`

## Suggested Folder Structure

```text
resources/js/workflows/
  api/
    workflows.api.ts
    contacts.api.ts
    analytics.api.ts
  components/
    editor/
      WorkflowEditorPage.tsx
      WorkflowTopBar.tsx
      sidebar/
      canvas/
      right-panel/
      nodes/
      validation/
      preview/
    analytics/
    contacts/
  stores/
    workflowEditor.store.ts
    executionMonitor.store.ts
    contacts.store.ts
  schemas/
    workflow.schema.ts
    nodes/
  hooks/
    useAutosave.ts
    useWorkflowValidation.ts
    useKeyboardShortcuts.ts
  types/
    workflow.types.ts
```

## Node Config Schema Strategy

- Use `zod` schemas per node type for form validation and API safety.
- Shared base:
  - `id`, `type`, `label`, `position`
- Per-type `config` schema:
  - Trigger: mode + filter config.
  - Send Message: channel + content + template metadata.
  - Delay: duration mode + value.
  - Branch: condition expression and yes/no targets.

## Autosave and Concurrency

- Debounce autosave at 10 seconds while `dirty=true`.
- Include workflow `version` in update request.
- On `409 conflict`:
  - freeze save,
  - show conflict dialog,
  - offer reload or copy local changes.

## Performance and Render Constraints

- Use memoized node components (`React.memo`).
- Keep node data minimal; avoid storing large editor payloads in node UI state.
- Virtualize heavy side lists (contacts/templates) outside canvas.
- Avoid global re-renders by using slice selectors in Zustand.

## Accessibility Baseline

- Keyboard shortcuts:
  - `Ctrl/Cmd+S` save draft
  - `Ctrl/Cmd+Z` undo
  - `Ctrl/Cmd+Shift+Z` redo
  - `Delete/Backspace` delete selected node/edge
- Focus ring and tab order for right panel forms.
- Color alone is not the only status indicator.

## Testing Strategy (Frontend)

- Unit tests:
  - node schema validation
  - store reducers/actions
  - variable token insertion
- Integration tests:
  - drag/add/connect/configure/publish flow
  - autosave + conflict handling
  - validation error surfacing
- E2E tests:
  - create and publish a workflow in under 15 minutes benchmark script.
