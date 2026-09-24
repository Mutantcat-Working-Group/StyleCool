<?php
// StyleCool — 设计样式可集成接口
// 独立部署于 stylecool.mutantcat.org（文件夹自包含，无外部依赖）
// ============================================================
// 由异猫工作群（mutantcat.org）发行
// GitHub: https://github.com/Mutantcat-Working-Group
// ============================================================
// URL： / 或 /skillapi
//   ?token=mutantcat&q={关键词}&cat={web|desktop|miniapp|mobile|all} → JSON
//   无参数 → HTML 文档页
// ============================================================

$IS_HTTP = (php_sapi_name() !== 'cli');
if ($IS_HTTP) { header('X-Robots-Tag: noindex, nofollow', true); }

// ── 分类 ──
$CATEGORIES = ['web', 'desktop', 'miniapp', 'mobile'];
$CAT_NAMES  = [
    'web'     => ['en' => 'Web',          'zh' => '网站 / Web App',     'scene' => 'SSR / SPA / PWA / 静态站'],
    'desktop' => ['en' => 'Desktop',       'zh' => '桌面端',             'scene' => 'Electron / 原生 / 大屏 Web'],
    'miniapp' => ['en' => 'Mini Program',  'zh' => '小程序',             'scene' => '微信 / 支付宝 / 抖音小程序'],
    'mobile'  => ['en' => 'Mobile',        'zh' => '手机 / 竖屏设备',   'scene' => 'iOS / Android / H5 移动端'],
];

// ── 密钥校验 ──
if (!function_exists('stylecool_valid')) { function stylecool_valid($token) {
    // 优先读项目内 data/skill_token_permanent.json（可用外置 JSON 覆盖），否则用内置白名单
    $ext = __DIR__ . '/data/skill_token_permanent.json';
    if (file_exists($ext)) {
        $perms = @json_decode(file_get_contents($ext), true);
        if (is_array($perms) && in_array($token, $perms, true)) return true;
    }
    // 内置白名单（独立部署时使用）
    $builtin = ['mutantcat'];
    if (in_array($token, $builtin, true)) return true;
    return false;
} }

// ── JSON 查询 ──
if (isset($_GET['token'], $_GET['q'], $_GET['cat'])) {
    $token = $_GET['token'];
    $query = trim($_GET['q']);
    $cat   = strtolower($_GET['cat']);

    if (!stylecool_valid($token)) {
        if ($IS_HTTP) header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Invalid token'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $cats = $CATEGORIES;
    if ($cat !== 'all' && in_array($cat, $cats, true)) {
        $cats = [$cat];
    }

    $results = [];
    $search  = mb_strtolower($query);

    foreach ($cats as $c) {
        $file = __DIR__ . "/{$c}.json";
        if (!file_exists($file)) continue;
        $data = @json_decode(file_get_contents($file), true);
        if (!$data) continue;
        foreach ($data as $entry) {
            $fields = [
                mb_strtolower($entry['name-en']        ?? ''),
                mb_strtolower($entry['name-zh']        ?? ''),
                mb_strtolower($entry['description-en'] ?? ''),
                mb_strtolower($entry['description-zh'] ?? ''),
                implode(' ', array_map('mb_strtolower', $entry['tags'] ?? [])),
            ];
            $hit = false;
            foreach ($fields as $f) {
                if ($search === '' || mb_strpos($f, $search) !== false) { $hit = true; break; }
            }
            if ($hit) {
                $entry['category'] = $c;
                $results[] = $entry;
            }
        }
    }

    if ($IS_HTTP) header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'results' => $results,
        'query'   => $query,
        'cat'     => $cat,
        'count'   => count($results),
    ], JSON_UNESCAPED_UNICODE);
    return;
}

