import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'masonry'

class Masonry extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      itemSelector: '.masonry-item'
    }
  }

  render() {
    if (typeof $.fn.masonry === 'undefined') {
      return
    }

    const $el = this.$el
    if ($.fn.imagesLoaded) {
      $el.imagesLoaded(function () {
        $el.masonry(this.options)
      })
    } else {
      $el.masonry(this.options)
    }
  }
}

Plugin.register(NAME, Masonry)

export default Masonry
