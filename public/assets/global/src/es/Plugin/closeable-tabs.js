import $ from 'jquery'

const pluginName = 'tabClose'
const dismiss = '[data-close="tab"]'

class TabClose {
  constructor(el) {
    $(el).on('click', dismiss, this.close)
  }

  close(e) {
    const $this = $(this)
    const $toggle = $this.closest('[data-toggle="tab"]')
    let selector = $toggle.data('target')
    const $li = $toggle.parent('li')

    if (!selector) {
      selector = $toggle.attr('href')
      selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '')
    }

    if ($toggle.hasClass('active')) {
      const $next = $li
        .siblings()
        .eq(0)
        .children('[data-toggle="tab"]')
      if ($next.length > 0) {
        const api = $next.tab().data('bs.tab')
        api.show()
      }
    }

    const $parent = $(selector)
    if (e) {
      e.preventDefault()
    }

    $parent.trigger(e = $.Event('close.bs.tab'))

    if (e.isDefaultPrevented()) {
      return
    }

    $parent.removeClass('in')

    function removeElement() {
      // detach from parent, fire event then clean up data
      $parent
        .detach()
        .trigger('closed.bs.tab')
        .remove()
      $li.detach().remove()
    }

    $.support.transition && $parent.hasClass('fade')
      ? $parent
        .one('bsTransitionEnd', removeElement)
        .emulateTransitionEnd(TabClose.TRANSITION_DURATION)
      : removeElement()
  }

  static _jQueryInterface(option) {
    console.log(option)
    return this.each(function () {
      const $this = $(this)
      let data = $this.data('bs.tab.close')

      if (!data) {
        $this.data('bs.tab.close', data = new TabClose(this))
      }
      if (typeof option === 'string') {
        data[option].call($this)
      }
    })
  }
}

TabClose.TRANSITION_DURATION = 150

$.fn[pluginName] = TabClose._jQueryInterface
$.fn[pluginName].Constructor = TabClose
$.fn[pluginName].noConflict = () => {
  $.fn[pluginName] = window.JQUERY_NO_CONFLICT
  return asSelectable._jQueryInterface
}

// TAB CLOSE DATA-API
// ==================

$(document).on(
  'click.bs.tab-close.data-api',
  dismiss,
  TabClose.prototype.close
)

export default TabClose
