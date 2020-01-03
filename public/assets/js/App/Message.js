(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/App/Message", ["exports", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Site);
    global.AppMessage = mod.exports;
  }
})(this, function (_exports, _Site2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.run = run;
  _exports.getInstance = getInstance;
  _exports.default = _exports.AppMessage = void 0;
  _Site2 = babelHelpers.interopRequireDefault(_Site2);

  var ChatsWrap =
  /*#__PURE__*/
  function () {
    function ChatsWrap($el) {
      var _this = this;

      babelHelpers.classCallCheck(this, ChatsWrap);
      this.$el = $el;
      this.$historyBtn = $('#historyBtn');
      this.scrollChatsToBottom();
      $(window).on('resize', function () {
        _this.scrollChatsToBottom();
      });
    }

    babelHelpers.createClass(ChatsWrap, [{
      key: "scrollChatsToBottom",
      value: function scrollChatsToBottom() {
        var $el = this.$el;
        var chatsWrapH = $el.height();
        var chatsH = $('.chats', $el).outerHeight();
        var historyBtnH = this.$historyBtn.outerHeight();
        $el.scrollTop(chatsH + historyBtnH - chatsWrapH);
      }
    }]);
    return ChatsWrap;
  }();

  var AppMessage =
  /*#__PURE__*/
  function (_Site) {
    babelHelpers.inherits(AppMessage, _Site);

    function AppMessage() {
      babelHelpers.classCallCheck(this, AppMessage);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AppMessage).apply(this, arguments));
    }

    babelHelpers.createClass(AppMessage, [{
      key: "initialize",
      value: function initialize() {
        babelHelpers.get(babelHelpers.getPrototypeOf(AppMessage.prototype), "initialize", this).call(this);
        this.newChatLists = [];
        this.$chatsWrap = $('.app-message-chats');
        this.chatApi = new ChatsWrap(this.$chatsWrap);
        this.$textArea = $('.message-input textarea');
        this.$textareaWrap = $('.app-message-input');
        this.$msgEdit = $('.message-input>.form-control');
        this.$sendBtn = $('.message-input-btn'); // states

        this.states = {
          chatListsLength: 0
        };
      }
    }, {
      key: "process",
      value: function process() {
        babelHelpers.get(babelHelpers.getPrototypeOf(AppMessage.prototype), "process", this).call(this);
        this.steupMessage();
        this.setupTextarea();
      }
    }, {
      key: "chatListsLength",
      value: function chatListsLength(length) {
        if (this.newChatLists[length - 1]) {
          var $newMsg = $("<div class='chat-content'><p>".concat(this.newChatLists[length - 1], "</p></div>"));
          $('.chat').last().find('.chat-body').append($newMsg);
          this.$msgEdit.attr('placeholder', '');
          this.$msgEdit.val('');
        } else {
          this.$msgEdit.attr('placeholder', 'type text here...');
        }

        this.chatApi.scrollChatsToBottom();
        this.states.chatListsLength = length;
      }
    }, {
      key: "setupTextarea",
      value: function setupTextarea() {
        var _this2 = this;

        autosize($('.message-input textarea'));
        this.$textArea.on('autosize:resized', function () {
          _this2.$chatsWrap.css('height', "calc(100% - ".concat(_this2.$textareaWrap.outerHeight(), "px)"));

          _this2.triggerResize();
        });
      }
    }, {
      key: "steupMessage",
      value: function steupMessage() {
        var _this3 = this;

        this.$sendBtn.on('click', function () {
          var num = _this3.states.chatListsLength;

          _this3.newChatLists.push(_this3.getMsg());

          _this3.chatListsLength(++num);
        });
      }
    }, {
      key: "getMsg",
      value: function getMsg() {
        return this.$msgEdit.val();
      }
    }]);
    return AppMessage;
  }(_Site2.default);

  _exports.AppMessage = AppMessage;
  var instance = null;

  function getInstance() {
    if (!instance) {
      instance = new AppMessage();
    }

    return instance;
  }

  function run() {
    var app = getInstance();
    app.run();
  }

  var _default = AppMessage;
  _exports.default = _default;
});