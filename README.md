<div align=center>
<img src="assets/logo.png" width="100"/>
<h2>StyleCool</h2>
<p>设计样式可集成接口 · 独立部署于 stylecool.mutantcat.org</p>
</div>

> 中文文档（默认） | [English](README_EN.md)

### 一、功能简述
- 从 FunctionCool 独立出来的自包含子站项目，可单独部署于 `stylecool.mutantcat.org`，文件夹自包含、无外部依赖。
- `/` 或 `/skillapi`：无参数时显示文档页；带 `?token=&q=&cat=` 时返回 JSON 查询结果（样式库搜索）。
- `/example`：样式示例画廊（100 个示例，带缩略图、关键词检索、分页、一键复制提示词）。
- 分类：`web` / `desktop` / `miniapp` / `mobile`，样式数据在对应 `*.json`（共 151 条）。
- 索引页与画廊页均内置中英文 i18n；样式规则由真实设计师人工编写，为 AI 注入联网的审美判断力。

### 二、部署方式
1. Apache：开启 `a2enmod rewrite`，站点根指向本项目，`.htaccess` 已配置 `/skillapi` 与 `/example` 路由。
2. 独立域名：将 `stylecool.mutantcat.org` 解析到本项目目录即可，页面 canonical 已指向该域。
3. Docker：可参考主项目 `FunctionCool/Dockerfile` 的 `php:8.3-apache` 方式构建。

### 三、使用教程
1. 直接访问 `/skillapi`（无参数）查看 HTML 文档页。
2. 调用样式搜索接口：
    ```
    https://stylecool.mutantcat.org/skillapi?token=mutantcat&q=button&cat=web
    ```
3. 访问 `/example` 浏览 100 个样式示例，支持 `?q=` 关键词检索与分页。

### 四、接口文档
1. 样式搜索 - `/skillapi`
   - 说明：通过 token 校验后，按关键词和分类返回样式库 JSON 结果。
   - 请求方式：GET（带 `token`、`q`、`cat` 三个参数时返回 JSON）
   - 请求参数：
     - `token`：永久密钥（默认 `mutantcat`）
     - `q`：搜索关键词，模糊匹配名称 / 描述 / 标签
     - `cat`：`web` / `desktop` / `miniapp` / `mobile` / `all`（未知值默认检索全部分类）
   - 返回示例：
     ```json
     {
         "results": [],
         "query": "button",
         "cat": "web",
         "count": 0
     }
     ```
2. 样式示例 - `/example`
   - 说明：样式示例画廊，每个示例含完整 HTML、缩略图与可直接复制的生成提示词。
   - 请求参数：
     - `q`：关键词（可选，模糊匹配名称 / 描述 / 标签 / 分类）
     - `p`：页码（可选，默认第 1 页）

### 五、专注的点
- 让 AI 与自动化工作流在动手写 CSS 之前获得人类设计师沉淀的“品味共识”，而不是生成“能用的 UI”。
- 按平台分类检索（网站 / 桌面 / 小程序 / 手机），每次只取最相关的设计规则，不打扰 AI 上下文。
- 样式数据独立维护，修改 JSON 即可热更新，无需改动前端代码。
- 只依赖 PHP 与 Apache 重写规则，文件夹自包含，可随时独立部署。

### 六、开发进度
- [X] 核心 API + 文档页（`/skillapi`）
- [X] token 校验（外置白名单优先，内置白名单兜底）
- [X] 样式示例画廊（`/example`，100 个示例）
- [X] 四类平台样式数据（web / desktop / miniapp / mobile）
- [X] 中英文 i18n 文档页与画廊
- [X] 独立部署重写规则（`.htaccess`）

### 七、目录结构
- `index.php` — 核心 API + 文档页（自带 token 校验）
- `example.php` — 样式示例画廊
- `web.json` / `desktop.json` / `miniapp.json` / `mobile.json` — 样式库数据
- `list/` — 100 个示例 HTML 与 `list/images/` 缩略图
- `assets/` — 本地化的 `style.css` / `i18n.js` / `logo.png`（已脱离主站依赖）
- `data/skill_token_permanent.json` — 永久密钥白名单（默认 `mutantcat`，可外置覆盖）
- `.htaccess` — 独立部署重写规则

### 八、密钥与安全
- 校验顺序：先读 `data/skill_token_permanent.json` 白名单，未命中时回退内置白名单 `['mutantcat']`。
- 注意：密钥写在公开仓库内即视为公开，如需保密请改造成环境变量方式。
