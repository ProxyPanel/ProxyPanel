import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'rating'

class Rating extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      targetKeep: true,
      icon: 'font',
      starType: 'i',
      starOff: 'icon wb-star',
      starOn: 'icon wb-star orange-600',
      cancelOff: 'icon wb-minus-circle',
      cancelOn: 'icon wb-minus-circle orange-600',
      starHalf: 'icon wb-star-half orange-500'
    }
  }

  render() {
    if (!$.fn.raty) {
      return
    }

    const $el = this.$el

    if (this.options.hints) {
      this.options.hints = this.options.hints.split(',')
    }

    $el.raty(this.options)
  }
}

Plugin.register(NAME, Rating)

export default Rating
