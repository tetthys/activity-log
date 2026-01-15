# tetthys/activity-log (core)

Framework-agnostic activity log core (PHP 8.x) built around Enum + Attributes.

This ZIP contains **core only**. Laravel integration is intended to live under `src/Integration/Laravel`.

## Quick usage (in-memory)

- Define your actions as an enum with attributes.
- Compose a logger with `CounteringWriter` for fast reads.

See the conversation for a full usage snippet.
