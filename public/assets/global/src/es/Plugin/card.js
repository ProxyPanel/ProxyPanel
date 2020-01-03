import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'card'

class Card extends Plugin {
  getName() {

  }

  static getDefaults() {
    return {}
  }

  render() {
    if (!$.fn.card) {
      return
    }

    const $el = this.$el

    const options = this.options

    if (options.target) {
      options.container = $(options.target)
    }
    $el.card(options)
  }
}

Plugin.register(NAME, Card)

export default Card