// ── HTML 文档页（无查询参数时）──
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StyleCool — 设计样式接口 | FunctionCool</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="canonical" href="https://stylecool.mutantcat.org/">
    <link rel="stylesheet" href="assets/style.css?v=20260610">
    <link rel="icon" type="image/png" href="assets/logo.png">
    <link rel="apple-touch-icon" href="assets/logo.png">
    <script src="assets/i18n.js?v=20260610"></script>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1><span data-i18n="stylecool-title">StyleCool</span><span style="font-weight:400;color:var(--ink-3);font-size:.75em;margin-left:.3em;" data-i18n="stylecool-badge">设计样式接口</span></h1>
                <p data-i18n="stylecool-header-subtitle">给 AI 与自动化工作流的设计样式 StyleCool — 联网为 AI 注入审美判断力</p>
            </div>
            <div class="language-switcher">
                <a href="example" class="skill-link" aria-label="样式示例预览" data-i18n="stylecool-example-link">🎨 样式示例</a>
                <a href="https://functioncool.mutantcat.org/" class="home-link" aria-label="返回首页" data-i18n="stylecool-back-link">← 返回首页</a>
                <button id="lang-btn" type="button">English</button>
            </div>
        </div>
    </header>
    <main>
        <div class="container" style="max-width:720px;margin:0 auto;">

            <!-- Intro -->
            <section class="skillapi-intro reveal reveal-1" style="text-align:center;padding:2rem 0;">
                <div class="skill-hero-badge" data-i18n="stylecool-hero-badge">AI 设计审美</div>
                <h2 class="grad-text" style="margin:1rem 0 0.6rem;font-size:2rem;font-weight:800;letter-spacing:-0.02em;" data-i18n="stylecool-hero-title">设计样式索引</h2>
                <p style="color:var(--ink-2);" data-i18n="stylecool-hero-desc">AI 模型擅长代码逻辑，但天生缺乏<strong>设计审美</strong>——它们不知道什么是"好看"、什么是"低级感"。<br>StyleCool 联网调用由人类设计师精心筛选的设计样式库，在 AI 动手写 CSS 之前<strong>为它注入品味</strong>：<br>正确的做法、必须避开的坑、平台特定的设计规则。结果不是"能用的 UI"，而是<strong>有审美的 UI</strong>。</p>

                <div class="skill-value-grid">
                    <div class="skill-value-card">
                        <h3 data-i18n="stylecool-value-tokens-title">为 AI 注入审美判断力</h3>
                        <p data-i18n="stylecool-value-tokens-desc">AI 不会"觉得一个按钮丑"——它需要有人告诉它。StyleCool 的设计规则由真实设计师编写，每一条都经过反复验证：为什么不能用霓虹外发光、为什么卡片不能三等分、为什么动画不能动 width。这不是代码生成器，这是<strong>联网的品味共识</strong>。</p>
                    </div>
                    <div class="skill-value-card">
                        <h3 data-i18n="stylecool-value-platform-title">平台专项设计语汇</h3>
                        <p data-i18n="stylecool-value-platform-desc">一个好的网页按钮和一个小程序按钮，在圆角、间距、反馈动效上应该有微妙的不同。按平台分类（网站 / 桌面 / 小程序 / 手机）检索，每次只取最相关的设计规则，不打搅 AI 的上下文。</p>
                    </div>
                </div>
            </section>

            <!-- 样式示例画廊入口 -->
            <section class="skill-card reveal reveal-2" style="margin-bottom:2rem;text-align:center;">
                <div class="skill-hero-badge" data-i18n="stylecool-example-gallery-badge">样式预览</div>
                <h3 style="color:var(--ink);font-size:1.15rem;margin-bottom:0.4rem;" data-i18n="stylecool-example-gallery-title">在线预览 100 种设计风格</h3>
                <p style="color:var(--ink-2);margin-bottom:1.2rem;" data-i18n="stylecool-example-gallery-desc">每种风格都有完整示例页面 + 真实截图 + 一键复制完整提示词，帮助 AI 和开发者直观理解设计语言。</p>
                <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                    <a class="skill-github-link" href="example" style="background:var(--sky-700);">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                        <span data-i18n="stylecool-example-gallery-btn">浏览样式示例 →</span>
                    </a>
                </div>
            </section>

            <!-- API 契约 + 分类表 -->
            <section class="skill-card reveal reveal-2" style="margin-bottom:2rem;">
                <ul style="margin:0 0 1.4rem 1.2em;color:var(--ink-2);line-height:1.8;">
                    <li data-i18n="stylecool-api-endpoint-desc">端点：<code>/skillapi?token=mutantcat&q={关键词}&cat={分类}</code>（无参数时见此文档页）</li>
                    <li data-i18n="stylecool-api-key-desc">密钥：<code>mutantcat</code>（永久有效，直接调用，无需获取）</li>
                    <li data-i18n="stylecool-api-response-desc">返回：JSON — <code>results</code>（设计样式数组）、<code>query</code>、<code>cat</code>、<code>count</code></li>
                </ul>

                <div class="skillapi-table-block">
                    <h3 class="skillapi-table-caption" data-i18n="stylecool-cat-table-caption">分类参数对照表</h3>
                    <div class="skillapi-table-wrap">
                        <table class="skillapi-addr-table">
                            <thead><tr><th data-i18n="stylecool-cat-th-value">cat 值</th><th data-i18n="stylecool-cat-th-zh">中文</th><th data-i18n="stylecool-cat-th-scene">覆盖场景</th></tr></thead>
                            <tbody>
                                <?php foreach ($CAT_NAMES as $k => $v): ?>
                                <tr><td><code><?=$k?></code></td><td><?=$v['zh']?></td><td><?=$v['scene']?></td></tr>
                                <?php endforeach; ?>
                                <tr><td><code>all</code></td><td data-i18n="stylecool-cat-all">全平台</td><td data-i18n="stylecool-cat-all-scene">跨分类检索</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- 快捷提示词 -->
            <section class="skill-card reveal reveal-3" style="margin-bottom:2rem;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:0.8rem;flex-wrap:wrap;margin-bottom:1rem;">
                    <div style="font-weight:800;color:var(--ink);font-size:1.15rem;" data-i18n="stylecool-quicktip-title">快捷提示词</div>
                    <button id="quicktip-copy" class="skill-copy-btn" data-i18n="quicktip-copy-btn">复制</button>
                </div>
                <p style="margin:0 0 0.9rem;color:var(--ink-2);">
                    <span data-i18n="stylecool-perm-key-label">永久密钥：</span>
                    <code style="background:#EAF4FC;color:var(--ink);padding:0.2rem 0.6rem;border-radius:8px;font-weight:700;">mutantcat</code>
                </p>
                <div id="stylecool-quicktip" class="skill-prompt-box">
                    <p style="margin:0 0 0.3rem;"><strong data-i18n="stylecool-quicktip-zh">【中文】 向以下地址发送 GET 请求，取回设计样式索引：</strong></p>
                    <p style="margin:0.2rem 0 0.7rem 0;"><code style="color:var(--gold-soft);font-family:ui-monospace,'SF Mono',monospace;word-break:break-word;overflow-wrap:anywhere;">https://stylecool.mutantcat.org/skillapi?token=mutantcat&q=button&cat=web</code></p>
                    <p style="margin:0 0 10px;">cat = web | desktop | miniapp | mobile | all</p>
                    <p style="margin:0.8rem 0 0.3rem;"><strong data-i18n="stylecool-quicktip-en">[English] GET https://stylecool.mutantcat.org/skillapi?token=mutantcat&q=button&cat=web</strong></p>
                </div>
            </section>

            <!-- 仓库 -->
            <section class="skill-card reveal reveal-4" style="margin-bottom:3rem;text-align:center;">
                <h3 style="color:var(--ink);font-size:1.15rem;margin-bottom:0.4rem;" data-i18n="stylecool-repo-title">设计样式 JSON 开源</h3>
                <p style="color:var(--ink-2);margin-bottom:1rem;" data-i18n="stylecool-repo-desc">欢迎扩充条目，在以下仓库提交 PR：</p>
                <a class="skill-github-link" href="https://github.com/Mutantcat-Working-Group/StyleCool-Skill" target="_blank" rel="noopener">
                    <svg viewBox="0 0 16 16"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8z"/></svg>
                    <span>github.com/Mutantcat-Working-Group/StyleCool-Skill</span>
                </a>
            </section>

        </div>
    </main>
    <footer>
        <div class="container">
            <p data-i18n="stylecool-footer-text">&copy; 2025-2026 函数库 | Powered by Mutantcat</p>
            <p class="footer-publisher">由异猫工作群（mutantcat.org）发行 · <a href="https://github.com/Mutantcat-Working-Group" target="_blank" rel="noopener">github.com/Mutantcat-Working-Group</a></p>
            <div class="friend-links">
                <span data-i18n="friend-links">友情链接：</span>
                <a href="https://www.mutantcat.org/" target="_blank" rel="noopener">异猫工作群</a>
                <a href="https://www.fcnesyouxi.top/" target="_blank" rel="noopener">FC/NES游戏</a>
                <a href="https://www.jqshengtian.top/" target="_blank" rel="noopener">学习资料</a>
            </div>
        </div>
    </footer>
    <script>
    // 语言初始化（依赖 i18n.js 中的全局函数）
    document.addEventListener('DOMContentLoaded', function() {
        try {
            setTimeout(function() {
                var current = window.getCurrentLanguage ? window.getCurrentLanguage() : 'zh';
                if (window.setLanguage) window.setLanguage(current);
                var langBtn = document.getElementById('lang-btn');
                if (langBtn) {
                    langBtn.textContent = current === 'zh' ? 'English' : '中文';
                    langBtn.onclick = function() {
                        if (window.toggleLanguage) {
                            window.toggleLanguage();
                            var cur = window.getCurrentLanguage ? window.getCurrentLanguage() : 'zh';
                            langBtn.textContent = cur === 'zh' ? 'English' : '中文';
                            updateCopyButtonText(cur);
                        }
                    };
                }
            }, 100);
        } catch (e) {
            console.error('StyleCool language init error:', e);
        }
    });

    function updateCopyButtonText(lang) {
        var btn = document.getElementById('quicktip-copy');
        if (btn && btn.textContent.indexOf('✓') === -1) {
            btn.textContent = lang === 'zh' ? '复制' : 'Copy';
        }
    }

    document.getElementById('quicktip-copy').onclick = function() {
        var t = document.getElementById('stylecool-quicktip').innerText;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(t);
        } else {
            var ta = document.createElement('textarea');
            ta.value = t;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
        }
        this.textContent = '✓ 已复制';
        var b = this;
        setTimeout(function() {
            var cur = window.getCurrentLanguage ? window.getCurrentLanguage() : 'zh';
            b.textContent = cur === 'zh' ? '复制' : 'Copy';
        }, 1200);
    };
    </script>
</body>
</html>
