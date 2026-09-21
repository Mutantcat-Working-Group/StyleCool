<?php
// ============================================================
// StyleCool — 设计样式示例浏览（带缩略图、关键词检索、分页）
// 独立部署于 stylecool.mutantcat.org 时使用
// ============================================================
// URL：  /example（主域 index.php 转发）或 stylecool/example.php
//   ?q={关键词} 关键词检索（模糊匹配 名称/描述/标签/分类）
//   &p={页码}   分页（每页 9 个）
// ============================================================

$IS_HTTP = (php_sapi_name() !== 'cli');
if ($IS_HTTP) { header('X-Robots-Tag: noindex, nofollow', true); }

// ── 关键操作日志：方便定位分页/检索异常，杜绝玄学问题 ──
$logTag = '[stylecool/example]';
error_log($logTag . ' hit q=' . var_export($_GET['q'] ?? null, true)
    . ' p=' . var_export($_GET['p'] ?? null, true)
    . ' ua=' . substr($_SERVER['HTTP_USER_AGENT'] ?? '-', 0, 60));

// ── 入参（防御性处理） ──
$query   = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$page    = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$page    = max(1, $page);                       // 页码不能小于 1
$perPage = 9;                                   // 每页 9 个（3×3 网格）

// ── 分类元数据 ──
$CAT_META = [
    'web'     => ['en' => 'Web',         'zh' => '网站 / Web App'],
    'desktop' => ['en' => 'Desktop',     'zh' => '桌面端'],
    'miniapp' => ['en' => 'Mini Program', 'zh' => '小程序'],
    'mobile'  => ['en' => 'Mobile',      'zh' => '手机 / 竖屏设备'],
];

// 画廊为独立部署设计，样例与缩略图实际位于 /list/。
$EXAMPLE_ASSET_BASE = '/list/';

