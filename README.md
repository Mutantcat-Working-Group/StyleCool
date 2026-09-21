# StyleCool — 设计样式可集成接口

从 FunctionCool 独立出来的自包含子站项目,可单独部署于 `stylecool.mutantcat.org`。

## 功能
- `/` 或 `/skillapi`:无参数时显示文档页;带 `?token=&q=&cat=` 时返回 JSON 查询结果(样式库搜索)。
- `/example`:样式示例画廊(100 个示例,带缩略图、关键词检索、分页)。
- 分类:`web` / `desktop` / `miniapp` / `mobile`,数据在对应 `*.json`。

## 目录
- `index.php` — 核心 API + 文档页(自带 token 校验)
- `example.php` — 示例画廊
- `web.json` / `desktop.json` / `miniapp.json` / `mobile.json` — 样式库数据
- `list/` — 100 个示例 HTML 与 `list/images/` 缩略图
- `assets/` — 本地化的 `style.css` / `i18n.js` / `logo.png`(已脱离主站依赖)
- `data/skill_token_permanent.json` — 永久密钥白名单(默认 `mutantcat`,可外置覆盖)
- `.htaccess` — 独立部署重写规则

## 部署
- Apache:`a2enmod rewrite`,站点根指向本项目,`.htaccess` 已配置 `/skillapi` 与 `/example` 路由。
- 独立域名:`stylecool.mutantcat.org` 解析到本目录即可;页面 canonical 已指向该域。
- Docker:可参考主项目 `FunctionCool/Dockerfile` 的 `php:8.3-apache` 方式构建。

## 密钥
- 校验顺序:先读 `data/skill_token_permanent.json` 白名单,未命中时回退内置白名单 `['mutantcat']`。
- 注意:密钥写在公开仓库内即视为公开,如需保密请改造成环境变量方式。
