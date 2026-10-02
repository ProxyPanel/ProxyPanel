/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from "axios";
import Echo from "laravel-echo";

import Pusher from "pusher-js";
import broadcastingManager from "./broadcastingManager";
import normalizeReverbPath from "./reverbPath";

window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT || 80,
    wssPort: import.meta.env.VITE_REVERB_PORT || 443,
    // 规范形式 /casting；pusher-js 会拼成 wsPath + '/app/{key}'，尾斜杠必须去掉
    wsPath: normalizeReverbPath(import.meta.env.VITE_REVERB_PATH),
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
});

window.broadcastingManager = broadcastingManager;
