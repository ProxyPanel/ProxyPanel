import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'moreButton'

class MoreButton extends Plugin {
  getName() {
    return NAME
  }

  render() {
    this.$target = $(this.options.more)
    this.$el.data('moreButtonApi', this)
  }

  toggle() {
    this.$target.toggle()
  }

  static getDefaults() {
    return {
      more: ''
    }
  }
  static api() {
    return 'click|toggle'
  }
}

Plugin.register(NAME, MoreButton)

export default MoreButton
