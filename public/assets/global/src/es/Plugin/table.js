import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'tableSection'

class TableSection extends Plugin {
  getName() {
    return NAME
  }

  render() {
    this.$el.data('tableApi', this)
  }
  toggle(e) {
    const $el = this.$el
    if (
      e.target.type !== 'checkbox' &&
      e.target.type !== 'button' &&
      e.target.tagName.toLowerCase() !== 'a' &&
      !$(e.target).parent('div.checkbox-custom').length
    ) {
      if ($el.hasClass('active')) {
        $el.removeClass('active')
      } else {
        $el.siblings('.table-section').removeClass('active')
        $el.addClass('active')
      }
    }
  }
  static api() {
    let api = 'click|toggle'

    const touch = typeof document.ontouchstart !== 'undefined'

    if (touch) {
      api = 'touchstart|toggle'
    }
    return api
  }
}

Plugin.register(NAME, TableSection)

export default TableSection
