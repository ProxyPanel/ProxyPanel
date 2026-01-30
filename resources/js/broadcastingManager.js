/**
 * Laravel Reverb 广播统一管理模块
 * 提供通用的 WebSocket 连接管理、频道订阅和事件处理功能
 */

class BroadcastingManager {
    constructor() {
        this.channels = new Map();
        this.pollingIntervals = new Map();
        this.errorDisplayed = false;
        this.connectionState = "unknown";
    }

    /**
     * 检查 Echo 是否可用
     * @returns {boolean}
     */
    isEchoAvailable() {
        return typeof Echo !== "undefined" && Echo !== null;
    }

    /**
     * 检查连接是否正常
     * @returns {boolean}
     */
    isConnected() {
        if (!this.isEchoAvailable()) return false;

        const conn = this.getConnection();
        if (!conn) return false;

        const state = conn.state?.current ?? conn.readyState;
        return state === "connected" || state === "open" || state === 1;
    }

    /**
     * 获取连接对象
     * @returns {Object|null}
     */
    getConnection() {
        if (!this.isEchoAvailable()) return null;
        return (
            Echo.connector?.pusher?.connection || Echo.connector?.socket || null
        );
    }

    /**
     * 显示错误信息
     * @param {string} message
     */
    handleError(message) {
        if (!this.errorDisplayed && !this.isConnected()) {
            if (typeof showMessage !== "undefined") {
                showMessage({
                    title: i18n("broadcast.error"),
                    message: message,
                    icon: "error",
                    showConfirmButton: true,
                });
            } else {
                console.error(message);
            }
            this.errorDisplayed = true;
        }
    }

    /**
     * 清除错误状态
     */
    clearError() {
        this.errorDisplayed = false;
    }

    /**
     * 订阅频道并监听事件
     * @param {string} channelName - 频道名称
     * @param {string} event - 事件名称
     * @param {Function} handler - 事件处理函数
     * @returns {boolean} 是否订阅成功
     */
    subscribe(channelName, event, handler) {
        // 清理同名频道（如果存在）- 确保彻底清除旧监听器
        this.unsubscribe(channelName);

        if (!this.isEchoAvailable()) {
            this.handleError(i18n("broadcast.websocket_unavailable"));
            return false;
        }

        try {
            // 创建新频道并监听事件
            const channel = Echo.channel(channelName);
            channel.listen(event, handler);
            this.channels.set(channelName, channel);

            // 绑定连接状态事件
            const conn = this.getConnection();
            if (conn?.bind) {
                conn.bind("connected", () => {
                    this.connectionState = "connected";
                    this.clearError();
                });
                conn.bind("disconnected", () => {
                    this.connectionState = "disconnected";
                    this.handleError(i18n("broadcast.websocket_disconnected"));
                });
            }

            return true;
        } catch (e) {
            if (!this.isConnected()) {
                this.handleError(
                    `${i18n("broadcast.setup_failed")}: ${e?.message || e}`,
                );
            }
            return false;
        }
    }

    /**
     * 取消订阅频道
     * @param {string} channelName
     */
    unsubscribe(channelName) {
        if (this.channels.has(channelName)) {
            try {
                // Laravel Echo 官方推荐方式：直接调用 Echo.leave()
                // 它会自动清除频道对象、所有监听器和内部缓存
                if (typeof Echo.leave === "function") {
                    Echo.leave(channelName);
                }
            } catch (e) {
                console.warn(`Failed to unsubscribe from ${channelName}:`, e);
            }

            this.channels.delete(channelName);
        }
    }

    /**
     * 处理 AJAX 请求的错误 - 统一错误处理逻辑
     * @param {string} title - 错误标题
     * @param {string} message - 错误消息
     */
    handleAjaxError(title = null, message = null) {
        if (!this.isConnected()) {
            this.handleError(i18n("broadcast.websocket_unavailable"));
        } else if (message || title) {
            if (typeof showMessage !== "undefined") {
                showMessage({
                    title: title || i18n("common.error"),
                    message: message,
                    icon: "error",
                    showConfirmButton: true,
                });
            } else {
                console.error(title, message);
            }
        }
    }

    /**
     * 生成频道名称
     * @param {string} type - 频道类型
     * @param {string|number} id - 资源 ID（可选）
     * @returns {string} 频道名称
     */
    getChannelName(type, id = null) {
        return id ? `${type}.${id}` : `${type}.all`;
    }

    /**
     * 清理所有频道
     */
    cleanup() {
        for (const channelName of this.channels.keys()) {
            this.unsubscribe(channelName);
        }
        this.channels.clear();
    }

    /**
     * 启动轮询降级机制
     * @param {string} intervalId - 轮询ID
     * @param {Function} pollFunction - 轮询函数
     * @param {number} interval - 轮询间隔（毫秒）
     */
    startPolling(intervalId, pollFunction, interval = 3000) {
        this.stopPolling(intervalId);
        const pollInterval = setInterval(pollFunction, interval);
        this.pollingIntervals.set(intervalId, pollInterval);
        return pollInterval;
    }

    /**
     * 停止轮询
     * @param {string} intervalId
     */
    stopPolling(intervalId) {
        if (this.pollingIntervals.has(intervalId)) {
            clearInterval(this.pollingIntervals.get(intervalId));
            this.pollingIntervals.delete(intervalId);
        }
    }

    /**
     * 停止所有轮询
     */
    stopAllPolling() {
        for (const intervalId of this.pollingIntervals.keys()) {
            this.stopPolling(intervalId);
        }
        this.pollingIntervals.clear();
    }

    /**
     * 断开 Echo 连接
     */
    disconnect() {
        try {
            if (this.isEchoAvailable()) {
                Echo.connector?.disconnect?.();
            }
        } catch (e) {
            console.error(i18n("broadcast.disconnect_failed"), e);
        }
    }

    /**
     * 等待连接建立
     * @param {number} timeout - 超时时间（毫秒）
     * @returns {Promise<boolean>}
     */
    waitForConnection(timeout = 5000) {
        return new Promise((resolve) => {
            if (this.isConnected()) {
                resolve(true);
                return;
            }

            const startTime = Date.now();
            const checkConnection = () => {
                if (this.isConnected()) {
                    resolve(true);
                } else if (Date.now() - startTime > timeout) {
                    resolve(false);
                } else {
                    setTimeout(checkConnection, 100);
                }
            };

            checkConnection();
        });
    }
}

// 导出单例
const broadcastingManager = new BroadcastingManager();
export default broadcastingManager;
