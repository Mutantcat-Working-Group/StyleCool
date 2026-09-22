<div align=center>
<img src="assets/logo.png" width="100"/>
<h2>StyleCool</h2>
<p>Integratable design-style API · Standalone deployment at stylecool.mutantcat.org</p>
</div>

> English | [中文](README.md)

### 1. Overview
- A self-contained sub-site extracted from FunctionCool. It can be deployed independently to `stylecool.mutantcat.org`; the folder is self-contained with no external dependencies.
- `/` or `/skillapi`: with no parameters it shows the documentation page; with `?token=&q=&cat=` it returns JSON search results from the style library.
- `/example`: a style example gallery (100 examples with thumbnails, keyword search, pagination, and copy-ready prompts).
- Categories: `web` / `desktop` / `miniapp` / `mobile`, with style data stored in the matching `*.json` files (151 entries total).
- The index page and gallery both ship with built-in Chinese/English i18n; style rules are hand-written by human designers to give AI a shared sense of taste online.

### 2. Deployment
1. Apache: enable `a2enmod rewrite`, point the site root at this project; `.htaccess` already routes `/skillapi` and `/example`.
2. Standalone domain: resolve `stylecool.mutantcat.org` to this directory; page canonical URLs already point to that domain.
3. Docker: follow the `php:8.3-apache` image approach from the parent project `FunctionCool/Dockerfile`.

### 3. Usage
1. Visit `/skillapi` directly (no parameters) to view the HTML documentation page.
2. Call the style search endpoint:
    ```
    https://stylecool.mutantcat.org/skillapi?token=mutantcat&q=button&cat=web
    ```
3. Visit `/example` to browse the 100 style examples; `?q=` keyword search and pagination are supported.

### 4. API
1. Style search - `/skillapi`
   - Description: after token validation, returns style library results as JSON filtered by keyword and category.
   - Method: GET (returns JSON when `token`, `q`, and `cat` are present)
   - Parameters:
     - `token`: permanent key (default `mutantcat`)
     - `q`: search keyword, fuzzy-matched against names / descriptions / tags
     - `cat`: `web` / `desktop` / `miniapp` / `mobile` / `all` (unknown values search all categories)
   - Response example:
     ```json
     {
         "results": [],
         "query": "button",
         "cat": "web",
         "count": 0
     }
     ```
2. Style examples - `/example`
   - Description: a style example gallery where each example includes full HTML, a thumbnail, and a copy-ready generation prompt.
   - Query parameters:
     - `q`: keyword (optional; fuzzy matches name / description / tags / category)
     - `p`: page number (optional; defaults to page 1)

### 5. Design Focus
- Gives AI and automation workflows a designer-curated “shared sense of taste” before they write CSS, instead of producing merely “workable UI”.
- Category-based lookup (Web / Desktop / Mini Program / Mobile) returns only the most relevant rules per request, keeping AI context clean.
- Style data is maintained independently; edit the JSON files to hot-update without changing frontend code.
- Requires only PHP and Apache rewrite rules; the folder is self-contained and can be deployed anywhere at any time.

### 6. Roadmap
- [X] Core API + documentation page (`/skillapi`)
- [X] Token validation (external whitelist first, built-in whitelist as fallback)
- [X] Style example gallery (`/example`, 100 examples)
- [X] Four platform style datasets (web / desktop / miniapp / mobile)
- [X] Chinese/English i18n documentation page and gallery
- [X] Standalone deployment rewrite rules (`.htaccess`)

### 7. Directory Layout
- `index.php` — core API + documentation page (built-in token validation)
- `example.php` — style example gallery
- `web.json` / `desktop.json` / `miniapp.json` / `mobile.json` — style library data
- `list/` — 100 example HTML files and `list/images/` thumbnails
- `assets/` — local `style.css` / `i18n.js` / `logo.png` (no parent-site dependency)
- `data/skill_token_permanent.json` — permanent key whitelist (default `mutantcat`, replaceable externally)
- `.htaccess` — standalone deployment rewrite rules

### 8. Keys & Security
- Validation order: the `data/skill_token_permanent.json` whitelist is checked first; if no match, the built-in whitelist `['mutantcat']` is used.
- Note: a key committed to a public repository is considered public; move it to environment variables if you need it to stay secret.
