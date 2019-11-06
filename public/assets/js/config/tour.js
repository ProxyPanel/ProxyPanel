(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/config/tour", ["Config"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("Config"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.Config);
    global.configTour = mod.exports;
  }
})(this, function (_Config) {
  "use strict";

  (0, _Config.set)('tour', {
      steps: [{
          element: "#toggleMenubar",
          position: "right",
          intro: "侧翼菜单 <p class='content'>该按钮可以关闭/开启侧翼菜单，您可以在移动端获得更加完善的体验。</p>"
      }, {
          element: "#toggleFullscreen",
          position: "right",
          intro: "全屏 <p class='content'>界面太小了？试试我们的全屏按钮吧，全屏浏览本站！</p>"
      }, {
          element: "#toggleLanguage",
          position: 'left',
          intro: "语言切换 <p class='content'>本站主流语言为中文。但是我们还是对其他语言进行了非实时性的支持！</p>"
      }, {
          element: "#toggerUsermenu",
          position: 'left',
          intro: "账号设置 <p class='content'>您可以在这里找到账号的基础设置页面！</p>"
      }, {
          element: "#home",
          position: "right",
          intro: "个人主页 <p class='content'>在这里您可以看到【服务使用教程】，【您账号的基础信息】，【充值余额】，【公告栏】等信息</p>"
      }, {
          element: "#services",
          position: "right",
          intro: "购买服务 <p class='content'>在这里您可以购买本站的服务套餐，我们支持<strong>微信，支付宝，QQ在线支付</strong>，同时你也可以用之前充值的余额购买！</p>"
      }, {
          element: "#nodes",
          position: "right",
          intro: "我的线路 <p class='content'>在这里你可以完整的看到，您的账号可以使用的全部线路及其信息。</p>"
      }, {
          element: "#help",
          position: "right",
          intro: "帮助中心 <p class='content'>如果您在使用过程中存在问题，这里将是你需要来的第一站！我们在这里会不断的完善用户所遇到的问题库，并依依进行详细解答。</p>"
      }, {
          element: "#tickets",
          position: "right",
          intro: "人工服务 <p class='content'>在您发现帮助中心无法回答你的疑问时，你可以创建工单窗口，寻求客服人员帮助。使用前，记得关注一下客服的在线时间呦！</p>"
      }, {
          element: "#traffic",
          position: "right",
          intro: "流量记录 <p class='content'>在这里您可以查看您使用的流量信息</p>"
      }, {
          element: "#invoices",
          position: "right",
          intro: "我的账单 <p class='content'>在这里您可以查看历史购买记录</p>"
      }],
    skipLabel: '<i class=\'wb-close\'></i>',
    doneLabel: '<i class=\'wb-close\'></i>',
    nextLabel: '下一个 <i class=\'wb-chevron-right-mini\'></i>',
    prevLabel: '<i class=\'wb-chevron-left-mini\'></i>上一个',
    showBullets: true
  });
});