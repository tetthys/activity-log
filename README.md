# tetthys/activity-log

A **query-first activity logging system** for PHP 8.x, built around **Enums and Attributes**.

This package provides a clean alternative to the “one table per action” pattern by combining:

- a single append-only activity log
- attribute-driven validation and policy
- counter-backed fast reads for analytics

The core is framework-agnostic, with an optional **Laravel 12 integration**.

---

## Why this exists

Most applications eventually need to answer questions like:

- How many times did a user perform an action?
- How often does this happen per day?
- How many times did a user interact with a specific resource?

Traditional logging approaches make these questions expensive by forcing
full table scans or ad-hoc aggregation queries.

**tetthys/activity-log** is designed so that:

- actions are explicitly defined
- counting rules are declared up front
- reads are fast by default

---

## Core Ideas (Quick Overview)

### 1. Actions are Enums

Instead of logging arbitrary strings, every activity is a **typed enum case**.

Each case declares:
- its public name
- whether an actor is required
- whether a target object is required
- what metadata is allowed
- how the action should be counted

The enum becomes the **single source of truth**.

---

### 2. Two data layers

| Layer | Purpose |
|-----|--------|
| `activity_logs` | Append-only event log (auditing, debugging, history) |
| `activity_counters` | Pre-aggregated counters for fast queries |

The log table is never scanned for common queries.

---

### 3. Queryability is built in

Actions explicitly declare what should be counted:
- per actor
- per object
- per actor + object
- per time bucket (day, hour)

This makes common analytics **O(1)** or small range reads.

---

## Defining Actions (Generic Example)

```php
<?php

use Tetthys\ActivityLog\Attributes\ActivityAction;
use Tetthys\ActivityLog\Attributes\Actor;
use Tetthys\ActivityLog\Attributes\ObjectRef;
use Tetthys\ActivityLog\Attributes\MetaSchema;
use Tetthys\ActivityLog\Attributes\CountBy;
use Tetthys\ActivityLog\Attributes\Uniq;

enum AppAction
{
    #[ActivityAction(
        name: 'resource.view',
        category: 'interaction',
        description: 'A resource was viewed',
        version: 1
    )]
    #[Actor(type: 'user', required: true)]
    #[ObjectRef(type: 'resource', required: true)]
    #[MetaSchema(key: 'source', type: 'string', maxLen: 32)]
    #[CountBy(dimension: 'actor', bucket: 'all')]
    #[CountBy(dimension: 'actor', bucket: 'day')]
    #[CountBy(dimension: 'actor_object', bucket: 'all')]
    #[Uniq(mode: 'trace', ttlSeconds: 300)]
    case ResourceView;
}
````

What this definition gives you automatically:

* actor and object validation
* metadata schema enforcement
* idempotent logging (same request won’t double-count)
* per-user totals
* per-user daily counts
* per-user-per-resource counts

No additional query logic is required.

---

## Laravel 12 Usage

### 1. Install

```bash
composer require tetthys/activity-log
```

Laravel auto-discovers the service provider.

---

### 2. Publish config and migrations

```bash
php artisan vendor:publish --tag=activity-log-config
php artisan vendor:publish --tag=activity-log-migrations
php artisan migrate
```

This creates:

* `activity_logs`
* `activity_counters`

---

### 3. Logging an action

```php
use Tetthys\ActivityLog\Contracts\ActivityLogger;
use Tetthys\ActivityLog\Model\ActorRef;
use Tetthys\ActivityLog\Model\ObjectReference;

$logger->log(
    action: AppAction::ResourceView,
    actor: new ActorRef('user', (string) $user->id),
    object: new ObjectReference('resource', (string) $resourceId),
    metadata: ['source' => 'web'],
);
```

That single call:

* validates the action definition
* sanitizes metadata
* inserts into `activity_logs`
* updates all declared counters atomically

---

## Fast Queries (What makes this useful)

Laravel binds a counter-backed stats service:

```php
use Tetthys\ActivityLog\Contracts\ActivityStats;
```

### Total count per user

```php
$count = $stats->countForActor(
    AppAction::ResourceView,
    new ActorRef('user', (string) $user->id),
);
```

---

### Daily time series

```php
$series = $stats->seriesForActor(
    AppAction::ResourceView,
    new ActorRef('user', (string) $user->id),
    bucket: 'day',
    from: now()->subDays(6)->startOfDay()->toImmutable(),
    to: now()->startOfDay()->toImmutable(),
);
```

---

### Per user + per resource count

```php
$count = $stats->countForActorObject(
    AppAction::ResourceView,
    new ActorRef('user', (string) $user->id),
    new ObjectReference('resource', (string) $resourceId),
);
```

All of these queries read from `activity_counters`, not the raw log table.

---

## Optional: Request Context Automation

The Laravel adapter includes middleware that captures:

* IP address
* User agent
* request trace ID

Register the middleware:

```php
->withMiddleware(function ($middleware) {
    $middleware->web([
        \Tetthys\ActivityLog\Integration\Laravel\Http\Middleware\ActivityRequestContextMiddleware::class,
    ]);
});
```

Then use the contextual logger:

```php
$ctxLogger->log(
    AppAction::ResourceView,
    actor: new ActorRef('user', (string) $user->id),
    object: new ObjectReference('resource', (string) $resourceId),
);
```

---

## Design Principles

* Actions are contracts, not strings
* Logging rules are explicit and declarative
* Writes are correct and idempotent
* Reads are fast by default
* Analytics should never require log scans

This package is intentionally opinionated toward **systems that need reliable usage analytics**.

---

## License

MIT
