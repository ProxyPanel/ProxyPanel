import $ from 'jquery'

const pluginName = 'responsiveHorizontalTabs'

const defaults = {
  navSelector: '.nav-tabs',
  itemSelector: '.nav-item',
  dropdownSelector: '>.dropdown',
  dropdownItemSelector: '.dropdown-item',
  tabSelector: '.tab-pane',
  activeClassName: 'active'
}

class responsiveHorizontalTabs {
  constructor(el, options) {
    const $tabs = this.$tabs = $(el)
    this.options = options = $.extend(true, {}, defaults, options)

    const $nav = this.$nav = $tabs.find(this.options.navSelector)
    const $dropdown = this.$dropdown = $nav.find(
      this.options.dropdownSelector
    )
    const $items = this.$items = $nav
      .find(this.options.itemSelector)
      .filter(function () {
        return !$(this).is($dropdown)
      })

    this.$dropdownItems = $dropdown.find(this.options.dropdownItemSelector)
    this.$tabPanel = this.$tabs.find(this.options.tabSelector)

    this.breakpoints = []

    $items.each(function () {
      $(this).data('width', $(this).width())
    })

    this.init()
    this.bind()
  }

  init() {
    if (this.$dropdown.length === 0) {
      return
    }

    this.$dropdown.show()
    this.breakpoints = []

    const length = this.length = this.$items.length
    const dropWidth = this.dropWidth = this.$dropdown.width()
    let total = 0

    this.flag = length

    if (length <= 1) {
      this.$dropdown.hide()
      return
    }

    for (var i = 0; i < length - 2; i++) {
      if (i === 0) {
        this.breakpoints.push(this.$items.eq(i).outerWidth() + dropWidth)
      } else {
        this.breakpoints.push(
          this.breakpoints[i - 1] + this.$items.eq(i).width()
        )
      }
    }

    for (i = 0; i < length; i++) {
      total += this.$items.eq(i).outerWidth()
    }
    this.breakpoints.push(total)

    this.layout()
  }

  layout() {
    if (this.breakpoints.length <= 0) {
      return
    }

    const width = this.$nav.width()
    let i = 0
    const activeClassName = this.options.activeClassName
    const active = this.$tabPanel.filter(`.${activeClassName}`).index()

    for (; i < this.breakpoints.length; i++) {
      if (this.breakpoints[i] > width) {
        break
      }
    }

    if (i === this.flag) {
      return
    }

    this.$items.children().removeClass(activeClassName)
    this.$dropdownItems.removeClass(activeClassName)
    this.$dropdown.children().removeClass(activeClassName)

    if (i === this.breakpoints.length) {
      this.$dropdown.hide()
      this.$items.show()
      this.$items
        .eq(active)
        .children()
        .addClass(activeClassName)
    } else {
      this.$dropdown.show()
      for (let j = 0; j < this.length; j++) {
        if (j < i) {
          this.$items.eq(j).show()
          this.$dropdownItems.eq(j).hide()
        } else {
          this.$items.eq(j).hide()
          this.$dropdownItems.eq(j).show()
        }
      }

      if (active < i) {
        this.$items
          .eq(active)
          .children()
          .addClass(activeClassName)
      } else {
        this.$dropdown.children().addClass(activeClassName)
        this.$dropdownItems.eq(active).addClass(activeClassName)
      }
    }

    this.flag = i
  }

  bind() {
    const self = this

    $(window).resize(() => {
      self.layout()
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
        $.data(this, pluginName, new responsiveHorizontalTabs(this, options))
      } else {
        $.data(this, pluginName).init()
      }
    })
  }
}

$.fn[pluginName] = responsiveHorizontalTabs._jQueryInterface
$.fn[pluginName].constructor = responsiveHorizontalTabs
$.fn[pluginName].noConflict = () => {
  $.fn[pluginName] = window.JQUERY_NO_CONFLICT
  return responsiveHorizontalTabs._jQueryInterface
}

export default responsiveHorizontalTabs
