import $ from 'jquery'

const pluginName = 'stickyHeader'

const defaults = {
  headerSelector: '.header',
  changeHeaderOn: 100,
  activeClassName: 'active-sticky-header',
  min: 50,
  method: 'toggle'
}

class stickyHeader {
  constructor(el, options) {
    this.isActive = false
    this.init(options)
    this.bind()
  }

  init(options) {
    const $el = this.$el.css('transition', 'none')

    const $header = this.$header = $el.find(options.headerSelector).css({
      position: 'absolute',
      top: 0,
      left: 0
    })

    this.options = $.extend(true, {}, defaults, options, $header.data())
    this.headerHeight = $header.outerHeight()
    // this.offsetTop()
    // $el.css('transition','all .5s linear');
    // $header.css('transition','all .5s linear');
    this.$el.css('paddingTop', this.headerHeight)
  }

  _toggleActive() {
    if (this.isActive) {
      this.$header.css('height', this.options.min)
    } else {
      this.$header.css('height', this.headerHeight)
    }
  }

  bind() {
    const self = this
    this.$el.on('scroll', function () {
      if (self.options.method === 'toggle') {
        if (
          $(this).scrollTop() > self.options.changeHeaderOn &&
          !self.isActive
        ) {
          self.$el.addClass(self.options.activeClassName)
          self.isActive = true
          self.$header.css('height', self.options.min)
          self.$el.trigger('toggle:sticky', [self, self.isActive])
        } else if (
          $(this).scrollTop() <= self.options.changeHeaderOn &&
          self.isActive
        ) {
          self.$el.removeClass(self.options.activeClassName)
          self.isActive = false
          self.$header.css('height', self.headerHeight)
          self.$el.trigger('toggle:sticky', [self, self.isActive])
        }
      } else if (self.options.method === 'scroll') {
        const offset = Math.max(
          self.headerHeight - $(this).scrollTop(),
          self.options.min
        )
        if (offset === self.headerHeight) {
          self.$el.removeClass(self.options.activeClassName)
        } else {
          self.$el.addClass(self.options.activeClassName)
        }
        self.$header.css('height', offset)
        self.$el.trigger('toggle:sticky', [self])
      }
    })
  }

  static _jQueryInterface(options, ...args) {
    if (typeof options === 'string') {
      const method = options
      if (/^\_/.test(method)) {
        return false
      }
      return this.each(function () {
        const api = $.data(this, pluginName)
        if (api && typeof api[method] === 'function') {
          api[method](...args)
        }
      })
    }
    return this.each(function () {
      if (!$.data(this, pluginName)) {
        $.data(this, pluginName, new stickyHeader(this, options))
      } else {
        $.data(this, pluginName).init(options)
      }
    })
  }
}

$.fn[pluginName] = stickyHeader._jQueryInterface
$.fn[pluginName].constructor = stickyHeader
$.fn[pluginName].noConflict = () => {
  $.fn[pluginName] = window.JQUERY_NO_CONFLICT
  return stickyHeader._jQueryInterface
}

export default stickyHeader
