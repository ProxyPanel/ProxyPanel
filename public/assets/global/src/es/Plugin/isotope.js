import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'isotope'

class Isotope extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }

  render() {
    if (typeof $.fn.isotope === 'undefined') {
      return
    }

    const callback = () => {
      const $el = this.$el

      $el.isotope(this.options)
    }
    if (this !== document) {
      callback()
    } else {
      $(window).on('load', () => {
        callback()
      })
    }
  }
}

Plugin.register(NAME, Isotope)

export default Isotope