$EXAMPLES = [
    // ── 真实示例（已渲染截图）：液态玻璃 ──
    [
        'id'          => 'liquid-glass',
        'name-zh'     => '液态玻璃',
        'name-en'     => 'Liquid Glass',
        'desc-zh'     => '高斯模糊 + 流动渐变光晕 + 多层透明叠加，模拟液体玻璃质感',
        'desc-en'     => 'Gaussian blur + flowing gradient orbs + translucent layers, simulating liquid glass',
        'tags'        => ['liquid', 'glass', 'blur', 'orb', '液态', '玻璃', '玻璃拟态', '动画', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/liquid-glass.png',
        'thumb'       => 'linear-gradient(135deg, #1e1b4b 0%, #4c1d95 50%, #831843 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/liquid-glass.html',
        'prompt'      => "采用液态玻璃（Liquid Glass）设计风格。背景使用动态渐变（深紫 #1e1b4b → 紫 #4c1d95 → 深蓝 #1e3a8a → 玫红 #831843），多色径向光晕（紫水晶 #6B46C1 / 玫粉 #F472B6 / 青蓝 #38BDF8 / 深海军 #1B4E7A）以 18s 缓动周期缓慢漂移，形成液态流动感。卡片使用高斯模糊：backdrop-filter: blur(40px) saturate(180%)，背景色 rgba(255,255,255,0.18)，1px 白色半透明边框，圆角 24px，内嵌 1px 顶部反射阴影。所有按钮、输入框、标签、导航、警告框均沿用相同的玻璃语言：blur 20-40px + 半透明白底 + 渐变主色按钮。整体节奏轻盈、富有未来感。",
    ],
    // ── 真实示例（已渲染截图）：玻璃拟态 ──
    [
        'id'          => 'glassmorphism',
        'name-zh'     => '玻璃拟态',
        'name-en'     => 'Glassmorphism',
        'desc-zh'     => '中等强度模糊 + 浅色渐变光晕 + 柔和投影，轻盈梦幻',
        'desc-en'     => 'Medium blur + light gradient orbs + soft shadows, light and dreamy',
        'tags'        => ['glass', 'blur', 'translucent', 'soft', '玻璃', '浅色', '梦幻', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/glassmorphism.png',
        'thumb'       => 'linear-gradient(135deg, #E0F4FF 0%, #FFE0EC 50%, #F0E5FF 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/glassmorphism.html',
        'prompt'      => "采用玻璃拟态（Glassmorphism）设计风格。背景使用浅色渐变（浅天蓝 #E0F4FF → 浅樱粉 #FFE0EC → 浅薰衣草 #F0E5FF），柔和漂浮大色斑（天蓝 #4D96FF / 樱粉 #FF6B9D / 薰衣草 #A18CD1 / 樱花粉 #FBC2EB）以 16-20s 缓动周期缓慢漂移。卡片使用中等强度模糊：backdrop-filter: blur(20px) saturate(180%)，背景色 rgba(255,255,255,0.4)，1px 白色半透明边框，圆角 16-24px，单层柔和浅灰投影 0 8px 32px rgba(128,142,174,0.18)。所有按钮、输入框、标签、导航、警告框均沿用相同的玻璃语言：blur 20px + 半透明白底 + 圆角 12px + 浅灰边框。整体保持轻盈、梦幻、专业的玻璃质感。",
    ],
    // ── 真实示例（已渲染截图）：孟菲斯 ──
    [
        'id'          => 'memphis',
        'name-zh'     => '孟菲斯',
        'name-en'     => 'Memphis',
        'desc-zh'     => '大胆几何 + 多彩碰撞，年轻张扬的 80 年代设计复兴',
        'desc-en'     => 'Bold geometry + colorful clashes, 80s design revival',
        'tags'        => ['memphis', 'colorful', 'geometric', '80s', '几何', '多彩', '年轻', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/memphis.png',
        'thumb'       => 'linear-gradient(135deg, #FF6B6B 0%, #FFEAB0 33%, #73B9E6 66%, #95E1D3 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/memphis.html',
        'prompt'      => "采用孟菲斯（Memphis）设计风格。背景使用奶油色 #FFF8E7 浅暖基底，点缀大胆几何元素：随机散布的圆点（珊瑚红 #FF6B6B 8px）、斑马纹波浪（黑白相间 4px）、三角形（樱草黄 #FFD93D 异向）、网格线条（天蓝 #4D96FF 半透明）。配色高饱和：珊瑚红 #FF6B6B + 樱草黄 #FFD93D + 薄荷绿 #6BCB77 + 天蓝 #4D96FF + 樱花粉 #FF8FAB。主容器使用 12px 实色硬边框（无圆角）、背景纯色硬块（如 #FFEAB0 浅黄）。字体圆润粗体（Inter Black / 思源黑体 Heavy）。圆角 0-12px 极端对比。整体活泼、张扬、年轻、充满街头感。",
    ],
    // ── 真实示例（已渲染截图）：极简白 ──
    [
        'id'          => 'minimal-white',
        'name-zh'     => '极简白',
        'name-en'     => 'Minimal White',
        'desc-zh'     => '克制的白色 + 大量留白 + 单一蓝色重音 + 极轻描边',
        'desc-en'     => 'Restrained white, generous whitespace, single accent',
        'tags'        => ['minimal', 'white', 'clean', 'whitespace', '白', '极简', '克制', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/minimal-white.png',
        'thumb'       => 'linear-gradient(135deg, #FFFFFF 0%, #F4F8FC 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/minimal-white.html',
        'prompt'      => "采用极简白（Minimal White）设计风格。背景纯白 #FFFFFF，大量留白（区块间距 64-96px）。卡片背景 #FFFFFF，1px 边框 #EAF2F8，圆角 10px，无阴影。字体使用系统无衬线（-apple-system / Inter），标题字重 700，正文 16px / 行高 1.7，颜色极少：主色 #15324C 文字 + #8395A6 次要 + 单一重音 #73B9E6（仅用于强调、聚焦、选中态）。按钮无填充 + 1px 边框 #DCE6EE，hover 加深至 #15324C；主要按钮填充 #73B9E6 + 白字。输入框聚焦时蓝色 1px 边框 + 3px rgba(115,185,230,0.15) 光环。导航选中态用单色 2px 底部蓝色下划线。整页只用 1 个主色 + 1 个重音色，所有元素极度克制、留白充足、阅读优先。",
    ],
    // ── 真实示例（已渲染截图）：暗色科技 ──
    [
        'id'          => 'dark-cyber',
        'name-zh'     => '暗色科技',
        'name-en'     => 'Dark Cyber',
        'desc-zh'     => '深海军蓝底色 + 霓虹青蓝强调 + 网格扫描线，冷峻终端感',
        'desc-en'     => 'Deep navy with neon cyan accents, grid scanlines, terminal aesthetic',
        'tags'        => ['dark', 'cyber', 'tech', 'neon', '暗', '霓虹', '终端', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/dark-cyber.png',
        'thumb'       => 'linear-gradient(135deg, #0E2E4A 0%, #15324C 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/dark-cyber.html',
        'prompt'      => "采用暗色科技（Dark Cyber）设计风格。背景深海军蓝 #0F2438 / #0E2E4A，叠加网格扫描线（40px 间距，霓虹青半透明 rgba(56,189,248,0.03)）。卡片使用 rgba(21,50,76,0.5) + backdrop-filter: blur(20px)，1px 霓虹青边框 rgba(56,189,248,0.25)，发光投影 0 0 12px rgba(56,189,248,0.15)。主色霓虹青 #38BDF8 + 金色高亮 #F2B53C。字体使用等宽 ui-monospace，标题大号粗体。按钮悬停时霓虹发光（box-shadow 0 0 12px）。所有元素保持暗色基调 + 霓虹描边 + 微小发光。整体冷峻、专业、终端感。",
    ],
    // ── 真实示例（已渲染截图）：新拟态 ──
    [
        'id'          => 'neumorphism',
        'name-zh'     => '新拟态',
        'name-en'     => 'Neumorphism',
        'desc-zh'     => '柔和阴影模拟凸起与凹陷，触感优先的软UI设计',
        'desc-en'     => 'Soft shadows simulating extrusion and inset, tactile soft UI',
        'tags'        => ['soft', 'shadow', 'monochrome', '拟态', '阴影', '触感', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/neumorphism.png',
        'thumb'       => 'linear-gradient(135deg, #E1EFF8 0%, #EAF2F8 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/neumorphism.html',
        'prompt'      => "采用新拟态（Neumorphism）设计风格。背景浅灰蓝 #EAF2F8，所有元素无边框、无模糊、无鲜艳色彩。卡片使用双重阴影模拟凸起：外向 8px 8px 16px rgba(163,177,198,0.6) + 内向 -8px -8px 16px rgba(255,255,255,0.7)。输入框使用内阴影模拟凹陷：inset 4px 4px 8px rgba(163,177,198,0.4)。按钮按下时切换为内阴影。圆角统一 12-20px。颜色极度克制，仅一抹天蓝 #73B9E6 作为强调。整体触感优先、柔和、安静。",
    ],
    // ── 真实示例（已渲染截图）：大色块 ──
    [
        'id'          => 'bold-blocks',
        'name-zh'     => '大色块',
        'name-en'     => 'Bold Blocks',
        'desc-zh'     => '高饱和色块大胆拼接 + 硬底阴影，强视觉冲击',
        'desc-en'     => 'High-saturation color blocks, hard bottom shadows, strong visual impact',
        'tags'        => ['bold', 'color', 'flat', '色块', '扁平', '硬影', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/bold-blocks.png',
        'thumb'       => 'linear-gradient(135deg, #FF6B6B 0%, #FFD93D 50%, #6BCB77 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/bold-blocks.html',
        'prompt'      => "采用大色块（Bold Blocks）设计风格。配色高饱和：红 #FF6B6B、黄 #FFD93D、绿 #6BCB77、蓝 #4D96FF、紫 #A18CD1、粉 #FF8FAB。色块之间无渐变，硬边分明。背景浅灰 #F8F9FA。卡片白色底 + 4px 硬底阴影 box-shadow: 0 4px 0 #1A1A2E。按钮 3px 硬底阴影，按下时回弹。输入框 3px 硬边框，聚焦时变蓝。字体粗体 800-900，标题特大。文字色 #1A1A2E 深黑，或白色反色。标签硬边框多彩。整体扁平、果断、强视觉冲击。",
    ],
    // ── 真实示例（已渲染截图）：网格系统 ──
    [
        'id'          => 'grid-system',
        'name-zh'     => '网格系统',
        'name-en'     => 'Grid System',
        'desc-zh'     => '12 列 Grid + 非对称布局 + 1px 发丝线，结构严谨',
        'desc-en'     => '12-column grid with asymmetric layout and hairline borders',
        'tags'        => ['grid', 'system', '12col', 'asymmetric', '网格', '结构', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/grid-system.png',
        'thumb'       => 'linear-gradient(135deg, #15324C 0%, #1B4E7A 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/grid-system.html',
        'prompt'      => "采用网格系统（Grid System）设计风格。背景深海军蓝 #15324C。所有内容使用 12 列 CSS Grid（grid-template-columns: repeat(12, 1fr)），gap 统一 16px。卡片 1px 发丝线边框 rgba(255,255,255,0.15)，圆角 8px，背景 #0F2438。主色天蓝 #73B9E6 用于强调、选中、聚焦。文字白色 #FFFFFF / 次要 rgba(255,255,255,0.7)。Hero 区使用非对称布局（7:3 分栏），右侧展示 3 个统计数字。底部组件区 6 张卡片按不对称 span 排列。所有元素严格对齐网格，无一处例外。",
    ],
    // ── 真实示例（已渲染截图）：杂志编辑 ──
    [
        'id'          => 'editorial',
        'name-zh'     => '杂志编辑',
        'name-en'     => 'Editorial',
        'desc-zh'     => '衬线大字标题 + 金色点缀 + 多栏编排，致敬印刷时代',
        'desc-en'     => 'Serif large headlines, gold accents, multi-column layout, print-era homage',
        'tags'        => ['editorial', 'serif', 'magazine', 'gold', '编辑', '杂志', '衬线', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/editorial.png',
        'thumb'       => 'linear-gradient(135deg, #F4F8FC 0%, #FBFDFF 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/editorial.html',
        'prompt'      => "采用杂志编辑（Editorial）设计风格。背景 #FBFDFF 浅白。字体：标题使用衬线 Playfair Display（700-900），正文使用 Noto Serif SC / Georgia（400-600），UI 文字使用系统无衬线。主色 #15324C 深黑 + 金色点缀 #F2B53C。标题极大（clamp 2.5-4.5rem），字距 -0.02em。引入 Kicker（大写宽字距金色标签）、Dek（副标题）、Byline（作者行）等编辑元素。卡片左侧 3px 金色竖条 + 1px 浅灰边框。输入框仅底线，聚焦时金色。按钮经典边框 + 反色悬停。引用块左侧金色条 + 斜体。整体优雅、克制，致敬印刷时代。",
    ],
    // ── 真实示例（已渲染截图）：经典商务 ──
    [
        'id'          => 'business-classic',
        'name-zh'     => '经典商务',
        'name-en'     => 'Classic Business',
        'desc-zh'     => '深蓝主调 + 等级分明 + 稳重可靠，企业级设计语言',
        'desc-en'     => 'Deep blue, clear hierarchy, stable and reliable, enterprise-grade',
        'tags'        => ['business', 'corporate', 'blue', 'enterprise', '商务', '稳重', 'desktop'],
        'cat'         => 'desktop',
        'thumb-image' => 'list/images/business-classic.png',
        'thumb'       => 'linear-gradient(135deg, #1B4E7A 0%, #3A8DD0 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/business-classic.html',
        'prompt'      => "采用经典商务（Classic Business）设计风格。背景浅冷灰 #F4F8FC。导航栏深蓝 #1B4E7A 横栏（sticky top），白色文字。Hero 区深蓝渐变（#1B4E7A → #3A6A9E），白色大标题 + 两个 CTA 按钮（白底 / 透明描边）。卡片白底 + 1px 边框 rgba(20,58,92,0.1)，圆角 4-6px。按钮实心深蓝 / 描边深蓝 / 边框浅灰 三级。输入框 1px 边框 + 聚焦深蓝光环。标签实心蓝 / 浅灰底 / 描边 三种。导航选中态底部深蓝下划线。整体稳重、可靠、克制，适合企业级产品。",
    ],
    // ── 真实示例（已渲染截图）：仪表盘 ──
    [
        'id'          => 'dashboard',
        'name-zh'     => '仪表盘',
        'name-en'     => 'Dashboard',
        'desc-zh'     => '数据密集型控制台 + KPI 卡片 + 等宽数字，深色高效',
        'desc-en'     => 'Data-dense console with KPI cards and monospace numbers',
        'tags'        => ['dashboard', 'data', 'console', 'kpi', '数据', '控制台', '等宽', 'desktop'],
        'cat'         => 'desktop',
        'thumb-image' => 'list/images/dashboard.png',
        'thumb'       => 'linear-gradient(135deg, #0F2438 0%, #15324C 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/dashboard.html',
        'prompt'      => "采用仪表盘（Dashboard）设计风格。背景深色 #0F2438。顶部 KPI 区 4 列网格（等宽数字 JetBrains Mono 800 字重），正增长绿色 #6BCB77 / 负增长红色 #FF6B6B。卡片背景 #15324C + 1px 边框 rgba(255,255,255,0.1)，圆角 8px。主色天蓝 #73B9E6。按钮主要蓝 / 成功绿 / 危险红描边三级。输入框等宽字体 + 深底。状态标签 4 种语义色（默认/成功/警告/危险）。导航选中态底部蓝色下划线。数字使用等宽字体 JetBrains Mono。所有图表克制、留白适度。整体数据密集、高效、专业。",
    ],
    // ── 真实示例（已渲染截图）：移动卡片 ──
    [
        'id'          => 'mobile-card',
        'name-zh'     => '移动卡片',
        'name-en'     => 'Mobile Card',
        'desc-zh'     => '竖屏卡片流 + 底部 Tab Bar + 按压缩放，便于一指操作',
        'desc-en'     => 'Vertical card stream with bottom tab bar, easy thumb operation',
        'tags'        => ['mobile', 'card', 'thumb', 'tabbar', '卡片', '移动', '触屏', 'mobile'],
        'cat'         => 'mobile',
        'thumb-image' => 'list/images/mobile-card.png',
        'thumb'       => 'linear-gradient(135deg, #C5DFF0 0%, #EAF2F8 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/mobile-card.html',
        'prompt'      => "采用移动卡片（Mobile Card）设计风格。背景 #F4F8FC 浅灰蓝。卡片白底 + 圆角 12px + 垂直间距 12px + 微弱投影。底部 Tab Bar 高 56px，图标为主、文字为辅。点击态 0.96 缩放 100ms 反馈。间距偏宽，适合触摸。按钮无边框 + 按压缩放。输入框无边框 + 浅灰底 + 聚焦放大。导航 iOS 风格分段控件。整体竖屏卡片流，便于一指操作。",
    ],
    // ── 真实示例（已渲染截图）：视频流 ──
    [
        'id'          => 'video-stream',
        'name-zh'     => '视频流',
        'name-en'     => 'Video Stream',
        'desc-zh'     => '深色沉浸 + 封面蒙层 + 头像时长，让视频成为主角',
        'desc-en'     => 'Dark immersive with cover gradient mask, avatar and duration',
        'tags'        => ['video', 'dark', 'media', 'stream', '视频', '暗色', '沉浸', 'mobile'],
        'cat'         => 'mobile',
        'thumb-image' => 'list/images/video-stream.png',
        'thumb'       => 'linear-gradient(135deg, #0E2E4A 0%, #1B4E7A 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/video-stream.html',
        'prompt'      => "采用视频流（Video Stream）设计风格。背景深海军蓝 #0E2E4A 全屏沉浸。卡片无边框，封面占主（16:9 渐变），标题压于底部渐变蒙层（transparent → rgba(0,0,0,0.7)）。文字白色，标题字重 600。圆角 8px。头像 32px 圆角 + 边框。视频时长右下角半透明黑色标签。按钮主要蓝 / 描边 / 红色危险三级。底部 Tab Bar 深色玻璃。整体低对比、不抢戏，让视频成为主角。",
    ],
    // ── 真实示例（已渲染截图）：小程序简约 ──
    [
        'id'          => 'miniapp-minimal',
        'name-zh'     => '小程序简约',
        'name-en'     => 'MiniApp Minimal',
        'desc-zh'     => '微信风绿色点缀 + 胶囊菜单 + 统一圆角，简洁克制',
        'desc-en'     => 'WeChat-style green accent, capsule menu, unified radii, clean and restrained',
        'tags'        => ['miniapp', 'wechat', 'minimal', 'green', '小程序', '微信', '胶囊', 'miniapp'],
        'cat'         => 'miniapp',
        'thumb-image' => 'list/images/miniapp-minimal.png',
        'thumb'       => 'linear-gradient(135deg, #EAF2F8 0%, #F4F8FC 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/miniapp-minimal.html',
        'prompt'      => "采用小程序简约（MiniApp Minimal）设计风格。背景 #F4F8FC 浅灰。卡片背景 #FFFFFF，圆角 8px，1px 边框 #EDEDED，微弱投影。主色微信绿 #1AAD19（点缀不要多，仅按钮、选中态、标签使用）。按钮实心绿 / 描边绿 / 幽灵 三级，圆角 4px。输入框浅灰底 + 聚焦绿色光环。顶部胶囊菜单距右。底部 Tab Bar 白色 + 绿色选中态。整体简洁、克制，微信小程序经典风格。",
    ],
    // ── 真实示例（已渲染截图）：复古像素 ──
    [
        'id'          => 'retro-pixel',
        'name-zh'     => '复古像素',
        'name-en'     => 'Retro Pixel',
        'desc-zh'     => '8-bit 像素风 + Press Start 2P 字体 + 硬边框，童年怀旧',
        'desc-en'     => '8-bit pixel aesthetic with Press Start 2P font and hard borders',
        'tags'        => ['pixel', 'retro', '8bit', 'nostalgic', '像素', '复古', '怀旧', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/retro-pixel.png',
        'thumb'       => 'linear-gradient(135deg, #FFD93D 0%, #FF6B6B 50%, #4D96FF 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/retro-pixel.html',
        'prompt'      => "采用复古像素（Retro Pixel）设计风格。字体使用像素字体 Press Start 2P（8-16px），全大写。颜色 8-bit 调色板：红 #FF6B6B、黄 #FFD93D、蓝 #4D96FF、绿 #6BCB77。边框硬边 2-3px 实线，无圆角（border-radius: 0）。image-rendering: pixelated。背景深色 #1A1A2E。卡片 #2D2D44 + 3px 白色硬边框。阴影 text-shadow 4px 4px 0（无模糊）。按钮 hover 反色，按下 translate(2px,2px)。整体 8-bit 怀旧、像素化、童年感。",
    ],
    // ── 真实示例（已渲染截图）：日式和风 ──
    [
        'id'          => 'japanese-wafu',
        'name-zh'     => '日式和风',
        'name-en'     => 'Japanese Wafu',
        'desc-zh'     => '米白 + 朱红 + 侘寂留白 + 印章点缀，和の美意識',
        'desc-en'     => 'Cream + vermilion, wabi-sabi whitespace, seal accents, Japanese aesthetics',
        'tags'        => ['japanese', 'wafu', 'minimal', 'zen', '和风', '侘寂', '朱红', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/japanese-wafu.png',
        'thumb'       => 'linear-gradient(135deg, #F5EDE0 0%, #E8D3B7 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/japanese-wafu.html',
        'prompt'      => "采用日式和风（Japanese Wafu）设计风格。背景米白 #F5EDE0，侘寂留白。主色朱红 #B23A2F + 墨黑 #1F1F1F。字体衬线 Noto Serif JP，标题字重 900，字距 0.04em。Hero 右上角朱红圆形印章。卡片白底 + 1px 浅灰边框，hover 朱红边框。按钮线框/实心朱红/幽灵 三级。输入框仅底线，聚焦朱红。导航底部朱红下划线。左侧朱红条 + 浅红底提示。整体不均斉、簡素、枯高。",
    ],
    // ── 真实示例（已渲染截图）：瑞士极简 ──
    [
        'id'          => 'swiss-design',
        'name-zh'     => '瑞士极简',
        'name-en'     => 'Swiss Design',
        'desc-zh'     => '非对称网格 + 无衬线大字 + 朱红重音，形式服从功能',
        'desc-en'     => 'Asymmetric grid, sans-serif large headlines, red accent, form follows function',
        'tags'        => ['swiss', 'grid', 'helvetica', 'asymmetric', '瑞士', '极简', '朱红', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/swiss-design.png',
        'thumb'       => 'linear-gradient(135deg, #FFFFFF 0%, #F4F8FC 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/swiss-design.html',
        'prompt'      => "采用瑞士极简（Swiss Design）设计风格。背景纯白 #FFFFFF。字体无衬线 Helvetica Neue / Inter，标题极大（clamp 2.5-6rem），字重 800，字距 -0.04em，行高 0.95。布局非对称网格（7:5 分栏），元素左对齐。强调色朱红 #E63946。圆角 0。边框 2px 硬实线。按钮大写 + 宽字距 0.04em + hover 反色。组件标题全大写。所有元素极致克制、理性、几何。形式服从功能。",
    ],
    // ── 真实示例（已渲染截图）：渐变梦幻 ──
    [
        'id'          => 'gradient-dream',
        'name-zh'     => '渐变梦幻',
        'name-en'     => 'Gradient Dream',
        'desc-zh'     => '粉紫渐变 + 玻璃卡 + 柔和大色斑，梦幻柔和',
        'desc-en'     => 'Pink-purple gradient + glass cards + soft orbs, dreamy and soft',
        'tags'        => ['gradient', 'dream', 'pastel', 'glass', '渐变', '梦幻', '柔和', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/gradient-dream.png',
        'thumb'       => 'linear-gradient(135deg, #FFAFBD 0%, #A18CD1 50%, #FBC2EB 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/gradient-dream.html',
        'prompt'      => "采用渐变梦幻（Gradient Dream）设计风格。背景粉紫渐变 linear-gradient(135deg, #FFAFBD 0%, #A18CD1 50%, #FBC2EB 100%)，20s 缓动漂移。柔和漂浮大色斑（粉 #FFD1E8 / 紫 #A18CD1 / 粉 #FBC2EB）以 16-20s 周期缓慢漂移。卡片使用 rgba(255,255,255,0.5) + backdrop-filter: blur(20px) saturate(180%)，1px 白色半透明边框，圆角 20-24px，柔和紫色投影。文字深紫 #2D1B4E。按钮玻璃胶囊 + 渐变主按钮。整体梦幻、柔和、女性向。",
    ],
    // ── 真实示例（已渲染截图）：文档型 ──
    [
        'id'          => 'documentation',
        'name-zh'     => '文档型',
        'name-en'     => 'Documentation',
        'desc-zh'     => '代码块 + 侧边导航 + 左侧色条标题，开发者友好',
        'desc-en'     => 'Code blocks + sidebar nav + left accent bar, developer-friendly',
        'tags'        => ['docs', 'code', 'developer', 'sidebar', '文档', '代码', 'API', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/documentation.png',
        'thumb'       => 'linear-gradient(135deg, #FBFDFF 0%, #F4F8FC 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/documentation.html',
        'prompt'      => "采用文档型（Documentation）设计风格。背景纯白 #FFFFFF。左侧固定导航 240px，主区最大宽 800px。标题字重 700，h2 左侧 3px 深蓝 #1B4E7A 色条。代码块背景 #0F2438 + 等宽字体 SF Mono / JetBrains Mono，语法高亮：注释灰 #8BA4BC、关键字蓝 #73B9E6、字符串绿 #6BCB77。链接主色 #1B4E7A。卡片浅灰底 + 1px 边框，圆角 6px。整体干净、结构化、开发者友好。",
    ],
    // ── 真实示例（已渲染截图）：404 页面 ──
    [
        'id'          => 'not-found',
        'name-zh'     => '404 页面',
        'name-en'     => '404 Page',
        'desc-zh'     => '大号等宽数字 + 搜索框 + 行动指引，有意义的错误状态',
        'desc-en'     => 'Large monospace number + search + actions, meaningful error state',
        'tags'        => ['404', 'error', 'search', 'not-found', '错误', '状态', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/not-found.png',
        'thumb'       => 'linear-gradient(135deg, #F4F8FC 0%, #FFFFFF 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/not-found.html',
        'prompt'      => "采用 404 页面（404 Page）设计风格。背景浅灰蓝 #F4F8FC。页面居中显示大号等宽数字 404（clamp 6-10rem，天蓝 #73B9E6，3s 浮动动画）。下方标题 + 描述 + 搜索框 + 两个操作按钮（返回首页 / 查看帮助）。底部常用链接横排。整体有意义的错误状态，不空白、不冷漠，给予用户行动指引。",
    ],
    // ── 真实示例（已渲染截图）：滚动吸附 ──
    [
        'id'          => 'scroll-snap',
        'name-zh'     => '滚动吸附',
        'name-en'     => 'Scroll Snap',
        'desc-zh'     => '五色全屏逐章 + 右侧圆点导航 + 纯 CSS 吸附，逐页故事叙述',
        'desc-en'     => 'Five full-screen chapters, dot navigation, pure CSS snap, storytelling',
        'tags'        => ['scroll', 'snap', 'fullscreen', 'story', '吸附', '全屏', '故事', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/scroll-snap.png',
        'thumb'       => 'linear-gradient(135deg, #FF6B6B 0%, #FFD93D 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/scroll-snap.html',
        'prompt'      => "采用滚动吸附（Scroll Snap）设计风格。html 设置 scroll-snap-type: y mandatory。每个 section 占满 100vh（scroll-snap-align: start）。五色依次推进：红 #FF6B6B、黄 #FFD93D、绿 #6BCB77、蓝 #4D96FF、紫 #A18CD1。每节大号数字（01-05）+ 标题 + 描述。右侧固定圆点指示器。组件区深色底 + 1px 亮边。整体逐页故事叙述，视觉节奏明快。",
    ],
    // ── 真实示例（已渲染截图）：骨架屏 ──
    [
        'id'          => 'skeleton-loading',
        'name-zh'     => '骨架屏',
        'name-en'     => 'Skeleton Loading',
        'desc-zh'     => '形状匹配的 Shimmer 加载占位 + 一键切换真实/骨架',
        'desc-en'     => 'Shape-matched shimmer loading placeholders, toggle real/skeleton',
        'tags'        => ['skeleton', 'loading', 'shimmer', '骨架', '加载', '占位', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/skeleton-loading.png',
        'thumb'       => 'linear-gradient(135deg, #E8ECF0 0%, #F0F3F6 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/skeleton-loading.html',
        'prompt'      => "采用骨架屏（Skeleton Loading）设计风格。加载占位使用形状匹配的 shimmer 动画（linear-gradient 90deg, 背景色 #E8ECF0 → #F0F3F6 → #E8ECF0，1.5s 循环）。按钮骨架 80x36px，输入框 100%x36px，卡片头像 40px 圆形 + 文字条，标签 56x24px，导航 100%x32px，提示 100%x40px。所有骨架形状与真实内容一一对应，避免布局跳动。整体流畅、专业、减少感知等待。",
    ],
    // ── 真实示例（已渲染截图）：赛博朋克 ──
    [
        'id'          => 'cyberpunk',
        'name-zh'     => '赛博朋克',
        'name-en'     => 'Cyberpunk',
        'desc-zh'     => '霓虹洋红 + 电光青蓝 + 深紫黑底 + 网格扫描线，高对比未来主义',
        'desc-en'     => 'Neon magenta + electric cyan + deep purple, grid scanlines, high-contrast futurism',
        'tags'        => ['cyberpunk', 'neon', 'futuristic', 'magenta', '赛博', '霓虹', '未来', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/cyberpunk.png',
        'thumb'       => 'linear-gradient(135deg, #FF2E97 0%, #12081F 50%, #00E5FF 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/cyberpunk.html',
        'prompt'      => "采用赛博朋克（Cyberpunk）设计风格。背景深紫黑 #12081F / #1A0B2E，叠加 40px 网格扫描线（青蓝 rgba(0,229,255,0.05) + 洋红 rgba(255,46,151,0.05)）。主色洋红 #FF2E97 + 电光青蓝 #00E5FF + 黄色 #FFF200。标题渐变（洋红→青蓝），drop-shadow 发光。卡片深紫底 #1E1035 + 1px 洋红边框 + 发光投影。按钮大写字母 + 宽字距 + 悬停发光。输入框聚焦青蓝发光。标签洋红/青蓝/黄三色。导航洋红发光选中。整体高对比、霓虹、未来感。",
    ],
    // ── 真实示例（已渲染截图）：波普艺术 ──
    [
        'id'          => 'pop-art',
        'name-zh'     => '波普艺术',
        'name-en'     => 'Pop Art',
        'desc-zh'     => '明黄底 + 红蓝黑三色 + 硬边 + 圆点，安迪·沃霍尔式大众文化美学',
        'desc-en'     => 'Yellow background, red/blue/black, hard edges, dots, Warhol-style pop culture',
        'tags'        => ['pop', 'art', 'warhol', 'bold', '波普', '艺术', '明黄', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/pop-art.png',
        'thumb'       => 'linear-gradient(135deg, #FFF225 0%, #ED1C24 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/pop-art.html',
        'prompt'      => "采用波普艺术（Pop Art）设计风格。背景明黄 #FFF225，叠加 16px 圆点图案（radial-gradient 1px 黑色半透明）。主色红 #ED1C24 + 蓝 #0051A8 + 黑 #231F20。标题 6vw 字重 900 红色 + 双层位移阴影（6px 蓝 + 12px 黑），-webkit-text-stroke 2px 黑。卡片白底 + 3px 黑硬边，hover 偏移 -3px + 6px 投影。按钮大写字母 + 3px 硬边 + 按下回弹。整体大胆、重复、沃霍尔式。",
    ],
    // ── 真实示例（已渲染截图）：蒸汽波 ──
    [
        'id'          => 'vaporwave',
        'name-zh'     => '蒸汽波',
        'name-en'     => 'Vaporwave',
        'desc-zh'     => '粉紫渐变 + 青蓝霓虹 + 网格透视 + 宽字距标题，复古未来主义',
        'desc-en'     => 'Pink-purple gradients, cyan neon, grid perspective, retro-futurism',
        'tags'        => ['vaporwave', 'retro', 'futurism', 'neon', '蒸汽波', '复古未来', '迷幻', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/vaporwave.png',
        'thumb'       => 'linear-gradient(135deg, #1A0B2E 0%, #FF6B9D 50%, #38BDF8 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/vaporwave.html',
        'prompt'      => "采用蒸汽波（Vaporwave）设计风格。背景深紫黑渐变 #1A0B2E → #2D1B4E。叠加 60px 网格线（粉 rgba(255,107,157,0.08) + 青 rgba(56,189,248,0.08)）。主色粉 #FF6B9D + 紫 #A18CD1 + 青 #38BDF8 + 青绿 #00D4AA。标题 4.5rem 字重 200 + 宽字距 0.1em + 粉色发光 text-shadow。背景大号装饰符号。卡片深紫半透明 + blur(20px) + 1px 粉边。按钮粉紫渐变 + 发光。整体复古未来主义、迷幻、怀旧。",
    ],
    // ── 真实示例（已渲染截图）：极简日式 ──
    [
        'id'          => 'japandi',
        'name-zh'     => '极简日式',
        'name-en'     => 'Japandi',
        'desc-zh'     => '日式侘寂 + 北欧极简 + 温暖米白 + 陶土色点缀，少即是多',
        'desc-en'     => 'Japanese wabi-sabi meets Nordic minimalism, warm cream, terracotta accents',
        'tags'        => ['japandi', 'minimal', 'warm', 'scandinavian', '极简', '日式', '侘寂', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/japandi.png',
        'thumb'       => 'linear-gradient(135deg, #F7F3EE 0%, #C4956A 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/japandi.html',
        'prompt'      => "采用极简日式（Japandi）设计风格。背景米白 #F7F3EE。主色陶土 #C4956A + 深灰 #2C2C2C。字体衬线 Noto Serif JP。标题字重 300 + 宽字距 0.08em。卡片白底 + 1px 浅灰边框，hover 暖色边框。按钮线框或陶土实心。输入框仅底线，聚焦暖色。导航底部暖色线。整体少即是多，温暖宁静。",
    ],
    // ── 真实示例（已渲染截图）：复古中国风 ──
    [
        'id'          => 'retro-chinese',
        'name-zh'     => '复古中国风',
        'name-en'     => 'Retro Chinese',
        'desc-zh'     => '宣纸米白 + 朱砂红 + 毛笔字体 + 描金点缀，东方气韵',
        'desc-en'     => 'Rice paper cream, cinnabar red, calligraphy font, gold accents, Eastern aesthetics',
        'tags'        => ['chinese', 'retro', 'calligraphy', 'cinnabar', '中国风', '复古', '书法', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/retro-chinese.png',
        'thumb'       => 'linear-gradient(135deg, #F8F0E1 0%, #A62C2C 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/retro-chinese.html',
        'prompt'      => "采用复古中国风（Retro Chinese）设计风格。背景宣纸米白 #F8F0E1。主色朱砂红 #A62C2C + 描金 #B8860B。标题使用毛笔字体 Ma Shan Zheng（4rem + 字距 0.1em）。Hero 背景大号福字水印。卡片米白 #FFFDF8 + 1px 朱砂边框，hover 金色边框。按钮线框或朱砂实心。输入框仅底线聚焦朱砂。导航朱砂底部线。整体东方气韵、文人墨客。",
    ],
    // ── 真实示例（已渲染截图）：暗黑模式 ──
    [
        'id'          => 'dark-mode',
        'name-zh'     => '暗黑模式',
        'name-en'     => 'Dark Mode',
        'desc-zh'     => 'Material Dark + 紫罗兰强调 + 深灰分层，夜间友好',
        'desc-en'     => 'Material Dark, violet accent, layered grays, night-friendly',
        'tags'        => ['dark', 'mode', 'material', 'night', '暗黑', '深色', '夜间', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/dark-mode.png',
        'thumb'       => 'linear-gradient(135deg, #121212 0%, #BB86FC 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/dark-mode.html',
        'prompt'      => "采用暗黑模式（Dark Mode）设计风格。背景纯黑 #121212，分层 #1E1E1E / #2D2D2D。主色紫罗兰 #BB86FC + 青绿 #03DAC6。文字三层 #E0E0E0 / #A0A0A0 / #707070。卡片深灰 + 1px rgba(255,255,255,0.08) 边框。按钮默认深底紫边，主要紫罗兰实心。输入框深底 + 聚焦紫光环。导航紫罗兰下划线。整体 Material Dark、夜间友好。",
    ],
    // ── 真实示例（已渲染截图）：极简北欧风 ──
    [
        'id'          => 'scandinavian',
        'name-zh'     => '极简北欧风',
        'name-en'     => 'Scandinavian',
        'desc-zh'     => '明亮中性色 + 天然木材点缀 + 陶土色，Hygge 舒适理念',
        'desc-en'     => 'Bright neutrals, natural wood accents, warm terracotta, Hygge comfort',
        'tags'        => ['scandinavian', 'nordic', 'hygge', 'minimal', '北欧', '极简', '温暖', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/scandinavian.png',
        'thumb'       => 'linear-gradient(135deg, #FAF9F6 0%, #D9965A 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/scandinavian.html',
        'prompt'      => "采用极简北欧风（Scandinavian）设计风格。背景米白 #FAF9F6。主色陶土 #D9965A + 深灰 #1E1E1E。标题字重 300。卡片白底，hover 浅米。按钮线框或陶土实心。输入框仅底线聚焦陶土。导航陶土底线。整体明亮、舒适、Hygge。",
    ],
    // ── 真实示例（已渲染截图）：新粗野主义 ──
    [
        'id'          => 'neo-brutalism',
        'name-zh'     => '新粗野主义',
        'name-en'     => 'Neo Brutalism',
        'desc-zh'     => '3px 硬边黑 + 6px 偏移投影 + 亮黄荧光粉，毫不妥协',
        'desc-en'     => 'Raw black borders, offset shadows, bold yellow and hot pink, uncompromising',
        'tags'        => ['brutalism', 'raw', 'bold', 'bricolage', '粗野', '硬边', '冲击', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/neo-brutalism.png',
        'thumb'       => 'linear-gradient(135deg, #FFFFFF 0%, #FFD400 50%, #FF5DA2 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/neo-brutalism.html',
        'prompt'      => "采用新粗野主义（Neo Brutalism）设计风格。背景纯白 #FFFFFF。主色黑 #000000 + 亮黄 #FFD400 + 荧光粉 #FF5DA2 + 电光青 #00E5FF。所有元素 3px 硬边黑边框 + 6px 偏移投影 box-shadow。卡片 hover 偏移 -3px + 9px 投影。按钮大写 + 粗体 800。输入框 3px 硬边，聚焦变黄底。导航黑底实心选中。整体粗野、强硬、毫不妥协。",
    ],
    // ── 真实示例（已渲染截图）：包豪斯 ──
    [
        'id'          => 'bauhaus',
        'name-zh'     => '包豪斯',
        'name-en'     => 'Bauhaus',
        'desc-zh'     => '几何构成 + 红黄蓝三原色 + 硬边 + 无衬线大写，形式服从功能',
        'desc-en'     => 'Geometric composition, primary colors, hard edges, form follows function',
        'tags'        => ['bauhaus', 'geometric', 'primary', 'german', '包豪斯', '几何', '三原色', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/bauhaus.png',
        'thumb'       => 'linear-gradient(135deg, #FFFFFF 0%, #E63946 50%, #0051A8 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/bauhaus.html',
        'prompt'      => "采用包豪斯（Bauhaus）设计风格。背景纯白 #FFFFFF。三原色红 #E63946 + 蓝 #0051A8 + 黄 #FFD93D + 黑 #1A1A1A。标题大号黑框 + 4px 边框。所有元素 2-3px 硬边黑边框。卡片 hover 变黄底。按钮线框黑边或红色实心。输入框 2px 硬边，聚焦黄底。导航红色下划线。整体几何构成、形式服从功能。",
    ],
    // ── 真实示例（已渲染截图）：酸性设计 ──
    [
        'id'          => 'acid-design',
        'name-zh'     => '酸性设计',
        'name-en'     => 'Acid Design',
        'desc-zh'     => '霓虹洋红 + 电光青 + 荧光绿 + 网格，90 年代锐舞迷幻',
        'desc-en'     => 'Neon magenta, cyan, fluorescent green, grid, 90s rave psychedelic',
        'tags'        => ['acid', 'rave', 'neon', 'psychedelic', '酸性', '锐舞', '迷幻', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/acid-design.png',
        'thumb'       => 'linear-gradient(135deg, #0A0A0A 0%, #FF00FF 50%, #00FFFF 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/acid-design.html',
        'prompt'      => "采用酸性设计（Acid Design）风格。背景纯黑 #0A0A0A。主色霓虹洋红 #FF00FF + 电光青 #00FFFF + 荧光绿 #39FF14。标题 6rem 字重 900 渐变（洋红→青→绿），drop-shadow 发光。叠加 40px 网格线（洋红/青半透明）。卡片深紫底 #1A1A2E + 1px 洋红边框 + 发光投影。按钮洋红描边或青蓝实心 + 悬停发光。输入框聚焦青蓝发光。整体迷幻、高对比、90 年代锐舞文化。",
    ],
    // ── 真实示例（已渲染截图）：孟菲斯 2.0 ──
    [
        'id'          => 'memphis-2',
        'name-zh'     => '孟菲斯 2.0',
        'name-en'     => 'Memphis 2.0',
        'desc-zh'     => '圆角孟菲斯 + 奶油底 + 圆角硬边 + 偏移投影，80 年代几何复兴',
        'desc-en'     => 'Rounded Memphis, cream base, round hard edges, offset shadows, 80s revival',
        'tags'        => ['memphis', 'rounded', 'geometric', 'colorful', '孟菲斯', '圆角', '几何', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/memphis-2.png',
        'thumb'       => 'linear-gradient(135deg, #F5E6D3 0%, #FF6B6B 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/memphis-2.html',
        'prompt'      => "采用孟菲斯 2.0（Memphis 2.0）设计风格。背景奶油米白 #F5E6D3。主色珊瑚红 #FF6B6B + 天蓝 #4D96FF + 樱草黄 #FFD93D + 薄荷绿 #6BCB77。圆角硬边（8-16px）+ 偏移投影（6px 黑）。卡片白底 + 3px 硬边 + 圆角 16px + 6px 偏移。按钮圆角 8px + 2px 硬边 + 3px 偏移，hover 上移。输入框聚焦黄色 + 偏移。标签胶囊形 + 多彩。整体 80 年代几何复兴，更甜更圆润。",
    ],
    // ── 真实示例（已渲染截图）：玻璃拟态 2 ──
    [
        'id'          => 'glassmorphism-2',
        'name-zh'     => '玻璃拟态 2',
        'name-en'     => 'Glassmorphism 2',
        'desc-zh'     => '薰衣草紫渐变 + 更深玻璃 + 紫色渐变按钮，层次更丰富',
        'desc-en'     => 'Lavender gradient, deeper glass, purple gradient buttons, richer layers',
        'tags'        => ['glass', '2', 'lavender', 'purple', '玻璃', '紫色', '渐变', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/glassmorphism-2.png',
        'thumb'       => 'linear-gradient(135deg, #F0E6FF 0%, #E8F4FD 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/glassmorphism-2.html',
        'prompt'      => "采用玻璃拟态 2（Glassmorphism 2）设计风格。背景薰衣草紫渐变 #F0E6FF → #E8F4FD → #FCE4EC，15s 动画漂移。卡片 rgba(255,255,255,0.4) + blur(20px) + 圆角 20px + 紫色投影。主色薰衣草 #A18CD1 + 紫水晶 #6B46C1。按钮胶囊玻璃 + 渐变主按钮。输入框聚焦紫色光环。整体紫调玻璃、层次丰富。",
    ],
    // ── 真实示例（已渲染截图）：新拟态 2 ──
    [
        'id'          => 'neumorphism-2',
        'name-zh'     => '新拟态 2',
        'name-en'     => 'Neumorphism 2',
        'desc-zh'     => '更深阴影 + 蓝色强调 + 油灰底，Neumorphism 深度演绎',
        'desc-en'     => 'Deeper shadows, blue accent, putty base, deeper neumorphism',
        'tags'        => ['neumorphism', '2', 'soft', 'shadow', 'deep', '新拟态', '阴影', '触感', 'web'],
        'cat'         => 'web',
        'thumb-image' => 'list/images/neumorphism-2.png',
        'thumb'       => 'linear-gradient(135deg, #D9E4F0 0%, #4A90C8 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/neumorphism-2.html',
        'prompt'      => "采用新拟态 2（Neumorphism 2）设计风格。背景油灰蓝 #D9E4F0。双重阴影更强烈：外向 6px 6px 12px rgba(163,177,198,0.8) + 内向 -6px -6px 12px rgba(255,255,255,0.9)。输入框内阴影凹陷。按钮按下时切换内阴影。圆角 12-20px。主色天蓝 #4A90C8。整体触感强烈、柔和、安静。",
    ],
    // ── 真实示例（已渲染截图）：航海风格 ──
    [
        'id'          => 'nautical',
        'name-zh'     => '航海风格',
        'name-en'     => 'Nautical',
        'desc-zh'     => '海军蓝+朱红+绳缆金+米白底，航海传统美学',
        'desc-en'     => '海军蓝+朱红+绳缆金+米白底，航海传统美学',
        'tags'        => ["nautical","navy","sailor","航海","海军","传统","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/nautical.png',
        'thumb'       => 'linear-gradient(135deg, #F0F4F8 0%,#1B3A5C 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/nautical.html',
        'prompt'      => "采用航海风格（Nautical）设计。背景米白 #F0F4F8。主色海军蓝 #1B3A5C + 朱红 #D32F2F + 绳缆金 #C4A35A。卡片白底 + 顶部 3px 海军蓝条。按钮海军蓝实心或描边。导航朱红下划线。整体航海传统、经典可靠。",
    ],
    // ── 真实示例（已渲染截图）：热带风格 ──
    [
        'id'          => 'tropical',
        'name-zh'     => '热带风格',
        'name-en'     => 'Tropical',
        'desc-zh'     => '翡翠绿+珊瑚粉+日光黄+深绿叶，度假天堂',
        'desc-en'     => '翡翠绿+珊瑚粉+日光黄+深绿叶，度假天堂',
        'tags'        => ["tropical","beach","palm","热带","度假","绿色","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/tropical.png',
        'thumb'       => 'linear-gradient(135deg, #0D6B4A 0%,#FF6B6B 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/tropical.html',
        'prompt'      => "采用热带风格（Tropical）设计。背景深翡翠绿 #0D6B4A。主色珊瑚粉 #FF6B6B + 日光黄 #FFD93D + 棕榈绿 #2D8A4E。卡片深绿底 + 1px 金边。按钮珊瑚粉实心。导航金色下划线。整体度假天堂、热带风情。",
    ],
    // ── 真实示例（已渲染截图）：蒸汽朋克 ──
    [
        'id'          => 'steampunk',
        'name-zh'     => '蒸汽朋克',
        'name-en'     => 'Steampunk',
        'desc-zh'     => '铜色+深褐+黄铜+齿轮纹，维多利亚工业幻想',
        'desc-en'     => '铜色+深褐+黄铜+齿轮纹，维多利亚工业幻想',
        'tags'        => ["steampunk","vintage","brass","蒸汽朋克","复古","工业","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/steampunk.png',
        'thumb'       => 'linear-gradient(135deg, #2C1810 0%,#C4956A 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/steampunk.html',
        'prompt'      => "采用蒸汽朋克（Steampunk）设计。背景深褐 #2C1810。主色黄铜 #C4956A + 铜绿 #5A8A7A + 锈红 #A0522D。卡片深褐底 + 1px 黄铜边框。按钮黄铜金属色。导航铜色下划线。整体维多利亚工业幻想。",
    ],
    // ── 真实示例（已渲染截图）：可爱风格 ──
    [
        'id'          => 'kawaii',
        'name-zh'     => '可爱风格',
        'name-en'     => 'Kawaii',
        'desc-zh'     => '粉彩+圆角+小图标+柔和渐变，日系卡哇伊',
        'desc-en'     => '粉彩+圆角+小图标+柔和渐变，日系卡哇伊',
        'tags'        => ["kawaii","cute","pastel","japanese","可爱","粉彩","日系","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/kawaii.png',
        'thumb'       => 'linear-gradient(135deg, #FFE4E6 0%,#F0ABFC 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/kawaii.html',
        'prompt'      => "采用可爱风格（Kawaii）设计。背景粉彩渐变 #FFE4E6 → #F0ABFC。主色樱花粉 #FF8FAB + 薰衣草 #A18CD1 + 薄荷 #6BCB77。卡片白底 + 圆角 20px + 柔和投影。按钮圆角胶囊 + 粉色。导航圆角分段。整体日系卡哇伊、可爱。",
    ],
    // ── 真实示例（已渲染截图）：哥特风格 ──
    [
        'id'          => 'gothic',
        'name-zh'     => '哥特风格',
        'name-en'     => 'Gothic',
        'desc-zh'     => '纯黑+暗红+银色+尖拱，暗黑浪漫主义',
        'desc-en'     => '纯黑+暗红+银色+尖拱，暗黑浪漫主义',
        'tags'        => ["gothic","dark","romantic","victorian","哥特","暗黑","浪漫","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/gothic.png',
        'thumb'       => 'linear-gradient(135deg, #0A0A0A 0%,#8B0000 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/gothic.html',
        'prompt'      => "采用哥特风格（Gothic）设计。背景纯黑 #0A0A0A。主色暗红 #8B0000 + 银灰 #C0C0C0。卡片黑底 + 1px 暗红边框。按钮暗红实心。导航暗红下划线。整体暗黑浪漫主义、哥特。",
    ],
    // ── 真实示例（已渲染截图）：装饰艺术 ──
    [
        'id'          => 'art-deco',
        'name-zh'     => '装饰艺术',
        'name-en'     => 'Art Deco',
        'desc-zh'     => '黑色+金色+几何+对称，1920 年代奢华',
        'desc-en'     => '黑色+金色+几何+对称，1920 年代奢华',
        'tags'        => ["art","deco","gold","geometry","装饰","艺术","奢华","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/art-deco.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%,#D4AF37 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/art-deco.html',
        'prompt'      => "采用装饰艺术（Art Deco）设计。背景黑 #1A1A1A。主色金 #D4AF37 + 象牙 #FFFFF0。卡片黑底 + 1px 金边。按钮金色实心。导航金色下划线。整体 1920 年代奢华、几何对称。",
    ],
    // ── 真实示例（已渲染截图）：太空风格 ──
    [
        'id'          => 'space',
        'name-zh'     => '太空风格',
        'name-en'     => 'Space',
        'desc-zh'     => '深空蓝+星云紫+银白+星星，宇宙探索',
        'desc-en'     => '深空蓝+星云紫+银白+星星，宇宙探索',
        'tags'        => ["space","galaxy","star","cosmic","太空","宇宙","星云","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/space.png',
        'thumb'       => 'linear-gradient(135deg, #0B0B2B 0%,#6B2FA0 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/space.html',
        'prompt'      => "采用太空风格（Space）设计。背景深空蓝 #0B0B2B。主色星云紫 #6B2FA0 + 银白 #E0E0FF。卡片深蓝底 + 1px 紫边。按钮紫色实心。导航星云紫下划线。整体宇宙探索、星辰大海。",
    ],
    // ── 真实示例（已渲染截图）：海岸风格 ──
    [
        'id'          => 'coastal',
        'name-zh'     => '海岸风格',
        'name-en'     => 'Coastal',
        'desc-zh'     => '沙滩白+海浪蓝+贝壳粉，轻松海滨生活',
        'desc-en'     => '沙滩白+海浪蓝+贝壳粉，轻松海滨生活',
        'tags'        => ["coastal","beach","ocean","summer","海岸","海滨","度假","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/coastal.png',
        'thumb'       => 'linear-gradient(135deg, #F5F0EB 0%,#7EC8E3 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/coastal.html',
        'prompt'      => "采用海岸风格（Coastal）设计。背景沙滩白 #F5F0EB。主色海浪蓝 #7EC8E3 + 贝壳粉 #F4A0A0。卡片白底 + 1px 浅蓝边。按钮蓝色实心。导航海浪蓝下划线。整体轻松海滨生活。",
    ],
    // ── 真实示例（已渲染截图）：极简暗黑 ──
    [
        'id'          => 'minimal-dark',
        'name-zh'     => '极简暗黑',
        'name-en'     => 'Minimal Dark',
        'desc-zh'     => '纯黑+单色白+极致留白，少到极致',
        'desc-en'     => '纯黑+单色白+极致留白，少到极致',
        'tags'        => ["minimal","dark","black","monochrome","极简","暗黑","黑白","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/minimal-dark.png',
        'thumb'       => 'linear-gradient(135deg, #000000 0%,#333333 100%)',
        'thumb-dark'  => true,
        'file'        => 'list/minimal-dark.html',
        'prompt'      => "采用极简暗黑（Minimal Dark）设计。背景纯黑 #000000。主色白 #FFFFFF + 灰 #666。卡片黑底 + 1px 灰边。按钮白色实心黑字。导航白色下划线。整体少到极致、黑白极简。",
    ],
    // ── 真实示例（已渲染截图）：暖秋风格 ──
    [
        'id'          => 'warm-autumn',
        'name-zh'     => '暖秋风格',
        'name-en'     => 'Warm Autumn',
        'desc-zh'     => '枫叶红+南瓜橙+麦穗金，丰收温暖',
        'desc-en'     => '枫叶红+南瓜橙+麦穗金，丰收温暖',
        'tags'        => ["autumn","warm","orange","fall","秋天","暖色","丰收","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/warm-autumn.png',
        'thumb'       => 'linear-gradient(135deg, #F5E6D3 0%,#D35400 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/warm-autumn.html',
        'prompt'      => "采用暖秋风格（Warm Autumn）设计。背景奶油米白 #F5E6D3。主色枫叶红 #D35400 + 南瓜橙 #E67E22 + 麦穗金 #F1C40F。卡片白底 + 1px 橙边。按钮橙色实心。导航枫叶红下划线。整体丰收温暖、秋日。",
    ],

    // ── 真实示例（已渲染截图）：深酒窖 ──
    [
        'id'          => 'dark-vineyard',
        'name-zh'     => '深酒窖',
        'name-en'     => 'Dark Vineyard',
        'desc-zh'     => '酒红深棕+勃艮第+墨绿+金褐，醇厚窖藏',
        'desc-en'     => '酒红深棕+勃艮第+墨绿+金褐，醇厚窖藏',
        'tags'        => ["dark","vineyard","wine","burgundy","深色","酒窖","品质","desktop"],
        'cat'         => 'desktop',
        'thumb-image' => 'list/images/dark-vineyard.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/dark-vineyard.html',
        'prompt'      => "采用深酒窖（Dark Vineyard）设计。背景酒红深棕 #2D1B1B。主色勃艮第 #722F37 + 墨绿 #1B2D1B。卡片深底 + 1px 金褐边。按钮酒红实心。整体醇厚、窖藏、品质。",
    ],
    // ── 真实示例（已渲染截图）：赛博后室 ──
    [
        'id'          => 'cyber-lofi',
        'name-zh'     => '赛博后室',
        'name-en'     => 'Cyber Lo-fi',
        'desc-zh'     => '深紫+霓虹紫+暖橙，lo-fi赛博放松夜生活',
        'desc-en'     => '深紫+霓虹紫+暖橙，lo-fi赛博放松夜生活',
        'tags'        => ["cyber","lofi","purple","vapor","赛博","后室","夜生活","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/cyber-lofi.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/cyber-lofi.html',
        'prompt'      => "采用赛博后室（Cyber Lo-fi）设计。背景深紫 #1B1030。主色霓虹紫 #8A2BE2 + 暖橙 #FF8C42。卡片深紫底 + 1px 紫边。按钮霓虹紫实心。整体 lo-fi 赛博、放松、夜生活。",
    ],
    // ── 真实示例（已渲染截图）：粗野红 ──
    [
        'id'          => 'brutal-red',
        'name-zh'     => '粗野红',
        'name-en'     => 'Brutal Red',
        'desc-zh'     => '米白+血红+3px硬边，硬朗果断力量',
        'desc-en'     => '米白+血红+3px硬边，硬朗果断力量',
        'tags'        => ["brutal","red","bold","strong","粗野","红色","硬朗","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/brutal-red.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/brutal-red.html',
        'prompt'      => "采用粗野红（Brutal Red）设计。背景米白。主色血红 #8B0000。卡片白底 + 3px 红硬边。按钮血红实心。导航红色下划线。整体硬朗、果断、力量。",
    ],
    // ── 真实示例（已渲染截图）：象牙奢华 ──
    [
        'id'          => 'ivory-lux',
        'name-zh'     => '象牙奢华',
        'name-en'     => 'Ivory Luxury',
        'desc-zh'     => '象牙白+金棕+米灰，奢华精致高级感',
        'desc-en'     => '象牙白+金棕+米灰，奢华精致高级感',
        'tags'        => ["ivory","luxury","gold","elegant","象牙","奢华","高级","desktop"],
        'cat'         => 'desktop',
        'thumb-image' => 'list/images/ivory-lux.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/ivory-lux.html',
        'prompt'      => "采用象牙奢华（Ivory Luxury）设计。背景象牙白 #FFFFF0。主色金棕 #B8860B + 米灰。卡片象牙底 + 1px 金边。按钮金色实心。整体奢华、精致、高级感。",
    ],
    // ── 真实示例（已渲染截图）：深海 ──
    [
        'id'          => 'ocean-deep',
        'name-zh'     => '深海',
        'name-en'     => 'Ocean Deep',
        'desc-zh'     => '深海蓝+珊瑚青+荧光蓝，深邃静谧海洋',
        'desc-en'     => '深海蓝+珊瑚青+荧光蓝，深邃静谧海洋',
        'tags'        => ["ocean","deep","blue","sea","深海","蓝色","海洋","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/ocean-deep.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/ocean-deep.html',
        'prompt'      => "采用深海（Ocean Deep）设计。背景深海蓝 #0B1D2B。主色珊瑚青 #38B8D8 + 荧光蓝。卡片深蓝底 + 1px 青边。按钮珊瑚青实心。整体深邃、静谧、海洋。",
    ],
    // ── 真实示例（已渲染截图）：像素暗黑 ──
    [
        'id'          => 'pixel-dark',
        'name-zh'     => '像素暗黑',
        'name-en'     => 'Pixel Dark',
        'desc-zh'     => '暗黑蓝+霓虹紫+荧光绿+2px硬边，复古游戏暗黑',
        'desc-en'     => '暗黑蓝+霓虹紫+荧光绿+2px硬边，复古游戏暗黑',
        'tags'        => ["pixel","dark","game","retro","像素","暗黑","复古","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/pixel-dark.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/pixel-dark.html',
        'prompt'      => "采用像素暗黑（Pixel Dark）设计。背景暗黑蓝 #0A0A1E。主色霓虹紫 #7B2FE0 + 荧光绿。卡片暗底 + 2px 硬边。按钮荧光色。整体复古游戏暗黑。",
    ],
    // ── 真实示例（已渲染截图）：极简绿 ──
    [
        'id'          => 'minimal-green',
        'name-zh'     => '极简绿',
        'name-en'     => 'Minimal Green',
        'desc-zh'     => '浅绿白+深绿，清新自然克制',
        'desc-en'     => '浅绿白+深绿，清新自然克制',
        'tags'        => ["minimal","green","fresh","nature","极简","绿色","清新","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/minimal-green.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/minimal-green.html',
        'prompt'      => "采用极简绿（Minimal Green）设计。背景浅绿白 #F2F7F0。主色深绿 #2D4A2D。卡片白底 + 1px 浅绿边。按钮深绿实心。整体清新、自然、克制。",
    ],
    // ── 真实示例（已渲染截图）：日落霓虹 ──
    [
        'id'          => 'sunset-neon',
        'name-zh'     => '日落霓虹',
        'name-en'     => 'Sunset Neon',
        'desc-zh'     => '日落渐变+霓虹紫+玻璃，黄昏热烈霓虹',
        'desc-en'     => '日落渐变+霓虹紫+玻璃，黄昏热烈霓虹',
        'tags'        => ["sunset","neon","gradient","glass","日落","霓虹","热烈","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/sunset-neon.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/sunset-neon.html',
        'prompt'      => "采用日落霓虹（Sunset Neon）设计。背景日落渐变 #FF6B6B → #FF8C42 → #FFD93D。主色霓虹紫 #8A2BE2。卡片半透明玻璃 + blur。整体黄昏、霓虹、热烈。",
    ],
    // ── 真实示例（已渲染截图）：单色红 ──
    [
        'id'          => 'mono-red',
        'name-zh'     => '单色红',
        'name-en'     => 'Mono Red',
        'desc-zh'     => '全站仅红色系+浅红白，单一强烈专注',
        'desc-en'     => '全站仅红色系+浅红白，单一强烈专注',
        'tags'        => ["mono","red","single","color","单色","红色","强烈","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/mono-red.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/mono-red.html',
        'prompt'      => "采用单色红（Mono Red）设计。背景浅红白。全站仅红色系。卡片白底 + 1px 红边。按钮血红实心。导航红色下划线。整体单一、强烈、专注。",
    ],
    // ── 真实示例（已渲染截图）：森林 ──
    [
        'id'          => 'forest',
        'name-zh'     => '森林',
        'name-en'     => 'Forest',
        'desc-zh'     => '浅林绿白+深林绿+苔藓，自然沉稳生态',
        'desc-en'     => '浅林绿白+深林绿+苔藓，自然沉稳生态',
        'tags'        => ["forest","green","nature","trees","森林","绿色","自然","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/forest.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/forest.html',
        'prompt'      => "采用森林（Forest）设计。背景浅林绿白 #F0F5F0。主色深林绿 #1B3A2B + 苔藓 #5A7A4A。卡片白底 + 1px 林绿边。按钮深绿实心。整体自然、沉稳、生态。",
    ],
    // ── 真实示例（已渲染截图）：暗色玻璃 ──
    [
        'id'          => 'glass-dark',
        'name-zh'     => '暗色玻璃',
        'name-en'     => 'Glass Dark',
        'desc-zh'     => '深紫黑+半透明玻璃+blur+白边，通透高级',
        'desc-en'     => '深紫黑+半透明玻璃+blur+白边，通透高级',
        'tags'        => ["glass","dark","translucent","blur","暗色","玻璃","通透","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/glass-dark.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/glass-dark.html',
        'prompt'      => "采用暗色玻璃（Glass Dark）设计。背景深紫黑。卡片半透明黑 + blur(30px) + 1px 白边。按钮玻璃胶囊。整体暗色玻璃、通透、高级。",
    ],
    // ── 真实示例（已渲染截图）：向日葵 ──
    [
        'id'          => 'sunflower',
        'name-zh'     => '向日葵',
        'name-en'     => 'Sunflower',
        'desc-zh'     => '暖黄白+葵黄+深棕，阳光明亮温暖',
        'desc-en'     => '暖黄白+葵黄+深棕，阳光明亮温暖',
        'tags'        => ["sunflower","yellow","warm","sunny","向日葵","黄色","阳光","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/sunflower.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/sunflower.html',
        'prompt'      => "采用向日葵（Sunflower）设计。背景暖黄白 #FFF8E1。主色向日葵黄 #F1C40F + 深棕。卡片白底 + 1px 金黄边。按钮葵黄实心。整体阳光、明亮、温暖。",
    ],
    // ── 真实示例（已渲染截图）：薰衣草 ──
    [
        'id'          => 'lavender',
        'name-zh'     => '薰衣草',
        'name-en'     => 'Lavender',
        'desc-zh'     => '浅紫白+薰衣草紫，浪漫宁静治愈',
        'desc-en'     => '浅紫白+薰衣草紫，浪漫宁静治愈',
        'tags'        => ["lavender","purple","romantic","calm","薰衣草","紫色","浪漫","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/lavender.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/lavender.html',
        'prompt'      => "采用薰衣草（Lavender）设计。背景浅紫白 #F5F0FF。主色薰衣草紫 #7B5FA8。卡片白底 + 1px 紫边。按钮紫色实心。整体浪漫、宁静、治愈。",
    ],
    // ── 真实示例（已渲染截图）：樱桃 ──
    [
        'id'          => 'cherry',
        'name-zh'     => '樱桃',
        'name-en'     => 'Cherry',
        'desc-zh'     => '浅粉白+樱桃红，甜美清新果味',
        'desc-en'     => '浅粉白+樱桃红，甜美清新果味',
        'tags'        => ["cherry","red","sweet","fresh","樱桃","红色","甜美","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/cherry.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/cherry.html',
        'prompt'      => "采用樱桃（Cherry）设计。背景浅粉白。主色樱桃红 #C0392B。卡片白底 + 1px 樱桃红边。按钮樱桃红实心。整体甜美、清新、果味。",
    ],
    // ── 真实示例（已渲染截图）：水墨 ──
    [
        'id'          => 'ink-wash',
        'name-zh'     => '水墨',
        'name-en'     => 'Ink Wash',
        'desc-zh'     => '宣纸白+墨黑+淡灰+衬线，东方水墨意境',
        'desc-en'     => '宣纸白+墨黑+淡灰+衬线，东方水墨意境',
        'tags'        => ["ink","wash","chinese","calligraphy","水墨","东方","意境","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/ink-wash.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/ink-wash.html',
        'prompt'      => "采用水墨（Ink Wash）设计。背景宣纸白。主色墨黑 + 淡灰。字体衬线。卡片宣纸底 + 毛笔边框。按钮墨色。整体东方水墨、留白、意境。",
    ],

    // ── 真实示例（已渲染截图）：腮红粉彩 ──
    [
        'id'          => 'pastel-blush',
        'name-zh'     => '腮红粉彩',
        'name-en'     => 'Pastel Blush',
        'desc-zh'     => '浅粉白+玫瑰粉+奶油白，温柔甜美粉嫩',
        'desc-en'     => '浅粉白+玫瑰粉+奶油白，温柔甜美粉嫩',
        'tags'        => ["pastel","blush","pink","soft","粉彩","腮红","温柔","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/pastel-blush.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/pastel-blush.html',
        'prompt'      => "采用腮红粉彩（Pastel Blush）设计。背景浅粉白 #FFF0F5。主色玫瑰粉 #E8809C + 奶油白。卡片白底 + 1px 粉边。按钮玫瑰粉实心。整体温柔、甜美、粉嫩。",
    ],
    // ── 真实示例（已渲染截图）：亮色玻璃 ──
    [
        'id'          => 'light-glass',
        'name-zh'     => '亮色玻璃',
        'name-en'     => 'Light Glass',
        'desc-zh'     => '薄荷绿白+半透明玻璃+blur，轻盈通透清新',
        'desc-en'     => '薄荷绿白+半透明玻璃+blur，轻盈通透清新',
        'tags'        => ["light","glass","mint","translucent","亮色","玻璃","清新","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/light-glass.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/light-glass.html',
        'prompt'      => "采用亮色玻璃（Light Glass）设计。背景薄荷绿白 #E8F8F5。卡片半透明白 + blur(30px) + 1px 白边 + 柔和投影。主色薄荷 #38C8A0。整体轻盈、通透、清新。",
    ],
    // ── 真实示例（已渲染截图）：皇家深蓝 ──
    [
        'id'          => 'deep-royal',
        'name-zh'     => '皇家深蓝',
        'name-en'     => 'Deep Royal',
        'desc-zh'     => '深皇家蓝+皇家金+宝石蓝，皇家尊贵庄重',
        'desc-en'     => '深皇家蓝+皇家金+宝石蓝，皇家尊贵庄重',
        'tags'        => ["royal","blue","gold","luxury","皇家","深蓝","尊贵","desktop"],
        'cat'         => 'desktop',
        'thumb-image' => 'list/images/deep-royal.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/deep-royal.html',
        'prompt'      => "采用皇家深蓝（Deep Royal）设计。背景深皇家蓝 #0B1D3A。主色皇家金 #D4AF37 + 宝石蓝 #3A6A9E。卡片深蓝底 + 1px 金边。按钮金色实心。整体皇家、尊贵、庄重。",
    ],
    // ── 真实示例（已渲染截图）：米色极简 ──
    [
        'id'          => 'beige-minimal',
        'name-zh'     => '米色极简',
        'name-en'     => 'Beige Minimal',
        'desc-zh'     => '米白+深棕，极简温暖素净',
        'desc-en'     => '米白+深棕，极简温暖素净',
        'tags'        => ["beige","minimal","neutral","warm","米色","极简","温暖","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/beige-minimal.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/beige-minimal.html',
        'prompt'      => "采用米色极简（Beige Minimal）设计。背景米白 #F5F0E8。主色深棕 #3A2D1B。卡片白底 + 1px 米边。按钮深棕实心。整体极简、温暖、素净。",
    ],
    // ── 真实示例（已渲染截图）：霓虹街机 ──
    [
        'id'          => 'neon-arcade',
        'name-zh'     => '霓虹街机',
        'name-en'     => 'Neon Arcade',
        'desc-zh'     => '深紫+霓虹绿+电光粉+发光，街机电子游戏',
        'desc-en'     => '深紫+霓虹绿+电光粉+发光，街机电子游戏',
        'tags'        => ["neon","arcade","game","glow","霓虹","街机","游戏","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/neon-arcade.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/neon-arcade.html',
        'prompt'      => "采用霓虹街机（Neon Arcade）设计。背景深紫 #1A0B2E。主色霓虹绿 #39FF14 + 电光粉 #FF00FF。卡片深底 + 1px 霓虹边 + 发光。整体街机、霓虹、电子游戏。",
    ],
    // ── 真实示例（已渲染截图）：柔和蓝调 ──
    [
        'id'          => 'soft-blue',
        'name-zh'     => '柔和蓝调',
        'name-en'     => 'Soft Blue',
        'desc-zh'     => '浅蓝白+天蓝，柔和清爽宁静',
        'desc-en'     => '浅蓝白+天蓝，柔和清爽宁静',
        'tags'        => ["soft","blue","sky","calm","柔和","蓝调","清爽","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/soft-blue.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/soft-blue.html',
        'prompt'      => "采用柔和蓝调（Soft Blue）设计。背景浅蓝白 #F0F7FF。主色天蓝 #5AA8E8。卡片白底 + 1px 浅蓝边。按钮天蓝实心。整体柔和、清爽、宁静。",
    ],
    // ── 真实示例（已渲染截图）：金色奢华 ──
    [
        'id'          => 'gold-luxury',
        'name-zh'     => '金色奢华',
        'name-en'     => 'Gold Luxury',
        'desc-zh'     => '乳金白+黄金+深棕，奢华尊贵闪光',
        'desc-en'     => '乳金白+黄金+深棕，奢华尊贵闪光',
        'tags'        => ["gold","luxury","shiny","elegant","金色","奢华","闪光","desktop"],
        'cat'         => 'desktop',
        'thumb-image' => 'list/images/gold-luxury.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/gold-luxury.html',
        'prompt'      => "采用金色奢华（Gold Luxury）设计。背景乳金白 #FFF8E8。主色黄金 #D4AF37 + 深棕。卡片金边 + 金色渐变。按钮黄金实心。整体奢华、尊贵、闪光。",
    ],
    // ── 真实示例（已渲染截图）：咖啡摩卡 ──
    [
        'id'          => 'cafe-mocha',
        'name-zh'     => '咖啡摩卡',
        'name-en'     => 'Cafe Mocha',
        'desc-zh'     => '暖棕白+摩卡+焦糖，温暖醇香休闲',
        'desc-en'     => '暖棕白+摩卡+焦糖，温暖醇香休闲',
        'tags'        => ["cafe","mocha","coffee","warm","咖啡","摩卡","休闲","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/cafe-mocha.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/cafe-mocha.html',
        'prompt'      => "采用咖啡摩卡（Cafe Mocha）设计。背景暖棕白 #F5EFE6。主色摩卡 #6B4A2B + 焦糖 #B07030。卡片白底 + 1px 棕边。按钮摩卡实心。整体温暖、醇香、休闲。",
    ],
    // ── 真实示例（已渲染截图）：全息幻彩 ──
    [
        'id'          => 'holographic',
        'name-zh'     => '全息幻彩',
        'name-en'     => 'Holographic',
        'desc-zh'     => '浅彩虹白+幻彩渐变+炫彩边框，未来科技',
        'desc-en'     => '浅彩虹白+幻彩渐变+炫彩边框，未来科技',
        'tags'        => ["holographic","rainbow","gradient","future","全息","幻彩","未来","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/holographic.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/holographic.html',
        'prompt'      => "采用全息幻彩（Holographic）设计。背景浅彩虹白。主色全息幻彩渐变（粉→紫→青→绿）。卡片白底 + 幻彩渐变边框。按钮幻彩渐变实心。整体未来、炫彩、科技。",
    ],
    // ── 真实示例（已渲染截图）：粉色垃圾 ──
    [
        'id'          => 'pink-grunge',
        'name-zh'     => '粉色垃圾',
        'name-en'     => 'Pink Grunge',
        'desc-zh'     => '浅粉+品红+破坏感，叛逆摇滚个性',
        'desc-en'     => '浅粉+品红+破坏感，叛逆摇滚个性',
        'tags'        => ["grunge","pink","rock","punk","垃圾","品红","叛逆","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/pink-grunge.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/pink-grunge.html',
        'prompt'      => "采用粉色垃圾（Pink Grunge）设计。背景浅粉。主色品红 #D41B8C + 深灰。卡片白底 + 2px 品红边 + 破坏感。整体叛逆、摇滚、个性。",
    ],
    // ── 真实示例（已渲染截图）：午夜 ──
    [
        'id'          => 'midnight',
        'name-zh'     => '午夜',
        'name-en'     => 'Midnight',
        'desc-zh'     => '暗夜蓝+月光白+星尘，静谧深沉星空',
        'desc-en'     => '暗夜蓝+月光白+星尘，静谧深沉星空',
        'tags'        => ["midnight","night","star","moon","午夜","暗夜","星空","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/midnight.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/midnight.html',
        'prompt'      => "采用午夜（Midnight）设计。背景暗夜蓝 #0A0A1E。主色月光白 #E8E8F0 + 星尘 #8888B0。卡片暗底 + 1px 白边。按钮月光白实心。整体静谧、深沉、星空。",
    ],
    // ── 真实示例（已渲染截图）：花砖庭院 ──
    [
        'id'          => 'terrace-tile',
        'name-zh'     => '花砖庭院',
        'name-en'     => 'Terrace Tile',
        'desc-zh'     => '米白+苔绿+陶土+青花，地中海园艺清新',
        'desc-en'     => '米白+苔绿+陶土+青花，地中海园艺清新',
        'tags'        => ["terrace","tile","mediterranean","garden","庭院","花砖","地中海","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/terrace-tile.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/terrace-tile.html',
        'prompt'      => "采用花砖庭院（Terrace Tile）设计。背景米白。主色苔绿 #3A8A5C + 陶土 #B07030 + 青花 #3A6A8A。卡片白底 + 几何花砖边框。整体地中海、园艺、清新。",
    ],

    // real: 陈年红酒
    [
        'id'          => 'vintage-wine',
        'name-zh'     => '陈年红酒',
        'name-en'     => 'Vintage Wine',
        'desc-zh'     => '采用陈年红酒（Vintage Wine）设计。背景浅酒红。主色勃艮第红 #6B1A3C + 金褐。卡片白底 + 1px 酒红边。按钮酒红实心。整体陈年、醇厚、经典。',
        'desc-en'     => '采用陈年红酒（Vintage Wine）设计。背景浅酒红。主色勃艮第红 #6B1A3C + 金褐。卡片白底 + 1px 酒红边。按钮酒红实心。整体陈年、醇厚、经典。',
        'tags'        => ["vintagewine","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/vintage-wine.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/vintage-wine.html',
        'prompt'      => '采用陈年红酒（Vintage Wine）设计。背景浅酒红。主色勃艮第红 #6B1A3C + 金褐。卡片白底 + 1px 酒红边。按钮酒红实心。整体陈年、醇厚、经典。',
    ],
    // real: 赛博黄
    [
        'id'          => 'cyber-yellow',
        'name-zh'     => '赛博黄',
        'name-en'     => 'Cyber Yellow',
        'desc-zh'     => '采用赛博黄（Cyber Yellow）设计。背景暗黑黄 #1A1A0B。主色霓虹黄 #FFD400 + 黑色。卡片暗底 + 1px 黄边 + 发光。按钮霓虹黄实心。整体赛博、高对比、警示。',
        'desc-en'     => '采用赛博黄（Cyber Yellow）设计。背景暗黑黄 #1A1A0B。主色霓虹黄 #FFD400 + 黑色。卡片暗底 + 1px 黄边 + 发光。按钮霓虹黄实心。整体赛博、高对比、警示。',
        'tags'        => ["cyberyellow","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/cyber-yellow.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/cyber-yellow.html',
        'prompt'      => '采用赛博黄（Cyber Yellow）设计。背景暗黑黄 #1A1A0B。主色霓虹黄 #FFD400 + 黑色。卡片暗底 + 1px 黄边 + 发光。按钮霓虹黄实心。整体赛博、高对比、警示。',
    ],
    // real: 天空蓝
    [
        'id'          => 'sky-blue',
        'name-zh'     => '天空蓝',
        'name-en'     => 'Sky Blue',
        'desc-zh'     => '采用天空蓝（Sky Blue）设计。背景天蓝白 #F0F8FF。主色天空蓝 #5A9AE8。卡片白底 + 1px 蓝边。按钮天蓝实心。整体开阔、自由、清爽。',
        'desc-en'     => '采用天空蓝（Sky Blue）设计。背景天蓝白 #F0F8FF。主色天空蓝 #5A9AE8。卡片白底 + 1px 蓝边。按钮天蓝实心。整体开阔、自由、清爽。',
        'tags'        => ["skyblue","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/sky-blue.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/sky-blue.html',
        'prompt'      => '采用天空蓝（Sky Blue）设计。背景天蓝白 #F0F8FF。主色天空蓝 #5A9AE8。卡片白底 + 1px 蓝边。按钮天蓝实心。整体开阔、自由、清爽。',
    ],
    // real: 霜冻薄荷
    [
        'id'          => 'frost-mint',
        'name-zh'     => '霜冻薄荷',
        'name-en'     => 'Frost Mint',
        'desc-zh'     => '采用霜冻薄荷（Frost Mint）设计。背景薄荷白 #F0FFF8。主色薄荷绿 #38C8A0 + 冰蓝。卡片白底 + 1px 薄荷边。按钮薄荷实心。整体清凉、冰爽、薄荷。',
        'desc-en'     => '采用霜冻薄荷（Frost Mint）设计。背景薄荷白 #F0FFF8。主色薄荷绿 #38C8A0 + 冰蓝。卡片白底 + 1px 薄荷边。按钮薄荷实心。整体清凉、冰爽、薄荷。',
        'tags'        => ["frostmint","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/frost-mint.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/frost-mint.html',
        'prompt'      => '采用霜冻薄荷（Frost Mint）设计。背景薄荷白 #F0FFF8。主色薄荷绿 #38C8A0 + 冰蓝。卡片白底 + 1px 薄荷边。按钮薄荷实心。整体清凉、冰爽、薄荷。',
    ],
    // real: 暗夜玫瑰
    [
        'id'          => 'dark-rose',
        'name-zh'     => '暗夜玫瑰',
        'name-en'     => 'Dark Rose',
        'desc-zh'     => '采用暗夜玫瑰（Dark Rose）设计。背景暗黑红 #1A0B0B。主色玫瑰红 #C0392B + 暗金。卡片暗底 + 1px 玫瑰红边。整体暗黑、浪漫、玫瑰。',
        'desc-en'     => '采用暗夜玫瑰（Dark Rose）设计。背景暗黑红 #1A0B0B。主色玫瑰红 #C0392B + 暗金。卡片暗底 + 1px 玫瑰红边。整体暗黑、浪漫、玫瑰。',
        'tags'        => ["darkrose","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/dark-rose.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/dark-rose.html',
        'prompt'      => '采用暗夜玫瑰（Dark Rose）设计。背景暗黑红 #1A0B0B。主色玫瑰红 #C0392B + 暗金。卡片暗底 + 1px 玫瑰红边。整体暗黑、浪漫、玫瑰。',
    ],
    // real: 洁净白
    [
        'id'          => 'clean-white',
        'name-zh'     => '洁净白',
        'name-en'     => 'Clean White',
        'desc-zh'     => '采用洁净白（Clean White）设计。背景纯白。主色纯黑。卡片白底 + 极浅灰边。按钮纯黑实心。整体极致干净、无杂质、医疗级。',
        'desc-en'     => '采用洁净白（Clean White）设计。背景纯白。主色纯黑。卡片白底 + 极浅灰边。按钮纯黑实心。整体极致干净、无杂质、医疗级。',
        'tags'        => ["cleanwhite","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/clean-white.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/clean-white.html',
        'prompt'      => '采用洁净白（Clean White）设计。背景纯白。主色纯黑。卡片白底 + 极浅灰边。按钮纯黑实心。整体极致干净、无杂质、医疗级。',
    ],
    // real: 紫丁香
    [
        'id'          => 'lilac-spring',
        'name-zh'     => '紫丁香',
        'name-en'     => 'Lilac Spring',
        'desc-zh'     => '采用紫丁香（Lilac Spring）设计。背景淡紫白 #F8F5FF。主色丁香紫 #9B7BC8。卡片白底 + 1px 紫边。按钮丁香紫实心。整体春日、紫罗兰、浪漫。',
        'desc-en'     => '采用紫丁香（Lilac Spring）设计。背景淡紫白 #F8F5FF。主色丁香紫 #9B7BC8。卡片白底 + 1px 紫边。按钮丁香紫实心。整体春日、紫罗兰、浪漫。',
        'tags'        => ["lilacspring","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/lilac-spring.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/lilac-spring.html',
        'prompt'      => '采用紫丁香（Lilac Spring）设计。背景淡紫白 #F8F5FF。主色丁香紫 #9B7BC8。卡片白底 + 1px 紫边。按钮丁香紫实心。整体春日、紫罗兰、浪漫。',
    ],
    // real: 蜂巢
    [
        'id'          => 'honeycomb',
        'name-zh'     => '蜂巢',
        'name-en'     => 'Honeycomb',
        'desc-zh'     => '采用蜂巢（Honeycomb）设计。背景暖黄白 #FFF8F0。主色蜂蜜黄 #E8A020 + 深棕。卡片白底 + 六边形装饰。按钮蜂蜜黄实心。整体温暖、自然、甘甜。',
        'desc-en'     => '采用蜂巢（Honeycomb）设计。背景暖黄白 #FFF8F0。主色蜂蜜黄 #E8A020 + 深棕。卡片白底 + 六边形装饰。按钮蜂蜜黄实心。整体温暖、自然、甘甜。',
        'tags'        => ["honeycomb","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/honeycomb.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/honeycomb.html',
        'prompt'      => '采用蜂巢（Honeycomb）设计。背景暖黄白 #FFF8F0。主色蜂蜜黄 #E8A020 + 深棕。卡片白底 + 六边形装饰。按钮蜂蜜黄实心。整体温暖、自然、甘甜。',
    ],
    // real: 极简灰
    [
        'id'          => 'minimal-gray',
        'name-zh'     => '极简灰',
        'name-en'     => 'Minimal Gray',
        'desc-zh'     => '采用极简灰（Minimal Gray）设计。背景浅灰 #F5F5F5。主色中灰 #2D2D2D。卡片白底 + 1px 灰边。按钮中灰实心。整体克制、中性、冷静。',
        'desc-en'     => '采用极简灰（Minimal Gray）设计。背景浅灰 #F5F5F5。主色中灰 #2D2D2D。卡片白底 + 1px 灰边。按钮中灰实心。整体克制、中性、冷静。',
        'tags'        => ["minimalgray","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/minimal-gray.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/minimal-gray.html',
        'prompt'      => '采用极简灰（Minimal Gray）设计。背景浅灰 #F5F5F5。主色中灰 #2D2D2D。卡片白底 + 1px 灰边。按钮中灰实心。整体克制、中性、冷静。',
    ],
    // real: 赛博橙
    [
        'id'          => 'cyber-orange',
        'name-zh'     => '赛博橙',
        'name-en'     => 'Cyber Orange',
        'desc-zh'     => '采用赛博橙（Cyber Orange）设计。背景暗黑橙 #1A0B00。主色霓虹橙 #FF8C42 + 黑色。卡片暗底 + 1px 橙边 + 发光。按钮霓虹橙实心。整体赛博、热烈、能量。',
        'desc-en'     => '采用赛博橙（Cyber Orange）设计。背景暗黑橙 #1A0B00。主色霓虹橙 #FF8C42 + 黑色。卡片暗底 + 1px 橙边 + 发光。按钮霓虹橙实心。整体赛博、热烈、能量。',
        'tags'        => ["cyberorange","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/cyber-orange.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/cyber-orange.html',
        'prompt'      => '采用赛博橙（Cyber Orange）设计。背景暗黑橙 #1A0B00。主色霓虹橙 #FF8C42 + 黑色。卡片暗底 + 1px 橙边 + 发光。按钮霓虹橙实心。整体赛博、热烈、能量。',
    ],
    // real: 珍珠白
    [
        'id'          => 'pearl-white',
        'name-zh'     => '珍珠白',
        'name-en'     => 'Pearl White',
        'desc-zh'     => '采用珍珠白（Pearl White）设计。背景珍珠白 #FFFFF5。主色珍珠粉 #F0E0D0 + 米灰。卡片白底 + 珠光渐变。按钮珍珠粉实心。整体柔和、光泽、优雅。',
        'desc-en'     => '采用珍珠白（Pearl White）设计。背景珍珠白 #FFFFF5。主色珍珠粉 #F0E0D0 + 米灰。卡片白底 + 珠光渐变。按钮珍珠粉实心。整体柔和、光泽、优雅。',
        'tags'        => ["pearlwhite","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/pearl-white.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/pearl-white.html',
        'prompt'      => '采用珍珠白（Pearl White）设计。背景珍珠白 #FFFFF5。主色珍珠粉 #F0E0D0 + 米灰。卡片白底 + 珠光渐变。按钮珍珠粉实心。整体柔和、光泽、优雅。',
    ],
    // real: 抹茶
    [
        'id'          => 'green-tea',
        'name-zh'     => '抹茶',
        'name-en'     => 'Green Tea',
        'desc-zh'     => '采用抹茶（Green Tea）设计。背景抹茶白 #F5F8F0。主色抹茶绿 #6A9A4A。卡片白底 + 1px 抹茶绿边。按钮抹茶绿实心。整体清新、茶道、自然。',
        'desc-en'     => '采用抹茶（Green Tea）设计。背景抹茶白 #F5F8F0。主色抹茶绿 #6A9A4A。卡片白底 + 1px 抹茶绿边。按钮抹茶绿实心。整体清新、茶道、自然。',
        'tags'        => ["greentea","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/green-tea.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/green-tea.html',
        'prompt'      => '采用抹茶（Green Tea）设计。背景抹茶白 #F5F8F0。主色抹茶绿 #6A9A4A。卡片白底 + 1px 抹茶绿边。按钮抹茶绿实心。整体清新、茶道、自然。',
    ],
    // real: 暗珊瑚
    [
        'id'          => 'dark-coral',
        'name-zh'     => '暗珊瑚',
        'name-en'     => 'Dark Coral',
        'desc-zh'     => '采用暗珊瑚（Dark Coral）设计。背景暗黑珊瑚 #1A0A0A。主色珊瑚红 #FF6B6B + 暗金。卡片暗底 + 1px 珊瑚红边。整体暗黑、珊瑚、海洋。',
        'desc-en'     => '采用暗珊瑚（Dark Coral）设计。背景暗黑珊瑚 #1A0A0A。主色珊瑚红 #FF6B6B + 暗金。卡片暗底 + 1px 珊瑚红边。整体暗黑、珊瑚、海洋。',
        'tags'        => ["darkcoral","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/dark-coral.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/dark-coral.html',
        'prompt'      => '采用暗珊瑚（Dark Coral）设计。背景暗黑珊瑚 #1A0A0A。主色珊瑚红 #FF6B6B + 暗金。卡片暗底 + 1px 珊瑚红边。整体暗黑、珊瑚、海洋。',
    ],
    // real: 雪白
    [
        'id'          => 'snow-white',
        'name-zh'     => '雪白',
        'name-en'     => 'Snow White',
        'desc-zh'     => '采用雪白（Snow White）设计。背景雪白 #FAFAFF。主色深蓝 #1A1A2E。卡片白底 + 1px 浅蓝边。按钮深蓝实心。整体纯净、冰雪、清冷。',
        'desc-en'     => '采用雪白（Snow White）设计。背景雪白 #FAFAFF。主色深蓝 #1A1A2E。卡片白底 + 1px 浅蓝边。按钮深蓝实心。整体纯净、冰雪、清冷。',
        'tags'        => ["snowwhite","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/snow-white.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/snow-white.html',
        'prompt'      => '采用雪白（Snow White）设计。背景雪白 #FAFAFF。主色深蓝 #1A1A2E。卡片白底 + 1px 浅蓝边。按钮深蓝实心。整体纯净、冰雪、清冷。',
    ],
    // real: 暖沙
    [
        'id'          => 'warm-sand',
        'name-zh'     => '暖沙',
        'name-en'     => 'Warm Sand',
        'desc-zh'     => '采用暖沙（Warm Sand）设计。背景沙白 #F5F0E8。主色沙棕 #C4A67A。卡片白底 + 1px 沙色边。按钮沙棕实心。整体沙漠、温暖、自然。',
        'desc-en'     => '采用暖沙（Warm Sand）设计。背景沙白 #F5F0E8。主色沙棕 #C4A67A。卡片白底 + 1px 沙色边。按钮沙棕实心。整体沙漠、温暖、自然。',
        'tags'        => ["warmsand","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/warm-sand.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/warm-sand.html',
        'prompt'      => '采用暖沙（Warm Sand）设计。背景沙白 #F5F0E8。主色沙棕 #C4A67A。卡片白底 + 1px 沙色边。按钮沙棕实心。整体沙漠、温暖、自然。',
    ],
    // real: 夜空
    [
        'id'          => 'night-sky',
        'name-zh'     => '夜空',
        'name-en'     => 'Night Sky',
        'desc-zh'     => '采用夜空（Night Sky）设计。背景暗夜蓝 #0A0A1E。主色星光 #D0D0F0 + 银河紫。卡片暗底 + 1px 星光边。整体深邃、银河、夜空。',
        'desc-en'     => '采用夜空（Night Sky）设计。背景暗夜蓝 #0A0A1E。主色星光 #D0D0F0 + 银河紫。卡片暗底 + 1px 星光边。整体深邃、银河、夜空。',
        'tags'        => ["nightsky","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/night-sky.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/night-sky.html',
        'prompt'      => '采用夜空（Night Sky）设计。背景暗夜蓝 #0A0A1E。主色星光 #D0D0F0 + 银河紫。卡片暗底 + 1px 星光边。整体深邃、银河、夜空。',
    ],
    // real: 香槟
    [
        'id'          => 'champagne',
        'name-zh'     => '香槟',
        'name-en'     => 'Champagne',
        'desc-zh'     => '采用香槟（Champagne）设计。背景香槟金 #FFF8F0。主色香槟黄 #D4B88A + 金棕。卡片白底 + 1px 香槟边。整体庆祝、优雅、气泡。',
        'desc-en'     => '采用香槟（Champagne）设计。背景香槟金 #FFF8F0。主色香槟黄 #D4B88A + 金棕。卡片白底 + 1px 香槟边。整体庆祝、优雅、气泡。',
        'tags'        => ["champagne","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/champagne.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/champagne.html',
        'prompt'      => '采用香槟（Champagne）设计。背景香槟金 #FFF8F0。主色香槟黄 #D4B88A + 金棕。卡片白底 + 1px 香槟边。整体庆祝、优雅、气泡。',
    ],
    // real: 暗丝绒
    [
        'id'          => 'dark-velvet',
        'name-zh'     => '暗丝绒',
        'name-en'     => 'Dark Velvet',
        'desc-zh'     => '采用暗丝绒（Dark Velvet）设计。背景暗紫黑 #1A0B1A。主色玫红 #C0396B + 暗金。卡片暗底 + 1px 玫红边。整体丝绒、奢华、暗夜。',
        'desc-en'     => '采用暗丝绒（Dark Velvet）设计。背景暗紫黑 #1A0B1A。主色玫红 #C0396B + 暗金。卡片暗底 + 1px 玫红边。整体丝绒、奢华、暗夜。',
        'tags'        => ["darkvelvet","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/dark-velvet.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/dark-velvet.html',
        'prompt'      => '采用暗丝绒（Dark Velvet）设计。背景暗紫黑 #1A0B1A。主色玫红 #C0396B + 暗金。卡片暗底 + 1px 玫红边。整体丝绒、奢华、暗夜。',
    ],
    // real: 海洋蓝
    [
        'id'          => 'ocean-blue',
        'name-zh'     => '海洋蓝',
        'name-en'     => 'Ocean Blue',
        'desc-zh'     => '采用海洋蓝（Ocean Blue）设计。背景海洋白 #F0F8FF。主色海洋蓝 #2A6A9E。卡片白底 + 1px 蓝边。按钮海洋蓝实心。整体海洋、广阔、深邃。',
        'desc-en'     => '采用海洋蓝（Ocean Blue）设计。背景海洋白 #F0F8FF。主色海洋蓝 #2A6A9E。卡片白底 + 1px 蓝边。按钮海洋蓝实心。整体海洋、广阔、深邃。',
        'tags'        => ["oceanblue","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/ocean-blue.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/ocean-blue.html',
        'prompt'      => '采用海洋蓝（Ocean Blue）设计。背景海洋白 #F0F8FF。主色海洋蓝 #2A6A9E。卡片白底 + 1px 蓝边。按钮海洋蓝实心。整体海洋、广阔、深邃。',
    ],
    // real: 尘埃玫瑰
    [
        'id'          => 'dusty-rose',
        'name-zh'     => '尘埃玫瑰',
        'name-en'     => 'Dusty Rose',
        'desc-zh'     => '采用尘埃玫瑰（Dusty Rose）设计。背景浅灰粉 #F8F0F0。主色尘埃玫瑰 #B06070 + 灰粉。卡片白底 + 1px 灰粉边。整体复古、玫瑰、尘埃。',
        'desc-en'     => '采用尘埃玫瑰（Dusty Rose）设计。背景浅灰粉 #F8F0F0。主色尘埃玫瑰 #B06070 + 灰粉。卡片白底 + 1px 灰粉边。整体复古、玫瑰、尘埃。',
        'tags'        => ["dustyrose","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/dusty-rose.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/dusty-rose.html',
        'prompt'      => '采用尘埃玫瑰（Dusty Rose）设计。背景浅灰粉 #F8F0F0。主色尘埃玫瑰 #B06070 + 灰粉。卡片白底 + 1px 灰粉边。整体复古、玫瑰、尘埃。',
    ],
    // real: 赛博蓝
    [
        'id'          => 'cyber-blue',
        'name-zh'     => '赛博蓝',
        'name-en'     => 'Cyber Blue',
        'desc-zh'     => '采用赛博蓝（Cyber Blue）设计。背景暗蓝黑 #0B0B1A。主色霓虹蓝 #38B8FF + 黑色。卡片暗底 + 1px 蓝边 + 发光。按钮霓虹蓝实心。整体赛博、科技、冷静。',
        'desc-en'     => '采用赛博蓝（Cyber Blue）设计。背景暗蓝黑 #0B0B1A。主色霓虹蓝 #38B8FF + 黑色。卡片暗底 + 1px 蓝边 + 发光。按钮霓虹蓝实心。整体赛博、科技、冷静。',
        'tags'        => ["cyberblue","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/cyber-blue.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/cyber-blue.html',
        'prompt'      => '采用赛博蓝（Cyber Blue）设计。背景暗蓝黑 #0B0B1A。主色霓虹蓝 #38B8FF + 黑色。卡片暗底 + 1px 蓝边 + 发光。按钮霓虹蓝实心。整体赛博、科技、冷静。',
    ],
    // real: 肉桂
    [
        'id'          => 'cinnamon',
        'name-zh'     => '肉桂',
        'name-en'     => 'Cinnamon',
        'desc-zh'     => '采用肉桂（Cinnamon）设计。背景暖白 #F8F0E8。主色肉桂红 #B05030 + 深棕。卡片白底 + 1px 肉桂边。按钮肉桂红实心。整体温暖、香料、烘焙。',
        'desc-en'     => '采用肉桂（Cinnamon）设计。背景暖白 #F8F0E8。主色肉桂红 #B05030 + 深棕。卡片白底 + 1px 肉桂边。按钮肉桂红实心。整体温暖、香料、烘焙。',
        'tags'        => ["cinnamon","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/cinnamon.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/cinnamon.html',
        'prompt'      => '采用肉桂（Cinnamon）设计。背景暖白 #F8F0E8。主色肉桂红 #B05030 + 深棕。卡片白底 + 1px 肉桂边。按钮肉桂红实心。整体温暖、香料、烘焙。',
    ],
    // real: 暗蓝宝石
    [
        'id'          => 'dark-sapphire',
        'name-zh'     => '暗蓝宝石',
        'name-en'     => 'Dark Sapphire',
        'desc-zh'     => '采用暗蓝宝石（Dark Sapphire）设计。背景暗蓝 #0B0B1A。主色蓝宝石 #2A4A9E + 银白。卡片暗底 + 1px 蓝宝石边。整体宝石、深邃、珍贵。',
        'desc-en'     => '采用暗蓝宝石（Dark Sapphire）设计。背景暗蓝 #0B0B1A。主色蓝宝石 #2A4A9E + 银白。卡片暗底 + 1px 蓝宝石边。整体宝石、深邃、珍贵。',
        'tags'        => ["darksapphire","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/dark-sapphire.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/dark-sapphire.html',
        'prompt'      => '采用暗蓝宝石（Dark Sapphire）设计。背景暗蓝 #0B0B1A。主色蓝宝石 #2A4A9E + 银白。卡片暗底 + 1px 蓝宝石边。整体宝石、深邃、珍贵。',
    ],
    // real: 薄荷奶油
    [
        'id'          => 'mint-cream',
        'name-zh'     => '薄荷奶油',
        'name-en'     => 'Mint Cream',
        'desc-zh'     => '采用薄荷奶油（Mint Cream）设计。背景薄荷白 #F5FFF8。主色奶油绿 #6AA88A。卡片白底 + 1px 薄荷边。按钮奶油绿实心。整体清爽、奶油、柔和。',
        'desc-en'     => '采用薄荷奶油（Mint Cream）设计。背景薄荷白 #F5FFF8。主色奶油绿 #6AA88A。卡片白底 + 1px 薄荷边。按钮奶油绿实心。整体清爽、奶油、柔和。',
        'tags'        => ["mintcream","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/mint-cream.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/mint-cream.html',
        'prompt'      => '采用薄荷奶油（Mint Cream）设计。背景薄荷白 #F5FFF8。主色奶油绿 #6AA88A。卡片白底 + 1px 薄荷边。按钮奶油绿实心。整体清爽、奶油、柔和。',
    ],
    // real: 黑曜石
    [
        'id'          => 'dark-obsidian',
        'name-zh'     => '黑曜石',
        'name-en'     => 'Dark Obsidian',
        'desc-zh'     => '采用黑曜石（Dark Obsidian）设计。背景纯黑 #0A0A0A。主色银灰 #C0C0C0。卡片黑底 + 1px 灰边。按钮银灰实心。整体岩石、坚硬、沉稳。',
        'desc-en'     => '采用黑曜石（Dark Obsidian）设计。背景纯黑 #0A0A0A。主色银灰 #C0C0C0。卡片黑底 + 1px 灰边。按钮银灰实心。整体岩石、坚硬、沉稳。',
        'tags'        => ["darkobsidian","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/dark-obsidian.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/dark-obsidian.html',
        'prompt'      => '采用黑曜石（Dark Obsidian）设计。背景纯黑 #0A0A0A。主色银灰 #C0C0C0。卡片黑底 + 1px 灰边。按钮银灰实心。整体岩石、坚硬、沉稳。',
    ],
    // real: 百合白
    [
        'id'          => 'lily-white',
        'name-zh'     => '百合白',
        'name-en'     => 'Lily White',
        'desc-zh'     => '采用百合白（Lily White）设计。背景百合白 #FFFFF8。主色浅绿 #6A8A5A。卡片白底 + 1px 浅绿边。整体洁白、百合、纯洁。',
        'desc-en'     => '采用百合白（Lily White）设计。背景百合白 #FFFFF8。主色浅绿 #6A8A5A。卡片白底 + 1px 浅绿边。整体洁白、百合、纯洁。',
        'tags'        => ["lilywhite","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/lily-white.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/lily-white.html',
        'prompt'      => '采用百合白（Lily White）设计。背景百合白 #FFFFF8。主色浅绿 #6A8A5A。卡片白底 + 1px 浅绿边。整体洁白、百合、纯洁。',
    ],
    // real: 赛博红
    [
        'id'          => 'cyber-red',
        'name-zh'     => '赛博红',
        'name-en'     => 'Cyber Red',
        'desc-zh'     => '采用赛博红（Cyber Red）设计。背景暗红黑 #1A0B0B。主色霓虹红 #FF2E2E + 黑色。卡片暗底 + 1px 红边 + 发光。按钮霓虹红实心。整体赛博、危险、激情。',
        'desc-en'     => '采用赛博红（Cyber Red）设计。背景暗红黑 #1A0B0B。主色霓虹红 #FF2E2E + 黑色。卡片暗底 + 1px 红边 + 发光。按钮霓虹红实心。整体赛博、危险、激情。',
        'tags'        => ["cyberred","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/cyber-red.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/cyber-red.html',
        'prompt'      => '采用赛博红（Cyber Red）设计。背景暗红黑 #1A0B0B。主色霓虹红 #FF2E2E + 黑色。卡片暗底 + 1px 红边 + 发光。按钮霓虹红实心。整体赛博、危险、激情。',
    ],
    // real: 暖奶油
    [
        'id'          => 'warm-cream',
        'name-zh'     => '暖奶油',
        'name-en'     => 'Warm Cream',
        'desc-zh'     => '采用暖奶油（Warm Cream）设计。背景奶油白 #FFFDF8。主色奶油黄 #E8D0A0。卡片白底 + 1px 奶油边。整体温暖、奶油、柔和。',
        'desc-en'     => '采用暖奶油（Warm Cream）设计。背景奶油白 #FFFDF8。主色奶油黄 #E8D0A0。卡片白底 + 1px 奶油边。整体温暖、奶油、柔和。',
        'tags'        => ["warmcream","style","web"],
        'cat'         => 'web',
        'thumb-image' => 'list/images/warm-cream.png',
        'thumb'       => 'linear-gradient(135deg, #1A1A1A 0%, #666 100%)',
        'thumb-dark'  => false,
        'file'        => 'list/warm-cream.html',
        'prompt'      => '采用暖奶油（Warm Cream）设计。背景奶油白 #FFFDF8。主色奶油黄 #E8D0A0。卡片白底 + 1px 奶油边。整体温暖、奶油、柔和。',
    ],

];
$totalAll = count($EXAMPLES);
error_log($logTag . ' dataset total=' . $totalAll);

// ── 关键词检索（模糊匹配 多字段） ──
$search = mb_strtolower($query);
$filtered = [];
if ($search === '') {
    $filtered = $EXAMPLES;
} else {
    foreach ($EXAMPLES as $ex) {
        $catLabel = $CAT_META[$ex['cat']]['zh'] ?? '';
        $haystack = mb_strtolower(
            ($ex['name-zh']    ?? '') . ' ' .
            ($ex['name-en']    ?? '') . ' ' .
            ($ex['desc-zh']    ?? '') . ' ' .
            ($ex['desc-en']    ?? '') . ' ' .
            implode(' ', $ex['tags'] ?? []) . ' ' .
            $catLabel
        );
        if (mb_strpos($haystack, $search) !== false) {
            $filtered[] = $ex;
        }
    }
}
error_log($logTag . ' search q=' . $query . ' hits=' . count($filtered));

// ── 分页 ──
$total      = count($filtered);
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) { $page = $totalPages; }
$offset   = ($page - 1) * $perPage;
$pageItems = array_slice($filtered, $offset, $perPage);

// ── 工具：构造带搜索词的分页 URL ──
function example_url($q, $p) {
    $params = [];
    if ($q !== '') $params['q'] = $q;
    if ($p > 1)    $params['p'] = $p;
    return '?' . http_build_query($params);
}

// ── 国际化翻译键（集中维护，便于一次性落表） ──
$I18N = [
    'page-title'        => ['示例 — StyleCool',                                  'Examples — StyleCool'],
    'header-title'      => ['StyleCool',                                          'StyleCool'],
    'header-badge'      => ['设计样式示例',                                      'Style Examples'],
    'header-subtitle'   => ['在线预览设计样式效果，每个示例可一键复制完整提示词', 'Preview design styles online; copy the full prompt with one click'],
    'back-link'         => ['← 返回 StyleCool',                                  '← Back to StyleCool'],
    'lang-btn'          => ['English',                                            '中文'],
    'hero-badge'        => ['设计样式画廊',                                      'Design Gallery'],
    'hero-title'        => ['样式示例',                                          'Style Examples'],
    'hero-desc'         => ['从 18 种风格示例中找灵感。点击进入可一键复制生成该风格所需的完整提示词。', 'Browse 18 style examples. Click one to copy the full prompt that reproduces it.'],
    'search-placeholder'=> ['输入关键词检索，例如 glass、卡片、渐变…',          'Type a keyword, e.g. glass, card, gradient…'],
    'search-btn'        => ['搜索',                                               'Search'],
    'count-format'      => ['共 %d 个示例',                                      '%d examples'],
    'count-filtered'    => ['"%s" 匹配 %d / %d 个',                              '"%s" matches %d of %d'],
    'empty-title'       => ['没有匹配的样式',                                    'No matching styles'],
    'empty-desc'        => ['试试更宽泛的关键词，或清空搜索查看全部示例。',     'Try a broader keyword or clear the search to see all examples.'],
    'empty-clear'       => ['清空搜索',                                           'Clear search'],
    'prev-page'         => ['← 上一页',                                           '← Prev'],
    'next-page'         => ['下一页 →',                                           'Next →'],
    'page-info'         => ['第 %d / %d 页',                                      'Page %d / %d'],
    'tags-label'        => ['标签',                                               'Tags'],
    'cat-label'         => ['分类',                                               'Category'],
    'visit-example'     => ['查看示例',                                           'View example'],
    'footer-text'       => ['© 2025-2026 函数库 | Powered by Mutantcat',          '© 2025-2026 Function Library | Powered by Mutantcat'],
    'friend-links'      => ['友情链接：',                                         'Friends:'],
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>样式示例 — StyleCool</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="canonical" href="https://stylecool.mutantcat.org/example">
    <link rel="stylesheet" href="assets/style.css?v=20260610">
    <link rel="icon" type="image/png" href="assets/logo.png">
    <link rel="apple-touch-icon" href="assets/logo.png">
    <script src="assets/i18n.js?v=20260610"></script>
    <style>
    /* ============================================================
       示例页专用样式 — 液态玻璃（Liquid Glass）主题
       ------------------------------------------------------------
       设计语言四要素：
       1. 高斯模糊背景：backdrop-filter: blur(40px) saturate(180%)
       2. 半透明叠加：rgba(255,255,255, 0.5~0.7) 营造玻璃质感
       3. 流动渐变光晕：背景色随时间缓慢漂移
       4. 折射边缘：圆角 + 1px 白色半透明边框 + 多层投影
       ============================================================ */

    /* —— 液态玻璃颜色变量 —— */
    :root {
        --liquid-1: #6B46C1;
        --liquid-2: #1B4E7A;
        --liquid-3: #F472B6;
        --liquid-4: #38BDF8;
        --glass-bg:     rgba(255, 255, 255, 0.55);
        --glass-bg-2:   rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.5);
        --glass-shadow: 0 8px 32px rgba(31, 38, 135, 0.08);
    }

    /* —— 页面背景：流动渐变 —— */
    body {
        background: linear-gradient(135deg,
            #f5f3ff 0%,      /* 薰衣草白 */
            #ffffff 25%,     /* 纯白 */
            #fff5f7 50%,     /* 樱花粉 */
            #f0f9ff 75%,     /* 天蓝白 */
            #f5f3ff 100%);
        background-size: 400% 400%;
        animation: liquid-bg 30s ease-in-out infinite;
    }
    @keyframes liquid-bg {
        0%, 100% { background-position: 0% 50%; }
        50%      { background-position: 100% 50%; }
    }

    /* —— 主容器 —— */
    .example-main {
        padding: clamp(32px, 5vw, 64px) clamp(20px, 4vw, 32px) clamp(48px, 6vw, 80px);
        max-width: 1200px;
        margin: 0 auto;
    }

    /* —— 标题区 —— */
    .example-intro {
        text-align: center;
        padding: var(--s-8) 0 var(--s-10);
    }
    .example-intro .kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 14px;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--liquid-1);
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 999px;
        margin-bottom: var(--s-3);
    }
    .example-intro .kicker::before {
        content: "";
        width: 6px; height: 6px;
        background: var(--liquid-3);
        border-radius: 50%;
    }
    .example-intro h2 {
        font-size: clamp(2rem, 4vw, 2.75rem);
        font-weight: 800;
        letter-spacing: -0.025em;
        margin: var(--s-3) 0;
        background: linear-gradient(135deg, var(--liquid-1) 0%, var(--liquid-2) 50%, var(--liquid-3) 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        color: transparent;
    }
    .example-intro p {
        max-width: 560px;
        margin: 0 auto;
        color: var(--ink-2);
        line-height: 1.7;
        font-size: 1.0625rem;
    }

    /* —— 搜索表单 —— */
    .example-search-form {
        display: flex;
        gap: var(--s-2);
        max-width: 560px;
        margin: 0 auto var(--s-6);
        padding: 8px;
        background: var(--glass-bg);
        backdrop-filter: blur(40px) saturate(180%);
        -webkit-backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: 999px;
        box-shadow: var(--glass-shadow);
    }
    .example-search-input {
        flex: 1;
        min-width: 0;
        padding: 12px 20px;
        font-size: var(--fs-base);
        color: var(--ink);
        background: transparent;
        border: 0;
        border-radius: 999px;
        outline: none;
        font-family: inherit;
    }
    .example-search-input::placeholder { color: var(--ink-3); }
    .example-search-input:focus {
        background: rgba(255, 255, 255, 0.4);
    }
    .example-search-btn {
        padding: 12px 28px;
        font-size: var(--fs-sm);
        font-weight: 600;
        color: #fff;
        background: linear-gradient(135deg, var(--liquid-1), var(--liquid-2));
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 999px;
        cursor: pointer;
        transition: all 0.3s var(--ease, cubic-bezier(0.22, 1, 0.36, 1));
        white-space: nowrap;
        font-family: inherit;
        box-shadow: 0 4px 16px rgba(107, 70, 193, 0.3);
    }
    .example-search-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(107, 70, 193, 0.45);
    }

    /* —— 结果计数 —— */
    .example-meta {
        text-align: center;
        color: var(--ink-3);
        font-size: var(--fs-sm);
        margin-bottom: var(--s-6);
        font-variant-numeric: tabular-nums;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(20px);
        border-radius: 999px;
        display: inline-block;
        position: relative;
        left: 50%;
        transform: translateX(-50%);
    }

    /* —— 卡片网格 —— */
    .example-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: var(--s-5);
        margin-bottom: var(--s-10);
    }

    /* —— 玻璃卡片 —— */
    .example-card {
        display: flex;
        flex-direction: column;
        background: linear-gradient(135deg,
            rgba(255, 255, 255, 0.65),
            rgba(255, 255, 255, 0.4));
        backdrop-filter: blur(40px) saturate(180%);
        -webkit-backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        box-shadow: var(--glass-shadow);
        transition: transform 0.5s var(--ease, cubic-bezier(0.22, 1, 0.36, 1)),
                    box-shadow 0.5s var(--ease, cubic-bezier(0.22, 1, 0.36, 1));
        position: relative;
    }
    .example-card::before {
        /* 顶部反光线 */
        content: "";
        position: absolute;
        top: 0; left: 10%; right: 10%;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
        pointer-events: none;
    }
    .example-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 48px rgba(31, 38, 135, 0.15);
    }
    .example-card:focus-visible {
        outline: 2px solid var(--liquid-1);
        outline-offset: 2px;
    }

    /* —— 缩略图 —— */
    .example-thumb {
        position: relative;
        aspect-ratio: 16 / 9;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .example-thumb-image::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0) 60%, rgba(0, 0, 0, 0.05));
        pointer-events: none;
    }
    .example-thumb-label {
        font-size: var(--fs-lg);
        font-weight: 700;
        color: rgba(255, 255, 255, 0.94);
        letter-spacing: 0.05em;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.18);
        padding: 0 var(--s-4);
        text-align: center;
    }
    .example-thumb-label.dark {
        color: rgba(20, 58, 92, 0.85);
        text-shadow: none;
    }

    /* —— 卡片正文 —— */
    .example-body {
        padding: var(--s-4);
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .example-card-title {
        font-size: var(--fs-md);
        font-weight: 600;
        color: var(--ink);
        margin: 0 0 var(--s-1);
        letter-spacing: -0.005em;
    }
    .example-card-desc {
        font-size: var(--fs-sm);
        color: var(--ink-2);
        line-height: 1.5;
        margin: 0 0 var(--s-3);
        flex: 1;
    }
    .example-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .example-tag {
        font-size: var(--fs-xs);
        color: var(--ink-2);
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        padding: 2px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }

    /* —— 空状态 —— */
    .example-empty {
        text-align: center;
        padding: var(--s-16) var(--s-4);
        background: var(--glass-bg);
        backdrop-filter: blur(40px) saturate(180%);
        -webkit-backdrop-filter: blur(40px) saturate(180%);
        border: 1px dashed rgba(107, 70, 193, 0.3);
        border-radius: 16px;
        color: var(--ink-2);
        margin-bottom: var(--s-10);
        box-shadow: var(--glass-shadow);
        position: relative;
    }
    .example-empty::before {
        content: "";
        position: absolute;
        top: 0; left: 10%; right: 10%;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
    }
    .example-empty h3 {
        font-size: var(--fs-lg);
        font-weight: 600;
        color: var(--ink);
        margin: 0 0 var(--s-2);
    }
    .example-empty p {
        font-size: var(--fs-sm);
        margin: 0 0 var(--s-4);
    }
    .example-empty a {
        display: inline-block;
        padding: 10px 20px;
        font-size: var(--fs-sm);
        font-weight: 600;
        color: #fff;
        background: linear-gradient(135deg, var(--liquid-1), var(--liquid-2));
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 999px;
        text-decoration: none;
        transition: all 0.3s var(--ease, cubic-bezier(0.22, 1, 0.36, 1));
        box-shadow: 0 4px 16px rgba(107, 70, 193, 0.3);
    }
    .example-empty a:hover {
        transform: scale(1.05);
    }

    /* —— 分页 —— */
    .example-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: var(--s-3);
        padding: var(--s-6) 0;
        font-size: var(--fs-sm);
        flex-wrap: wrap;
    }
    .example-pagination a,
    .example-pagination .example-pagination-disabled {
        display: inline-flex;
        align-items: center;
        padding: 10px 20px;
        color: var(--ink-2);
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 999px;
        text-decoration: none;
        transition: all 0.3s var(--ease, cubic-bezier(0.22, 1, 0.36, 1));
        min-width: 100px;
        justify-content: center;
        font-weight: 500;
    }
    .example-pagination a:hover {
        color: #fff;
        background: linear-gradient(135deg, var(--liquid-1), var(--liquid-2));
        border-color: rgba(255, 255, 255, 0.3);
        transform: scale(1.05);
        box-shadow: 0 4px 16px rgba(107, 70, 193, 0.3);
    }
    .example-pagination .example-pagination-disabled {
        color: var(--ink-4);
        background: transparent;
        border-color: rgba(255, 255, 255, 0.3);
        cursor: not-allowed;
    }
    .example-pagination-info {
        color: var(--ink-2);
        font-variant-numeric: tabular-nums;
        padding: 10px 16px;
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(10px);
        border-radius: 999px;
        font-weight: 500;
    }

    /* —— 液态玻璃底部（自定义） —— */
    .liquid-footer {
        margin: 32px clamp(20px, 4vw, 32px) 16px;
        padding: 24px 32px;
        background: var(--glass-bg);
        backdrop-filter: blur(40px) saturate(180%);
        -webkit-backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        box-shadow: var(--glass-shadow);
        text-align: center;
        position: relative;
    }
    .liquid-footer::before {
        content: "";
        position: absolute;
        top: 0; left: 10%; right: 10%;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
        pointer-events: none;
    }
    .liquid-footer p {
        font-size: var(--fs-sm);
        color: var(--ink-2);
        margin: 0 0 8px;
    }
    .liquid-footer-links {
        font-size: var(--fs-xs);
        color: var(--ink-3);
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }
    .liquid-footer-links span { margin-right: 8px; }
    .liquid-footer-links a {
        color: var(--ink-3);
        text-decoration: none;
        margin: 0 8px;
        padding: 4px 10px;
        border-radius: 999px;
        transition: all 0.3s var(--ease, cubic-bezier(0.22, 1, 0.36, 1));
    }
    .liquid-footer-links a:hover {
        color: var(--liquid-1);
        background: rgba(107, 70, 193, 0.08);
    }

    /* —— 响应式 —— */
    @media (max-width: 768px) {
        header {
            padding: var(--s-3) 0;
        }
        header .container {
            flex-direction: column;
            gap: var(--s-3);
            text-align: center;
        }
        .example-grid { grid-template-columns: 1fr; }
        .example-search-form { flex-direction: column; border-radius: 16px; }
        .example-search-btn { width: 100%; }
        .liquid-footer { padding: 20px 16px; border-radius: 20px; }
        .liquid-footer-links a { margin: 4px; }
    }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>
                    <a href="https://stylecool.mutantcat.org/" style="color:inherit;text-decoration:none;">
                        <span data-i18n="example-header-title"><?=$I18N['header-title'][0]?></span>
                        <span style="font-weight:400;color:var(--ink-3);font-size:.75em;margin-left:.3em;" data-i18n="example-header-badge"><?=$I18N['header-badge'][0]?></span>
                    </a>
                </h1>
                <p data-i18n="example-header-subtitle"><?=$I18N['header-subtitle'][0]?></p>
            </div>
            <div class="language-switcher">
                <a href="https://stylecool.mutantcat.org/" class="home-link" aria-label="<?=$I18N['back-link'][0]?>" data-i18n="example-back-link"><?=$I18N['back-link'][0]?></a>
                <button id="example-lang-btn" type="button"><?=$I18N['lang-btn'][0]?></button>
            </div>
        </div>
    </header>

    <main>
        <div class="example-main">

            <!-- 标题区 -->
            <section class="example-intro reveal reveal-1">
                <div class="skill-hero-badge" data-i18n="example-hero-badge"><?=$I18N['hero-badge'][0]?></div>
                <h2 data-i18n="example-hero-title"><?=$I18N['hero-title'][0]?></h2>
                <p data-i18n="example-hero-desc"><?=$I18N['hero-desc'][0]?></p>
            </section>

            <!-- 检索表单 -->
            <form class="example-search-form reveal reveal-2" method="get" action="" role="search">
                <input
                    class="example-search-input"
                    type="text"
                    name="q"
                    value="<?=htmlspecialchars($query, ENT_QUOTES, 'UTF-8')?>"
                    placeholder="<?=$I18N['search-placeholder'][0]?>"
                    aria-label="<?=$I18N['search-placeholder'][0]?>"
                    data-i18n-placeholder="example-search-placeholder"
                >
                <button class="example-search-btn" type="submit" data-i18n="example-search-btn"><?=$I18N['search-btn'][0]?></button>
            </form>

            <!-- 结果计数 -->
            <div class="example-meta reveal reveal-3">
                <?php if ($query === ''): ?>
                    <span data-i18n="example-count-format" data-count="<?=$totalAll?>"><?=str_replace('%d', $totalAll, $I18N['count-format'][0])?></span>
                <?php else: ?>
                    <span data-i18n="example-count-filtered" data-q="<?=htmlspecialchars($query, ENT_QUOTES)?>" data-hits="<?=$total?>" data-total="<?=$totalAll?>"><?=sprintf($I18N['count-filtered'][0], htmlspecialchars($query, ENT_QUOTES), $total, $totalAll)?></span>
                <?php endif; ?>
            </div>

            <!-- 卡片网格 / 空状态 -->
            <?php if (empty($pageItems)): ?>
                <div class="example-empty reveal reveal-4">
                    <h3 data-i18n="example-empty-title"><?=$I18N['empty-title'][0]?></h3>
                    <p data-i18n="example-empty-desc"><?=$I18N['empty-desc'][0]?></p>
                    <a href="<?=example_url('', 1)?>" data-i18n="example-empty-clear"><?=$I18N['empty-clear'][0]?></a>
                </div>
            <?php else: ?>
                <div class="example-grid reveal reveal-4">
                    <?php foreach ($pageItems as $ex): ?>
                        <a class="example-card" href="<?=htmlspecialchars($EXAMPLE_ASSET_BASE . ltrim($ex['file'], '/'), ENT_QUOTES)?>" target="_blank" rel="noopener">
                            <div class="example-thumb<?php echo !empty($ex['thumb-image']) ? ' example-thumb-image' : ''; ?>"
                                <?php if (!empty($ex['thumb-image'])): ?>
                                    style="background-image: url('<?=htmlspecialchars($EXAMPLE_ASSET_BASE . ltrim($ex['thumb-image'], '/'), ENT_QUOTES)?>'); background-size: cover; background-position: center; background-color: <?=htmlspecialchars($ex['thumb'] ?? 'transparent', ENT_QUOTES)?>;"
                                <?php else: ?>
                                    style="background: <?=htmlspecialchars($ex['thumb'], ENT_QUOTES)?>;"
                                <?php endif; ?>
                            >
                                <?php if (empty($ex['thumb-image'])): ?>
                                    <span class="example-thumb-label <?=!empty($ex['thumb-dark']) ? 'dark' : ''?>">
                                        <?=htmlspecialchars($ex['name-zh'], ENT_QUOTES)?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="example-body">
                                <h3 class="example-card-title">
                                    <span class="name-zh"><?=htmlspecialchars($ex['name-zh'], ENT_QUOTES)?></span>
                                    <span class="name-en"><?=htmlspecialchars($ex['name-en'], ENT_QUOTES)?></span>
                                </h3>
                                <p class="example-card-desc">
                                    <span class="desc-zh"><?=htmlspecialchars($ex['desc-zh'], ENT_QUOTES)?></span>
                                    <span class="desc-en"><?=htmlspecialchars($ex['desc-en'], ENT_QUOTES)?></span>
                                </p>
                                <div class="example-tags">
                                    <?php foreach (array_slice($ex['tags'], 0, 4) as $tag): ?>
                                        <span class="example-tag">#<?=htmlspecialchars($tag, ENT_QUOTES)?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- 分页 -->
            <?php if ($total > 0 && $totalPages > 1): ?>
                <nav class="example-pagination reveal reveal-5" aria-label="分页">
                    <?php if ($page > 1): ?>
                        <a href="<?=htmlspecialchars(example_url($query, $page - 1), ENT_QUOTES)?>" data-i18n="example-prev-page"><?=$I18N['prev-page'][0]?></a>
                    <?php else: ?>
                        <span class="example-pagination-disabled" data-i18n="example-prev-page"><?=$I18N['prev-page'][0]?></span>
                    <?php endif; ?>

                    <span class="example-pagination-info" data-i18n="example-page-info" data-page="<?=$page?>" data-total="<?=$totalPages?>"><?=sprintf($I18N['page-info'][0], $page, $totalPages)?></span>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?=htmlspecialchars(example_url($query, $page + 1), ENT_QUOTES)?>" data-i18n="example-next-page"><?=$I18N['next-page'][0]?></a>
                    <?php else: ?>
                        <span class="example-pagination-disabled" data-i18n="example-next-page"><?=$I18N['next-page'][0]?></span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>

        </div>
    </main>

    <footer class="liquid-footer">
        <p data-i18n="example-footer-text"><?=$I18N['footer-text'][0]?></p>
        <div class="liquid-footer-links">
            <span data-i18n="example-friend-links"><?=$I18N['friend-links'][0]?></span>
            <a href="https://www.mutantcat.org/" target="_blank" rel="noopener">异猫工作群</a>
            <a href="https://www.fcnesyouxi.top/" target="_blank" rel="noopener">FC/NES游戏</a>
            <a href="https://www.jqshengtian.top/" target="_blank" rel="noopener">学习资料</a>
        </div>
    </footer>

    <script>
    // ── i18n 注入 ──
    // 将 PHP 端的 $I18N 翻译表注入到 i18n.js 期望的全局命名空间下
    // 这样 i18n.js 调用 t('key') 时即可拿到对应文案
    (function() {
        var I18N = <?php echo json_encode($I18N, JSON_UNESCAPED_UNICODE); ?>;
        // 兼容多种 i18n 工具的写法：window.I18N / window.__I18N__ / data-i18n
        window.I18N = I18N;
        window.__I18N__ = I18N;
        window.__STYLECOOL_EXAMPLE_I18N__ = I18N;

        // 简易翻译：扫描 [data-i18n]，按当前语言替换文本
        function applyI18n(lang) {
            var dict = window.I18N || {};
            document.querySelectorAll('[data-i18n]').forEach(function(el) {
                var key = el.getAttribute('data-i18n');
                if (!dict[key]) return;
                var val = dict[key][lang === 'en' ? 1 : 0];
                if (val !== undefined) el.textContent = val;
            });
            // 占位符
            document.querySelectorAll('[data-i18n-placeholder]').forEach(function(el) {
                var key = el.getAttribute('data-i18n-placeholder');
                if (!dict[key]) return;
                var val = dict[key][lang === 'en' ? 1 : 0];
                if (val !== undefined) el.setAttribute('placeholder', val);
            });
        }
        window.__applyI18n = applyI18n;
    })();

    // ── 初始化语言 ──
    document.addEventListener('DOMContentLoaded', function() {
        try {
            setTimeout(function() {
                var current = window.getCurrentLanguage ? window.getCurrentLanguage() : 'zh';
                if (window.setLanguage) window.setLanguage(current);
                if (window.__applyI18n) window.__applyI18n(current);
                var btn = document.getElementById('example-lang-btn');
                if (btn) {
                    btn.textContent = current === 'zh' ? 'English' : '中文';
                    btn.onclick = function() {
                        if (window.toggleLanguage) window.toggleLanguage();
                        var cur = window.getCurrentLanguage ? window.getCurrentLanguage() : 'zh';
                        if (window.__applyI18n) window.__applyI18n(cur);
                        btn.textContent = cur === 'zh' ? 'English' : '中文';
                    };
                }
            }, 100);
        } catch (e) {
            console.error('StyleCool example language init error:', e);
        }
    });
    </script>
</body>
</html>
