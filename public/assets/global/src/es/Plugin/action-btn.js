import $ from 'jquery'
import Plugin from 'Plugin'

const pluginName = 'actionBtn'
const defaults = {
  trigger: 'click', // click, hover
  toggleSelector: '.site-action-toggle',
  listSelector: '.site-action-buttons',
  activeClass: 'active',
  onShow() {},
  onHide() {}
}

class actionBtn {
  constructor(element, options) {
    this.element = element
    this.$element = $(element)

    this.options = $.extend({}, defaults, options, this.$element.data())

    this.init()
  }
  init() {
    this.showed = false

    this.$toggle = this.$element.find(this.options.toggleSelector)
    this.$list = this.$element.find(this.options.listSelector)

    const self = this

    if (this.options.trigger === 'hover') {
      this.$element.on('mouseenter', this.options.toggleSelector, () => {
        if (!self.showed) {
          self.show()
        }
      })
      this.$element.on('mouseleave', this.options.toggleSelector, () => {
        if (self.showed) {
          self.hide()
        }
      })
    } else {
      this.$element.on('click', this.options.toggleSelector, () => {
        if (self.showed) {
          self.hide()
        } else {
          self.show()
        }
      })
    }
  }

  show() {
    if (!this.showed) {
      this.$element.addClass(this.options.activeClass)
      this.showed = true

      this.options.onShow.call(this)
    }
  }
  hide() {
    if (this.showed) {
      this.$element.removeClass(this.options.activeClass)
      this.showed = false

      this.options.onHide.call(this)
    }
  }

  static _jQueryInterface(options, ...args) {
    if (typeof options === 'string') {
      const method = options

      if (/^\_/.test(method)) {
        return false
      } else if (/^(get)$/.test(method)) {
        const api = this.first().data(pluginName)
        if (api && typeof api[method] === 'function') {
          return api[method](...args)
        }
      } else {
        return this.each(function () {
          const api = $.data(this, pluginName)
          if (api && typeof api[method] === 'function') {
            api[method](...args)
          }
        })
      }
    } else {
      return this.each(function () {
        if (!$.data(this, pluginName)) {
          $.data(this, pluginName, new actionBtn(this, options))
        }
      })
    }
  }
}

$.fn[pluginName] = actionBtn._jQueryInterface
$.fn[pluginName].constructor = actionBtn
$.fn[pluginName].noConflict = () => {
  $.fn[pluginName] = window.JQUERY_NO_CONFLICT
  return actionBtn._jQueryInterface
}

class ActionBtn extends Plugin {
  getName() {
    return pluginName
  }

  static getDefaults() {
    return defaults
  }
}

Plugin.register(pluginName, ActionBtn)

export default ActionBtn
