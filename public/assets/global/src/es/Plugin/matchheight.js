import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'matchHeight'

class MatchHeight extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }

  render() {
    if (typeof $.fn.matchHeight === 'undefined') {
      return
    }

    const $el = this.$el

    const matchSelector = $el.data('matchSelector')

    if (matchSelector) {
      $el.find(matchSelector).matchHeight(this.options)
    } else {
      $el.children().matchHeight(this.options)
    }
  }
}

Plugin.register(NAME, MatchHeight)

export default MatchHeight
