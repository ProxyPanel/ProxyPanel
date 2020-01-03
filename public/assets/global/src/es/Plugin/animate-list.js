import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'animateList'

class AnimateList extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      child: '.panel',
      duration: 250,
      delay: 50,
      animate: 'scale-up',
      fill: 'backwards'
    }
  }

  render() {
    const $el = this.$el

    class animatedBox {
      constructor($el, opts) {
        this.options = opts
        this.$children = $el.find(opts.child)
        this.$children.addClass(`animation-${opts.animate}`)
        this.$children.css('animation-fill-mode', opts.fill)
        this.$children.css('animation-duration', `${opts.duration}ms`)

        let delay = 0

        const self = this

        this.$children.each(function () {
          $(this).css('animation-delay', `${delay}ms`)
          delay += self.options.delay
        })
      }

      run(type) {
        this.$children.removeClass(`animation-${this.options.animate}`)
        if (typeof type !== 'undefined') {
          this.options.animate = type
        }
        setTimeout(() => {
          this.$children.addClass(`animation-${this.options.animate}`)
        }, 0)
      }
    }

    $el.data('animateList', new animatedBox($el, this.options))
  }
}

Plugin.register(NAME, AnimateList)

export default AnimateList
