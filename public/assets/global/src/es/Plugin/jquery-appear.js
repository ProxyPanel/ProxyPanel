import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'appear'

class Appear extends Plugin {
  getName() {
    return NAME
  }

  bind() {
    this.$el.on('appear', () => {
      if (this.$el.hasClass('appear-no-repeat')) {
        return
      }
      this.$el
        .removeClass('invisible')
        .addClass(`animation-${this.options.animate}`)

      if (this.$el.data('repeat') === false) {
        this.$el.addClass('appear-no-repeat')
      }
    })

    $(document).on('disappear', () => {
      if (this.$el.hasClass('appear-no-repeat')) {
        return
      }

      this.$el
        .addClass('invisible')
        .removeClass(`animation-${this.options.animate}`)
    })
  }

  render() {
    if (!$.fn.appear) {
      return
    }

    this.$el.appear(this.options)
    this.$el.not(':appeared').addClass('invisible')

    this.bind()
  }
}

Plugin.register(NAME, Appear)
export default Appear
