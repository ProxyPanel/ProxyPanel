import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'selectable'

class Selectable extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      allSelector: '.selectable-all',
      itemSelector: '.selectable-item',
      rowSelector: 'tr',
      rowSelectable: false,
      rowActiveClass: 'active',
      onChange: null
    }
  }

  render() {
    if (!$.fn.asSelectable) {
      return
    }

    const $el = this.$el

    $el.asSelectable(this.options)
  }
}

Plugin.register(NAME, Selectable)

export default Selectable
