# Laravel Blueprint

A clean Laravel + Vue 3 + Inertia.js starter template with authentication, modern UI, and black/white theme.

## 🚀 Quick Start

### Fork & Clone
1. **Fork this repository** on GitHub (creates YOUR copy)
2. **Clone YOUR fork**:
   ```bash
   git clone https://github.com/YOUR_USERNAME/Laravel-bluprint.git
   cd Laravel-bluprint
   ```
   
   > **Note**: This clones YOUR fork, so any changes you push will go to your repository, not the original.

### Setup
1. **Copy environment file**:
   ```bash
   cp .env.example .env
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   npm run setup:hooks
   ```

3. **Generate app key**:
   ```bash
   php artisan key:generate
   ```

4. **Setup database**:
   - Configure your database in `.env`
   - Run migrations:
   ```bash
   php artisan migrate
   ```

5. **Build assets**:
   ```bash
   npm run build
   ```

6. **Start development**:
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` - you're ready to go! 🎉

## Customising This Blueprint (Rename & Rebrand)

After cloning, use the prompt below with Claude Code (or any AI assistant) to rename the app and update all branding in one pass. Replace `<YOUR APP NAME>` with your chosen name before running.

### AI Customisation Prompt

Copy and paste this prompt into Claude Code:

````
I have just cloned the Laravel Blueprint starter template. I want to rename it to "<YOUR APP NAME>".

Please update the following to replace "Laravel Blueprint" with "<YOUR APP NAME>":

1. `.env` — set `APP_NAME="<YOUR APP NAME>"`

2. `resources/views/app.blade.php` — update the `config('app.name', 'Laravel Blueprint')` fallback in all meta tags

3. `resources/js/pages/Welcome.vue`, `resources/js/pages/Auth/Login.vue`, `resources/js/pages/Auth/Register.vue`, `resources/js/pages/Auth/ResetPassword.vue` — the `appName` computed fallback string

Also remind me to manually replace these binary assets with my own branding:
- `public/favicon.ico`
- `public/favicon.svg`
- `public/apple-touch-icon.png`
- `public/images/og-image.png` (1200×630 for social sharing)
````

### Manual Asset Checklist

These binary files must be replaced manually with your own branding:

| File | Size | Purpose |
|------|------|---------|
| `public/favicon.ico` | any | Browser tab icon |
| `public/favicon.svg` | any | Scalable browser icon (currently a generic "B" placeholder) |
| `public/apple-touch-icon.png` | 180×180 | iOS home screen icon |
| `public/images/og-image.png` | 1200×630 | Social media share image |

## ✨ Features

- ✅ **Laravel Breeze** authentication
- ✅ **Vue 3 + Inertia.js** SPA experience
- ✅ **Clean black/white theme**
- ✅ **Responsive design**
- ✅ **User registration/login**
- ✅ **Profile management**
- ✅ **Professional dashboard**

## 🛠️ Tech Stack

- **Backend**: Laravel 11
- **Frontend**: Vue 3 + Inertia.js
- **Styling**: Tailwind CSS
- **Authentication**: Laravel Breeze

## 📝 Next Steps

1. Update branding (change "Laravel Blueprint" to your project name)
2. Customize dashboard content
3. Add your specific features
4. Deploy and enjoy!

---

**Perfect starting point for any Laravel + Vue project!** 🚀