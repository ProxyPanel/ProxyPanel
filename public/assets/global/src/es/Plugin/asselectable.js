import $ from 'jquery'

const pluginName = 'asSelectable'
const defaults = {
  allSelector: '.selectable-all',
  itemSelector: '.selectable-item',
  rowSelector: 'tr',
  rowSelectable: false,
  rowActiveClass: 'active',
  onChange: null
}

class asSelectable {
  constructor(element, options) {
    this.element = element
    this.$element = $(element)
    this.options = $.extend({}, defaults, options, this.$element.data())

    this.init()
  }

  init() {
    const self = this
    const options = this.options

    self.$element.on('change', options.allSelector, function () {
      const value = $(this).prop('checked')
      self.getItems().each(function () {
        const $one = $(this)
        $one.prop('checked', value).trigger('change', [true])
        self.selectRow($one, value)
      })
    })

    self.$element.on('click', options.itemSelector, function (e) {
      const $one = $(this)
      const value = $one.prop('checked')
      self.selectRow($one, value)
      e.stopPropagation()
    })

    self.$element.on('change', options.itemSelector, function () {
      const $all = self.$element.find(options.allSelector)
      const $row = self.getItems()
      const total = $row.length
      const checked = self.getSelected().length

      if (total === checked) {
        $all.prop('checked', true)
      } else {
        $all.prop('checked', false)
      }

      self._trigger('change', checked)

      if (typeof options.callback === 'function') {
        options.callback.call(this)
      }
    })

    if (options.rowSelectable) {
      self.$element.on('click', options.rowSelector, function (e) {
        if (
          e.target.type !== 'checkbox' &&
          e.target.type !== 'button' &&
          e.target.tagName.toLowerCase() !== 'a' &&
          !$(e.target).parent('div.checkbox-custom').length
        ) {
          const $checkbox = $(options.itemSelector, this)
          const value = $checkbox.prop('checked')
          $checkbox.prop('checked', !value)
          self.selectRow($checkbox, !value)
        }
      })
    }
  }

  selectRow(item, value) {
    if (value) {
      item
        .parents(this.options.rowSelector)
        .addClass(this.options.rowActiveClass)
    } else {
      item
        .parents(this.options.rowSelector)
        .removeClass(this.options.rowActiveClass)
    }
  }

  getItems() {
    return this.$element.find(this.options.itemSelector)
  }

  getSelected() {
    return this.getItems().filter(':checked')
  }

  _trigger(eventType) {
    const method_arguments = Array.prototype.slice.call(arguments, 1)
    const data = [this].concat(method_arguments)

    // event
    this.$element.trigger(`${pluginName}::${eventType}`, data)

    // callback
    eventType = eventType.replace(
      /\b\w+\b/g,
      (word) => word.substring(0, 1).toUpperCase() + word.substring(1)
    )
    const onFunction = `on${eventType}`
    if (typeof this.options[onFunction] === 'function') {
      this.options[onFunction].apply(this, method_arguments)
    }
  }

  static _jQueryInterface(options, ...args) {
    if (typeof options === 'string') {
      const method = options

      if (/^\_/.test(method)) {
        return false
      } else if (/^(get)/.test(method)) {
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
          $.data(this, pluginName, new asSelectable(this, options))
        }
      })
    }
  }
}

$.fn[pluginName] = asSelectable._jQueryInterface
$.fn[pluginName].constructor = asSelectable
$.fn[pluginName].noConflict = () => {
  $.fn[pluginName] = window.JQUERY_NO_CONFLICT
  return asSelectable._jQueryInterface
}

export default asSelectable
