# Remove Letsmerge.it Branding & Add README Customisation Prompt

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove all "Letsmerge.it" / "Let's Merge It" references, dead GitHub OAuth code, broken AI service files, and app-specific composer packages; replace old favicon assets with generic placeholders; and add a README "Customise This Blueprint" prompt.

**Architecture:** Content/asset changes plus dead-code deletion. No new routes, controllers, or migrations needed. GitHub OAuth controllers and AI service files are fully deleted (not refactored) as they have no callers in this blueprint. Composer packages specific to Let's Merge It are removed.

**Tech Stack:** Laravel 11, Vue 3, Inertia.js, Blade templates, Tailwind CSS

---

## Chunk 1: Fix Letsmerge.it References in Source Files

### Task 1: Fix `app.blade.php` — remove old Letsmerge.it fallbacks and SEO cruft

**Files:**
- Modify: `resources/views/app.blade.php`

**Background:**
The file has four places where `'Letsmerge.it'` is the hardcoded fallback for `config('app.name', ...)`. It also contains old PR-generator SEO meta tags, an old Clarity analytics snippet, and references to `images/homepage.png` / `images/maker.png` (Let's Merge It screenshots).

- [ ] **Step 1: Read the file**

  Open `resources/views/app.blade.php` and confirm the four `Letsmerge.it` occurrences and the OG/Twitter image references.

- [ ] **Step 2: Replace all four `Letsmerge.it` fallbacks with `Laravel Blueprint`**

  Change every:
  ```blade
  config('app.name', 'Letsmerge.it')
  ```
  to:
  ```blade
  config('app.name', 'Laravel Blueprint')
  ```
  There are four of these: `<title>`, `<meta name="author">`, `<meta property="og:site_name">`, `<meta name="application-name">`.

- [ ] **Step 3: Replace old PR-generator description meta tags with generic placeholders**

  Replace the old `<meta name="description">` and `<meta name="keywords">` with generic blueprint ones:
  ```blade
  <meta name="description" content="{{ config('app.name', 'Laravel Blueprint') }} — a clean Laravel + Vue 3 + Inertia.js starter template.">
  <meta name="keywords" content="laravel, vue3, inertia, starter, template">
  ```

- [ ] **Step 4: Replace old OG/Twitter hard-coded titles and image references**

  Change:
  ```blade
  <meta property="og:title" content="Pull Request Generator - AI-Powered GitHub Workflow">
  ```
  to:
  ```blade
  <meta property="og:title" content="{{ config('app.name', 'Laravel Blueprint') }}">
  ```

  Change both Twitter and OG description metas to:
  ```blade
  <meta property="og:description" content="{{ config('app.name', 'Laravel Blueprint') }} — a clean Laravel + Vue 3 + Inertia.js starter template.">
  ```
  Same for `<meta name="twitter:description">` and `<meta name="twitter:title">`.

  Change both OG and Twitter image references from `images/homepage.png` to `images/og-image.png` (a generic placeholder — created in Task 4):
  ```blade
  <meta property="og:image" content="{{ asset('images/og-image.png') }}">
  <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">
  ```
  Update the alt text too:
  ```blade
  <meta property="og:image:alt" content="{{ config('app.name', 'Laravel Blueprint') }} Screenshot">
  <meta name="twitter:image:alt" content="{{ config('app.name', 'Laravel Blueprint') }} Screenshot">
  ```

- [ ] **Step 5: Remove the Clarity analytics script**

  Delete these lines entirely (they embed a Let's Merge It tracking ID):
  ```html
  <script type="text/javascript">
      (function(c,l,a,r,i,t,y){
          c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
          t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
          y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
      })(window, document, "clarity", "script", "rt7tzo2scn");
  </script>
  ```

- [ ] **Step 6: Change theme colour meta from purple `#6366f1` to neutral black**

  ```blade
  <meta name="theme-color" content="#000000">
  <meta name="msapplication-TileColor" content="#000000">
  ```

- [ ] **Step 7: Commit**

  ```bash
  git add resources/views/app.blade.php
  git commit -m "fix: remove Letsmerge.it branding from app.blade.php"
  ```

---

### Task 2: Fix `ResetPassword.vue` — strip old dark theme and letsmerge.it branding

**Files:**
- Modify: `resources/js/pages/Auth/ResetPassword.vue`

**Background:**
This page still uses the old dark purple Let's Merge It design (dark background, gradient button, `letsmerge.it` logo text) and includes a "Sign in with GitHub instead" block that links to a `route('account.redirect')` route that does not exist in this blueprint. It needs to match the clean black/white theme used by `Login.vue` and `Register.vue`.

- [ ] **Step 1: Read the file**

  Open `resources/js/pages/Auth/ResetPassword.vue` and confirm the issues described.

- [ ] **Step 2: Replace the entire `<template>` with a clean version matching Login.vue style**

  Replace the `<template>` block:
  ```vue
  <template>
      <GuestLayout>
          <Head title="Reset Password" />

          <!-- Header with Logo -->
          <div class="mb-8 text-center">
              <span class="text-xl font-bold text-black">
                  Laravel Blueprint
              </span>
              <h2 class="mt-2 text-2xl font-semibold text-gray-900">Reset Your Password</h2>
              <p class="text-sm text-gray-600">Create a new secure password for your account</p>
          </div>

          <form @submit.prevent="submit">
              <div>
                  <InputLabel for="email" value="Email" />
                  <TextInput
                      id="email"
                      type="email"
                      class="mt-1 block w-full"
                      v-model="form.email"
                      required
                      autofocus
                      autocomplete="username"
                      readonly
                  />
                  <InputError class="mt-2" :message="form.errors.email" />
                  <p class="mt-1 text-xs text-gray-500">This email address cannot be changed</p>
              </div>

              <div class="mt-4">
                  <InputLabel for="password" value="New Password" />
                  <TextInput
                      id="password"
                      type="password"
                      class="mt-1 block w-full"
                      v-model="form.password"
                      required
                      autocomplete="new-password"
                  />
                  <InputError class="mt-2" :message="form.errors.password" />
              </div>

              <div class="mt-4">
                  <InputLabel for="password_confirmation" value="Confirm New Password" />
                  <TextInput
                      id="password_confirmation"
                      type="password"
                      class="mt-1 block w-full"
                      v-model="form.password_confirmation"
                      required
                      autocomplete="new-password"
                  />
                  <InputError class="mt-2" :message="form.errors.password_confirmation" />
              </div>

              <div class="mt-6">
                  <PrimaryButton
                      class="w-full justify-center"
                      :class="{ 'opacity-70': form.processing }"
                      :disabled="form.processing"
                  >
                      {{ form.processing ? 'Resetting...' : 'Reset Password' }}
                  </PrimaryButton>
              </div>

              <div class="mt-4 text-center">
                  <Link
                      :href="route('login')"
                      class="text-sm text-gray-600 hover:text-black transition-colors"
                  >
                      Return to login
                  </Link>
              </div>
          </form>
      </GuestLayout>
  </template>
  ```

- [ ] **Step 3: Remove the `<style scoped>` block entirely** (it contains old dark-theme overrides).

- [ ] **Step 4: Remove the unused `Link` import from `<script setup>`** — `Link` is used in the new template for "Return to login", so keep it. But remove the GitHub `<svg>` block and the `route('account.redirect')` reference, which are gone from the template now.

- [ ] **Step 5: Commit**

  ```bash
  git add resources/js/pages/Auth/ResetPassword.vue
  git commit -m "fix: replace Letsmerge.it dark theme on ResetPassword page with blueprint style"
  ```

---

### Task 3: Make app name dynamic on Login, Register, and Welcome pages

**Files:**
- Modify: `resources/js/pages/Auth/Login.vue`
- Modify: `resources/js/pages/Auth/Register.vue`
- Modify: `resources/js/pages/Welcome.vue`

**Background:**
The logo text `"Laravel Blueprint"` is hardcoded in these three files. When a user renames the app, the pages won't automatically reflect the new name. The app name is available via Inertia's shared props as `page.props.appName` (set in `HandleInertiaRequests.php`).

- [ ] **Step 1: Check HandleInertiaRequests.php to confirm how app name is shared**

  Read `app/Http/Middleware/HandleInertiaRequests.php` and look for the `share()` method. Note the key used for the app name.

- [ ] **Step 2: If app name is not currently shared, add it**

  In `HandleInertiaRequests.php`, within the `share()` method's return array, add:
  ```php
  'appName' => config('app.name'),
  ```

- [ ] **Step 3: Update `Login.vue` to use `page.props.appName`**

  In the `<script setup>` block, `usePage` is already imported. Add:
  ```js
  const appName = computed(() => page.props.appName ?? 'Laravel Blueprint');
  ```
  And import `computed` from vue:
  ```js
  import { computed } from 'vue';
  ```
  Then in the template change:
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

- [ ] **Step 4: Apply the same change to `Register.vue`**

  Same as Step 3, mirrored for Register.vue.

- [ ] **Step 5: Update `Welcome.vue` to use `page.props.appName`**

  In `Welcome.vue`, `usePage` is not yet imported. Import it:
  ```js
  import { Head, Link, usePage } from '@inertiajs/vue3';
  import { computed } from 'vue';
  const page = usePage();
  const appName = computed(() => page.props.appName ?? 'Laravel Blueprint');
  ```
  Then change the hardcoded `Laravel Blueprint` heading to `{{ appName }}`.

- [ ] **Step 6: Commit**

  ```bash
  git add app/Http/Middleware/HandleInertiaRequests.php \
          resources/js/pages/Auth/Login.vue \
          resources/js/pages/Auth/Register.vue \
          resources/js/pages/Welcome.vue
  git commit -m "feat: make app name dynamic via Inertia shared props on auth and welcome pages"
  ```

---

## Chunk 2: Replace Old Favicon/Image Assets

### Task 4: Replace favicon and app icon assets with generic placeholders

**Files:**
- Replace: `public/favicon.ico`
- Replace: `public/favicon.svg`
- Replace: `public/apple-touch-icon.png`
- Replace: `public/images/og-image.png` (new generic OG image)
- Delete: `public/images/homepage.png` (old Let's Merge It screenshot)
- Delete: `public/images/maker.png` (old Let's Merge It screenshot)

**Background:**
All current icon assets are from the Let's Merge It app. They need to be replaced with generic, neutral placeholder assets that users can swap out when branding their new project.

- [ ] **Step 1: Create a minimal generic SVG favicon**

  Overwrite `public/favicon.svg` with a simple black square with a white "B" — a neutral placeholder:
  ```svg
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
    <rect width="32" height="32" rx="6" fill="#000"/>
    <text x="50%" y="50%" dominant-baseline="central" text-anchor="middle"
          font-family="sans-serif" font-size="20" font-weight="bold" fill="#fff">B</text>
  </svg>
  ```

- [ ] **Step 2: Generate `favicon.ico` from the SVG**

  The simplest approach is to copy the SVG to `favicon.ico` as a placeholder. Most modern browsers accept an SVG-encoded ICO or will fall back gracefully. Alternatively use an online converter (e.g. `https://favicon.io`) to generate a proper `.ico` file and place it at `public/favicon.ico`. If running locally, the manual step is acceptable and should be noted in the README (see Task 5).

  For now, document this in the plan as a **manual step** for the developer: "Replace `public/favicon.ico` with a proper `.ico` generated from your branding."

- [ ] **Step 3: Create a generic `apple-touch-icon.png` placeholder**

  This is a 180×180 PNG. Since we cannot generate binary images here, document as a manual step: "Replace `public/apple-touch-icon.png` with a 180×180 PNG of your app icon."

- [ ] **Step 4: Create a generic `og-image.png` placeholder**

  Document as a manual step: "Replace `public/images/og-image.png` with a 1200×630 PNG for social sharing."

- [ ] **Step 5: Delete the old Let's Merge It images**

  ```bash
  rm public/images/homepage.png
  rm public/images/maker.png
  ```

- [ ] **Step 6: Commit**

  ```bash
  git add public/favicon.svg
  git rm public/images/homepage.png public/images/maker.png
  git commit -m "fix: replace Letsmerge.it favicon and remove old screenshot assets"
  ```

---

## Chunk 3: README Customisation Prompt

### Task 5: Add "Customise This Blueprint" section to README

**Files:**
- Modify: `README.md`

**Background:**
Users who clone this repo need a clear, copy-pasteable prompt they can hand to Claude (or any AI assistant) to rename the app and set up their own branding in one go. The section also serves as a human-readable checklist of every file that needs editing.

- [ ] **Step 1: Read README.md**

  Open `README.md` and identify where to insert the new section (after the existing "Quick Start" section, before "Features").

- [ ] **Step 2: Add the "Customise This Blueprint" section**

  Insert the following after the "Quick Start" section:

  ````markdown
  ## Customising This Blueprint (Rename & Rebrand)

  After cloning, use the prompt below with Claude Code (or any AI assistant) to rename the app and update all branding in one pass. Replace `<YOUR APP NAME>` with your chosen name before running.

  ### AI Customisation Prompt

  Copy and paste this prompt into Claude Code:

  ```
  I have just cloned the Laravel Blueprint starter template. I want to rename it to "<YOUR APP NAME>".

  Please update the following files to replace "Laravel Blueprint" with "<YOUR APP NAME>" everywhere it appears as a display name, title, or heading:

  1. `resources/views/app.blade.php`
     - The `config('app.name', 'Laravel Blueprint')` fallback in all meta tags
     - The OG/Twitter title and description meta tags
     - The page `<title>` fallback

  2. `resources/js/pages/Welcome.vue`
     - The main `<h1>` heading

  3. `resources/js/pages/Auth/Login.vue`
     - The logo/brand span at the top of the login form

  4. `resources/js/pages/Auth/Register.vue`
     - The logo/brand span at the top of the register form

  5. `resources/js/pages/Auth/ResetPassword.vue`
     - The logo/brand span at the top of the reset password form

  6. `.env` (or `.env.example`)
     - Set `APP_NAME="<YOUR APP NAME>"`

  Also remind me to manually replace these binary assets with my own branding:
  - `public/favicon.ico` — app favicon (any size .ico)
  - `public/favicon.svg` — scalable app icon (SVG)
  - `public/apple-touch-icon.png` — 180×180 PNG for iOS home screen
  - `public/images/og-image.png` — 1200×630 PNG for social media sharing
  ```

  ### Manual Asset Checklist

  These binary files cannot be changed by an AI — swap them with your own:

  | File | Size | Purpose |
  |------|------|---------|
  | `public/favicon.ico` | any | Browser tab icon |
  | `public/favicon.svg` | any | Scalable browser icon |
  | `public/apple-touch-icon.png` | 180×180 | iOS home screen icon |
  | `public/images/og-image.png` | 1200×630 | Social media share image |

  ````

- [ ] **Step 3: Commit**

  ```bash
  git add README.md
  git commit -m "docs: add Customise This Blueprint section with AI prompt and asset checklist"
  ```

---

---

## Chunk 4: Remove Dead GitHub OAuth Code

### Task 6: Delete GitHub OAuth controllers and remove the package

**Files:**
- Delete: `app/Http/Controllers/Auth/HandleGitHubAccountRedirectController.php`
- Delete: `app/Http/Controllers/Auth/HandleAccountController.php`
- Modify: `config/services.php` — remove `github` block
- Modify: `composer.json` — remove `socialiteproviders/github` and `socialiteproviders/zoho`

**Background:**
Both controllers reference `App\Services\AccountService` which does not exist — they are dead code. There is no `account.redirect` route, and no GitHub-related routes in `routes/auth.php`. The `socialiteproviders/zoho` package is also a Let's Merge It leftover with no callers.

- [ ] **Step 1: Delete the two dead controllers**

  ```bash
  rm app/Http/Controllers/Auth/HandleGitHubAccountRedirectController.php
  rm app/Http/Controllers/Auth/HandleAccountController.php
  ```

- [ ] **Step 2: Remove the `github` block from `config/services.php`**

  Delete:
  ```php
  'github' => [
      'client_id' => env('GITHUB_CLIENT_ID'),
      'client_secret' => env('GITHUB_CLIENT_SECRET'),
      'redirect' => env('GITHUB_REDIRECT_URI')
  ],
  ```

- [ ] **Step 3: Remove the socialite packages from `composer.json`**

  Remove from the `require` object:
  ```json
  "socialiteproviders/github": "^4.1",
  "socialiteproviders/zoho": "^4.1",
  ```

- [ ] **Step 4: Remove packages from vendor and update lock file**

  ```bash
  composer remove socialiteproviders/github socialiteproviders/zoho
  ```

- [ ] **Step 5: Commit**

  ```bash
  git add app/Http/Controllers/Auth/HandleGitHubAccountRedirectController.php \
          app/Http/Controllers/Auth/HandleAccountController.php \
          config/services.php composer.json composer.lock
  git commit -m "fix: remove dead GitHub OAuth controllers and socialite packages"
  ```

---

## Chunk 5: Remove AI Service Files and App-Specific Packages

### Task 7: Delete Gemini AI service, GeminiChat model, and leftover packages

**Files:**
- Delete: `app/Services/AIService.php`
- Delete: `app/Models/GeminiChat.php`
- Modify: `config/services.php` — remove `gemini` block
- Modify: `composer.json` — remove `pbmedia/laravel-ffmpeg`

**Background:**
`AIService.php` is a Gemini 2.0 Flash client and `GeminiChat.php` is a chat history model — both are Let's Merge It feature code with no callers in this blueprint. `pbmedia/laravel-ffmpeg` is a video processing library used for Let's Merge It's PR diff analysis; it has no use in a generic blueprint.

- [ ] **Step 1: Delete the AI service files**

  ```bash
  rm app/Services/AIService.php
  rm app/Models/GeminiChat.php
  ```

- [ ] **Step 2: Remove the `gemini` block from `config/services.php`**

  Delete:
  ```php
  'gemini' => [
      'api_key' => env('GEMINI_API_KEY'),
  ],
  ```

- [ ] **Step 3: Remove `pbmedia/laravel-ffmpeg` from `composer.json`**

  ```bash
  composer remove pbmedia/laravel-ffmpeg
  ```

- [ ] **Step 4: Commit**

  ```bash
  git add app/Services/AIService.php app/Models/GeminiChat.php \
          config/services.php composer.json composer.lock
  git commit -m "fix: remove Gemini AI service files and ffmpeg package leftovers"
  ```

---

## Summary of All Files Changed

| File | Change |
|------|--------|
| `resources/views/app.blade.php` | Remove Letsmerge.it fallbacks, old SEO, Clarity script |
| `resources/js/pages/Auth/ResetPassword.vue` | Replace dark Let's Merge It theme with blueprint style |
| `resources/js/pages/Auth/Login.vue` | Make app name dynamic |
| `resources/js/pages/Auth/Register.vue` | Make app name dynamic |
| `resources/js/pages/Welcome.vue` | Make app name dynamic |
| `app/Http/Middleware/HandleInertiaRequests.php` | Share `appName` via Inertia |
| `public/favicon.svg` | Replace with generic "B" placeholder |
| `public/images/homepage.png` | Delete (Let's Merge It screenshot) |
| `public/images/maker.png` | Delete (Let's Merge It screenshot) |
| `README.md` | Add Customise section with AI prompt |
| `app/Http/Controllers/Auth/HandleGitHubAccountRedirectController.php` | Delete (dead code, references missing AccountService) |
| `app/Http/Controllers/Auth/HandleAccountController.php` | Delete (dead code, references missing AccountService) |
| `config/services.php` | Remove `github` and `gemini` blocks |
| `composer.json` | Remove `socialiteproviders/github`, `socialiteproviders/zoho`, `pbmedia/laravel-ffmpeg` |
| `app/Services/AIService.php` | Delete (Gemini AI feature from Let's Merge It) |
| `app/Models/GeminiChat.php` | Delete (Gemini chat model from Let's Merge It) |
