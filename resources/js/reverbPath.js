/**
 * Reverb 子路径（REVERB_PATH）的归一化。
 *
 * .env 里的 REVERB_PATH 是唯一需要手写的值，规范形式为「前导 / + 结尾不带 /」（如 /casting），
 * 留空表示 Reverb 直接挂在独立端口或子域上。VITE_REVERB_PATH / REVERB_SERVER_PATH 都由它派生，
 * 但三方需要的形式并不相同：
 *   - 浏览器（pusher-js 的 wsPath）：必须没有尾斜杠，否则会拼出 /casting//app/{key}；
 *   - 后端广播客户端（config/broadcasting.php）：必须带尾斜杠，见 app/helpers.php 的 reverb_client_path()；
 *   - Reverb 服务端（config/reverb.php）：前导斜杠、无尾斜杠。
 * 所以这里不直接使用原值，先归一化——写错斜杠只会被纠正，不会让 WebSocket 连不上。
 *
 * @param {string|null|undefined} raw
 * @returns {string} 规范形式，例如 "/casting"；不使用子路径时为空串
 */
export function normalizeReverbPath(raw) {
    const cleaned = String(raw ?? "")
        .trim()
        .replace(/^\/+|\/+$/g, "");

    return cleaned === "" ? "" : `/${cleaned}`;
}

export default normalizeReverbPath;
