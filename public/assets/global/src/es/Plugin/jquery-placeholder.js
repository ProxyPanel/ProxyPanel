import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'placeholder'

class Placeholder extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }

  render() {
    if (!$.fn.placeholder) {
      return
    }

    const $el = this.$el

    $el.placeholder()
  }
}

Plugin.register(NAME, Placeholder)

export default Placeholder
