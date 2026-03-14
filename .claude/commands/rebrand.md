# Rebrand This Application

You are helping the user rebrand this Laravel Blueprint starter template into their own application.

## Arguments

$ARGUMENTS

## Step 1 — Gather inputs

If `$ARGUMENTS` is empty, ask the user for these two things before doing anything else:

1. **App name** — What should this app be called? (e.g. `TaskFlow`)
2. **App goal** — What does the app do? Write 1–2 sentences suitable for an SEO meta description. (e.g. `A collaborative task management platform for remote teams. Organise projects, assign tasks, and ship faster.`)

If `$ARGUMENTS` was provided, parse it as:
- First quoted value = app name
- Second quoted value = app goal / description

Once you have both values, proceed.

---

## Step 2 — Update every branded file

Work through each file below in order. Make all edits, then commit at the end.

### `.env`

- Change `APP_NAME` to the new app name:
  ```
  APP_NAME="<NEW NAME>"
  ```
- If `.env` does not exist, create it from `.env.example` first (`cp .env.example .env`), then update `APP_NAME`.

---

### `resources/views/app.blade.php`

Replace all occurrences of `'Laravel Blueprint'` (the fallback string) with `'<NEW NAME>'`.

Also update the description and keywords meta tags to reflect the app goal:
```blade
<meta name="description" content="{{ config('app.name', '<NEW NAME>') }} — <APP GOAL>">
<meta name="keywords" content="<3-5 relevant keywords based on the goal>">
```

And update the OG/Twitter description tags to match:
```blade
<meta property="og:description" content="<APP GOAL>">
<meta name="twitter:description" content="<APP GOAL>">
```

---

### `resources/js/app.ts`

Change the progress bar colour from purple to black:
```ts
progress: {
    color: '#000000',
    showSpinner: true
},
```

---

### `resources/js/pages/Auth/Login.vue`

Update the fallback string in the computed:
```js
const appName = computed(() => page.props.appName ?? '<NEW NAME>');
```

---

### `resources/js/pages/Auth/Register.vue`

Same as Login.vue — update the computed fallback.

---

### `resources/js/pages/Auth/ResetPassword.vue`

Same — update the computed fallback.

---

### `resources/js/pages/Auth/ForgotPassword.vue`

This file has a hardcoded span. Update it to be dynamic like the other auth pages:

In `<script setup>`, add:
```js
import { computed } from 'vue';
const page = usePage(); // usePage is already imported
const appName = computed(() => page.props.appName ?? '<NEW NAME>');
```

In the template, change:
```vue
<span class="text-xl font-bold text-black">
    Laravel Blueprint
</span>
```
to:
```vue
<span class="text-xl font-bold text-black">
    {{ appName }}
</span>
```

---

### `resources/js/pages/Welcome.vue`

Update the computed fallback:
```js
const appName = computed(() => page.props.appName ?? '<NEW NAME>');
```

---

### `resources/js/pages/Dashboard.vue`

Update the welcome heading:
```vue
<h3 class="text-lg font-medium text-gray-900 mb-4">Welcome to <NEW NAME></h3>
```

Or make it dynamic using the shared appName prop — add `usePage` + `computed` imports if not already present, and use `{{ appName }}`.

---

### `resources/js/Layouts/AuthenticatedLayout.vue`

The nav logo is hardcoded. Make it dynamic:

In `<script setup>`, add:
```js
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3'; // already imported as Link is there, add usePage
const page = usePage();
const appName = computed(() => page.props.appName ?? '<NEW NAME>');
```

In the template, change:
```vue
<span class="text-xl font-bold text-black">
    Laravel Blueprint
</span>
```
to:
```vue
<span class="text-xl font-bold text-black">
    {{ appName }}
</span>
```

---

### `resources/js/Components/ApplicationLogo.vue`

Same — make it dynamic:

```vue
<template>
    <div class="text-xl font-bold text-black">
        {{ appName }}
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
const page = usePage();
const appName = computed(() => page.props.appName ?? '<NEW NAME>');
</script>
```

---

## Step 3 — Commit

```bash
git add -A
git commit -m "rebrand: rename app to <NEW NAME>"
```

---

## Step 4 — Remind about manual assets

After committing, tell the user:

> **Manual steps remaining** — these binary files can't be changed automatically:
>
> | File | Purpose |
> |------|---------|
> | `public/favicon.ico` | Browser tab icon |
> | `public/favicon.svg` | Scalable icon (currently a generic "B" placeholder) |
> | `public/apple-touch-icon.png` | iOS home screen icon (180×180) |
> | `public/images/og-image.png` | Social share image (1200×630) |
>
> Replace these with your own branded assets to complete the rebrand.
